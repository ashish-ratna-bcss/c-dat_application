#!/usr/bin/env python3
"""Compare MSSQL pcsuspect source vs Postgres cdatpcsuspect."""
from __future__ import annotations

import os
import subprocess
import sys

import psycopg2
import pyodbc


def mssql_conn(db: str) -> pyodbc.Connection:
    sa = subprocess.check_output(
        ["docker", "exec", "mssql", "printenv", "MSSQL_SA_PASSWORD"], text=True
    ).strip()
    drv = next(d for d in pyodbc.drivers() if "SQL Server" in d)
    return pyodbc.connect(
        f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE={db};"
        f"UID=SA;PWD={sa};TrustServerCertificate=yes",
        timeout=300,
    )


def pg_conn() -> psycopg2.extensions.connection:
    pw = os.environ.get("PGPASSWORD") or open("/tmp/migrate_pgpass").read().strip()
    return psycopg2.connect(
        host="127.0.0.1", dbname="CDATDUPL_DB", user="postgres", password=pw
    )


def main() -> None:
    ms_db = os.environ.get("CDR_MSSQL_DB", "HYD_UNIT_CDAT")
    ms_table = os.environ.get("CDR_MSSQL_TABLE", "HYD_UNIT_CDATPCSUSPECT")

    print("=== MSSQL source ===")
    ms = mssql_conn(ms_db)
    cur = ms.cursor()
    cur.execute(f"SELECT COUNT_BIG(*) FROM dbo.[{ms_table}]")
    ms_total = int(cur.fetchone()[0])
    print(f"Table: {ms_db}.dbo.{ms_table}")
    print(f"Total rows: {ms_total:,}")

    cur.execute(f"SELECT MIN([UCID]), MAX([UCID]) FROM dbo.[{ms_table}]")
    ms_min, ms_max = cur.fetchone()
    print(f"UCID range: {ms_min} .. {ms_max}")

    cur.execute(f"SELECT TOP 1 [UCID],[PHONE],[OTHER] FROM dbo.[{ms_table}] ORDER BY [UCID]")
    print(f"First row (lowest UCID): {cur.fetchone()}")

    cur.execute(
        f"SELECT TOP 1 [UCID],[PHONE],[OTHER] FROM dbo.[{ms_table}] ORDER BY [UCID] DESC"
    )
    ms_last = cur.fetchone()
    print(f"Last row (highest UCID): {ms_last}")
    ms.close()

    print("\n=== Postgres cdatpcsuspect ===")
    pg = pg_conn()
    pcur = pg.cursor()
    pcur.execute(
        "SELECT n_live_tup FROM pg_stat_user_tables WHERE relname='cdatpcsuspect'"
    )
    pg_live = int(pcur.fetchone()[0])
    print(f"Live rows (stats): {pg_live:,}")

    pcur.execute("SET statement_timeout = '120s'")
    try:
        pcur.execute("SELECT MIN(ucid), MAX(ucid) FROM cdatpcsuspect")
        pg_min, pg_max = pcur.fetchone()
        print(f"UCID range: {pg_min} .. {pg_max}")
    except Exception as e:
        print(f"UCID range: timeout/slow ({e})")
        pg_min = pg_max = None

    pcur.execute("SELECT ucid, phone, other FROM cdatpcsuspect ORDER BY ucid ASC LIMIT 1")
    print(f"First row (lowest UCID): {pcur.fetchone()}")

    if pg_max is not None:
        pcur.execute(
            "SELECT ucid, phone, other FROM cdatpcsuspect ORDER BY ucid DESC LIMIT 1"
        )
        pg_last = pcur.fetchone()
        print(f"Last row (highest UCID): {pg_last}")

        # Spot-check: does Postgres max UCID row match MSSQL same UCID?
        if ms_last and pg_last:
            check_ucid = pg_last[0]
            ms2 = mssql_conn(ms_db)
            c2 = ms2.cursor()
            c2.execute(
                f"SELECT [UCID],[PHONE],[OTHER] FROM dbo.[{ms_table}] WHERE [UCID]=?",
                check_ucid,
            )
            ms_match = c2.fetchone()
            ms2.close()
            print(f"\n=== Spot check UCID {check_ucid} ===")
            print(f"Postgres: {pg_last}")
            print(f"MSSQL:    {ms_match}")
            if ms_match and tuple(ms_match) == (pg_last[0], pg_last[1], pg_last[2]):
                print("MATCH: highest copied UCID identical in MSSQL and Postgres")
            elif ms_match:
                print("PARTIAL: UCID found in MSSQL, compare phone/other above")
            else:
                print("UCID not found in MSSQL at this checkpoint")

    pct = (pg_live / ms_total * 100) if ms_total else 0
    print(f"\n=== Summary ===")
    print(f"MSSQL total:     {ms_total:,}")
    print(f"Postgres copied: {pg_live:,}")
    print(f"Progress:        {pct:.1f}%")
    print(f"Remaining:       {max(ms_total - pg_live, 0):,}")
    pg.close()


if __name__ == "__main__":
    main()
