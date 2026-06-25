from __future__ import annotations
from pathlib import Path
from typing import Any, Optional
import psycopg2.extras
from document_processing.db import JOBS_TABLE

def fetch_job(conn, job_id: int) -> Optional[dict[str, Any]]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(f"SELECT * FROM {JOBS_TABLE} WHERE job_id = %s AND module = 'cdr'", (job_id,))
        row = cur.fetchone()
        return dict(row) if row else None

def list_jobs(conn, *, limit: int=50, offset: int=0) -> list[dict[str, Any]]:
    with conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
        cur.execute(f"\n            SELECT *\n            FROM {JOBS_TABLE}\n            WHERE module = 'cdr'\n            ORDER BY job_id DESC\n            LIMIT %s OFFSET %s\n            ", (limit, offset))
        return [dict(row) for row in cur.fetchall()]

def create_queued_job(conn, *, source_file: str, file_path: str, file_hash: str, operator: str, target_phone: Optional[str], batch_size: int, total_rows_estimated: int, header_line_no: int, dry_run: bool=False) -> int:
    with conn.cursor() as cur:
        cur.execute(f"\n            INSERT INTO {JOBS_TABLE} (\n                module, source_file, source_basename, file_path, file_sha256,\n                operator, target_phone, status, phase, dry_run, batch_size,\n                total_rows_estimated\n            ) VALUES ('cdr', %s, %s, %s, %s, %s, %s, 'queued', 'import', %s, %s, %s)\n            ON CONFLICT (module, source_file, file_sha256) DO UPDATE\n            SET updated_at = NOW(),\n                file_path = EXCLUDED.file_path,\n                status = CASE\n                    WHEN {JOBS_TABLE}.status = 'completed' THEN {JOBS_TABLE}.status\n                    ELSE 'queued'\n                END\n            RETURNING job_id\n            ", (source_file, Path(source_file).name, file_path, file_hash, operator, target_phone, dry_run, batch_size, total_rows_estimated))
        return int(cur.fetchone()[0])
