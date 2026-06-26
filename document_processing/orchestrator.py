from __future__ import annotations
from pathlib import Path
from typing import Optional
from cdr_import.importer import CdrImportError
from cdr_import.service import analyze_file, enqueue_import, execute_queued_job, get_job_status
from document_processing.db import db_connection, ensure_schema, file_sha256
from document_processing.jobs import create_document_job
from document_processing.db import fetch_document_job

class DocumentProcessingError(Exception):
    pass
SUPPORTED_MODULES = {'cdr', 'sdr'}

def validate_document(file_path: Path, module: str) -> dict:
    module = module.lower().strip()
    if module not in SUPPORTED_MODULES:
        raise DocumentProcessingError(f'Unsupported module: {module}')
    if module == 'cdr':
        try:
            analysis = analyze_file(file_path)
        except (CdrImportError, ValueError) as exc:
            raise DocumentProcessingError(str(exc)) from exc
        return {'module': 'cdr', 'operator': analysis['operator'], 'target_phone': analysis['target_phone'], 'total_records': analysis['total_records'], 'warnings': analysis['warnings'], 'basename': analysis['basename']}
    from sdr_import.pipeline import analyze_sdr_upload
    analysis = analyze_sdr_upload(file_path)
    return {'module': 'sdr', 'basename': analysis['basename'], 'mssql_database': analysis['mssql_database'], 'message': analysis['message'], 'total_records': 0, 'warnings': []}

def enqueue_document(file_path: Path, *, module: str, batch_size: int, dry_run: bool=False) -> dict:
    module = module.lower().strip()
    if module == 'cdr':
        try:
            if dry_run:
                analysis = analyze_file(file_path)
                return {'job_id': None, 'module': 'cdr', 'status': 'validated', 'operator': analysis['operator'], 'target_phone': analysis['target_phone'], 'total_records': analysis['total_records'], 'warnings': analysis['warnings'], 'basename': analysis['basename'], 'dry_run': True}
            queued = enqueue_import(file_path, batch_size=batch_size)
        except (CdrImportError, ValueError) as exc:
            raise DocumentProcessingError(str(exc)) from exc
        queued['module'] = 'cdr'
        return queued
    if module == 'sdr':
        from sdr_import.service import enqueue_sdr_job
        return enqueue_sdr_job(file_path, batch_size=batch_size, dry_run=dry_run)
    raise DocumentProcessingError(f'Unsupported module: {module}')

def execute_document_job(job_id: int, *, resume: bool=True) -> dict:
    with db_connection() as conn:
        job = fetch_document_job(conn, job_id)
    if not job:
        raise DocumentProcessingError(f'Job {job_id} not found')
    module = job['module']
    if module == 'cdr':
        return execute_queued_job(job_id)
    if module == 'sdr':
        from sdr_import.service import execute_sdr_job
        return execute_sdr_job(job_id, resume=resume)
    raise DocumentProcessingError(f'Unsupported module: {module}')

def get_document_job_status(job_id: int) -> dict:
    with db_connection() as conn:
        job = fetch_document_job(conn, job_id)
    if not job:
        raise DocumentProcessingError(f'Job {job_id} not found')
    if job['module'] == 'sdr':
        from sdr_import.service import get_sdr_job_status
        return get_sdr_job_status(job_id)
    return _job_response(job)

def _job_response(job: dict) -> dict:
    return {'job_id': job['job_id'], 'module': job.get('module', 'cdr'), 'status': job['status'], 'phase': job.get('phase'), 'operator': job.get('operator'), 'target_phone': job.get('target_phone'), 'basename': job.get('source_basename'), 'total_records': job.get('total_rows_estimated'), 'rows_committed': job.get('rows_committed', 0), 'last_checkpoint_key': job.get('last_checkpoint_key', 0), 'error_message': job.get('error_message'), 'dry_run': job.get('dry_run'), 'file': job.get('file_path'), 'phase_state': job.get('phase_state'), 'mssql_database': job.get('mssql_database'), 'created_at': job.get('created_at'), 'updated_at': job.get('updated_at'), 'completed_at': job.get('completed_at')}
