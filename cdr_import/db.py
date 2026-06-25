from __future__ import annotations
import hashlib
from contextlib import contextmanager
from datetime import datetime, timezone
from pathlib import Path
from typing import Generator, Iterable, Optional
import psycopg2
import psycopg2.extras
from .config import JOBS_TABLE, STAGING_TABLE, load_db_config

def file_sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open('rb') as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b''):
            h.update(chunk)
    return h.hexdigest()

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
from document_processing.db import ensure_schema as ensure_document_schema

def ensure_schema(conn) -> None:
    ensure_document_schema(conn)

def get_or_create_job(conn, *, source_file: str, file_path: str, file_hash: str, operator: str, target_phone: Optional[str], dry_run: bool, batch_size: int) -> tuple[int, int, str]:
    with conn.cursor() as cur:
        cur.execute(f"\n            INSERT INTO {JOBS_TABLE} (\n                module, source_file, source_basename, file_path, file_sha256,\n                operator, target_phone, status, phase, dry_run, batch_size\n            ) VALUES ('cdr', %s, %s, %s, %s, %s, %s, 'pending', 'import', %s, %s)\n            ON CONFLICT (module, source_file, file_sha256) DO UPDATE\n            SET updated_at = NOW(),\n                file_path = EXCLUDED.file_path\n            RETURNING job_id, rows_committed, status\n            ", (source_file, Path(source_file).name, file_path, file_hash, operator, target_phone, dry_run, batch_size))
        job_id, rows_committed, status = cur.fetchone()
        return (int(job_id), int(rows_committed), status)

def update_job_progress(conn, job_id: int, *, rows_committed: int, last_source_row_no: int, header_line_no: Optional[int]=None, total_rows_estimated: Optional[int]=None, status: str='running', error_message: Optional[str]=None) -> None:
    with conn.cursor() as cur:
        cur.execute(f"\n            UPDATE {JOBS_TABLE}\n            SET rows_committed = %s,\n                last_source_row_no = %s,\n                total_rows_estimated = COALESCE(%s, total_rows_estimated),\n                status = %s,\n                error_message = %s,\n                updated_at = NOW(),\n                completed_at = CASE WHEN %s IN ('completed', 'failed') THEN NOW() ELSE completed_at END\n            WHERE job_id = %s\n            ", (rows_committed, last_source_row_no, total_rows_estimated, status, error_message, status, job_id))

def next_ucids(conn, count: int) -> list[int]:
    with conn.cursor() as cur:
        cur.execute("SELECT nextval('cdr_import_ucid_seq') FROM generate_series(1, %s)", (count,))
        return [int(row[0]) for row in cur.fetchall()]

def insert_staging_batch(conn, rows: Iterable[dict]) -> None:
    rows = list(rows)
    if not rows:
        return
    cols = list(rows[0].keys())
    values = [[row[c] for c in cols] for row in rows]
    with conn.cursor() as cur:
        psycopg2.extras.execute_values(cur, f'\n            INSERT INTO {STAGING_TABLE} ({', '.join(cols)})\n            VALUES %s\n            ', values, page_size=len(values))

def utcnow() -> datetime:
    return datetime.now(timezone.utc).replace(tzinfo=None)
