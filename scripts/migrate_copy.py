#!/usr/bin/env python3
"""Copy MSSQL tables into existing Postgres schemas (new DBs only).

Supports checkpoint/resume for large tables (e.g. cdatpcsuspect via UCID).
Set MIGRATE_RESUME=1 (default for cdatpcsuspect) to continue after a stop.
Checkpoint file: /tmp/migrate_checkpoint_<pg_db>_<pg_table>.json
"""
from __future__ import annotations

import json
import os
import re
import signal
import subprocess
import sys
from datetime import date, datetime, timezone
from decimal import Decimal
from pathlib import Path
from typing import Any

import psycopg2
import psycopg2.extras
import pyodbc

CHECKPOINT_DIR = Path(os.environ.get("MIGRATE_CHECKPOINT_DIR", "/tmp"))

# Postgres table -> column used to resume MSSQL reads (must exist on both sides).
RESUME_KEY_COLUMNS: dict[str, str] = {
    "cdatpcsuspect": "ucid",
}

PG_DEFAULTS: dict[str, dict[str, Any]] = {
    "jrms_total_2012_to_2017": {
        "name": "",
        "headofcrime": "",
        "identificationmark": "",
        "placeofidentificationmark": "",
        "fathersname": "",
    },
    "cdataddress": {
        "phone": "",
        "country": "",
        "doa": date(1900, 1, 1),
    },
}

_stop_requested = False
_checkpoint_state: dict[str, Any] | None = None


def pg_password() -> str:
    if os.environ.get("PGPASSWORD"):
        return os.environ["PGPASSWORD"]
    path = Path("/tmp/migrate_pgpass")
    if path.is_file():
        return path.read_text().strip()
    raise SystemExit("PGPASSWORD not set (and /tmp/migrate_pgpass missing)")


def norm(name: str) -> str:
    name = name.strip().lower()
    name = re.sub(r"[^a-z0-9]+", "_", name)
    return re.sub(r"_+", "_", name).strip("_")


def checkpoint_path(pg_db: str, pg_table: str) -> Path:
    safe = re.sub(r"[^a-z0-9]+", "_", f"{pg_db}_{pg_table}".lower()).strip("_")
    return CHECKPOINT_DIR / f"migrate_checkpoint_{safe}.json"


def load_checkpoint(path: Path) -> dict[str, Any] | None:
    if not path.is_file():
        return None
    try:
        return json.loads(path.read_text())
    except json.JSONDecodeError:
        return None


def save_checkpoint(path: Path, data: dict[str, Any]) -> None:
    data["updated_at"] = datetime.now(timezone.utc).isoformat()
    path.write_text(json.dumps(data, indent=2, default=str) + "\n")


def _handle_stop(signum: int, _frame: Any) -> None:
    global _stop_requested
    _stop_requested = True
    print(f"\nSTOP signal {signum}: finishing current batch then saving checkpoint...", flush=True)


def sa_password() -> str:
    return subprocess.check_output(
        ["docker", "exec", "mssql", "printenv", "MSSQL_SA_PASSWORD"],
        text=True,
    ).strip()


def mssql_connect(database: str) -> pyodbc.Connection:
    sa = sa_password()
    drivers = pyodbc.drivers()
    drv = next((d for d in drivers if "SQL Server" in d), None)
    if not drv:
        raise SystemExit(f"No SQL Server ODBC driver: {drivers!r}")
    return pyodbc.connect(
        f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE={database};"
        f"UID=SA;PWD={sa};TrustServerCertificate=yes",
        timeout=60,
    )


def pg_connect(dbname: str) -> psycopg2.extensions.connection:
    return psycopg2.connect(
        host="127.0.0.1",
        port=5432,
        dbname=dbname,
        user="postgres",
        password=pg_password(),
    )


def adapt_value(value: Any, pg_col: str) -> Any:
    if value is None:
        return None
    if isinstance(value, Decimal):
        return value
    if isinstance(value, datetime):
        return value.replace(tzinfo=None)
    if isinstance(value, date):
        return value
    if isinstance(value, (bytes, memoryview, bytearray)):
        return bytes(value)
    if isinstance(value, str) and pg_col == "image":
        return value.encode("utf-8", errors="replace")
    return value


def pg_columns(cur, table: str) -> list[dict[str, Any]]:
    cur.execute(
        """
        SELECT column_name, is_nullable, column_default, data_type
        FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = %s
        ORDER BY ordinal_position
        """,
        (table.lower(),),
    )
    return [
        {
            "name": r[0],
            "nullable": r[1] == "YES",
            "default": r[2],
            "data_type": r[3],
        }
        for r in cur.fetchall()
    ]


def mssql_columns(cur, table: str) -> list[str]:
    cur.execute(
        """
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION
        """,
        table,
    )
    return [r[0] for r in cur.fetchall()]


def mssql_tables(cur) -> dict[str, str]:
    cur.execute(
        """
        SELECT TABLE_NAME
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_TYPE = 'BASE TABLE'
        """
    )
    out: dict[str, str] = {}
    for (name,) in cur.fetchall():
        out[norm(name)] = name
    return out


def env_flag(name: str, default: bool = False) -> bool:
    raw = os.environ.get(name)
    if raw is None:
        return default
    return raw.strip().lower() in ("1", "true", "yes", "on")


def pg_table_row_estimate(conn, table: str) -> int:
    """Fast row estimate — avoids COUNT(*) on huge tables."""
    with conn.cursor() as cur:
        cur.execute(
            "SELECT n_live_tup FROM pg_stat_user_tables WHERE relname = %s",
            (table.lower(),),
        )
        row = cur.fetchone()
        if row and row[0] is not None and int(row[0]) > 0:
            return int(row[0])
        cur.execute(
            "SELECT reltuples::bigint FROM pg_class WHERE relname = %s",
            (table.lower(),),
        )
        row = cur.fetchone()
        return int(row[0] or 0) if row else 0


def pg_max_resume_key(conn, table: str, key: str, cp_path: Path) -> Any:
    """Get resume key value from checkpoint file or env (avoids slow MAX on huge tables)."""
    env_val = os.environ.get("MIGRATE_RESUME_FROM_UCID")
    if env_val is not None and str(env_val).strip() != "":
        return int(env_val)
    cp = load_checkpoint(cp_path)
    if cp and cp.get("resume_from") is not None:
        return cp["resume_from"]
    if cp and cp.get("last_ucid") is not None:
        return cp["last_ucid"]
    with conn.cursor() as cur:
        cur.execute(f"SET statement_timeout = '{os.environ.get('MIGRATE_MAX_UCID_TIMEOUT', '7200')}s'")
        cur.execute(f"SELECT COALESCE(MAX({key}), 0) FROM {table}")
        return cur.fetchone()[0]

    val = os.environ.get(name)
    if val is None:
        return default
    return val.lower() in ("1", "true", "yes")


def copy_table(
    mssql_db: str,
    pg_db: str,
    pg_table: str,
    mssql_table: str | None = None,
) -> int:
    global _checkpoint_state, _stop_requested

    ms = mssql_connect(mssql_db)
    pg = pg_connect(pg_db)
    pg.autocommit = False
    cp_path = checkpoint_path(pg_db, pg_table)
    resume_key = RESUME_KEY_COLUMNS.get(pg_table.lower())
    resume = env_flag("MIGRATE_RESUME", default=bool(resume_key))

    signal.signal(signal.SIGTERM, _handle_stop)
    signal.signal(signal.SIGINT, _handle_stop)

    try:
        mcur = ms.cursor()
        tables = mssql_tables(mcur)
        src = mssql_table or tables.get(norm(pg_table))
        if not src:
            print(f"SKIP {pg_db}.{pg_table}: MSSQL table not found in {mssql_db}")
            return 0

        with pg.cursor() as pcur:
            existing = pg_table_row_estimate(pg, pg_table)
            if existing <= 0:
                pcur.execute(f"SELECT count(*) FROM {pg_table}")
                existing = int(pcur.fetchone()[0])

        force = env_flag("MIGRATE_FORCE_TRUNCATE")
        if existing > 0 and force:
            print(f"TRUNCATE {pg_db}.{pg_table}: removing {existing} rows")
            with pg.cursor() as pcur:
                pcur.execute(f"TRUNCATE {pg_table}")
            pg.commit()
            existing = 0
            if cp_path.is_file():
                cp_path.unlink()
        elif existing > 0 and not resume:
            print(f"SKIP {pg_db}.{pg_table}: already has {existing} rows (set MIGRATE_RESUME=1 to continue)")
            return existing
        elif existing > 0 and resume and not resume_key:
            print(f"SKIP {pg_db}.{pg_table}: already has {existing} rows (no resume key configured)")
            return existing

        with pg.cursor() as pcur:
            pg_cols = pg_columns(pcur, pg_table)
        ms_cols = mssql_columns(mcur, src)
        ms_by_norm = {norm(c): c for c in ms_cols}

        pairs: list[tuple[str, str]] = []
        for col in pg_cols:
            n = norm(col["name"])
            if n in ms_by_norm:
                pairs.append((ms_by_norm[n], col["name"]))

        if not pairs:
            print(f"SKIP {pg_db}.{pg_table}: no matching columns with {src}")
            return 0

        defaults = PG_DEFAULTS.get(pg_table, {})
        select_sql = ", ".join(f"[{m}]" if " " in m else m for m, _ in pairs)
        insert_cols = ", ".join(p for _, p in pairs)
        placeholders = ", ".join(["%s"] * len(pairs))
        batch_size = int(os.environ.get("MIGRATE_BATCH_SIZE", "5000"))
        mcur.arraysize = batch_size
        insert_sql = f"INSERT INTO {pg_table} ({insert_cols}) VALUES ({placeholders})"

        resume_from: Any = None
        base_total = existing
        if existing > 0 and resume and resume_key:
            ms_key = ms_by_norm.get(norm(resume_key))
            if not ms_key:
                raise SystemExit(f"Cannot resume {pg_table}: MSSQL column {resume_key!r} not found in {src}")
            resume_from = pg_max_resume_key(pg, pg_table, resume_key, cp_path)
            print(
                f"RESUME {pg_db}.{pg_table} from {resume_key} > {resume_from} "
                f"(~{existing:,} rows already in Postgres)",
                flush=True,
            )
            mcur.execute(
                f"SELECT {select_sql} FROM dbo.[{src}] WHERE [{ms_key}] > ? ORDER BY [{ms_key}]",
                resume_from,
            )
        else:
            print(f"START {pg_db}.{pg_table} <= {mssql_db}.dbo.{src}", flush=True)
            mcur.execute(f"SELECT {select_sql} FROM dbo.[{src}]")

        rows_this_run = 0
        _checkpoint_state = {
            "pg_db": pg_db,
            "pg_table": pg_table,
            "mssql_db": mssql_db,
            "mssql_table": src,
            "resume_key": resume_key,
            "resume_from": resume_from,
            "pg_rows_at_start": existing,
            "rows_this_run": 0,
            "status": "running",
        }

        while True:
            if _stop_requested:
                break
            rows = mcur.fetchmany(batch_size)
            if not rows:
                break
            out = []
            for row in rows:
                vals = []
                for (_m_name, p_name), val in zip(pairs, row):
                    if val is None and not next(
                        c["nullable"] for c in pg_cols if c["name"] == p_name
                    ):
                        val = defaults.get(p_name, defaults.get(norm(p_name), ""))
                    vals.append(adapt_value(val, p_name))
                out.append(tuple(vals))
            with pg.cursor() as pcur:
                psycopg2.extras.execute_batch(
                    pcur, insert_sql, out, page_size=min(len(out), 1000)
                )
            pg.commit()
            rows_this_run += len(out)
            total = base_total + rows_this_run
            if resume_key and out:
                key_idx = next(i for i, (_, p) in enumerate(pairs) if norm(p) == norm(resume_key))
                last_key = out[-1][key_idx]
                _checkpoint_state["last_ucid"] = last_key
                _checkpoint_state["resume_from"] = last_key
            _checkpoint_state["rows_this_run"] = rows_this_run
            _checkpoint_state["pg_total_rows"] = total
            save_checkpoint(cp_path, _checkpoint_state)
            print(f"... {pg_db}.{pg_table}: {total} rows", flush=True)

        with pg.cursor() as pcur:
            serial_col = next(
                (c["name"] for c in pg_cols if c["default"] and "nextval" in str(c["default"])),
                None,
            )
            if serial_col:
                pcur.execute(
                    f"SELECT setval(pg_get_serial_sequence(%s, %s), "
                    f"COALESCE((SELECT MAX({serial_col}) FROM {pg_table}), 1))",
                    (pg_table, serial_col),
                )
            pcur.execute(f"SELECT count(*) FROM {pg_table}")
            count = int(pcur.fetchone()[0])
        pg.commit()

        if _stop_requested:
            _checkpoint_state["status"] = "stopped"
            save_checkpoint(cp_path, _checkpoint_state)
            print(f"STOPPED {pg_db}.{pg_table}: checkpoint saved -> {cp_path} ({count:,} rows in Postgres)")
            return count

        if cp_path.is_file():
            cp_path.unlink()
        print(f"OK {pg_db}.{pg_table} <= {mssql_db}.dbo.{src}: {count:,} rows")
        return count
    finally:
        ms.close()
        pg.close()


def main() -> None:
    if len(sys.argv) < 2:
        raise SystemExit(
            "usage: migrate_copy.py <job>  "
            "(jrms|ir|rowdy|cdatdupl|cellids|address|other_rwd|pcsuspect|cdr)"
        )

    job = sys.argv[1].lower()
    if job == "jrms":
        copy_table("mssql_dump_jrms", "JRMS_DB", "jrms_total_2012_to_2017", mssql_table="JRMS_TOTAL")
    elif job == "ir":
        ir_tables = [
            "image_table", "local_contacts_facilitators", "habitual_offenders",
            "ir_particulars", "offence_details", "disposal_of_property", "family_history",
            "brief_facts", "jail", "previous_offence_details",
            "fingerprint_matched_undetected_cases_withimage",
            "relationship_with_other_associates",
        ]
        for tbl in ir_tables:
            copy_table("mssql_dump_ir", "IR_DB", tbl)
    elif job == "rowdy":
        copy_table(
            "mssql_dump_ir", "ROWDY_SHEETS_DB", "rowdy_sheeter_complete_data",
            mssql_table="ROWDY SHEETERS TO CHECK",
        )
    elif job == "cellids":
        copy_table("cellids_db", "CDATDUPL_DB", "cdatcelltowerareanew", mssql_table="CELLIDS")
    elif job == "address":
        copy_table("address_db", "CDATDUPL_DB", "cdataddress", mssql_table="CDATADDRESS")
    elif job == "other_rwd":
        copy_table(
            "mssql_dump_other_image_rwd", "ROWDY_SHEETS_DB", "rowdy_sheeter_data1",
            mssql_table="ROWDY_SHEETER_DATA1",
        )
        copy_table(
            "mssql_dump_other_image_rwd", "CDATDUPL_DB", "suspect_image_table",
            mssql_table="SUSPECT_IMAGE_TABLE",
        )
    elif job == "pcsuspect":
        copy_table(
            os.environ.get("CDR_MSSQL_DB", "HYD_UNIT_CDAT"),
            "CDATDUPL_DB",
            "cdatpcsuspect",
            mssql_table=os.environ.get("CDR_MSSQL_TABLE", "HYD_UNIT_CDATPCSUSPECT"),
        )
    elif job == "cdatdupl":
        cdatdupl_tables = [
            ("cdat_civilsupply", "CDAT_CIVILSUPPLY"),
            ("cdat_gas_details", "CDAT_GAS_DETAILS"),
            ("cdat_passport", "CDAT_PASSPORT"),
            ("cdatphonearea", "CDATPHONEAREA"),
            ("cdatsuspect", "CDATSUSPECT"),
            ("complete_mo_classification", "COMPLETE_MO_CLASSIFICATION"),
            ("mcc_mnc", "MCC_MNC"),
            ("mnc_codes", "MNC_CODES"),
            ("mo_image_table", "MO_IMAGE_TABLE"),
        ]
        for pg_tbl, ms_tbl in cdatdupl_tables:
            copy_table("mssql_dump_cdatdupl", "CDATDUPL_DB", pg_tbl, mssql_table=ms_tbl)
    elif job == "cdr":
        mssql_db = os.environ.get("CDR_MSSQL_DB", "mssql_dump_cdr")
        for tbl in [
            "cdatpcsuspect", "cdatsuspect", "cdatcelltowerareanew", "cdatphonearea",
            "cdataddress", "cdat_provider_master", "cdat_state_master", "cdat_rta",
            "cdat_civilsupply", "cdat_gas_details", "cdat_licence", "cdat_passport",
            "mcc_mnc", "mnc_codes", "complete_mo_classification", "rowdy_sheeter_data1",
            "ndps_abstract_1",
        ]:
            copy_table(mssql_db, "CDATDUPL_DB", tbl)
    else:
        raise SystemExit(f"unknown job: {job}")


if __name__ == "__main__":
    main()
