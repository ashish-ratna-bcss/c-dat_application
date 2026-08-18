from __future__ import annotations
import hashlib
import json
from contextlib import contextmanager
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Optional
import psycopg2
import psycopg2.extras
from cdr_import.config import load_db_config
JOBS_TABLE = 'document_jobs'

def file_sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open('rb') as handle:
        for chunk in iter(lambda: handle.read(1024 * 1024), b''):
            digest.update(chunk)
    return digest.hexdigest()

@contextmanager
def db_connection():
    cfg = load_db_config()
    conn = psycopg2.connect(host=cfg['host'], port=cfg['port'], dbname=cfg['database'], user=cfg['user'], password=cfg['password'])
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()

def ensure_schema(conn) -> None:
    """Upload schema is defined in sql/cdr_db.sql and applied during DB setup."""
    return

def utcnow() -> datetime:
    return datetime.now(timezone.utc).replace(tzinfo=None)

def fetch_document_job(conn, job_id: int) -> Optional[dict[str, Any]]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(f'SELECT * FROM {JOBS_TABLE} WHERE job_id = %s', (job_id,))
        row = cur.fetchone()
        return dict(row) if row else None

def find_job_by_hash(conn, *, module: str, file_hash: str) -> Optional[dict[str, Any]]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(
            f"""
            SELECT * FROM {JOBS_TABLE}
            WHERE module = %s AND file_sha256 = %s
            ORDER BY (status IN ('completed', 'pending_verification')) DESC, job_id DESC
            LIMIT 1
            """,
            (module, file_hash),
        )
        row = cur.fetchone()
        return dict(row) if row else None

def list_document_jobs(conn, *, module: Optional[str]=None, limit: int=50, offset: int=0) -> list[dict[str, Any]]:
    clauses = []
    params: list[Any] = []
    if module:
        clauses.append('module = %s')
        params.append(module)
    where = f'WHERE {' AND '.join(clauses)}' if clauses else ''
    params.extend([limit, offset])
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(f'\n            SELECT *\n            FROM {JOBS_TABLE}\n            {where}\n            ORDER BY job_id DESC\n            LIMIT %s OFFSET %s\n            ', params)
        return [dict(row) for row in cur.fetchall()]

def update_document_job(conn, job_id: int, **fields) -> None:
    if not fields:
        return
    if 'phase_state' in fields and isinstance(fields['phase_state'], dict):
        fields['phase_state'] = psycopg2.extras.Json(fields['phase_state'])
    assignments = []
    values = []
    for key, value in fields.items():
        assignments.append(f'{key} = %s')
        values.append(value)
    assignments.append('updated_at = NOW()')
    if fields.get('status') in ('completed', 'failed'):
        assignments.append("completed_at = CASE WHEN %s IN ('completed', 'failed') THEN NOW() ELSE completed_at END")
        values.append(fields.get('status'))
    values.append(job_id)
    with conn.cursor() as cur:
        cur.execute(f'UPDATE {JOBS_TABLE} SET {', '.join(assignments)} WHERE job_id = %s', values)
