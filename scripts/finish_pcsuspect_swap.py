#!/usr/bin/env python3
"""After the heap copy finishes: build indexes and rename onto live cdatpcsuspect.

Does not drop MSSQL. Does not drop the old live table (kept as cdatpcsuspect_pre_fullcopy).
Safe to run more than once: no-ops if already swapped.
"""
from __future__ import annotations

import fcntl
import json
import os
import sys
from datetime import datetime, timezone
from pathlib import Path

import psycopg2

sys.path.insert(0, str(Path(__file__).resolve().parent))
import migrate_copy as mc

DEST_TABLE = "cdatpcsuspect_new"
LIVE_TABLE = "cdatpcsuspect"
OLD_TABLE = "cdatpcsuspect_pre_fullcopy"
PG_DB = "CDATDUPL_DB"
MSSQL_TARGET = 1_055_080_912
MIN_ROWS = int(MSSQL_TARGET * 0.99)
LOCK_PATH = Path("/tmp/pcsuspect_finish.lock")
DONE_PATH = Path("/tmp/pcsuspect_MIGRATION_DONE")
CP_PATH = mc.checkpoint_path(PG_DB, DEST_TABLE)

INDEXES = (
    ("idx_cdatpcsuspect_new_phone", "phone"),
    ("idx_cdatpcsuspect_new_other", "other"),
    ("idx_cdatpcsuspect_new_starttime", "starttime"),
    ("idx_cdatpcsuspect_new_celltowerid", "celltowerid"),
    ("idx_cdatpcsuspect_new_provider_key", "provider_key"),
)


def log(msg: str) -> None:
    print(f"[{datetime.now().astimezone().isoformat(timespec='seconds')}] {msg}", flush=True)


def load_cp() -> dict:
    if not CP_PATH.is_file():
        return {}
    try:
        return json.loads(CP_PATH.read_text())
    except json.JSONDecodeError:
        return {}


def save_cp(data: dict) -> None:
    data["updated_at"] = datetime.now(timezone.utc).isoformat()
    CP_PATH.write_text(json.dumps(data, indent=2, default=str) + "\n")


def table_exists(cur, name: str) -> bool:
    cur.execute(
        """
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = %s
        """,
        (name,),
    )
    return cur.fetchone() is not None


def live_est(cur, name: str) -> int:
    cur.execute(
        "SELECT COALESCE(n_live_tup, 0) FROM pg_stat_user_tables WHERE relname = %s",
        (name,),
    )
    row = cur.fetchone()
    return int(row[0] or 0) if row else 0


def finish() -> int:
    lockf = LOCK_PATH.open("w")
    try:
        fcntl.flock(lockf.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)
    except BlockingIOError:
        log("finish already running; exit")
        return 0

    if DONE_PATH.is_file() and "swapped" in DONE_PATH.read_text():
        log("already swapped (done file); exit")
        return 0

    cp = load_cp()
    if cp.get("status") == "swapped":
        log("checkpoint already swapped; exit")
        return 0

    pg = mc.pg_connect(PG_DB)
    pg.autocommit = True
    try:
        with pg.cursor() as cur:
            cur.execute("SET statement_timeout = 0")
            cur.execute("SET lock_timeout = 0")
            cur.execute("SET maintenance_work_mem = '2GB'")

            if not table_exists(cur, DEST_TABLE):
                if table_exists(cur, OLD_TABLE):
                    log(f"{DEST_TABLE} gone and {OLD_TABLE} exists — swap already done")
                    DONE_PATH.write_text("swapped\n")
                    return 0
                log(f"ERROR: {DEST_TABLE} does not exist; cannot swap")
                return 2

            dest_rows = live_est(cur, DEST_TABLE)
            copied = int(cp.get("pg_total_rows") or 0)
            best = max(dest_rows, copied)
            log(
                f"pre-swap check: dest n_live_tup={dest_rows:,} "
                f"checkpoint={copied:,} best={best:,} target={MSSQL_TARGET:,}"
            )
            if best < MIN_ROWS:
                log(
                    f"ERROR: dest too small ({best:,} < {MIN_ROWS:,}). "
                    "Not swapping. Copy is not complete."
                )
                return 3

            for idx_name, col in INDEXES:
                log(f"CREATE INDEX {idx_name} ON {DEST_TABLE} ({col}) — this can take hours")
                cur.execute(
                    f"CREATE INDEX IF NOT EXISTS {idx_name} ON {DEST_TABLE} ({col})"
                )
                log(f"index {idx_name} ready")

            log(f"ANALYZE {DEST_TABLE}")
            cur.execute(f"ANALYZE {DEST_TABLE}")
            cur.execute(f"ALTER TABLE {DEST_TABLE} SET (autovacuum_enabled = true)")

            log("swapping table names (app will use the full copy)")
        pg.autocommit = False
        try:
            with pg.cursor() as cur:
                cur.execute(f"ALTER TABLE {LIVE_TABLE} RENAME TO {OLD_TABLE}")
                cur.execute(f"ALTER TABLE {DEST_TABLE} RENAME TO {LIVE_TABLE}")
            pg.commit()
        except Exception:
            pg.rollback()
            raise
        pg.autocommit = True
        with pg.cursor() as cur:

            live_now = live_est(cur, LIVE_TABLE)
            log(
                f"SWAP DONE. public.{LIVE_TABLE} is the full copy "
                f"(~{live_now:,} rows). Old table kept as public.{OLD_TABLE}. "
                "Do not drop MSSQL until someone confirms the app."
            )
            cp["status"] = "swapped"
            cp["swapped_at"] = datetime.now(timezone.utc).isoformat()
            save_cp(cp)
            DONE_PATH.write_text(
                f"swapped {datetime.now().astimezone().isoformat()}\n"
                f"live={LIVE_TABLE} old={OLD_TABLE} rows~{best}\n"
                "Do not drop MSSQL until verified.\n"
            )
            return 0
    finally:
        pg.close()
        lockf.close()


if __name__ == "__main__":
    raise SystemExit(finish())
