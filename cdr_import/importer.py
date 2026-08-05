from __future__ import annotations
import logging
from pathlib import Path
from typing import Optional
from .config import DEFAULT_BATCH_SIZE, PROGRESS_UPDATE_EVERY_BATCHES
from .db import db_connection, ensure_schema, ensure_job_staging_table, file_sha256, get_or_create_job, insert_staging_batch, next_ucids, update_job_progress, utcnow
from document_processing.staging import ensure_staging_batch, finalize_staging_job
from .detect import detect_operator, extract_phone_from_filename
from .models import CdrRecord
from .parsers import get_parser
from .enrichment import normalize_record, resolve_operator
from .normalize import apply_operator_normalization
from .staging_dedup import refresh_cdr_staging_duplicates

logger = logging.getLogger(__name__)

class CdrImportError(Exception):
    pass

def import_file(file_path: str | Path, *, dry_run: bool=True, batch_size: int=DEFAULT_BATCH_SIZE, operator: Optional[str]=None, target_phone: Optional[str]=None, resume: bool=True) -> dict:
    path = Path(file_path).resolve()
    if not path.is_file():
        raise CdrImportError(f'File not found: {path}')
    op = resolve_operator(operator, detect_operator(path)) if operator else detect_operator(path)
    phone = target_phone or extract_phone_from_filename(path)
    digest = file_sha256(path)
    source_basename = path.name
    parser = get_parser(op, path, phone)
    header_line_no, total, parse_warnings = parser.count_data_rows()
    parsed_target = parser.target_phone or phone
    result = {'file': str(path), 'operator': op, 'target_phone': parsed_target, 'header_line_no': header_line_no + 1, 'total_records': total, 'rows_committed': 0, 'dry_run': dry_run, 'warnings': parse_warnings, 'status': 'validated', 'job_id': None}
    if parse_warnings:
        logger.warning('Parse warnings for %s: %s', path.name, parse_warnings[:5])
    if dry_run:
        result['rows_committed'] = 0
        result['message'] = f'Dry-run OK: parsed {total} records (no DB writes).'
        return result

    with db_connection(fast_staging=True) as conn:
        ensure_schema(conn)
        job_id, rows_committed, status = get_or_create_job(conn, source_file=str(path), file_path=str(path), file_hash=digest, operator=op, target_phone=parsed_target, dry_run=False, batch_size=batch_size)
        result['job_id'] = job_id
        staging_table = ensure_job_staging_table(conn, job_id, source_basename)
        result['staging_table'] = staging_table
        staging_tables = {'cdr': staging_table}

        if status == 'pending_verification' and rows_committed >= total:
            result['rows_committed'] = rows_committed
            result['status'] = 'pending_verification'
            result['message'] = f'{rows_committed} rows already processed into {staging_table} for manual verification.'
            return result
        if status == 'completed' and rows_committed >= total:
            result['rows_committed'] = rows_committed
            result['status'] = 'completed'
            result['message'] = 'Already fully imported.'
            return result

        batch_id = ensure_staging_batch(conn, job_id=job_id, module='cdr', staging_tables=staging_tables)
        result['staging_batch_id'] = batch_id

        if not resume:
            rows_committed = 0
        update_job_progress(conn, job_id, rows_committed=rows_committed, last_source_row_no=rows_committed, header_line_no=header_line_no + 1, total_rows_estimated=total, status='running', error_message=None)

        processed = rows_committed
        unique_inserted = 0
        dropped = 0
        batch_num = 0
        batch: list = []
        skip_remaining = rows_committed if resume else 0
        result['resumed_from_row'] = rows_committed
        op_enricher = None
        if op == 'airtel':
            from .normalize.airtel import AirtelDbEnricher
            op_enricher = AirtelDbEnricher(conn)
        elif op == 'bsnl':
            from .normalize.bsnl import BsnlDbEnricher
            op_enricher = BsnlDbEnricher(conn)
        elif op == 'jio':
            from .normalize.jio import JioDbEnricher
            op_enricher = JioDbEnricher(conn)
        elif op == 'vi':
            from .normalize.vi import ViDbEnricher
            op_enricher = ViDbEnricher(conn)

        try:
            for record, _row_warnings, _hdr in parser.iter_records():
                if skip_remaining > 0:
                    skip_remaining -= 1
                    continue
                normalize_record(record)
                normalized = apply_operator_normalization(
                    record, op, conn=conn, enricher=op_enricher
                )
                if normalized is None:
                    dropped += 1
                    processed += 1
                    continue
                batch.append(normalized)
                if len(batch) >= batch_size:
                    rows = _records_to_db_rows(batch, job_id, op, str(path), conn)
                    inserted = insert_staging_batch(
                        conn, rows, job_id=job_id, filename=source_basename
                    )
                    unique_inserted += inserted
                    processed += len(batch)
                    batch_num += 1
                    batch = []
                    if batch_num % PROGRESS_UPDATE_EVERY_BATCHES == 0:
                        update_job_progress(conn, job_id, rows_committed=processed, last_source_row_no=processed, status='running')
                        conn.commit()
                        logger.info(
                            'Job %s: processed %s/%s rows (%s unique into %s)',
                            job_id, processed, total, unique_inserted, staging_table,
                        )
            if batch:
                rows = _records_to_db_rows(batch, job_id, op, str(path), conn)
                inserted = insert_staging_batch(
                    conn, rows, job_id=job_id, filename=source_basename
                )
                unique_inserted += inserted
                processed += len(batch)
                batch_num += 1

            # Flag rows that already exist in production (not within-file dups).
            refresh_cdr_staging_duplicates(conn, staging_table, import_job_id=job_id)

            update_job_progress(conn, job_id, rows_committed=processed, last_source_row_no=processed, status='running')
            conn.commit()
            logger.info(
                'Job %s: processed %s/%s rows, unique_inserted=%s, dropped=%s, table=%s',
                job_id, processed, total, unique_inserted, dropped, staging_table,
            )

            finalize_staging_job(
                conn,
                job_id=job_id,
                batch_id=batch_id,
                module='cdr',
                staging_tables=staging_tables,
                rows_committed=processed,
                total_rows=total,
            )
            conn.commit()

            result['rows_committed'] = processed
            result['rows_unique_inserted'] = unique_inserted
            result['rows_dropped'] = dropped
            result['status'] = 'pending_verification'
            result['message'] = (
                f'Loaded {unique_inserted} unique rows into {staging_table} '
                f'for manual verification (processed {processed}/{total}).'
            )
            if dropped:
                result['message'] += f' Dropped {dropped} rows by operator normalization rules.'
        except Exception as exc:
            conn.rollback()
            update_job_progress(conn, job_id, rows_committed=processed, last_source_row_no=processed, status='failed', error_message=str(exc))
            conn.commit()
            result['rows_committed'] = processed
            result['status'] = 'failed'
            result['message'] = f'Failed after {processed}/{total} rows. Re-run to resume. Error: {exc}'
            raise CdrImportError(result['message']) from exc
    return result

def _records_to_db_rows(records: list[CdrRecord], job_id: int, operator: str, source_file: str, conn) -> list[dict]:
    ucids = next_ucids(conn, len(records))
    now = utcnow()
    rows = []
    for rec, ucid in zip(records, ucids):
        rows.append({'import_job_id': job_id, 'source_row_number': rec.source_row_number, 'ucid': ucid, 'phone': rec.phone, 'other': rec.other, 'starttime': rec.starttime, 'duration': rec.duration, 'incoming': rec.incoming, 'imeinumber': rec.imeinumber, 'imsinumber': rec.imsinumber, 'celltowerid': rec.celltowerid, 'otherinfo': rec.otherinfo, 'tower_key': rec.tower_key, 'provider_key': rec.provider_key, 'state_key': rec.state_key, 'first_cellid': rec.first_cellid, 'last_cellid': rec.last_cellid, 'roaming_nw': rec.roaming_nw, 'call_type': rec.call_type, 'calling_no': rec.calling_no, 'called_no': rec.called_no, 'asondate': now, 'operator': operator, 'source_file': source_file})
    return rows
