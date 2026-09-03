#!/usr/bin/env python3
"""Copy the full MSSQL pcsuspect heap into public.cdatpcsuspect_new.

Does not read or write public.cdatpcsuspect. No UCID filter.
Resume uses %%physloc%% (heap order). Swap names only after counts match.
"""
from __future__ import annotations

import os
import signal
import sys
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import psycopg2.extras

# Same directory as migrate_copy.py on the server and in this repo.
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


def physloc_to_hex(val: Any) -> str | None:
    if val is None:
        return None
    if isinstance(val, memoryview):
        val = val.tobytes()
    if isinstance(val, (bytes, bytearray)):
        return bytes(val).hex()
    return str(val)


def physloc_from_hex(text: str) -> bytes:
    return bytes.fromhex(text)


def ensure_dest_table(pg) -> None:
    with pg.cursor() as cur:
        cur.execute(
            """
            SELECT 1 FROM information_schema.tables
            WHERE table_schema = 'public' AND table_name = %s
            """,
            (DEST_TABLE,),
        )
        exists = cur.fetchone() is not None
        if not exists:
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


def probe_physloc(mcur, src: str) -> bool:
    try:
        mcur.execute(
            f"SELECT TOP 1 %%physloc%% AS physloc FROM dbo.[{src}] WITH (NOLOCK)"
        )
        row = mcur.fetchone()
        if not row or row[0] is None:
            print("physloc probe: empty/null", flush=True)
            return False
        print(f"physloc probe: ok ({type(row[0]).__name__})", flush=True)
        return True
    except Exception as exc:
        print(f"physloc probe failed: {exc}", flush=True)
        return False


def main() -> int:
    global _stop
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
        pg_cols = mc.pg_columns(pg.cursor(), DEST_TABLE)
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

        use_physloc = probe_physloc(mcur, src)
        cp = mc.load_checkpoint(cp_path) or {}
        resume_hex = cp.get("last_physloc") if use_physloc else None

        if existing > 0 and use_physloc and not resume_hex:
            print(
                f"WARNING: {DEST_TABLE} already has ~{existing:,} rows but no physloc "
                "checkpoint; refusing to insert duplicates. Delete the dest table or restore checkpoint.",
                flush=True,
            )
            return 2

        if existing > 0 and not use_physloc:
            print(
                f"WARNING: no physloc resume and dest already has ~{existing:,} rows; refusing.",
                flush=True,
            )
            return 2

        if use_physloc and resume_hex:
            print(
                f"RESUME {PG_DB}.{DEST_TABLE} from physloc {resume_hex} "
                f"(~{existing:,} rows already in dest)",
                flush=True,
            )
            mcur.execute(
                f"SELECT {select_sql}, %%physloc%% AS _physloc "
                f"FROM dbo.[{src}] WITH (NOLOCK) "
                f"WHERE %%physloc%% > ? ORDER BY %%physloc%%",
                physloc_from_hex(resume_hex),
            )
        elif use_physloc:
            print(
                f"START {PG_DB}.{DEST_TABLE} <= {mssql_db}.dbo.{src} "
                f"(full heap, physloc order). Live {LIVE_TABLE} is not touched.",
                flush=True,
            )
            mcur.execute(
                f"SELECT {select_sql}, %%physloc%% AS _physloc "
                f"FROM dbo.[{src}] WITH (NOLOCK) ORDER BY %%physloc%%"
            )
        else:
            print(
                f"START {PG_DB}.{DEST_TABLE} <= {mssql_db}.dbo.{src} "
                f"(full heap scan, no resume). Live {LIVE_TABLE} is not touched.",
                flush=True,
            )
            mcur.execute(f"SELECT {select_sql} FROM dbo.[{src}] WITH (NOLOCK)")

        rows_this_run = 0
        last_physloc_hex = resume_hex
        state = {
            "pg_db": PG_DB,
            "pg_table": DEST_TABLE,
            "mssql_db": mssql_db,
            "mssql_table": src,
            "mode": "physloc" if use_physloc else "heap_scan",
            "pg_rows_at_start": existing,
            "rows_this_run": 0,
            "status": "running",
            "mssql_target": MSSQL_TARGET,
            "note": f"do not swap onto {LIVE_TABLE} until dest ~ {MSSQL_TARGET:,}",
        }
        mc.save_checkpoint(cp_path, state)
        started = datetime.now(timezone.utc)
        last_log_at = 0

        while True:
            if _stop:
                break
            rows = mcur.fetchmany(batch_size)
            if not rows:
                break
            out = []
            for row in rows:
                data = row[:-1] if use_physloc else row
                vals = []
                for (_m_name, p_name), val in zip(pairs, data):
                    if val is None and not next(
                        c["nullable"] for c in pg_cols if c["name"] == p_name
                    ):
                        val = defaults.get(p_name, defaults.get(mc.norm(p_name), ""))
                    vals.append(mc.adapt_value(val, p_name))
                out.append(tuple(vals))
            if use_physloc:
                last_physloc_hex = physloc_to_hex(rows[-1][-1])
            with pg.cursor() as pcur:
                psycopg2.extras.execute_values(
                    pcur, insert_sql, out, page_size=min(len(out), 2000)
                )
            pg.commit()
            rows_this_run += len(out)
            total = existing + rows_this_run
            state["rows_this_run"] = rows_this_run
            state["pg_total_rows"] = total
            state["last_physloc"] = last_physloc_hex
            mc.save_checkpoint(cp_path, state)
            if total - last_log_at >= LOG_EVERY or rows_this_run == len(out):
                elapsed = max((datetime.now(timezone.utc) - started).total_seconds(), 1)
                rate = rows_this_run / elapsed
                remain = max(MSSQL_TARGET - total, 0)
                eta_s = int(remain / rate) if rate > 0 else 0
                print(
                    f"... {DEST_TABLE}: {total:,} rows  "
                    f"{rate:,.0f}/s  remain~{remain:,}  eta~{eta_s // 3600}h{(eta_s % 3600) // 60:02d}m",
                    flush=True,
                )
                last_log_at = total

        total = existing + rows_this_run
        if _stop:
            state["status"] = "stopped"
            mc.save_checkpoint(cp_path, state)
            print(
                f"STOPPED {DEST_TABLE}: checkpoint {cp_path} (~{total:,} rows in dest)",
                flush=True,
            )
            return 0

        state["status"] = "copied"
        state["pg_total_rows"] = total
        mc.save_checkpoint(cp_path, state)
        print(
            f"COPY DONE {DEST_TABLE}: {total:,} rows inserted this table "
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
