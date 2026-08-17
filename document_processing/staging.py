from __future__ import annotations
import hashlib
import json
import re
from pathlib import Path
from typing import Any, Optional
import psycopg2
from cdr_import.config import PRODUCTION_INSERT_COLUMNS
from document_processing.db import update_document_job
STAGING_SCHEMA = 'upload_staging'

def _safe_ident(name: str) -> str:
    if not re.fullmatch('[a-z][a-z0-9_]*', name):
        raise ValueError(f'Invalid staging identifier: {name}')
    return name

# API/inbox saves as "{uuid32}_{original}"; strip those so staging is keyed by
# the client filename. Repeated prefixes happen when a already-prefixed path is re-uploaded.
_UPLOAD_UUID_PREFIX_RE = re.compile(r'^(?:[0-9a-f]{32}_)+', re.IGNORECASE)


def original_cdr_basename(filename: str) -> str:
    """Client upload basename with inbox UUID prefix(es) removed."""
    base = Path(str(filename or 'file')).name
    stripped = _UPLOAD_UUID_PREFIX_RE.sub('', base)
    return stripped or base


def sanitize_cdr_filename(filename: str) -> str:
    """Build a stable, Postgres-safe token from an upload filename.

    Same client basename always maps to the same staging table so two uploads
    of the same file name append into one shared staging table
    (even when the on-disk path has a per-upload UUID prefix).
    """
    base = original_cdr_basename(filename)
    digest = hashlib.sha1(base.encode('utf-8', errors='replace')).hexdigest()[:8]
    stem = Path(base).stem.lower()
    cleaned = re.sub(r'[^a-z0-9]+', '_', stem).strip('_') or 'file'
    if not cleaned[0].isalpha():
        cleaned = 'f_' + cleaned
    # stg_cdr_ (8) + _digest (9) = 17; keep total ident <= 63
    cleaned = cleaned[:45].rstrip('_') or 'file'
    return f'{cleaned}_{digest}'

def cdr_staging_table_name(filename: str) -> str:
    return _safe_ident(f'stg_cdr_{sanitize_cdr_filename(filename)}')

def cdr_staging_table(job_id: int) -> str:
    """Legacy job-id naming (kept for older rows / callers)."""
    return _safe_ident(f'stg_cdr_{int(job_id)}')

def sdr_staging_table(job_id: int, pg_target: str) -> str:
    suffix = pg_target.lower().replace(' ', '_')
    return _safe_ident(f'stg_sdr_{job_id}_{suffix}')

def ensure_staging_schema(conn) -> None:
    with conn.cursor() as cur:
        cur.execute(f'CREATE SCHEMA IF NOT EXISTS {STAGING_SCHEMA}')

def create_cdr_staging_table(conn, filename: str) -> str:
    """Create (or reuse) filename-keyed CDR staging table."""
    ensure_staging_schema(conn)
    table = cdr_staging_table_name(filename)
    qualified = f'{STAGING_SCHEMA}.{table}'
    cols_sql = ',\n        '.join((f'{col} {_cdr_column_type(col)}' for col in PRODUCTION_INSERT_COLUMNS))
    ddl = f'''
        CREATE UNLOGGED TABLE IF NOT EXISTS {qualified} (
            staging_row_id BIGSERIAL PRIMARY KEY,
            {cols_sql},
            operator VARCHAR(20),
            source_file TEXT,
            source_row_number BIGINT,
            import_job_id BIGINT,
            is_duplicate BOOLEAN NOT NULL DEFAULT FALSE,
            duplicate_reason VARCHAR(50),
            user_edited BOOLEAN NOT NULL DEFAULT FALSE
        )
    '''
    # Legacy within-staging unique indexes — duplicates are checked only vs production.
    legacy_uq = [
        _safe_ident(f'uq_{table}_phone_other_start_call'),
        _safe_ident(f'uq_{table}_phone_other_start'),
    ]
    with conn.cursor() as cur:
        cur.execute(ddl)
        for uq_name in legacy_uq:
            cur.execute(f'DROP INDEX IF EXISTS {STAGING_SCHEMA}.{uq_name}')
    return qualified

def _cdr_column_type(col: str) -> str:
    types = {'ucid': 'BIGINT', 'phone': 'VARCHAR(25)', 'other': 'VARCHAR(50)', 'starttime': 'TIMESTAMP WITHOUT TIME ZONE NOT NULL', 'duration': 'NUMERIC(5,0) NOT NULL', 'incoming': 'SMALLINT NOT NULL', 'imeinumber': 'NUMERIC(15,0) NOT NULL', 'imsinumber': 'NUMERIC(18,0)', 'celltowerid': 'VARCHAR(50)', 'otherinfo': 'VARCHAR(50)', 'tower_key': 'NUMERIC(18,0)', 'provider_key': 'SMALLINT NOT NULL', 'state_key': 'SMALLINT', 'first_cellid': 'VARCHAR(50)', 'last_cellid': 'VARCHAR(50)', 'roaming_nw': 'VARCHAR(50)', 'call_type': 'VARCHAR(25)', 'calling_no': 'VARCHAR(50)', 'called_no': 'VARCHAR(50)', 'asondate': 'TIMESTAMP WITHOUT TIME ZONE'}
    return types.get(col, 'TEXT')

def create_sdr_staging_table(conn, job_id: int, pg_target: str, columns: list[dict[str, Any]]) -> str:
    from sdr_import.migrate import mssql_type_to_pg, normalize_pg_column
    ensure_staging_schema(conn)
    table = sdr_staging_table(job_id, pg_target)
    qualified = f'{STAGING_SCHEMA}.{table}'
    col_defs = ['staging_row_id BIGSERIAL PRIMARY KEY']
    for col in columns:
        pg_type = mssql_type_to_pg(col['data_type'], col['char_len'], col['num_precision'])
        null_sql = '' if col['nullable'] else ' NOT NULL'
        col_defs.append(f'{col["pg_name"]} {pg_type}{null_sql}')
    col_defs.extend(['is_duplicate BOOLEAN NOT NULL DEFAULT FALSE', 'duplicate_reason VARCHAR(50)', 'user_edited BOOLEAN NOT NULL DEFAULT FALSE'])
    ddl = f'CREATE UNLOGGED TABLE IF NOT EXISTS {qualified} ({", ".join(col_defs)})'
    with conn.cursor() as cur:
        cur.execute(ddl)
    return qualified

def ensure_staging_batch(conn, *, job_id: int, module: str, staging_tables: dict[str, str]) -> int:
    tables_json = json.dumps(staging_tables)
    with conn.cursor() as cur:
        cur.execute("\n            INSERT INTO upload_staging_batches (document_job_id, module, staging_tables)\n            VALUES (%s, %s, %s::jsonb)\n            ON CONFLICT (document_job_id) DO UPDATE\n            SET staging_tables = EXCLUDED.staging_tables,\n                verification_status = CASE\n                    WHEN upload_staging_batches.verification_status = 'approved' THEN 'approved'\n                    ELSE 'pending'\n                END\n            RETURNING batch_id\n            ", (job_id, module, tables_json))
        batch_id = int(cur.fetchone()[0])
        cur.execute('\n            UPDATE upload_activity_logs\n            SET staging_batch_id = %s\n            WHERE document_job_id = %s AND (staging_batch_id IS NULL OR staging_batch_id = %s)\n            ', (batch_id, job_id, batch_id))
        return batch_id

def finalize_staging_job(conn, *, job_id: int, batch_id: int, module: str, staging_tables: dict[str, str], rows_committed: int, total_rows: int) -> None:
    phase_state = {'staging_tables': staging_tables, 'verification_status': 'pending', 'staging_batch_id': batch_id}
    update_document_job(conn, job_id, status='pending_verification', phase='pending_verification', phase_state=phase_state, rows_committed=rows_committed, total_rows_estimated=total_rows)
    with conn.cursor() as cur:
        cur.execute("\n            UPDATE upload_activity_logs\n            SET upload_status = 'Pending Verification',\n                verification_status = 'pending',\n                staging_batch_id = %s,\n                total_records = %s,\n                inserted_records = 0,\n                failed_records = 0\n            WHERE document_job_id = %s\n            ", (batch_id, total_rows, job_id))

def register_staging_batch(conn, *, job_id: int, module: str, staging_tables: dict[str, str]) -> int:
    batch_id = ensure_staging_batch(conn, job_id=job_id, module=module, staging_tables=staging_tables)
    with conn.cursor() as cur:
        cur.execute('SELECT rows_committed, total_rows_estimated FROM document_jobs WHERE job_id = %s', (job_id,))
        row = cur.fetchone() or (0, 0)
    finalize_staging_job(conn, job_id=job_id, batch_id=batch_id, module=module, staging_tables=staging_tables, rows_committed=int(row[0] or 0), total_rows=int(row[1] or 0))
    return batch_id

def drop_staging_tables(conn, staging_tables: dict[str, str]) -> None:
    with conn.cursor() as cur:
        for qualified in staging_tables.values():
            if '.' in qualified:
                schema, table = qualified.split('.', 1)
                cur.execute(f'DROP TABLE IF EXISTS {_safe_ident(schema)}.{_safe_ident(table)} CASCADE')
            else:
                cur.execute(f'DROP TABLE IF EXISTS {_safe_ident(qualified)} CASCADE')

def count_pending_batches_for_cdr_table(conn, qualified_table: str, *, exclude_batch_id: Optional[int] = None) -> int:
    """How many non-finished batches still point at this shared CDR staging table."""
    with conn.cursor() as cur:
        cur.execute(
            """
            SELECT COUNT(*)
            FROM upload_staging_batches
            WHERE module = 'cdr'
              AND verification_status = 'pending'
              AND staging_tables->>'cdr' = %s
              AND (%s::bigint IS NULL OR batch_id <> %s)
            """,
            (qualified_table, exclude_batch_id, exclude_batch_id),
        )
        return int(cur.fetchone()[0] or 0)
