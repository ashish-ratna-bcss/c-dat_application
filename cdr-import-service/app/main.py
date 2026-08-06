from __future__ import annotations
import os
from typing import Literal, Optional
from fastapi import Body, Depends, FastAPI, File, Form, Header, HTTPException, Query, Request, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from .runner import DEFAULT_BATCH_SIZE, DocumentProcessingError, ensure_runtime_dirs, fetch_jobs, get_job_status, resume_background_job, save_upload, save_upload_stream, submit_background_document, submit_background_import, validate_upload
from .schemas import DocumentPreview, DocumentSubmitResponse, DocumentValidateResponse, HealthResponse, ImportSubmitResponse, ImportValidateResponse, JobListResponse, JobStatusResponse, ResumableUploadCompleteResponse, ResumableUploadInitRequest, ResumableUploadSessionResponse, StagingActionResponse, StagingRowsResponse, job_to_response
from document_processing.resumable_upload import ResumableUploadError, append_chunk, cancel_upload, complete_upload, get_upload_status, init_upload
from document_processing.verification import VerificationError, approve_staging_batch, fetch_staging_rows, reject_staging_batch
API_PREFIX = '/api/v1'
API_KEY = os.environ.get('CDR_API_KEY', '').strip()
# Production default: require a key. Opt out only with CDR_API_ALLOW_ANONYMOUS=1 (dev only).
ALLOW_ANONYMOUS = os.environ.get('CDR_API_ALLOW_ANONYMOUS', '').strip().lower() in ('1', 'true', 'yes')
ModuleName = Literal['cdr', 'sdr']

def verify_api_key(x_api_key: Optional[str] = Header(default=None, alias='X-API-Key')) -> None:
    if ALLOW_ANONYMOUS and not API_KEY:
        return
    if not API_KEY:
        raise HTTPException(
            status_code=503,
            detail='Document API is locked: set CDR_API_KEY (or CDR_API_ALLOW_ANONYMOUS=1 for local dev only).',
        )
    if not x_api_key or x_api_key != API_KEY:
        raise HTTPException(status_code=401, detail='Invalid or missing X-API-Key header')

def create_app() -> FastAPI:
    app = FastAPI(title='Document Processing Service', description='DB-driven document uploads for CDR (operator CSV) and SDR (MSSQL .bak) modules.', version='0.2.0')
    origins = os.environ.get('CDR_CORS_ORIGINS', '*').split(',')
    app.add_middleware(CORSMiddleware, allow_origins=origins, allow_credentials=True, allow_methods=['*'], allow_headers=['*'])

    @app.on_event('startup')
    def _startup() -> None:
        ensure_runtime_dirs()

    @app.get('/health', response_model=HealthResponse, tags=['system'])
    @app.get(f'{API_PREFIX}/health', response_model=HealthResponse, tags=['system'])
    def health() -> HealthResponse:
        return HealthResponse()

    @app.post(f'{API_PREFIX}/documents/validate', response_model=DocumentValidateResponse, tags=['documents'], dependencies=[Depends(verify_api_key)])
    async def validate_document(module: ModuleName=Form(..., description='Processing module: cdr or sdr'), file: UploadFile=File(...), operator: Optional[str]=Form(default=None, description='Operator override: airtel, bsnl, vi, jio')) -> DocumentValidateResponse:
        path = await save_upload_stream(file, module=module)
        try:
            preview = validate_upload(path, module, operator=operator)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return DocumentValidateResponse(message=preview.get('message') or f'Validated {preview.get('total_records', 0)} records.', preview=DocumentPreview(**preview))

    @app.post(f'{API_PREFIX}/documents', response_model=DocumentSubmitResponse, status_code=202, tags=['documents'], dependencies=[Depends(verify_api_key)])
    async def submit_document(module: ModuleName=Form(..., description='Processing module: cdr or sdr'), file: UploadFile=File(...), batch_size: int=Query(DEFAULT_BATCH_SIZE, ge=1, le=50000), dry_run: bool=Query(False, description='Validate only; do not restore/import'), operator: Optional[str]=Form(default=None, description='Operator override: airtel, bsnl, vi, jio')) -> DocumentSubmitResponse:
        path = await save_upload_stream(file, module=module)
        try:
            queued = submit_background_document(path, module=module, batch_size=batch_size, dry_run=dry_run, operator=operator)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        preview = DocumentPreview(module=queued.get('module', module), operator=queued.get('operator'), target_phone=queued.get('target_phone'), mssql_database=queued.get('mssql_database'), total_records=int(queued.get('total_records') or 0), warnings=queued.get('warnings', []), basename=queued['basename'], message=queued.get('message'))
        return DocumentSubmitResponse(job_id=queued.get('job_id'), module=preview.module, status=queued['status'], phase=queued.get('phase'), message=queued.get('message') or 'Document queued. Poll job status endpoint for progress.', preview=preview)

    @app.get(f'{API_PREFIX}/documents/{{job_id}}', response_model=JobStatusResponse, tags=['documents'], dependencies=[Depends(verify_api_key)])
    def get_document_job(job_id: int) -> JobStatusResponse:
        try:
            data = get_job_status(job_id)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return job_to_response(data)

    @app.post(f'{API_PREFIX}/documents/{{job_id}}/resume', response_model=JobStatusResponse, status_code=202, tags=['documents'], dependencies=[Depends(verify_api_key)])
    def resume_document_job(job_id: int) -> JobStatusResponse:
        try:
            data = resume_background_job(job_id)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return job_to_response(data)

    @app.get(f'{API_PREFIX}/documents', response_model=JobListResponse, tags=['documents'], dependencies=[Depends(verify_api_key)])
    def list_document_jobs(module: Optional[ModuleName]=Query(None), limit: int=Query(50, ge=1, le=200), offset: int=Query(0, ge=0)) -> JobListResponse:
        jobs = [job_to_response(row) for row in fetch_jobs(module=module, limit=limit, offset=offset)]
        return JobListResponse(jobs=jobs, count=len(jobs))

    @app.post(f'{API_PREFIX}/imports/validate', response_model=ImportValidateResponse, tags=['imports'], dependencies=[Depends(verify_api_key)])
    async def validate_import(file: UploadFile=File(...), operator: Optional[str]=Form(None)) -> ImportValidateResponse:
        return await validate_document(module='cdr', file=file, operator=operator)

    @app.post(f'{API_PREFIX}/imports', response_model=ImportSubmitResponse, status_code=202, tags=['imports'], dependencies=[Depends(verify_api_key)])
    async def submit_import(file: UploadFile=File(...), batch_size: int=Query(DEFAULT_BATCH_SIZE, ge=1, le=5000), operator: Optional[str]=Form(None)) -> ImportSubmitResponse:
        return await submit_document(module='cdr', file=file, batch_size=batch_size, dry_run=False, operator=operator)

    @app.get(f'{API_PREFIX}/imports/{{job_id}}', response_model=JobStatusResponse, tags=['imports'], dependencies=[Depends(verify_api_key)])
    def get_import_job(job_id: int) -> JobStatusResponse:
        return get_document_job(job_id)

    @app.post(f'{API_PREFIX}/imports/{{job_id}}/resume', response_model=JobStatusResponse, status_code=202, tags=['imports'], dependencies=[Depends(verify_api_key)])
    def resume_import_job(job_id: int) -> JobStatusResponse:
        return resume_document_job(job_id)

    @app.get(f'{API_PREFIX}/imports', response_model=JobListResponse, tags=['imports'], dependencies=[Depends(verify_api_key)])
    def list_import_jobs(limit: int=Query(50, ge=1, le=200), offset: int=Query(0, ge=0)) -> JobListResponse:
        return list_document_jobs(module='cdr', limit=limit, offset=offset)

    @app.post(f'{API_PREFIX}/uploads/sdr/init', response_model=ResumableUploadSessionResponse, tags=['resumable-uploads'], dependencies=[Depends(verify_api_key)])
    def init_sdr_upload(payload: ResumableUploadInitRequest) -> ResumableUploadSessionResponse:
        try:
            data = init_upload(
                filename=payload.filename,
                file_size=payload.file_size,
                file_key=payload.file_key,
                chunk_size=payload.chunk_size or 16 * 1024 * 1024,
            )
        except ResumableUploadError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return ResumableUploadSessionResponse(**data)

    @app.get(f'{API_PREFIX}/uploads/sdr/{{upload_id}}', response_model=ResumableUploadSessionResponse, tags=['resumable-uploads'], dependencies=[Depends(verify_api_key)])
    def sdr_upload_status(upload_id: str) -> ResumableUploadSessionResponse:
        try:
            data = get_upload_status(upload_id)
        except ResumableUploadError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return ResumableUploadSessionResponse(**data)

    @app.put(f'{API_PREFIX}/uploads/sdr/{{upload_id}}/chunk', response_model=ResumableUploadSessionResponse, tags=['resumable-uploads'], dependencies=[Depends(verify_api_key)])
    async def sdr_upload_chunk(
        upload_id: str,
        request: Request,
        offset: int = Header(..., alias='X-Upload-Offset'),
    ) -> ResumableUploadSessionResponse:
        data = await request.body()
        try:
            result = append_chunk(upload_id, offset, data)
        except ResumableUploadError as exc:
            status = 409 if 'Offset mismatch' in str(exc) else 400
            raise HTTPException(status_code=status, detail=str(exc)) from exc
        return ResumableUploadSessionResponse(**result)

    @app.post(f'{API_PREFIX}/uploads/sdr/{{upload_id}}/complete', response_model=ResumableUploadCompleteResponse, status_code=202, tags=['resumable-uploads'], dependencies=[Depends(verify_api_key)])
    def sdr_upload_complete(
        upload_id: str,
        batch_size: int = Query(DEFAULT_BATCH_SIZE, ge=1, le=50000),
    ) -> ResumableUploadCompleteResponse:
        try:
            final_path = complete_upload(upload_id)
            queued = submit_background_document(final_path, module='sdr', batch_size=batch_size, dry_run=False)
        except ResumableUploadError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return ResumableUploadCompleteResponse(
            upload_id=upload_id,
            job_id=queued.get('job_id'),
            module='sdr',
            status=queued.get('status', 'queued'),
            message=queued.get('message') or 'SDR backup queued for processing.',
            basename=final_path.name,
        )

    @app.delete(f'{API_PREFIX}/uploads/sdr/{{upload_id}}', tags=['resumable-uploads'], dependencies=[Depends(verify_api_key)])
    def sdr_upload_cancel(upload_id: str) -> JSONResponse:
        try:
            cancel_upload(upload_id)
        except ResumableUploadError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return JSONResponse({'ok': True, 'upload_id': upload_id, 'status': 'cancelled'})

    @app.get(f'{API_PREFIX}/documents/{{job_id}}/staging', response_model=StagingRowsResponse, tags=['verification'], dependencies=[Depends(verify_api_key)])
    def get_staging_rows(job_id: int, table_key: Optional[str]=Query(None), limit: int=Query(100, ge=1, le=500), offset: int=Query(0, ge=0)) -> StagingRowsResponse:
        try:
            data = fetch_staging_rows(job_id, table_key=table_key, limit=limit, offset=offset)
        except VerificationError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return StagingRowsResponse(**data)

    @app.post(f'{API_PREFIX}/documents/{{job_id}}/staging/approve', response_model=StagingActionResponse, tags=['verification'], dependencies=[Depends(verify_api_key)])
    def approve_staging(job_id: int, username: str=Query('api')) -> StagingActionResponse:
        try:
            data = approve_staging_batch(job_id, username=username)
        except VerificationError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return StagingActionResponse(**data)

    @app.post(f'{API_PREFIX}/documents/{{job_id}}/staging/reject', response_model=StagingActionResponse, tags=['verification'], dependencies=[Depends(verify_api_key)])
    def reject_staging(job_id: int, username: str=Query('api')) -> StagingActionResponse:
        try:
            data = reject_staging_batch(job_id, username=username)
        except VerificationError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return StagingActionResponse(**data)

    return app
app = create_app()

def run() -> None:
    import uvicorn
    host = os.environ.get('CDR_API_HOST', '127.0.0.1')
    port = int(os.environ.get('CDR_API_PORT', '8088'))
    uvicorn.run('app.main:app', host=host, port=port, reload=False)
if __name__ == '__main__':
    run()
