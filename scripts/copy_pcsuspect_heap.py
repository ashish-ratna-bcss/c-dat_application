#!/usr/bin/env python3
"""Copy the full MSSQL pcsuspect heap into public.cdatpcsuspect_new.

Does not read or write public.cdatpcsuspect. No UCID filter.
Reads the heap in ASONDATE day windows (that column is indexed on MSSQL) so the
job can resume without ORDER BY UCID.

Swap names only after dest is near 1,055,080,912 rows, then build indexes.
"""
from __future__ import annotations

import os
import signal
import sys
from datetime import date, datetime, timedelta, timezone
from pathlib import Path
from typing import Any

import psycopg2.extras

sys.path.insert(0, str(Path(__file__).resolve().parent))
import migrate_copy as mc

DEST_TABLE = "cdatpcsuspect_new"
LIVE_TABLE = "cdatpcsuspect"
PG_DB = "CDATDUPL_DB"
MSSQL_TARGET = 1_055_080_912
LOG_EVERY = 100_000

_stop = False


def _handle_stop(signum: int, _frame: Any) -> None:
    global _stop
    _stop = True
    print(f"\nSTOP signal {signum}: finishing current batch then saving checkpoint...", flush=True)


def as_date(value: Any) -> date:
    if isinstance(value, datetime):
        return value.date()
    if isinstance(value, date):
        return value
    return datetime.fromisoformat(str(value)).date()


def ensure_dest_table(pg) -> None:
    with pg.cursor() as cur:
        cur.execute(
            """
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = %s
            """,
            (DEST_TABLE,),
        )
        if cur.fetchone() is None:
            cur.execute(
                f"""
                CREATE TABLE public.{DEST_TABLE} (
                    LIKE public.{LIVE_TABLE}
                    INCLUDING DEFAULTS INCLUDING CONSTRAINTS
                )
                """
            )
            cur.execute(
                f"""
                COMMENT ON TABLE public.{DEST_TABLE} IS
                'Full MSSQL heap recopy. App must keep using {LIVE_TABLE} until swap.'
                """
            )
            print(f"created public.{DEST_TABLE} (LIKE {LIVE_TABLE}, no indexes)", flush=True)
        cur.execute(
            f"ALTER TABLE public.{DEST_TABLE} SET (autovacuum_enabled = false)"
        )
    pg.commit()


def mssql_asondate_range(mcur, src: str) -> tuple[date | None, date | None]:
    mcur.execute(
        f"SELECT MIN(ASONDATE), MAX(ASONDATE) FROM dbo.[{src}] WITH (NOLOCK) WHERE ASONDATE IS NOT NULL"
    )
    lo, hi = mcur.fetchone()
    if lo is None or hi is None:
        return None, None
    return as_date(lo), as_date(hi)


def insert_rows(pg, insert_sql: str, pairs, pg_cols, defaults, rows) -> int:
    out = []
    for row in rows:
        vals = []
        for (_m_name, p_name), val in zip(pairs, row):
            if val is None and not next(
                c["nullable"] for c in pg_cols if c["name"] == p_name
            ):
                val = defaults.get(p_name, defaults.get(mc.norm(p_name), ""))
            vals.append(mc.adapt_value(val, p_name))
        out.append(tuple(vals))
    if not out:
        return 0
    with pg.cursor() as pcur:
        psycopg2.extras.execute_values(
            pcur, insert_sql, out, page_size=min(len(out), 2000)
        )
    pg.commit()
    return len(out)


def copy_query(
    *,
    pg,
    mcur,
    select_sql: str,
    insert_sql: str,
    pairs,
    pg_cols,
    defaults,
    batch_size: int,
    state: dict[str, Any],
    cp_path: Path,
    existing: int,
    started: datetime,
    last_log_at: list[int],
    sql: str,
    params: tuple[Any, ...],
) -> int:
    if params:
        mcur.execute(sql, params)
    else:
        mcur.execute(sql)
    copied = 0
    while True:
        if _stop:
            break
        rows = mcur.fetchmany(batch_size)
        if not rows:
            break
        copied += insert_rows(pg, insert_sql, pairs, pg_cols, defaults, rows)
        total = existing + state["rows_this_run"] + copied
        state["chunk_rows"] = copied
        state["pg_total_rows"] = total
        state["updated_at"] = datetime.now(timezone.utc).isoformat()
        mc.save_checkpoint(cp_path, state)
        if total - last_log_at[0] >= LOG_EVERY or last_log_at[0] == 0:
            elapsed = max((datetime.now(timezone.utc) - started).total_seconds(), 1)
            done = state["rows_this_run"] + copied
            rate = done / elapsed
            remain = max(MSSQL_TARGET - total, 0)
            eta_s = int(remain / rate) if rate > 0 else 0
            print(
                f"... {DEST_TABLE}: {total:,} rows  "
                f"{rate:,.0f}/s  remain~{remain:,}  eta~{eta_s // 3600}h{(eta_s % 3600) // 60:02d}m  "
                f"chunk={state.get('chunk')}",
                flush=True,
            )
            last_log_at[0] = total
    return copied


def main() -> int:
    signal.signal(signal.SIGTERM, _handle_stop)
    signal.signal(signal.SIGINT, _handle_stop)

    mssql_db = os.environ.get("CDR_MSSQL_DB", "HYD_UNIT_CDAT")
    mssql_table = os.environ.get("CDR_MSSQL_TABLE", "HYD_UNIT_CDATPCSUSPECT")
    batch_size = int(os.environ.get("MIGRATE_BATCH_SIZE", "10000"))
    cp_path = mc.checkpoint_path(PG_DB, DEST_TABLE)

    ms = mc.mssql_connect(mssql_db)
    ms.timeout = 0
    pg = mc.pg_connect(PG_DB)
    pg.autocommit = False
    with pg.cursor() as cur:
        cur.execute("SET synchronous_commit = off")
        cur.execute("SET statement_timeout = 0")
        cur.execute("SET lock_timeout = 0")
    pg.commit()

    try:
        ensure_dest_table(pg)
        existing = mc.pg_table_row_estimate(pg, DEST_TABLE)

        mcur = ms.cursor()
        mcur.arraysize = batch_size
        src = mssql_table
        with pg.cursor() as pcur:
            pg_cols = mc.pg_columns(pcur, DEST_TABLE)
        ms_cols = mc.mssql_columns(mcur, src)
        ms_by_norm = {mc.norm(c): c for c in ms_cols}
        pairs: list[tuple[str, str]] = []
        for col in pg_cols:
            n = mc.norm(col["name"])
            if n in ms_by_norm:
                pairs.append((ms_by_norm[n], col["name"]))
        if not pairs:
            raise SystemExit("no matching columns")

        defaults = mc.PG_DEFAULTS.get(LIVE_TABLE, {})
        select_sql = ", ".join(f"[{m}]" if " " in m else m for m, _ in pairs)
        insert_cols = ", ".join(p for _, p in pairs)
        insert_sql = f"INSERT INTO {DEST_TABLE} ({insert_cols}) VALUES %s"

        print("reading ASONDATE min/max from MSSQL index...", flush=True)
        lo, hi = mssql_asondate_range(mcur, src)
        print(f"ASONDATE range: {lo} .. {hi}", flush=True)

        cp = mc.load_checkpoint(cp_path) or {}
        last_done = cp.get("last_completed_day")
        nulls_done = bool(cp.get("nulls_done"))
        if last_done:
            last_done = as_date(last_done)

        unfinished = cp.get("in_progress_day")
        if unfinished:
            print(f"resume: removing incomplete chunk {unfinished} from dest...", flush=True)
            with pg.cursor() as cur:
                cur.execute("SET statement_timeout = 0")
                if unfinished == "NULL":
                    cur.execute(f"DELETE FROM {DEST_TABLE} WHERE asondate IS NULL")
                else:
                    day = as_date(unfinished)
                    nxt = day + timedelta(days=1)
                    cur.execute(
                        f"DELETE FROM {DEST_TABLE} WHERE asondate >= %s AND asondate < %s",
                        (day, nxt),
                    )
                deleted = cur.rowcount
            pg.commit()
            print(f"resume: deleted {deleted:,} incomplete rows", flush=True)
            existing = mc.pg_table_row_estimate(pg, DEST_TABLE)

        if existing > 0 and not cp:
            print(
                f"WARNING: {DEST_TABLE} already has ~{existing:,} rows and no checkpoint; "
                "refusing to insert duplicates.",
                flush=True,
            )
            return 2

        print(
            f"START {PG_DB}.{DEST_TABLE} <= {mssql_db}.dbo.{src} "
            f"(ASONDATE day windows, no UCID filter). "
            f"Live {LIVE_TABLE} is not touched. dest already ~{existing:,}",
            flush=True,
        )

        state: dict[str, Any] = {
            "pg_db": PG_DB,
            "pg_table": DEST_TABLE,
            "mssql_db": mssql_db,
            "mssql_table": src,
            "mode": "asondate_days",
            "pg_rows_at_start": existing,
            "rows_this_run": 0,
            "status": "running",
            "mssql_target": MSSQL_TARGET,
            "asondate_min": str(lo) if lo else None,
            "asondate_max": str(hi) if hi else None,
            "last_completed_day": str(last_done) if last_done else None,
            "nulls_done": nulls_done,
            "note": f"do not swap onto {LIVE_TABLE} until dest ~ {MSSQL_TARGET:,}",
        }
        mc.save_checkpoint(cp_path, state)
        started = datetime.now(timezone.utc)
        last_log_at = [0]
        rows_this_run = 0

        days: list[date | None] = []
        if lo and hi:
            d = lo
            while d <= hi:
                if last_done is None or d > last_done:
                    days.append(d)
                d += timedelta(days=1)
        if not nulls_done:
            days.append(None)

        for chunk in days:
            if _stop:
                break
            if chunk is None:
                state["chunk"] = "ASONDATE IS NULL"
                sql = (
                    f"SELECT {select_sql} FROM dbo.[{src}] WITH (NOLOCK) "
                    f"WHERE ASONDATE IS NULL"
                )
                params: tuple[Any, ...] = ()
            else:
                nxt = chunk + timedelta(days=1)
                state["chunk"] = str(chunk)
                sql = (
                    f"SELECT {select_sql} FROM dbo.[{src}] WITH (NOLOCK) "
                    f"WHERE ASONDATE >= ? AND ASONDATE < ?"
                )
                params = (datetime.combine(chunk, datetime.min.time()), datetime.combine(nxt, datetime.min.time()))
            state["in_progress_day"] = str(chunk) if chunk else "NULL"
            copied = copy_query(
                pg=pg,
                mcur=mcur,
                select_sql=select_sql,
                insert_sql=insert_sql,
                pairs=pairs,
                pg_cols=pg_cols,
                defaults=defaults,
                batch_size=batch_size,
                state=state,
                cp_path=cp_path,
                existing=existing,
                started=started,
                last_log_at=last_log_at,
                sql=sql,
                params=params,
            )
            rows_this_run += copied
            state["rows_this_run"] = rows_this_run
            state["pg_total_rows"] = existing + rows_this_run
            if _stop:
                state["status"] = "stopped"
                mc.save_checkpoint(cp_path, state)
                print(
                    f"STOPPED {DEST_TABLE}: checkpoint {cp_path} "
                    f"(~{existing + rows_this_run:,} rows in dest, mid chunk {state.get('chunk')})",
                    flush=True,
                )
                return 0
            if chunk is None:
                state["nulls_done"] = True
            else:
                state["last_completed_day"] = str(chunk)
            state["in_progress_day"] = None
            mc.save_checkpoint(cp_path, state)

        total = existing + rows_this_run
        state["status"] = "copied"
        state["pg_total_rows"] = total
        mc.save_checkpoint(cp_path, state)
        print(
            f"COPY DONE {DEST_TABLE}: {total:,} rows in dest "
            f"(MSSQL target {MSSQL_TARGET:,}). "
            f"Do not swap until dest is near target; then build indexes and rename.",
            flush=True,
        )
        return 0
    finally:
        ms.close()
        pg.close()


if __name__ == "__main__":
    raise SystemExit(main())
