from __future__ import annotations
import json
import os
import re
from typing import Any, Optional
import psycopg2
import psycopg2.extras
from cdr_import.config import load_db_config
from cdr_import.staging_dedup import refresh_cdr_staging_duplicates

class VerificationError(Exception):
    pass

def _conn():
    cfg = load_db_config()
    return psycopg2.connect(host=cfg['host'], port=cfg['port'], dbname=cfg['database'], user=cfg['user'], password=cfg['password'])

def _assert_qualified(table: str) -> None:
    if not re.fullmatch('upload_staging\\.[a-z][a-z0-9_]*', table):
        raise VerificationError('Invalid staging table reference.')

def _as_staging_tables(value: Any) -> dict[str, Any]:
    if value is None:
        return {}
    if isinstance(value, dict):
        return value
    if isinstance(value, (bytes, bytearray)):
        value = value.decode()
    if isinstance(value, str):
        return json.loads(value or '{}')
    return dict(value)


def get_batch_by_job_id(job_id: int) -> Optional[dict[str, Any]]:
    with _conn() as conn, conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute('\n            SELECT b.*, j.module, j.source_basename AS file_name\n            FROM upload_staging_batches b\n            JOIN document_jobs j ON j.job_id = b.document_job_id\n            WHERE b.document_job_id = %s\n            ', (job_id,))
        row = cur.fetchone()
        if not row:
            return None
        data = dict(row)
        data['staging_tables'] = _as_staging_tables(data.get('staging_tables'))
        return data

def fetch_staging_rows(job_id: int, *, table_key: Optional[str]=None, limit: int=100, offset: int=0) -> dict:
    batch = get_batch_by_job_id(job_id)
    if not batch:
        raise VerificationError('Staging batch not found for job.')
    tables = batch['staging_tables']
    module = (batch.get('module') or 'cdr').lower()
    key = table_key or ('cdr' if module == 'cdr' else next(iter(tables), ''))
    qualified = tables.get(key)
    if not qualified:
        raise VerificationError('Staging table missing.')
    _assert_qualified(qualified)
    with _conn() as conn, conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        if module == 'cdr':
            refresh_cdr_staging_duplicates(conn, qualified, import_job_id=job_id)
            cur.execute(
                f'SELECT COUNT(*) FROM {qualified} WHERE import_job_id = %s',
                (job_id,),
            )
            total = int(cur.fetchone()['count'])
            cur.execute(
                f'''
                SELECT * FROM {qualified}
                WHERE import_job_id = %s
                ORDER BY staging_row_id
                LIMIT %s OFFSET %s
                ''',
                (job_id, limit, offset),
            )
            rows = [dict(r) for r in cur.fetchall()]
            cur.execute(
                f'SELECT COUNT(*) FROM {qualified} WHERE is_duplicate = TRUE AND import_job_id = %s',
                (job_id,),
            )
            dup = int(cur.fetchone()['count'])
        else:
            cur.execute(f'SELECT COUNT(*) FROM {qualified}')
            total = int(cur.fetchone()['count'])
            cur.execute(
                f'SELECT * FROM {qualified} ORDER BY staging_row_id LIMIT %s OFFSET %s',
                (limit, offset),
            )
            rows = [dict(r) for r in cur.fetchall()]
            cur.execute(f'SELECT COUNT(*) FROM {qualified} WHERE is_duplicate = TRUE')
            dup = int(cur.fetchone()['count'])
    return {
        'job_id': job_id,
        'batch_id': batch['batch_id'],
        'module': module,
        'table_key': key,
        'table': qualified,
        'total': total,
        'duplicate_count': dup,
        'valid_count': total - dup,
        'rows': rows,
    }

def approve_staging_batch(job_id: int, username: str='api') -> dict:
    batch = get_batch_by_job_id(job_id)
    if not batch:
        raise VerificationError('Staging batch not found.')
    if batch.get('verification_status') == 'approved':
        raise VerificationError('Batch already approved.')
    script = os.path.join(os.path.dirname(__file__), '..', 'scripts', 'approve_staging_cli.php')
    if os.path.isfile(script):
        import subprocess
        proc = subprocess.run(['php', script, str(batch['batch_id']), username], capture_output=True, text=True, timeout=86400)
        if proc.returncode != 0:
            raise VerificationError(proc.stderr or proc.stdout or 'Approval failed.')
        try:
            return json.loads(proc.stdout)
        except json.JSONDecodeError:
            return {'ok': True, 'message': proc.stdout.strip()}
    return _approve_cdr_only(batch, username)

def reject_staging_batch(job_id: int, username: str='api') -> dict:
    batch = get_batch_by_job_id(job_id)
    if not batch:
        raise VerificationError('Staging batch not found.')
    tables = batch['staging_tables']
    batch_id = batch['batch_id']
    module = (batch.get('module') or '').lower()
    with _conn() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                UPDATE upload_approval_queue
                SET status = 'cancelled', completed_at = NOW()
                WHERE batch_id = %s AND status = 'queued'
                """,
                (batch_id,),
            )
            for key, qualified in tables.items():
                _assert_qualified(qualified)
                if module == 'cdr' and key == 'cdr':
                    cur.execute(
                        f'DELETE FROM {qualified} WHERE import_job_id = %s',
                        (job_id,),
                    )
                    cur.execute(f'SELECT COUNT(*) FROM {qualified}')
                    if int(cur.fetchone()[0] or 0) == 0:
                        cur.execute(f'DROP TABLE IF EXISTS {qualified} CASCADE')
                else:
                    cur.execute(f'DROP TABLE IF EXISTS {qualified} CASCADE')
            cur.execute(
                """
                UPDATE upload_staging_batches
                SET verification_status = 'rejected', verified_at = NOW(), verified_by = %s
                WHERE batch_id = %s
                """,
                (username, batch['batch_id']),
            )
            cur.execute(
                """
                UPDATE upload_activity_logs
                SET upload_status = 'Rejected', verification_status = 'rejected'
                WHERE staging_batch_id = %s OR document_job_id = %s
                """,
                (batch['batch_id'], job_id),
            )
            cur.execute(
                "UPDATE document_jobs SET status = 'rejected', phase = 'rejected', updated_at = NOW() WHERE job_id = %s",
                (job_id,),
            )
        conn.commit()
    return {'ok': True, 'job_id': job_id, 'batch_id': batch['batch_id'], 'status': 'rejected'}


def _approve_cdr_only(batch: dict, username: str) -> dict:
    tables = batch['staging_tables']
    table = tables.get('cdr')
    if not table:
        raise VerificationError('CDR staging table missing.')
    _assert_qualified(table)
    job_id = int(batch['document_job_id'])
    with _conn() as conn:
        refresh_cdr_staging_duplicates(conn, table, import_job_id=job_id)
        with conn.cursor() as cur:
            cur.execute(
                f'''
                WITH upload_rows AS (
                    SELECT *
                    FROM {table} s
                    WHERE COALESCE(s.is_duplicate, FALSE) = FALSE
                      AND s.import_job_id = %s
                ),
                dedup_upload AS (
                    SELECT DISTINCT ON (
                        phone,
                        other,
                        starttime,
                        duration,
                        incoming
                    ) *
                    FROM upload_rows
                    ORDER BY
                        phone,
                        other,
                        starttime,
                        duration,
                        incoming,
                        staging_row_id ASC
                )
                INSERT INTO cdatpcsuspect (
                    ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber,
                    celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid,
                    last_cellid, roaming_nw, call_type, calling_no, called_no, asondate
                )
                SELECT
                    d.ucid, d.phone, d.other, d.starttime, d.duration, d.incoming, d.imeinumber, d.imsinumber,
                    d.celltowerid, d.otherinfo, d.tower_key, d.provider_key, d.state_key, d.first_cellid,
                    d.last_cellid, d.roaming_nw, d.call_type, d.calling_no, d.called_no, COALESCE(d.asondate, NOW())
                FROM dedup_upload d
                WHERE NOT EXISTS (
                    SELECT 1 FROM cdatpcsuspect t
                    WHERE t.starttime IS NOT DISTINCT FROM d.starttime
                      AND t.phone IS NOT DISTINCT FROM d.phone
                      AND t.other IS NOT DISTINCT FROM d.other
                      AND t.duration IS NOT DISTINCT FROM d.duration
                      AND t.incoming IS NOT DISTINCT FROM d.incoming
                )
                ''',
                (job_id,),
            )
            inserted = cur.rowcount
            cur.execute(f'DELETE FROM {table} WHERE import_job_id = %s', (job_id,))
            cur.execute(f'SELECT COUNT(*) FROM {table}')
            if int(cur.fetchone()[0] or 0) == 0:
                cur.execute(f'DROP TABLE IF EXISTS {table} CASCADE')
            cur.execute(
                """
                UPDATE upload_staging_batches
                SET verification_status = 'approved', verified_at = NOW(), verified_by = %s
                WHERE batch_id = %s
                """,
                (username, batch['batch_id']),
            )
            cur.execute(
                """
                UPDATE document_jobs
                SET status = 'completed', phase = 'completed', completed_at = NOW(), updated_at = NOW()
                WHERE job_id = %s
                """,
                (job_id,),
            )
            cur.execute(
                """
                UPDATE upload_activity_logs
                SET upload_status = 'Success', verification_status = 'approved',
                    inserted_records = %s
                WHERE staging_batch_id = %s OR document_job_id = %s
                """,
                (inserted, batch['batch_id'], job_id),
            )
        conn.commit()
    return {'ok': True, 'inserted': inserted, 'job_id': job_id}
