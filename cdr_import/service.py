from __future__ import annotations
import logging
from pathlib import Path
from typing import Optional
from .db import db_connection, ensure_schema, file_sha256
from .detect import detect_operator, extract_phone_from_filename
from .importer import CdrImportError
from .jobs import create_queued_job, fetch_job
from .enrichment import normalize_record, resolve_operator
from .parsers import get_parser

def analyze_file(file_path: str | Path, *, operator: Optional[str]=None, target_phone: Optional[str]=None) -> dict:
    path = Path(file_path).resolve()
    if not path.is_file():
        raise CdrImportError(f'File not found: {path}')
    op = resolve_operator(operator, detect_operator(path)) if operator else detect_operator(path)
    phone = target_phone or extract_phone_from_filename(path)
    digest = file_sha256(path)
    parser = get_parser(op, path, phone)
    header_line_no, total, warnings = parser.count_data_rows()
    return {'file': str(path), 'basename': path.name, 'operator': op, 'target_phone': parser.target_phone or phone, 'header_line_no': header_line_no + 1, 'total_records': total, 'file_sha256': digest, 'warnings': warnings}

def run_import(file_path: str | Path, *, dry_run: bool=True, batch_size: int=500, operator: Optional[str]=None, target_phone: Optional[str]=None, resume: bool=True) -> dict:
    from .importer import import_file
    return import_file(file_path, dry_run=dry_run, batch_size=batch_size, operator=operator, target_phone=target_phone, resume=resume)

def enqueue_import(file_path: str | Path, *, batch_size: int=500, operator: Optional[str]=None, target_phone: Optional[str]=None) -> dict:
    analysis = analyze_file(file_path, operator=operator, target_phone=target_phone)
    path = Path(analysis['file'])
    digest = analysis['file_sha256']
    with db_connection() as conn:
        ensure_schema(conn)
        from document_processing.locks import content_lock
        from document_processing.db import find_job_by_hash
        with content_lock(conn, 'cdr', digest):
            existing = find_job_by_hash(conn, module='cdr', file_hash=digest)
            if existing and existing['status'] in ('pending_verification', 'running', 'queued'):
                return {
                    'job_id': existing['job_id'],
                    'status': existing['status'],
                    'operator': existing.get('operator') or analysis['operator'],
                    'target_phone': existing.get('target_phone') or analysis['target_phone'],
                    'total_records': existing.get('total_rows_estimated') or analysis['total_records'],
                    'warnings': analysis['warnings'],
                    'file': existing.get('file_path') or analysis['file'],
                    'basename': existing.get('source_basename') or analysis['basename'],
                    'message': 'Reusing existing CDR job for identical file content.',
                }
            job_id = create_queued_job(conn, source_file=str(path), file_path=str(path), file_hash=digest, operator=analysis['operator'], target_phone=analysis['target_phone'], batch_size=batch_size, total_rows_estimated=analysis['total_records'], header_line_no=analysis['header_line_no'])
    return {'job_id': job_id, 'status': 'queued', 'operator': analysis['operator'], 'target_phone': analysis['target_phone'], 'total_records': analysis['total_records'], 'warnings': analysis['warnings'], 'file': analysis['file'], 'basename': analysis['basename']}

def execute_queued_job(job_id: int, *, resume: bool=True) -> dict:
    with db_connection() as conn:
        job = fetch_job(conn, job_id)
    if not job:
        raise CdrImportError(f'Job {job_id} not found')
    if job['status'] in ('completed', 'pending_verification'):
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


def _record_to_preview_dict(rec) -> dict:
    return {
        'source_row': rec.source_row_number,
        'phone': rec.phone or '',
        'other': rec.other or '',
        'starttime': rec.starttime.strftime('%Y-%m-%d %H:%M:%S') if rec.starttime else '',
        'duration': rec.duration,
        'direction': 'IN' if rec.incoming else 'OUT',
        'imei': rec.imeinumber,
        'imsi': rec.imsinumber or '',
        'cell_id': rec.celltowerid or rec.first_cellid or '',
        'call_type': rec.call_type or '',
        'calling_no': rec.calling_no or '',
        'called_no': rec.called_no or '',
        'service': rec.otherinfo or '',
        'roaming': rec.roaming_nw or '',
    }


PREVIEW_COLUMNS = [
    'source_row', 'phone', 'other', 'starttime', 'duration', 'direction',
    'imei', 'imsi', 'cell_id', 'call_type', 'calling_no', 'called_no', 'service', 'roaming',
]


def preview_file(file_path: str | Path, *, operator: Optional[str] = None, target_phone: Optional[str] = None, limit: int = 150) -> dict:
    """Parse and normalize CDR rows for read-only UI preview (no DB writes)."""
    path = Path(file_path).resolve()
    if not path.is_file():
        raise CdrImportError(f'File not found: {path}')
    op = resolve_operator(operator, detect_operator(path)) if operator else detect_operator(path)
    phone = target_phone or extract_phone_from_filename(path)
    parser = get_parser(op, path, phone)
    header_line_no, total, warnings = parser.count_data_rows()
    rows: list[dict] = []
    parse_errors = 0
    for record, row_warnings, _hdr in parser.iter_records():
        warnings.extend(row_warnings)
        try:
            normalize_record(record)
            rows.append(_record_to_preview_dict(record))
        except Exception as exc:
            parse_errors += 1
            warnings.append(f'Row {record.source_row_number}: {exc}')
        if len(rows) >= limit:
            break
    return {
        'operator': op,
        'target_phone': parser.target_phone or phone,
        'header_line_no': header_line_no + 1,
        'total_records': total,
        'preview_count': len(rows),
        'parse_errors': parse_errors,
        'truncated': total > len(rows),
        'warnings': warnings[:25],
        'columns': PREVIEW_COLUMNS,
        'rows': rows,
    }
