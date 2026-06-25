from __future__ import annotations
import logging
from pathlib import Path
from typing import Optional
from .config import DEFAULT_BATCH_SIZE
from .db import db_connection, ensure_schema, file_sha256, get_or_create_job, insert_staging_batch, next_ucids, update_job_progress, utcnow
from .detect import detect_operator, extract_phone_from_filename
from .models import CdrRecord
from .parsers import get_parser
logger = logging.getLogger(__name__)

class CdrImportError(Exception):
    pass

def import_file(file_path: str | Path, *, dry_run: bool=True, batch_size: int=DEFAULT_BATCH_SIZE, operator: Optional[str]=None, target_phone: Optional[str]=None, resume: bool=True) -> dict:
    path = Path(file_path).resolve()
    if not path.is_file():
        raise CdrImportError(f'File not found: {path}')
    op = operator or detect_operator(path)
    phone = target_phone or extract_phone_from_filename(path)
    digest = file_sha256(path)
    parser = get_parser(op, path, phone)
    parsed = parser.parse()
    total = len(parsed.records)
    result = {'file': str(path), 'operator': op, 'target_phone': parsed.target_phone or phone, 'header_line_no': parsed.header_line_no, 'total_records': total, 'rows_committed': 0, 'dry_run': dry_run, 'warnings': parsed.warnings, 'status': 'validated', 'job_id': None}
    if parsed.warnings:
        logger.warning('Parse warnings for %s: %s', path.name, parsed.warnings[:5])
    if dry_run:
        result['rows_committed'] = 0
        result['message'] = f'Dry-run OK: parsed {total} records (no DB writes).'
        return result
    with db_connection() as conn:
        ensure_schema(conn)
        job_id, rows_committed, status = get_or_create_job(conn, source_file=str(path), file_path=str(path), file_hash=digest, operator=op, target_phone=parsed.target_phone or phone, dry_run=False, batch_size=batch_size)
        result['job_id'] = job_id
        if status == 'completed' and rows_committed >= total:
            result['rows_committed'] = rows_committed
            result['status'] = 'completed'
            result['message'] = 'Already fully imported; no deduplication applied on re-run.'
            return result
        if not resume:
            rows_committed = 0
        pending = parsed.records[rows_committed:]
        result['resumed_from_row'] = rows_committed
        update_job_progress(conn, job_id, rows_committed=rows_committed, last_source_row_no=rows_committed, header_line_no=parsed.header_line_no, total_rows_estimated=total, status='running', error_message=None)
        committed = rows_committed
        try:
            for start in range(0, len(pending), batch_size):
                batch = pending[start:start + batch_size]
                rows = _records_to_db_rows(batch, job_id, op, str(path), conn)
                insert_staging_batch(conn, rows)
                committed += len(batch)
                last_row_no = batch[-1].source_row_number
                update_job_progress(conn, job_id, rows_committed=committed, last_source_row_no=last_row_no, status='running')
                conn.commit()
                logger.info('Job %s: committed %s/%s rows', job_id, committed, total)
            update_job_progress(conn, job_id, rows_committed=committed, last_source_row_no=pending[-1].source_row_number if pending else rows_committed, status='completed')
            result['rows_committed'] = committed
            result['status'] = 'completed'
            result['message'] = f'Imported {committed} rows into staging.'
        except Exception as exc:
            conn.rollback()
            update_job_progress(conn, job_id, rows_committed=committed, last_source_row_no=committed, status='failed', error_message=str(exc))
            conn.commit()
            result['rows_committed'] = committed
            result['status'] = 'failed'
            result['message'] = f'Failed after {committed}/{total} rows. Re-run the same command to resume from checkpoint. Error: {exc}'
            raise CdrImportError(result['message']) from exc
    return result

def _records_to_db_rows(records: list[CdrRecord], job_id: int, operator: str, source_file: str, conn) -> list[dict]:
    ucids = next_ucids(conn, len(records))
    now = utcnow()
    rows = []
    for rec, ucid in zip(records, ucids):
        rows.append({'import_job_id': job_id, 'source_row_number': rec.source_row_number, 'ucid': ucid, 'phone': rec.phone, 'other': rec.other, 'starttime': rec.starttime, 'duration': rec.duration, 'incoming': rec.incoming, 'imeinumber': rec.imeinumber, 'imsinumber': rec.imsinumber, 'celltowerid': rec.celltowerid, 'otherinfo': rec.otherinfo, 'tower_key': rec.tower_key, 'provider_key': rec.provider_key, 'state_key': rec.state_key, 'first_cellid': rec.first_cellid, 'last_cellid': rec.last_cellid, 'roaming_nw': rec.roaming_nw, 'call_type': rec.call_type, 'calling_no': rec.calling_no, 'called_no': rec.called_no, 'asondate': now, 'operator': operator, 'source_file': source_file})
    return rows
