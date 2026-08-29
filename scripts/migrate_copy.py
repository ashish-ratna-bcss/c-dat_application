#!/usr/bin/env python3
"""Copy MSSQL tables into existing Postgres schemas (new DBs only)."""
from __future__ import annotations

import os
import re
import subprocess
import sys
from datetime import date, datetime
from decimal import Decimal
from pathlib import Path
from typing import Any

import psycopg2
import psycopg2.extras
import pyodbc


def pg_password() -> str:
    if os.environ.get("PGPASSWORD"):
        return os.environ["PGPASSWORD"]
    path = Path("/tmp/migrate_pgpass")
    if path.is_file():
        return path.read_text()
    raise SystemExit("PGPASSWORD not set (and /tmp/migrate_pgpass missing)")

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


def norm(name: str) -> str:
    name = name.strip().lower()
    name = re.sub(r"[^a-z0-9]+", "_", name)
    return re.sub(r"_+", "_", name).strip("_")


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
    password = pg_password()
    return psycopg2.connect(
        host="127.0.0.1",
        port=5432,
        dbname=dbname,
        user="postgres",
        password=password,
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


def copy_table(
    mssql_db: str,
    pg_db: str,
    pg_table: str,
    mssql_table: str | None = None,
) -> int:
    ms = mssql_connect(mssql_db)
    pg = pg_connect(pg_db)
    pg.autocommit = False
    try:
        mcur = ms.cursor()
        tables = mssql_tables(mcur)
        src = mssql_table or tables.get(norm(pg_table))
        if not src:
            print(f"SKIP {pg_db}.{pg_table}: MSSQL table not found in {mssql_db}")
            return 0

        with pg.cursor() as pcur:
            pcur.execute(f"SELECT count(*) FROM {pg_table}")
            existing = int(pcur.fetchone()[0])
            if existing > 0:
                force = os.environ.get("MIGRATE_FORCE_TRUNCATE", "").lower() in ("1", "true", "yes")
                if force or (pg_table == "cdatpcsuspect" and existing < 10000):
                    print(f"TRUNCATE {pg_db}.{pg_table}: removing {existing} rows")
                    pcur.execute(f"TRUNCATE {pg_table}")
                    pg.commit()
                    existing = 0
                else:
                    print(f"SKIP {pg_db}.{pg_table}: already has {existing} rows")
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
        mcur.execute(f"SELECT {select_sql} FROM dbo.[{src}]")
        insert_sql = f"INSERT INTO {pg_table} ({insert_cols}) VALUES ({placeholders})"
        total = 0
        while True:
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
            total += len(out)
            print(f"... {pg_db}.{pg_table}: {total} rows", flush=True)

        with pg.cursor() as pcur:
            serial_col = next(
                (c["name"] for c in pg_cols if c["default"] and "nextval" in c["default"]),
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
        print(f"OK {pg_db}.{pg_table} <= {mssql_db}.dbo.{src}: {count} rows")
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
        copy_table(
            "mssql_dump_jrms",
            "JRMS_DB",
            "jrms_total_2012_to_2017",
            mssql_table="JRMS_TOTAL",
        )
    elif job == "ir":
        ir_tables = [
            "image_table",
            "local_contacts_facilitators",
            "habitual_offenders",
            "ir_particulars",
            "offence_details",
            "disposal_of_property",
            "family_history",
            "brief_facts",
            "jail",
            "previous_offence_details",
            "fingerprint_matched_undetected_cases_withimage",
            "relationship_with_other_associates",
        ]
        for tbl in ir_tables:
            copy_table("mssql_dump_ir", "IR_DB", tbl)
    elif job == "rowdy":
        copy_table(
            "mssql_dump_ir",
            "ROWDY_SHEETS_DB",
            "rowdy_sheeter_complete_data",
            mssql_table="ROWDY SHEETERS TO CHECK",
        )
    elif job == "cellids":
        copy_table(
            "cellids_db",
            "CDATDUPL_DB",
            "cdatcelltowerareanew",
            mssql_table="CELLIDS",
        )
    elif job == "address":
        copy_table(
            "address_db",
            "CDATDUPL_DB",
            "cdataddress",
            mssql_table="CDATADDRESS",
        )
    elif job == "other_rwd":
        # OTHER_IMAGE_RWD.zip restore: mssql_dump_other_image_rwd
        # Rowdy -> ROWDY_SHEETS_DB only (separate rowdy DB)
        # Suspect images -> CDATDUPL_DB.suspect_image_table
        copy_table(
            "mssql_dump_other_image_rwd",
            "ROWDY_SHEETS_DB",
            "rowdy_sheeter_data1",
            mssql_table="ROWDY_SHEETER_DATA1",
        )
        copy_table(
            "mssql_dump_other_image_rwd",
            "CDATDUPL_DB",
            "suspect_image_table",
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
            ("cdat_civilsupply",           "CDAT_CIVILSUPPLY"),
            ("cdat_gas_details",           "CDAT_GAS_DETAILS"),
            ("cdat_passport",              "CDAT_PASSPORT"),
            ("cdatphonearea",              "CDATPHONEAREA"),
            ("cdatsuspect",                "CDATSUSPECT"),
            ("complete_mo_classification", "COMPLETE_MO_CLASSIFICATION"),
            ("mcc_mnc",                    "MCC_MNC"),
            ("mnc_codes",                  "MNC_CODES"),
            ("mo_image_table",             "MO_IMAGE_TABLE"),
        ]
        for pg_tbl, ms_tbl in cdatdupl_tables:
            copy_table("mssql_dump_cdatdupl", "CDATDUPL_DB", pg_tbl, mssql_table=ms_tbl)
    elif job == "cdr":
        mssql_db = os.environ.get("CDR_MSSQL_DB", "mssql_dump_cdr")
        cdr_tables = [
            "cdatpcsuspect",
            "cdatsuspect",
            "cdatcelltowerareanew",
            "cdatphonearea",
            "cdataddress",
            "cdat_provider_master",
            "cdat_state_master",
            "cdat_rta",
            "cdat_civilsupply",
            "cdat_gas_details",
            "cdat_licence",
            "cdat_passport",
            "mcc_mnc",
            "mnc_codes",
            "complete_mo_classification",
            "rowdy_sheeter_data1",
            "ndps_abstract_1",
        ]
        for tbl in cdr_tables:
            copy_table(mssql_db, "CDATDUPL_DB", tbl)
    else:
        raise SystemExit(f"unknown job: {job}")


if __name__ == "__main__":
    main()
