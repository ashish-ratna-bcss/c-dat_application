from __future__ import annotations
import hashlib
from contextlib import contextmanager
from datetime import datetime, timezone
from pathlib import Path
from typing import Generator, Iterable, Optional
import psycopg2
import psycopg2.extras
from .config import JOBS_TABLE, PRODUCTION_INSERT_COLUMNS, load_db_config
from document_processing.staging import create_cdr_staging_table, original_cdr_basename

def file_sha256(path: Path) -> str:
    h = hashlib.sha256()
    with path.open('rb') as f:
        for chunk in iter(lambda: f.read(1024 * 1024), b''):
            h.update(chunk)
    return h.hexdigest()

@contextmanager
def db_connection(*, fast_staging: bool = False):
    cfg = load_db_config()
    conn = psycopg2.connect(host=cfg['host'], port=cfg['port'], dbname=cfg['database'], user=cfg['user'], password=cfg['password'])
    if fast_staging:
        with conn.cursor() as cur:
            cur.execute('SET synchronous_commit TO OFF')
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
        cur.execute(
            f"SELECT job_id, rows_committed, status FROM {JOBS_TABLE} "
            f"WHERE module = 'cdr' AND file_sha256 = %s "
            f"ORDER BY (status IN ('completed', 'pending_verification')) DESC, job_id DESC LIMIT 1",
            (file_hash,),
        )
        existing = cur.fetchone()
        if existing and existing[2] in ('pending_verification', 'running'):
            cur.execute(
                f"UPDATE {JOBS_TABLE} SET updated_at = NOW(), file_path = %s WHERE job_id = %s",
                (file_path, existing[0]),
            )
            return (int(existing[0]), int(existing[1]), existing[2])
        cur.execute(f"\n            INSERT INTO {JOBS_TABLE} (\n                module, source_file, source_basename, file_path, file_sha256,\n                operator, target_phone, status, phase, dry_run, batch_size\n            ) VALUES ('cdr', %s, %s, %s, %s, %s, %s, 'pending', 'import', %s, %s)\n            ON CONFLICT (module, source_file, file_sha256) DO UPDATE\n            SET updated_at = NOW(),\n                file_path = EXCLUDED.file_path\n            RETURNING job_id, rows_committed, status\n            ", (source_file, original_cdr_basename(source_file), file_path, file_hash, operator, target_phone, dry_run, batch_size))
        job_id, rows_committed, status = cur.fetchone()
        return (int(job_id), int(rows_committed), status)

def update_job_progress(conn, job_id: int, *, rows_committed: int, last_source_row_no: int, header_line_no: Optional[int]=None, total_rows_estimated: Optional[int]=None, status: str='running', error_message: Optional[str]=None) -> None:
    with conn.cursor() as cur:
        cur.execute(f"\n            UPDATE {JOBS_TABLE}\n            SET rows_committed = %s,\n                last_source_row_no = %s,\n                total_rows_estimated = COALESCE(%s, total_rows_estimated),\n                status = %s,\n                error_message = %s,\n                updated_at = NOW(),\n                completed_at = CASE WHEN %s IN ('completed', 'failed') THEN NOW() ELSE completed_at END\n            WHERE job_id = %s\n            ", (rows_committed, last_source_row_no, total_rows_estimated, status, error_message, status, job_id))

def ensure_positive_ucid_sequence(conn, *, resync: bool = False) -> None:
    """Continue positive UCIDs after the highest UCID already in cdatpcsuspect.

    Auto-detected on each new import job (resync=True). No hardcoded start value:
    next UCID = MAX(existing production UCID) + 1, or 1 when production is empty.
    """
    with conn.cursor() as cur:
        cur.execute(
            "CREATE SEQUENCE IF NOT EXISTS cdr_import_ucid_seq START WITH 1 INCREMENT BY 1"
        )
        cur.execute('ALTER SEQUENCE cdr_import_ucid_seq INCREMENT BY 1')
        cur.execute("SELECT COALESCE(last_value, 0) FROM cdr_import_ucid_seq")
        seq_last = int(cur.fetchone()[0] or 0)
        if not resync and seq_last > 0:
            return
        cur.execute('SELECT COALESCE(MAX(ucid), 0) FROM cdatpcsuspect WHERE ucid > 0')
        max_ucid = int(cur.fetchone()[0] or 0)
        start_from = max(max_ucid, seq_last if seq_last > 0 else 0)
        cur.execute("SELECT setval('cdr_import_ucid_seq', %s, true)", (start_from,))

def next_ucids(conn, count: int) -> list[int]:
    ensure_positive_ucid_sequence(conn)
    with conn.cursor() as cur:
        cur.execute("SELECT nextval('cdr_import_ucid_seq') FROM generate_series(1, %s)", (count,))
        return [int(row[0]) for row in cur.fetchall()]

_job_staging_tables: dict[int, str] = {}


def ensure_job_staging_table(conn, job_id: int, filename: str | None = None) -> str:
    """Return filename-keyed shared CDR staging table (create if needed)."""
    if job_id in _job_staging_tables and filename is None:
        return _job_staging_tables[job_id]
    if not filename:
        # Fallback: look up basename from the job row.
        with conn.cursor() as cur:
            cur.execute(
                f"SELECT COALESCE(source_basename, source_file) FROM {JOBS_TABLE} WHERE job_id = %s",
                (job_id,),
            )
            row = cur.fetchone()
            filename = Path(row[0]).name if row and row[0] else f'job_{job_id}.csv'
    qualified = create_cdr_staging_table(conn, original_cdr_basename(filename))
    _job_staging_tables[job_id] = qualified
    return qualified


def insert_staging_batch(conn, rows: Iterable[dict], *, job_id: int, filename: str | None = None) -> int:
    """Append CDR rows into the shared filename staging table.

    Does not dedupe within the file or against existing staging rows.
    Production duplicates are flagged afterwards via refresh_cdr_staging_duplicates
    (phone + other + starttime + duration + incoming) and blocked again at promote.
    """
    rows = list(rows)
    if not rows:
        return 0
    table = ensure_job_staging_table(conn, job_id, filename)
    base_cols = [c for c in PRODUCTION_INSERT_COLUMNS if c in rows[0]]
    extra_cols = ['operator', 'source_file', 'source_row_number', 'import_job_id']
    cols = base_cols + [c for c in extra_cols if c in rows[0]]
    values = [[row.get(c) for c in cols] for row in rows]

    col_list = ', '.join(cols)
    typed_cols = []
    for c in cols:
        if c in ('starttime', 'asondate'):
            typed_cols.append(f'{c} TIMESTAMP WITHOUT TIME ZONE')
        elif c in ('ucid', 'source_row_number', 'import_job_id', 'imeinumber', 'imsinumber', 'tower_key'):
            typed_cols.append(f'{c} BIGINT')
        elif c == 'duration':
            typed_cols.append(f'{c} NUMERIC')
        elif c in ('incoming', 'provider_key', 'state_key'):
            typed_cols.append(f'{c} SMALLINT')
        else:
            typed_cols.append(f'{c} TEXT')

    with conn.cursor() as cur:
        cur.execute('DROP TABLE IF EXISTS _cdr_stg_batch')
        cur.execute(
            f'CREATE TEMP TABLE _cdr_stg_batch ({", ".join(typed_cols)}) ON COMMIT DROP'
        )
        psycopg2.extras.execute_values(
            cur,
            f'INSERT INTO _cdr_stg_batch ({col_list}) VALUES %s',
            values,
            page_size=min(len(values), 2000),
        )
        cur.execute(
            f'''
            INSERT INTO {table} ({col_list})
            SELECT {', '.join(f'b.{c}' for c in cols)}
            FROM _cdr_stg_batch b
            '''
        )
        inserted = cur.rowcount if cur.rowcount is not None and cur.rowcount >= 0 else 0
    return int(inserted)


def utcnow() -> datetime:
    return datetime.now(timezone.utc).replace(tzinfo=None)
