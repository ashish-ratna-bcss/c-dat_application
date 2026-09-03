"""Background CDR pipeline: stage CSV files and promote staging tables to live.

Up to two jobs run at the same time; extra jobs wait in the executor queue.
FastAPI starts this worker on boot.
"""
from __future__ import annotations

import logging
import threading
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime
from pathlib import Path
from typing import Any, Optional

from psycopg2.extras import RealDictCursor

from config import settings
from db import _ident, db_connection, ensure_cdatpcsuspect_schema, ensure_upload_schema
from .preview import decode_csv_bytes, filter_new_cdr_records, mapped_cdr_records
from .staging import (
    LIVE_COLUMNS,
    SAMPLE_TABLE,
    create_staging_table,
    drop_staging_table,
    insert_staging_rows,
    json_schema_row,
    promote_staging_to_live,
    schema_columns,
    unique_staging_table_name,
    _qualified,
    _table_exists,
)

logger = logging.getLogger("dataUpload.pipeline")

MAX_WORKERS = 2
JOBS_TABLE = "cdr_pipeline_jobs"
LIVE_TABLE = "public.cdatpcsuspect"

_executor: ThreadPoolExecutor | None = None
_lock = threading.Lock()


def _jobs_qualified() -> str:
    return f"{_ident(settings.upload_schema)}.{_ident(JOBS_TABLE)}"


def _logs_qualified() -> str:
    return f"{_ident(settings.upload_schema)}.{_ident('upload_activity_logs')}"


_JOB_COLUMNS = (
    ("source_records", "BIGINT NOT NULL DEFAULT 0"),
    ("duplicate_records", "BIGINT NOT NULL DEFAULT 0"),
    ("already_in_db", "BIGINT NOT NULL DEFAULT 0"),
    ("already_in_staging", "BIGINT NOT NULL DEFAULT 0"),
    ("new_records", "BIGINT NOT NULL DEFAULT 0"),
    ("staging_dropped", "BOOLEAN NOT NULL DEFAULT FALSE"),
    ("completed_at", "TIMESTAMPTZ"),
)


def _ensure_job_columns(cur: Any) -> None:
    schema = settings.upload_schema
    for name, ddl in _JOB_COLUMNS:
        cur.execute(
            """
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = %s AND table_name = %s AND column_name = %s
            """,
            (schema, JOBS_TABLE, name),
        )
        if cur.fetchone() is None:
            cur.execute(
                f"ALTER TABLE {_jobs_qualified()} ADD COLUMN {_ident(name)} {ddl}"
            )
            logger.info("Added %s.%s.%s", schema, JOBS_TABLE, name)
    cur.execute(
        f"""
        CREATE INDEX IF NOT EXISTS idx_cdr_pipeline_jobs_phase
        ON {_jobs_qualified()} (phase)
        """
    )
    cur.execute(
        f"""
        CREATE INDEX IF NOT EXISTS idx_cdr_pipeline_jobs_log
        ON {_jobs_qualified()} (log_id)
        """
    )


def ensure_jobs_table() -> None:
    ensure_upload_schema()
    ensure_cdatpcsuspect_schema()
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                f"""
                CREATE TABLE IF NOT EXISTS {_jobs_qualified()} (
                    job_id              BIGSERIAL PRIMARY KEY,
                    username            VARCHAR(100) NOT NULL DEFAULT 'user',
                    filename            TEXT NOT NULL,
                    file_path           TEXT,
                    file_size           BIGINT NOT NULL DEFAULT 0,
                    ip_address          VARCHAR(45),
                    module_name         VARCHAR(50) NOT NULL DEFAULT 'CDR',
                    staging_table       VARCHAR(63),
                    staging_dropped     BOOLEAN NOT NULL DEFAULT FALSE,
                    phase               VARCHAR(30) NOT NULL DEFAULT 'queued',
                    progress            SMALLINT NOT NULL DEFAULT 0,
                    progress_label      VARCHAR(80) NOT NULL DEFAULT 'Queued',
                    source_records      BIGINT NOT NULL DEFAULT 0,
                    duplicate_records   BIGINT NOT NULL DEFAULT 0,
                    already_in_db       BIGINT NOT NULL DEFAULT 0,
                    already_in_staging  BIGINT NOT NULL DEFAULT 0,
                    new_records         BIGINT NOT NULL DEFAULT 0,
                    total_records       BIGINT NOT NULL DEFAULT 0,
                    inserted_records    BIGINT NOT NULL DEFAULT 0,
                    failed_records      BIGINT NOT NULL DEFAULT 0,
                    error_message       TEXT,
                    log_id              BIGINT,
                    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
                    completed_at        TIMESTAMPTZ
                )
                """
            )
            _ensure_job_columns(cur)


def start_pipeline() -> None:
    global _executor
    ensure_jobs_table()
    _drop_inserted_staging_tables()
    with _lock:
        if _executor is None:
            _executor = ThreadPoolExecutor(max_workers=MAX_WORKERS, thread_name_prefix="cdr-pipe")
            logger.info("CDR pipeline started with %s workers", MAX_WORKERS)


def _drop_inserted_staging_tables() -> None:
    """Remove staging tables that already finished Insert DB (including leftover ones)."""
    try:
        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    f"""
                    SELECT DISTINCT staging_table
                    FROM {_jobs_qualified()}
                    WHERE phase = 'inserted'
                      AND staging_table IS NOT NULL
                      AND staging_table <> %s
                    """,
                    (SAMPLE_TABLE,),
                )
                names = [row[0] for row in cur.fetchall() if row[0]]
        for name in names:
            drop_staging_table(name)
        if names:
            with db_connection() as conn:
                with conn.cursor() as cur:
                    cur.execute(
                        f"""
                        UPDATE {_jobs_qualified()}
                        SET staging_dropped = TRUE, updated_at = NOW()
                        WHERE phase = 'inserted'
                          AND COALESCE(staging_dropped, FALSE) = FALSE
                        """
                    )
    except Exception:
        logger.exception("Could not drop leftover staging tables after insert")


def stop_pipeline() -> None:
    global _executor
    with _lock:
        if _executor is not None:
            _executor.shutdown(wait=False, cancel_futures=False)
            _executor = None


def _update_job(job_id: int, **fields: Any) -> None:
    if not fields:
        return
    fields["updated_at"] = datetime.now()
    assignments = ", ".join(f"{key} = %s" for key in fields)
    values = list(fields.values()) + [job_id]
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                f"UPDATE {_jobs_qualified()} SET {assignments} WHERE job_id = %s",
                values,
            )
            cur.execute(
                f"SELECT log_id, staging_table FROM {_jobs_qualified()} WHERE job_id = %s",
                (job_id,),
            )
            row = cur.fetchone()
            log_id = int(row[0]) if row and row[0] else 0
            staging_table = row[1] if row else None
            if log_id:
                _sync_activity_log(cur, log_id, staging_table, fields)


def _sync_activity_log(cur: Any, log_id: int, staging_table: Optional[str], fields: dict[str, Any]) -> None:
    mapping: dict[str, Any] = {}
    phase = fields.get("phase")
    if phase == "staging" or phase == "queued" or phase == "inserting":
        mapping["upload_status"] = "Processing"
    elif phase == "staged":
        mapping["upload_status"] = "Pending Verification"
    elif phase == "inserted":
        mapping["upload_status"] = "Success"
    elif phase == "failed":
        mapping["upload_status"] = "Failed"
    for key in (
        "total_records",
        "inserted_records",
        "failed_records",
        "source_records",
        "duplicate_records",
        "already_in_db",
        "already_in_staging",
        "new_records",
        "staging_dropped",
        "completed_at",
    ):
        if key in fields:
            mapping[key] = fields[key]
    if "error_message" in fields:
        mapping["error_reason"] = fields["error_message"]
    if staging_table:
        mapping["table_name"] = staging_table
    if not mapping:
        return
    sets = ", ".join(f"{k} = %s" for k in mapping)
    cur.execute(
        f"UPDATE {_logs_qualified()} SET {sets} WHERE id = %s",
        list(mapping.values()) + [log_id],
    )


def _create_activity_log(
    *,
    username: str,
    filename: str,
    file_size: int,
    ip_address: str,
    job_id: int,
) -> int:
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                f"""
                INSERT INTO {_logs_qualified()} (
                    username, module_name, file_name, file_size,
                    total_records, inserted_records, failed_records,
                    upload_status, ip_address, db_name, table_name, document_job_id
                ) VALUES (
                    %s, 'CDR', %s, %s, 0, 0, 0, 'Processing', %s, %s, %s, %s
                )
                RETURNING id
                """,
                (
                    username,
                    filename,
                    file_size,
                    ip_address or None,
                    settings.db_name,
                    settings.pcsuspect_schema,
                    job_id,
                ),
            )
            return int(cur.fetchone()[0])


def create_stage_job(
    *,
    filename: str,
    file_path: str,
    file_size: int,
    username: str,
    ip_address: str = "",
) -> dict[str, Any]:
    ensure_jobs_table()
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                f"""
                INSERT INTO {_jobs_qualified()} (
                    username, filename, file_path, file_size, ip_address,
                    phase, progress, progress_label
                ) VALUES (%s, %s, %s, %s, %s, 'queued', 0, 'Queued')
                RETURNING job_id
                """,
                (username, filename, file_path, file_size, ip_address or None),
            )
            job_id = int(cur.fetchone()[0])
    log_id = _create_activity_log(
        username=username,
        filename=filename,
        file_size=file_size,
        ip_address=ip_address,
        job_id=job_id,
    )
    _update_job(job_id, log_id=log_id)
    _submit(job_id, "stage")
    return {
        "ok": True,
        "job_id": job_id,
        "log_id": log_id,
        "phase": "queued",
        "progress": 0,
        "progress_label": "Queued",
        "message": "Staging queued. Watch progress on Upload History.",
    }


def enqueue_insert(job_id: int) -> dict[str, Any]:
    job = get_job(job_id)
    if not job:
        raise ValueError("Job was not found.")
    if job["phase"] == "inserting":
        return {"ok": True, "job_id": job_id, "phase": "inserting", "message": "Insert is already running."}
    if job["phase"] == "inserted":
        raise ValueError("This job is already inserted into cdatpcsuspect.")
    if job["phase"] != "staged" or not job.get("staging_table"):
        raise ValueError("Staging is not finished yet.")
    _update_job(
        job_id,
        phase="queued",
        progress=0,
        progress_label="Queued for insert",
        error_message=None,
    )
    _submit(job_id, "insert")
    return {
        "ok": True,
        "job_id": job_id,
        "phase": "queued",
        "progress_label": "Queued for insert",
        "message": "Insert queued. Watch progress on Upload History.",
    }


def _submit(job_id: int, kind: str) -> None:
    start_pipeline()
    assert _executor is not None
    _executor.submit(_run_job, job_id, kind)


def _run_job(job_id: int, kind: str) -> None:
    try:
        if kind == "stage":
            _run_stage(job_id)
        else:
            _run_insert(job_id)
    except Exception as exc:
        logger.exception("Pipeline job %s (%s) failed", job_id, kind)
        _update_job(
            job_id,
            phase="failed",
            progress=0,
            progress_label="Failed",
            error_message=str(exc),
            failed_records=1,
        )


def _run_stage(job_id: int) -> None:
    job = get_job(job_id)
    if not job:
        return
    path = Path(job["file_path"] or "")
    if not path.is_file():
        raise ValueError("Uploaded file is no longer on disk.")

    _update_job(job_id, phase="staging", progress=2, progress_label="Uploading to staging")
    raw = path.read_bytes()
    records = mapped_cdr_records(decode_csv_bytes(raw), filename=job["filename"])
    if not records:
        raise ValueError("No call records could be mapped into the cdatpcsuspect schema.")
    records, skipped = filter_new_cdr_records(records)
    _update_job(
        job_id,
        source_records=skipped["source"],
        duplicate_records=skipped["duplicates"],
        already_in_db=skipped["already_in_db"],
        already_in_staging=skipped["already_in_staging"],
        new_records=skipped["new"],
        total_records=skipped["source"],
        failed_records=skipped["duplicates"] + skipped["already_in_db"] + skipped["already_in_staging"],
    )
    if not records:
        raise ValueError(
            "No new records to stage. "
            f"Skipped {skipped['duplicates']} duplicate(s), "
            f"{skipped['already_in_db']} already in the database, "
            f"{skipped['already_in_staging']} already in staging."
        )

    table = unique_staging_table_name(job["username"], job["filename"])
    create_staging_table(table)
    _update_job(
        job_id,
        staging_table=table,
        staging_dropped=False,
        total_records=skipped["source"],
        new_records=len(records),
        progress=8,
        progress_label="Uploading to staging",
    )

    def on_progress(done: int, total: int) -> None:
        pct = 8 + int((done / max(total, 1)) * 90)
        _update_job(
            job_id,
            progress=min(pct, 98),
            inserted_records=done,
            progress_label="Uploading to staging",
        )

    try:
        inserted = insert_staging_rows(table, records, progress=on_progress)
    except Exception:
        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(f"DROP TABLE IF EXISTS {_qualified(table)}")
        raise

    logger.info(
        "Job %s staged %s new rows (skipped %s duplicate(s), %s already in DB, %s already in staging)",
        job_id,
        inserted,
        skipped["duplicates"],
        skipped["already_in_db"],
        skipped["already_in_staging"],
    )
    skipped_total = (
        skipped["duplicates"] + skipped["already_in_db"] + skipped["already_in_staging"]
    )
    _update_job(
        job_id,
        phase="staged",
        progress=100,
        progress_label="Staged",
        total_records=skipped["source"],
        new_records=inserted,
        inserted_records=0,
        failed_records=skipped_total,
        staging_table=table,
        staging_dropped=False,
    )


def _run_insert(job_id: int) -> None:
    job = get_job(job_id)
    if not job or not job.get("staging_table"):
        raise ValueError("Staging table is missing.")
    table = str(job["staging_table"])
    if table == SAMPLE_TABLE:
        raise ValueError("Cannot promote the sample structure table.")

    _update_job(job_id, phase="inserting", progress=2, progress_label="Inserting to DB")

    def on_progress(done: int, total: int) -> None:
        pct = 5 + int((done / max(total, 1)) * 90)
        _update_job(
            job_id,
            progress=min(pct, 98),
            inserted_records=done,
            progress_label="Inserting to DB",
        )

    copied = promote_staging_to_live(table, progress=on_progress)
    _update_job(
        job_id,
        phase="inserted",
        progress=100,
        progress_label="Inserted to DB",
        inserted_records=copied,
        staging_dropped=True,
        completed_at=datetime.now(),
    )


def _json_job(row: dict[str, Any]) -> dict[str, Any]:
    out: dict[str, Any] = {}
    for key, value in row.items():
        if isinstance(value, datetime):
            out[key] = value.strftime("%Y-%m-%d %H:%M:%S")
        else:
            out[key] = value
    phase = out.get("phase") or "queued"
    label = out.get("progress_label") or ""
    pct = int(out.get("progress") or 0)
    if phase in {"staging", "queued"} and label.lower().startswith("upload"):
        out["status_text"] = f"{label} {pct}%"
    elif phase in {"inserting", "queued"} and "insert" in label.lower():
        out["status_text"] = f"{label} {pct}%"
    elif phase == "queued":
        out["status_text"] = f"Queued {pct}%"
    elif phase == "staging":
        out["status_text"] = f"Uploading to staging {pct}%"
    elif phase == "inserting":
        out["status_text"] = f"Inserting to DB {pct}%"
    elif phase == "staged":
        out["status_text"] = "Staged"
    elif phase == "inserted":
        out["status_text"] = "Inserted to DB"
    elif phase == "failed":
        out["status_text"] = "Failed"
    else:
        out["status_text"] = label or phase
    out["qualified_table"] = (
        f"{settings.pcsuspect_schema}.{out['staging_table']}" if out.get("staging_table") else None
    )
    out["can_view"] = (
        bool(out.get("staging_table"))
        and not out.get("staging_dropped")
        and phase in {"staged", "inserting"}
    )
    out["can_insert"] = phase == "staged"
    return out


def get_job(job_id: int) -> Optional[dict[str, Any]]:
    ensure_jobs_table()
    with db_connection() as conn:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            cur.execute(f"SELECT * FROM {_jobs_qualified()} WHERE job_id = %s", (job_id,))
            row = cur.fetchone()
    return dict(row) if row else None


def list_jobs(job_ids: Optional[list[int]] = None, limit: int = 50) -> list[dict[str, Any]]:
    ensure_jobs_table()
    with db_connection() as conn:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            if job_ids:
                cur.execute(
                    f"SELECT * FROM {_jobs_qualified()} WHERE job_id = ANY(%s) ORDER BY job_id DESC",
                    (job_ids,),
                )
            else:
                cur.execute(
                    f"SELECT * FROM {_jobs_qualified()} ORDER BY job_id DESC LIMIT %s",
                    (limit,),
                )
            rows = cur.fetchall()
    return [_json_job(dict(row)) for row in rows]


def job_rows(job_id: int, *, limit: int = 200, offset: int = 0) -> dict[str, Any]:
    job = get_job(job_id)
    if not job or not job.get("staging_table"):
        raise ValueError("Staging table is not ready yet.")
    table = str(job["staging_table"])
    if (job.get("phase") or "") == "inserted" or not _table_exists(table):
        raise ValueError("This file is already in the live database. The staging table was removed.")
    qualified = _qualified(table)
    cols = ", ".join(LIVE_COLUMNS)
    with db_connection() as conn:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            cur.execute(f"SELECT COUNT(*) AS n FROM {qualified}")
            total = int(cur.fetchone()["n"])
            cur.execute(
                f"SELECT {cols} FROM {qualified} ORDER BY ucid LIMIT %s OFFSET %s",
                (limit, offset),
            )
            rows = cur.fetchall()
    sample = []
    for row in rows:
        rec = dict(row)
        ucid = int(rec.get("ucid") or 0)
        sample.append(json_schema_row(rec, ucid))
    return {
        "ok": True,
        "job_id": job_id,
        "qualified_table": f"{settings.pcsuspect_schema}.{table}",
        "total": total,
        "offset": offset,
        "limit": limit,
        "columns": schema_columns(),
        "rows": sample,
    }


def run() -> None:
    """Keep a worker process alive (optional; FastAPI already starts the pool)."""
    settings.configure_logging()
    start_pipeline()
    print(f"CDR pipeline worker running ({MAX_WORKERS} parallel jobs). Ctrl+C to stop.")
    try:
        threading.Event().wait()
    except KeyboardInterrupt:
        print("Stopping pipeline…")
        stop_pipeline()


if __name__ == "__main__":
    run()
