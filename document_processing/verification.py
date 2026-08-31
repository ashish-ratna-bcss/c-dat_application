from __future__ import annotations
import json
import os
import re
from typing import Any, Optional
import psycopg2
import psycopg2.extras
from cdr_import.config import load_db_config
from cdr_import.db import ensure_positive_ucid_sequence
from cdr_import.staging_dedup import production_not_exists_sql, refresh_cdr_staging_duplicates

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
            refresh_cdr_staging_duplicates(conn, qualified)
        cur.execute(f'SELECT COUNT(*) FROM {qualified}')
        total = int(cur.fetchone()['count'])
        cur.execute(f'SELECT * FROM {qualified} ORDER BY staging_row_id LIMIT %s OFFSET %s', (limit, offset))
        rows = [dict(r) for r in cur.fetchall()]
        cur.execute(f'SELECT COUNT(*) FROM {qualified} WHERE is_duplicate = TRUE')
        dup = int(cur.fetchone()['count'])
    return {'job_id': job_id, 'batch_id': batch['batch_id'], 'module': module, 'table_key': key, 'table': qualified, 'total': total, 'duplicate_count': dup, 'valid_count': total - dup, 'rows': rows}

def approve_staging_batch(job_id: int, username: str='api') -> dict:
    batch = get_batch_by_job_id(job_id)
    if not batch:
        raise VerificationError('Staging batch not found.')
    if batch.get('verification_status') == 'approved':
        raise VerificationError('Batch already approved.')
    script = os.path.join(os.path.dirname(__file__), 'approve_staging_cli.php')
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
            cur.execute("\n                UPDATE upload_approval_queue\n                SET status = 'cancelled', completed_at = NOW()\n                WHERE batch_id = %s AND status = 'queued'\n                ", (batch_id,))
            for key, qualified in tables.items():
                _assert_qualified(qualified)
                if module == 'cdr' and key == 'cdr':
                    cur.execute(
                        """
                        SELECT COUNT(*) FROM upload_staging_batches
                        WHERE module = 'cdr'
                          AND verification_status = 'pending'
                          AND staging_tables->>'cdr' = %s
                          AND batch_id <> %s
                        """,
                        (qualified, batch_id),
                    )
                    if int(cur.fetchone()[0] or 0) > 0:
                        continue
                cur.execute(f'DROP TABLE IF EXISTS {qualified} CASCADE')
            cur.execute("\n                UPDATE upload_staging_batches\n                SET verification_status = 'rejected', verified_at = NOW(), verified_by = %s\n                WHERE batch_id = %s\n                ", (username, batch['batch_id']))
            cur.execute("\n                UPDATE upload_activity_logs\n                SET upload_status = 'Rejected', verification_status = 'rejected'\n                WHERE staging_batch_id = %s OR document_job_id = %s\n                ", (batch['batch_id'], job_id))
            cur.execute("UPDATE document_jobs SET status = 'rejected', phase = 'rejected', updated_at = NOW() WHERE job_id = %s", (job_id,))
        conn.commit()
    return {'ok': True, 'job_id': job_id, 'batch_id': batch['batch_id'], 'status': 'rejected'}

def _approve_cdr_only(batch: dict, username: str) -> dict:
    tables = batch['staging_tables']
    table = tables.get('cdr')
    if not table:
        raise VerificationError('CDR staging table missing.')
    _assert_qualified(table)
    with _conn() as conn:
        refresh_cdr_staging_duplicates(conn, table)
        ensure_positive_ucid_sequence(conn, resync=True)
        with conn.cursor() as cur:
            cur.execute(
                f'''
                INSERT INTO cdatpcsuspect (
                    ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber,
                    celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid,
                    last_cellid, roaming_nw, call_type, calling_no, called_no, asondate
                )
                SELECT
                    CASE WHEN s.ucid < 0 THEN nextval('cdr_import_ucid_seq') ELSE s.ucid END,
                    s.phone, s.other, s.starttime, s.duration, s.incoming, s.imeinumber, s.imsinumber,
                    s.celltowerid, s.otherinfo, s.tower_key, s.provider_key, s.state_key, s.first_cellid,
                    s.last_cellid, s.roaming_nw, s.call_type, s.calling_no, s.called_no, COALESCE(s.asondate, NOW())
                FROM {table} s
                WHERE COALESCE(s.is_duplicate, FALSE) = FALSE
                  AND {production_not_exists_sql('s', 't')}
                '''
            )
            inserted = cur.rowcount
            cur.execute(f'DROP TABLE IF EXISTS {table} CASCADE')
            cur.execute(
                """
                UPDATE upload_staging_batches
                SET verification_status = 'approved', verified_at = NOW(), verified_by = %s
                WHERE batch_id = %s
                """,
                (username, batch['batch_id']),
            )
            # Complete sibling pending batches that shared this filename staging table.
            cur.execute(
                """
                UPDATE upload_staging_batches
                SET verification_status = 'approved', verified_at = NOW(), verified_by = %s
                WHERE module = 'cdr'
                  AND verification_status = 'pending'
                  AND staging_tables->>'cdr' = %s
                  AND batch_id <> %s
                RETURNING document_job_id
                """,
                (username, table, batch['batch_id']),
            )
            sibling_jobs = [int(r[0]) for r in cur.fetchall()]
            for sjid in sibling_jobs:
                cur.execute(
                    """
                    UPDATE document_jobs
                    SET status = 'completed', phase = 'completed', completed_at = NOW(), updated_at = NOW()
                    WHERE job_id = %s
                    """,
                    (sjid,),
                )
            cur.execute(
                """
                UPDATE document_jobs SET status = 'completed', phase = 'completed', completed_at = NOW()
                WHERE job_id = %s
                """,
                (batch['document_job_id'],),
            )
            cur.execute(
                """
                UPDATE upload_activity_logs
                SET upload_status = 'Success',
                    verification_status = 'approved',
                    inserted_records = %s,
                    failed_records = GREATEST(COALESCE(total_records, 0) - %s, 0)
                WHERE staging_batch_id = %s OR document_job_id = %s
                """,
                (inserted, inserted, batch['batch_id'], batch['document_job_id']),
            )
            for sjid in sibling_jobs:
                cur.execute(
                    """
                    UPDATE upload_activity_logs
                    SET upload_status = 'Success', verification_status = 'approved'
                    WHERE document_job_id = %s
                    """,
                    (sjid,),
                )
        conn.commit()
    return {'ok': True, 'inserted': inserted, 'job_id': batch['document_job_id']}
