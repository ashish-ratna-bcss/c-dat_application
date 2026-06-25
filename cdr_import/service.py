from __future__ import annotations
import logging
from pathlib import Path
from typing import Optional
from .db import db_connection, ensure_schema, file_sha256
from .detect import detect_operator, extract_phone_from_filename
from .importer import CdrImportError
from .jobs import create_queued_job, fetch_job
from .parsers import get_parser
logger = logging.getLogger(__name__)

def analyze_file(file_path: str | Path, *, operator: Optional[str]=None, target_phone: Optional[str]=None) -> dict:
    path = Path(file_path).resolve()
    if not path.is_file():
        raise CdrImportError(f'File not found: {path}')
    op = operator or detect_operator(path)
    phone = target_phone or extract_phone_from_filename(path)
    digest = file_sha256(path)
    parser = get_parser(op, path, phone)
    parsed = parser.parse()
    return {'file': str(path), 'basename': path.name, 'operator': op, 'target_phone': parsed.target_phone or phone, 'header_line_no': parsed.header_line_no, 'total_records': len(parsed.records), 'file_sha256': digest, 'warnings': parsed.warnings, 'parsed': parsed}

def run_import(file_path: str | Path, *, dry_run: bool=True, batch_size: int=500, operator: Optional[str]=None, target_phone: Optional[str]=None, resume: bool=True) -> dict:
    from .importer import import_file
    return import_file(file_path, dry_run=dry_run, batch_size=batch_size, operator=operator, target_phone=target_phone, resume=resume)

def enqueue_import(file_path: str | Path, *, batch_size: int=500, operator: Optional[str]=None, target_phone: Optional[str]=None) -> dict:
    analysis = analyze_file(file_path, operator=operator, target_phone=target_phone)
    path = Path(analysis['file'])
    with db_connection() as conn:
        ensure_schema(conn)
        job_id = create_queued_job(conn, source_file=str(path), file_path=str(path), file_hash=analysis['file_sha256'], operator=analysis['operator'], target_phone=analysis['target_phone'], batch_size=batch_size, total_rows_estimated=analysis['total_records'], header_line_no=analysis['header_line_no'])
    return {'job_id': job_id, 'status': 'queued', 'operator': analysis['operator'], 'target_phone': analysis['target_phone'], 'total_records': analysis['total_records'], 'warnings': analysis['warnings'], 'file': analysis['file'], 'basename': analysis['basename']}

def execute_queued_job(job_id: int, *, resume: bool=True) -> dict:
    with db_connection() as conn:
        job = fetch_job(conn, job_id)
    if not job:
        raise CdrImportError(f'Job {job_id} not found')
    if job['status'] == 'completed':
        return _job_response(job, message='Job already completed.')
    path = job.get('file_path') or job['source_file']
    try:
        result = run_import(path, dry_run=False, batch_size=job['batch_size'], operator=job['operator'], target_phone=job['target_phone'], resume=resume)
        return result
    except CdrImportError as exc:
        with db_connection() as conn:
            job = fetch_job(conn, job_id)
        if job:
            return _job_response(job, message=str(exc))
        raise

def _job_response(job: dict, message: str='') -> dict:
    return {'job_id': job['job_id'], 'status': job['status'], 'operator': job['operator'], 'target_phone': job['target_phone'], 'total_records': job.get('total_rows_estimated'), 'rows_committed': job.get('rows_committed', 0), 'error_message': job.get('error_message'), 'dry_run': job.get('dry_run'), 'file': job.get('source_file'), 'basename': job.get('source_basename'), 'message': message, 'created_at': job.get('created_at'), 'updated_at': job.get('updated_at'), 'completed_at': job.get('completed_at')}

def get_job_status(job_id: int) -> dict:
    with db_connection() as conn:
        job = fetch_job(conn, job_id)
    if not job:
        raise CdrImportError(f'Job {job_id} not found')
    return _job_response(job)
