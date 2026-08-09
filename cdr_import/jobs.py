from __future__ import annotations
from typing import Any, Optional
import psycopg2.extras
from document_processing.db import JOBS_TABLE
from document_processing.staging import original_cdr_basename

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
        cur.execute(
            f"""
            INSERT INTO {JOBS_TABLE} (
                module, source_file, source_basename, file_path, file_sha256,
                operator, target_phone, status, phase, dry_run, batch_size,
                total_rows_estimated
            ) VALUES ('cdr', %s, %s, %s, %s, %s, %s, 'queued', 'import', %s, %s, %s)
            ON CONFLICT (module, source_file, file_sha256) DO UPDATE
            SET updated_at = NOW(),
                file_path = EXCLUDED.file_path,
                status = CASE
                    WHEN {JOBS_TABLE}.status IN ('completed', 'failed', 'pending_verification') THEN 'queued'
                    ELSE {JOBS_TABLE}.status
                END,
                phase = 'import',
                rows_committed = CASE
                    WHEN {JOBS_TABLE}.status IN ('completed', 'failed', 'pending_verification') THEN 0
                    ELSE {JOBS_TABLE}.rows_committed
                END,
                error_message = CASE
                    WHEN {JOBS_TABLE}.status IN ('completed', 'failed', 'pending_verification') THEN NULL
                    ELSE {JOBS_TABLE}.error_message
                END
            RETURNING job_id
            """,
            (source_file, original_cdr_basename(source_file), file_path, file_hash, operator, target_phone, dry_run, batch_size, total_rows_estimated),
        )
        return int(cur.fetchone()[0])
