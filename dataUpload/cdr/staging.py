"""Create one staging table per CDR file inside schema cdatpcsuspectstagingdb."""
from __future__ import annotations

import logging
import re
from datetime import datetime
from pathlib import Path
from typing import Any

from psycopg2.extras import execute_values

from config import settings
from db import _ident, db_connection, ensure_cdatpcsuspect_schema
from cdr.preview import decode_csv_bytes, filter_new_cdr_records, mapped_cdr_records

logger = logging.getLogger("dataUpload.cdr.staging")

LIVE_TABLE = "public.cdatpcsuspect"
SAMPLE_TABLE = "cdatpcsuspect"
SCHEMA_SPEC = (
    ("ucid", "bigint", False),
    ("phone", "varchar(25)", False),
    ("other", "varchar(50)", False),
    ("starttime", "timestamp", False),
    ("duration", "numeric(5,0)", False),
    ("incoming", "smallint", False),
    ("imeinumber", "numeric(15,0)", False),
    ("imsinumber", "numeric(18,0)", True),
    ("celltowerid", "varchar(50)", True),
    ("otherinfo", "varchar(50)", True),
    ("tower_key", "numeric(18,0)", True),
    ("provider_key", "smallint", False),
    ("state_key", "smallint", True),
    ("first_cellid", "varchar(50)", True),
    ("last_cellid", "varchar(50)", True),
    ("roaming_nw", "varchar(50)", True),
    ("call_type", "varchar(25)", True),
    ("calling_no", "varchar(50)", True),
    ("called_no", "varchar(50)", True),
    ("asondate", "timestamp", True),
)
LIVE_COLUMNS = (
    "ucid",
    "phone",
    "other",
    "starttime",
    "duration",
    "incoming",
    "imeinumber",
    "imsinumber",
    "celltowerid",
    "otherinfo",
    "tower_key",
    "provider_key",
    "state_key",
    "first_cellid",
    "last_cellid",
    "roaming_nw",
    "call_type",
    "calling_no",
    "called_no",
    "asondate",
)


def _slug(text: str, max_len: int) -> str:
    cleaned = re.sub(r"[^A-Za-z0-9]+", "_", text or "").strip("_").lower()
    return (cleaned or "x")[:max_len]


def staging_table_name(username: str, filename: str, when: datetime | None = None) -> str:
    stamp = (when or datetime.now()).strftime("%Y%m%d_%H%M%S")
    user = _slug(username, 16)
    file_part = _slug(Path(filename).stem, 22)
    name = f"{user}_{file_part}_{stamp}"
    if not name[0].isalpha():
        name = "s_" + name
    return name[:63]


def _qualified(table: str) -> str:
    return f"{_ident(settings.pcsuspect_schema)}.{_ident(table)}"


def _table_exists(table: str) -> bool:
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT 1
                FROM information_schema.tables
                WHERE table_schema = %s AND table_name = %s
                """,
                (settings.pcsuspect_schema, table),
            )
            return cur.fetchone() is not None


def drop_staging_table(table: str) -> None:
    """Remove a per-file staging table after it has been copied to live."""
    if not table or table == SAMPLE_TABLE:
        return
    qualified = _qualified(table)
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(f"DROP TABLE IF EXISTS {qualified}")
    logger.info("Dropped staging table %s", qualified)


def unique_staging_table_name(username: str, filename: str) -> str:
    base = staging_table_name(username, filename)
    if base != SAMPLE_TABLE and not _table_exists(base):
        return base
    start = 2 if base == SAMPLE_TABLE or _table_exists(base) else 2
    for index in range(start, 100):
        suffix = f"_{index}"
        name = (base[: 63 - len(suffix)] + suffix)
        if name != SAMPLE_TABLE and not _table_exists(name):
            return name
    raise ValueError("Could not allocate a unique staging table name.")


def schema_columns() -> list[dict[str, Any]]:
    return [
        {"name": name, "type": col_type, "nullable": nullable}
        for name, col_type, nullable in SCHEMA_SPEC
    ]


def drop_unused_template_table() -> None:
    """Remove empty cdatpcsuspectstagingdb.cdatpcsuspect if an older run created it."""
    ensure_cdatpcsuspect_schema()
    qualified = _qualified(SAMPLE_TABLE)
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(f"DROP TABLE IF EXISTS {qualified}")
    logger.info("Dropped unused template table %s if it existed", qualified)


def json_schema_row(record: dict[str, Any], ucid: int) -> dict[str, Any]:
    from decimal import Decimal

    row: dict[str, Any] = {"ucid": ucid}
    for name, _col_type, _nullable in SCHEMA_SPEC:
        if name == "ucid":
            continue
        value = record.get(name)
        if isinstance(value, datetime):
            value = value.strftime("%Y-%m-%d %H:%M:%S")
        elif isinstance(value, Decimal):
            value = int(value) if value == value.to_integral_value() else float(value)
        row[name] = value
    return row


def create_staging_table(table: str) -> str:
    """Clone public.cdatpcsuspect column types into cdatpcsuspectstagingdb.<table>."""
    ensure_cdatpcsuspect_schema()
    qualified = _qualified(table)
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                f"""
                CREATE TABLE {qualified} (
                    LIKE {LIVE_TABLE} INCLUDING DEFAULTS INCLUDING GENERATED
                )
                """
            )
    logger.info("Created staging table %s", qualified)
    return qualified


def _rows_for_insert(records: list[dict[str, Any]]) -> list[tuple]:
    now = datetime.now().replace(microsecond=0)
    out: list[tuple] = []
    for index, rec in enumerate(records, start=1):
        out.append(
            (
                index,
                rec.get("phone") or "",
                rec.get("other") or None,
                rec["starttime"],
                rec.get("duration") or 0,
                rec.get("incoming") or 0,
                rec.get("imeinumber") or 0,
                rec.get("imsinumber"),
                rec.get("celltowerid"),
                rec.get("otherinfo"),
                rec.get("tower_key"),
                rec.get("provider_key") or 0,
                rec.get("state_key"),
                rec.get("first_cellid"),
                rec.get("last_cellid"),
                rec.get("roaming_nw"),
                rec.get("call_type"),
                rec.get("calling_no"),
                rec.get("called_no"),
                rec.get("asondate") or now,
            )
        )
    return out


def insert_staging_rows(
    table: str,
    records: list[dict[str, Any]],
    progress: Any | None = None,
) -> int:
    if not records:
        return 0
    qualified = _qualified(table)
    cols = ", ".join(LIVE_COLUMNS)
    sql = f"INSERT INTO {qualified} ({cols}) VALUES %s"
    values = _rows_for_insert(records)
    total = len(values)
    page = 1000
    done = 0
    with db_connection() as conn:
        with conn.cursor() as cur:
            for start in range(0, total, page):
                batch = values[start : start + page]
                execute_values(cur, sql, batch, page_size=page)
                done += len(batch)
                if progress:
                    progress(done, total)
    return total


def promote_staging_to_live(
    table: str,
    progress: Any | None = None,
) -> int:
    """Copy a staging table into public.cdatpcsuspect with continuing ucid values."""
    qualified = _qualified(table)
    dest_cols = ", ".join(LIVE_COLUMNS)
    lock_key = 872001
    page = 1000
    conn = None
    try:
        from db import connect

        conn = connect()
        conn.autocommit = False
        cur = conn.cursor()
        cur.execute("SELECT pg_advisory_lock(%s)", (lock_key,))
        cur.execute("SELECT COALESCE(MAX(ucid), 0) FROM public.cdatpcsuspect")
        last_id = int(cur.fetchone()[0] or 0)
        cur.execute(f"SELECT COUNT(*) FROM {qualified}")
        total = int(cur.fetchone()[0] or 0)
        if total == 0:
            if table != SAMPLE_TABLE:
                cur.execute(f"DROP TABLE IF EXISTS {qualified}")
            conn.commit()
            return 0
        copied = 0
        offset = 0
        other_cols = [c for c in LIVE_COLUMNS if c != "ucid"]
        select_cols = ", ".join(["(%s + t.ucid) AS ucid"] + [f"t.{c}" for c in other_cols])
        while offset < total:
            upper = min(offset + page, total)
            cur.execute(
                f"""
                INSERT INTO public.cdatpcsuspect ({dest_cols})
                SELECT {select_cols}
                FROM {qualified} t
                WHERE t.ucid > %s AND t.ucid <= %s
                  AND NOT EXISTS (
                      SELECT 1
                      FROM public.cdatpcsuspect live
                      WHERE live.phone = t.phone
                        AND NULLIF(BTRIM(COALESCE(live.other, '')), '')
                            IS NOT DISTINCT FROM NULLIF(BTRIM(COALESCE(t.other, '')), '')
                        AND live.starttime = t.starttime
                        AND live.duration IS NOT DISTINCT FROM t.duration
                        AND live.incoming IS NOT DISTINCT FROM t.incoming
                  )
                  AND NOT EXISTS (
                      SELECT 1
                      FROM {qualified} earlier
                      WHERE earlier.phone = t.phone
                        AND NULLIF(BTRIM(COALESCE(earlier.other, '')), '')
                            IS NOT DISTINCT FROM NULLIF(BTRIM(COALESCE(t.other, '')), '')
                        AND earlier.starttime = t.starttime
                        AND earlier.duration IS NOT DISTINCT FROM t.duration
                        AND earlier.incoming IS NOT DISTINCT FROM t.incoming
                        AND earlier.ucid < t.ucid
                  )
                """,
                (last_id, offset, upper),
            )
            copied += int(cur.rowcount or 0)
            offset = upper
            if progress:
                progress(min(offset, total), total)
        if table != SAMPLE_TABLE:
            cur.execute(f"DROP TABLE IF EXISTS {qualified}")
        conn.commit()
        logger.info("Promoted %s rows from %s and dropped the staging table", copied, qualified)
        return copied
    except Exception:
        if conn is not None:
            conn.rollback()
        raise
    finally:
        if conn is not None:
            try:
                with conn.cursor() as unlock:
                    unlock.execute("SELECT pg_advisory_unlock(%s)", (lock_key,))
                conn.commit()
            except Exception:
                pass
            conn.close()


def stage_csv_bytes(
    raw: bytes,
    *,
    filename: str,
    username: str,
) -> dict[str, Any]:
    text = decode_csv_bytes(raw)
    records = mapped_cdr_records(text, filename=filename)
    if not records:
        raise ValueError("No call records could be mapped into the cdatpcsuspect schema.")
    records, skipped = filter_new_cdr_records(records)
    if not records:
        raise ValueError(
            "No new records to stage. "
            f"Skipped {skipped['duplicates']} duplicate(s), "
            f"{skipped['already_in_db']} already in the database, "
            f"{skipped['already_in_staging']} already in staging."
        )

    table = unique_staging_table_name(username, filename)
    create_staging_table(table)
    try:
        inserted = insert_staging_rows(table, records)
    except Exception:
        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(f"DROP TABLE IF EXISTS {_qualified(table)}")
        raise
    qualified = f"{settings.pcsuspect_schema}.{table}"
    sample_rows = [json_schema_row(rec, index) for index, rec in enumerate(records[:20], start=1)]
    logger.info(
        "Staged %s new rows into %s (skipped %s duplicate(s), %s already in DB)",
        inserted,
        qualified,
        skipped["duplicates"],
        skipped["already_in_db"],
    )
    return {
        "ok": True,
        "schema": settings.pcsuspect_schema,
        "table": table,
        "qualified_table": qualified,
        "inserted": inserted,
        "filename": Path(filename).name,
        "username": username,
        "columns": schema_columns(),
        "schema_columns": schema_columns(),
        "schema_rows": sample_rows,
        "sample_rows": sample_rows,
        "message": f"Staged {inserted} rows into {qualified}.",
    }
