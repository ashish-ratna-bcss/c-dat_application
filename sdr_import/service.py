from __future__ import annotations
from pathlib import Path
from typing import Optional
from document_processing.db import db_connection, ensure_schema, fetch_document_job, file_sha256
from document_processing.jobs import create_document_job
from sdr_import.pipeline import SdrPipelineError, analyze_sdr_upload, run_sdr_job

def enqueue_sdr_job(file_path: str | Path, *, batch_size: int=10000, dry_run: bool=False) -> dict:
    analysis = analyze_sdr_upload(file_path)
    path = Path(analysis['file'])
    digest = file_sha256(path)
    with db_connection() as conn:
        ensure_schema(conn)
        job_id = create_document_job(conn, module='sdr', source_file=str(path), file_path=str(path), file_hash=digest, batch_size=batch_size, dry_run=dry_run, mssql_database=analysis['mssql_database'], phase='restore_mssql' if not dry_run else 'validated', status='queued' if not dry_run else 'validated')
    return {'job_id': job_id, 'module': 'sdr', 'status': 'queued' if not dry_run else 'validated', 'phase': 'restore_mssql' if not dry_run else 'validated', 'basename': analysis['basename'], 'file': analysis['file'], 'mssql_database': analysis['mssql_database'], 'message': analysis['message'], 'dry_run': dry_run}

def execute_sdr_job(job_id: int, *, resume: bool=True) -> dict:
    return run_sdr_job(job_id, resume=resume)

def get_sdr_job_status(job_id: int) -> dict:
    with db_connection() as conn:
        job = fetch_document_job(conn, job_id)
    if not job:
        raise SdrPipelineError(f'Job {job_id} not found')
    from sdr_import.pipeline import _response
    return _response(job)
