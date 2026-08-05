from __future__ import annotations
from pathlib import Path
from typing import Any, Optional
from .db import JOBS_TABLE

def create_document_job(conn, *, module: str, source_file: str, file_path: str, file_hash: str, batch_size: int, dry_run: bool=False, operator: Optional[str]=None, target_phone: Optional[str]=None, mssql_database: Optional[str]=None, phase: Optional[str]=None, status: str='queued', total_rows_estimated: Optional[int]=None) -> int:
    with conn.cursor() as cur:
        cur.execute(f"\n            INSERT INTO {JOBS_TABLE} (\n                module, source_file, source_basename, file_path, file_sha256,\n                operator, target_phone, mssql_database, status, phase,\n                dry_run, batch_size, total_rows_estimated\n            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)\n            ON CONFLICT (module, source_file, file_sha256) DO UPDATE\n            SET updated_at = NOW(),\n                file_path = EXCLUDED.file_path,\n                status = CASE\n                    WHEN {JOBS_TABLE}.status = 'completed' THEN {JOBS_TABLE}.status\n                    ELSE EXCLUDED.status\n                END,\n                phase = CASE\n                    WHEN {JOBS_TABLE}.status = 'completed' THEN {JOBS_TABLE}.phase\n                    ELSE EXCLUDED.phase\n                END\n            RETURNING job_id\n            ", (module, source_file, Path(source_file).name, file_path, file_hash, operator, target_phone, mssql_database, status, phase, dry_run, batch_size, total_rows_estimated))
        return int(cur.fetchone()[0])
