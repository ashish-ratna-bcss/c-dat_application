#!/usr/bin/env python3
"""Compare Postgres schema tables vs MSSQL (4 restored dumps only)."""
from __future__ import annotations

import re
import subprocess
import sys
from pathlib import Path

import psycopg2
import pyodbc

MSSQL_DUMPS = [
    "mssql_dump_pdact",
    "mssql_dump_jrms",
    "mssql_dump_ir",
    "mssql_dump_cdatdupl",
]

PG_DBS = ["PDACT_DB", "JRMS_DB", "IR_DB", "ROWDY_SHEETS_DB", "CDATDUPL_DB"]

# App-managed tables — not expected from .BAK dumps
APP_TABLES = {
    "logins",
    "user_sessions",
    "user_activity_logs",
    "user_quick_links",
    "admin_query_logs",
    "document_jobs",
    "upload_staging_batches",
    "upload_activity_logs",
    "upload_approval_queue",
    "cdatpcsuspect_staging",
}

# Known MSSQL name overrides (postgres_table -> search names)
ALIASES: dict[str, list[str]] = {
    "jrms_total_2012_to_2017": ["jrms_total"],
    "cdatcelltowerareanew": ["cdatcelltowerareanew", "cdat_towerdata", "cdat_towerdata_all"],
    "rowdy_sheeter_complete_data": [
        "rowdy_sheeter_complete_data",
        "rowdy_sheeters_total",
        "rowdy sheeters to check",
    ],
    "cdatpcsuspect": ["cdatpcsuspect", "hyd_unit_cdatpcsuspect"],
}


def norm(name: str) -> str:
    name = name.strip().lower()
    name = re.sub(r"[^a-z0-9]+", "_", name)
    return re.sub(r"_+", "_", name).strip("_")


def pg_password() -> str:
    if p := __import__("os").environ.get("PGPASSWORD"):
        return p
    path = Path("/tmp/migrate_pgpass")
    if path.is_file():
        return path.read_text()
    raise SystemExit("PGPASSWORD missing")


def sa_password() -> str:
    return subprocess.check_output(
        ["docker", "exec", "mssql", "printenv", "MSSQL_SA_PASSWORD"], text=True
    ).strip()


def mssql_connect(db: str) -> pyodbc.Connection:
    sa = sa_password()
    drv = next(d for d in pyodbc.drivers() if "SQL Server" in d)
    return pyodbc.connect(
        f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE={db};UID=SA;PWD={sa};TrustServerCertificate=yes",
        timeout=60,
    )


def mssql_inventory() -> dict[str, dict[str, int]]:
    """db -> {norm_table_name: row_count}"""
    inv: dict[str, dict[str, int]] = {}
    for db in MSSQL_DUMPS:
        conn = mssql_connect(db)
        cur = conn.cursor()
        cur.execute(
            """
            SELECT t.name, SUM(p.rows)
            FROM sys.tables t
            JOIN sys.partitions p ON t.object_id = p.object_id
            WHERE p.index_id IN (0, 1) AND t.is_ms_shipped = 0
            GROUP BY t.name
            """
        )
        inv[db] = {norm(name): (name, int(rows or 0)) for name, rows in cur.fetchall()}
        conn.close()
    return inv


def find_in_mssql(inv: dict[str, dict[str, int]], pg_table: str) -> tuple[str, str, int] | None:
    names = [norm(pg_table)] + [norm(n) for n in ALIASES.get(pg_table, [])]
    for db in MSSQL_DUMPS:
        for key in names:
            if key in inv[db]:
                orig, rows = inv[db][key]
                return db, orig, rows
    # fuzzy: postgres name contained in mssql norm name
    for db in MSSQL_DUMPS:
        for key, (orig, rows) in inv[db].items():
            if norm(pg_table) in key or key in norm(pg_table):
                if len(norm(pg_table)) >= 6:
                    return db, orig, rows
    return None


def pg_tables(db: str) -> list[str]:
    conn = psycopg2.connect(
        host="127.0.0.1", port=5432, dbname=db, user="postgres", password=pg_password()
    )
    cur = conn.cursor()
    cur.execute(
        """
        SELECT tablename FROM pg_tables
        WHERE schemaname = 'public'
        ORDER BY tablename
        """
    )
    names = [r[0] for r in cur.fetchall()]
    conn.close()
    return names


def main() -> None:
    print("=" * 72)
    print("POSTGRES TABLES vs MSSQL (4 dumps only)")
    print("  mssql_dump_pdact, mssql_dump_jrms, mssql_dump_ir, mssql_dump_cdatdupl")
    print("=" * 72)
    print()

    inv = mssql_inventory()

    missing: list[str] = []
    empty: list[str] = []
    ok: list[str] = []
    app: list[str] = []

    for pg_db in PG_DBS:
        print(f"--- {pg_db} ---")
        try:
            tables = pg_tables(pg_db)
        except Exception as e:
            print(f"  ERROR: cannot connect: {e}")
            continue
        if not tables:
            print("  (no tables)")
            print()
            continue

        for tbl in tables:
            if norm(tbl) in APP_TABLES:
                app.append(f"{pg_db}.{tbl}")
                print(f"  {tbl:45} APP TABLE (not from dump)")
                continue

            hit = find_in_mssql(inv, tbl)
            if not hit:
                missing.append(f"{pg_db}.{tbl}")
                print(f"  {tbl:45} MISSING in all 4 MSSQL dumps")
            else:
                mdb, mname, rows = hit
                if rows == 0:
                    empty.append(f"{pg_db}.{tbl} ({mdb}.dbo.{mname})")
                    print(f"  {tbl:45} FOUND but 0 rows  <- {mdb}.dbo.{mname}")
                else:
                    ok.append(f"{pg_db}.{tbl}")
                    print(f"  {tbl:45} OK  {rows:>12,} rows  <- {mdb}.dbo.{mname}")
        print()

    print("=" * 72)
    print("SUMMARY")
    print("=" * 72)
    print(f"  Found with data:     {len(ok)}")
    print(f"  Found but 0 rows:    {len(empty)}")
    print(f"  MISSING in MSSQL:    {len(missing)}")
    print(f"  App tables (skip):   {len(app)}")
    print()

    if missing:
        print("ASK FOR DATA — tables in Postgres schema but NOT in any of 4 dumps:")
        for m in missing:
            print(f"  - {m}")
        print()

    if empty:
        print("EMPTY in MSSQL — table exists but no rows (may be normal):")
        for e in empty:
            print(f"  - {e}")
        print()

    print("--- All MSSQL tables in 4 dumps (for reference) ---")
    for db in MSSQL_DUMPS:
        print(f"\n[{db}]")
        items = sorted(inv[db].items(), key=lambda x: -x[1][1])
        for _k, (orig, rows) in items[:20]:
            print(f"  {orig:45} {rows:>12,}")
        if len(items) > 20:
            print(f"  ... and {len(items) - 20} more tables")


if __name__ == "__main__":
    main()
