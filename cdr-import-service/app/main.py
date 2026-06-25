from __future__ import annotations
import os
from typing import Literal, Optional
from fastapi import Depends, FastAPI, File, Form, Header, HTTPException, Query, UploadFile
from fastapi.middleware.cors import CORSMiddleware
from .runner import DEFAULT_BATCH_SIZE, DocumentProcessingError, ensure_runtime_dirs, fetch_jobs, get_job_status, resume_background_job, save_upload, submit_background_document, submit_background_import, validate_upload
from .schemas import DocumentPreview, DocumentSubmitResponse, DocumentValidateResponse, HealthResponse, ImportSubmitResponse, ImportValidateResponse, JobListResponse, JobStatusResponse, job_to_response
API_PREFIX = '/api/v1'
API_KEY = os.environ.get('CDR_API_KEY', '')
ModuleName = Literal['cdr', 'sdr']

def verify_api_key(x_api_key: Optional[str]=Header(default=None, alias='X-API-Key')) -> None:
    if API_KEY and x_api_key != API_KEY:
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

    @app.post(f'{API_PREFIX}/documents/validate', response_model=DocumentValidateResponse, tags=['documents'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    async def validate_document(module: ModuleName=Form(..., description='Processing module: cdr or sdr'), file: UploadFile=File(...)) -> DocumentValidateResponse:
        content = await file.read()
        if not content:
            raise HTTPException(status_code=400, detail='Empty file upload')
        path = save_upload(file.filename or 'upload', content, module=module)
        try:
            preview = validate_upload(path, module)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        return DocumentValidateResponse(message=preview.get('message') or f'Validated {preview.get('total_records', 0)} records.', preview=DocumentPreview(**preview))

    @app.post(f'{API_PREFIX}/documents', response_model=DocumentSubmitResponse, status_code=202, tags=['documents'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    async def submit_document(module: ModuleName=Form(..., description='Processing module: cdr or sdr'), file: UploadFile=File(...), batch_size: int=Query(DEFAULT_BATCH_SIZE, ge=1, le=50000), dry_run: bool=Query(False, description='Validate only; do not restore/import')) -> DocumentSubmitResponse:
        content = await file.read()
        if not content:
            raise HTTPException(status_code=400, detail='Empty file upload')
        path = save_upload(file.filename or 'upload', content, module=module)
        try:
            queued = submit_background_document(path, module=module, batch_size=batch_size, dry_run=dry_run)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=400, detail=str(exc)) from exc
        preview = DocumentPreview(module=queued.get('module', module), operator=queued.get('operator'), target_phone=queued.get('target_phone'), mssql_database=queued.get('mssql_database'), total_records=int(queued.get('total_records') or 0), warnings=queued.get('warnings', []), basename=queued['basename'], message=queued.get('message'))
        return DocumentSubmitResponse(job_id=queued.get('job_id'), module=preview.module, status=queued['status'], phase=queued.get('phase'), message=queued.get('message') or 'Document queued. Poll job status endpoint for progress.', preview=preview)

    @app.get(f'{API_PREFIX}/documents/{{job_id}}', response_model=JobStatusResponse, tags=['documents'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def get_document_job(job_id: int) -> JobStatusResponse:
        try:
            data = get_job_status(job_id)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return job_to_response(data)

    @app.post(f'{API_PREFIX}/documents/{{job_id}}/resume', response_model=JobStatusResponse, status_code=202, tags=['documents'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def resume_document_job(job_id: int) -> JobStatusResponse:
        try:
            data = resume_background_job(job_id)
        except DocumentProcessingError as exc:
            raise HTTPException(status_code=404, detail=str(exc)) from exc
        return job_to_response(data)

    @app.get(f'{API_PREFIX}/documents', response_model=JobListResponse, tags=['documents'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def list_document_jobs(module: Optional[ModuleName]=Query(None), limit: int=Query(50, ge=1, le=200), offset: int=Query(0, ge=0)) -> JobListResponse:
        jobs = [job_to_response(row) for row in fetch_jobs(module=module, limit=limit, offset=offset)]
        return JobListResponse(jobs=jobs, count=len(jobs))

    @app.post(f'{API_PREFIX}/imports/validate', response_model=ImportValidateResponse, tags=['imports'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    async def validate_import(file: UploadFile=File(...)) -> ImportValidateResponse:
        return await validate_document(module='cdr', file=file)

    @app.post(f'{API_PREFIX}/imports', response_model=ImportSubmitResponse, status_code=202, tags=['imports'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    async def submit_import(file: UploadFile=File(...), batch_size: int=Query(DEFAULT_BATCH_SIZE, ge=1, le=5000)) -> ImportSubmitResponse:
        return await submit_document(module='cdr', file=file, batch_size=batch_size, dry_run=False)

    @app.get(f'{API_PREFIX}/imports/{{job_id}}', response_model=JobStatusResponse, tags=['imports'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def get_import_job(job_id: int) -> JobStatusResponse:
        return get_document_job(job_id)

    @app.post(f'{API_PREFIX}/imports/{{job_id}}/resume', response_model=JobStatusResponse, status_code=202, tags=['imports'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def resume_import_job(job_id: int) -> JobStatusResponse:
        return resume_document_job(job_id)

    @app.get(f'{API_PREFIX}/imports', response_model=JobListResponse, tags=['imports'], dependencies=[Depends(verify_api_key)] if API_KEY else [])
    def list_import_jobs(limit: int=Query(50, ge=1, le=200), offset: int=Query(0, ge=0)) -> JobListResponse:
        return list_document_jobs(module='cdr', limit=limit, offset=offset)
    return app
app = create_app()

def run() -> None:
    import uvicorn
    host = os.environ.get('CDR_API_HOST', '0.0.0.0')
    port = int(os.environ.get('CDR_API_PORT', '8088'))
    uvicorn.run('app.main:app', host=host, port=port, reload=False)
if __name__ == '__main__':
    run()
