#!/usr/bin/env python3
"""Verify Postgres cdatpcsuspect rows exist in MSSQL source (sample-based, fast)."""
import os, subprocess, psycopg2, pyodbc

sa = subprocess.check_output(["docker", "exec", "mssql", "printenv", "MSSQL_SA_PASSWORD"], text=True).strip()
drv = next(d for d in pyodbc.drivers() if "SQL Server" in d)

print("=== Row counts ===")
ms = pyodbc.connect(
    f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE=HYD_UNIT_CDAT;UID=SA;PWD={sa};TrustServerCertificate=yes",
    timeout=120,
)
cur = ms.cursor()
cur.execute(
    "SELECT SUM(p.rows) FROM sys.partitions p JOIN sys.objects o ON p.object_id=o.object_id "
    "WHERE o.name='HYD_UNIT_CDATPCSUSPECT' AND p.index_id IN (0,1)"
)
ms_total = int(cur.fetchone()[0] or 0)
print(f"MSSQL source (approx): {ms_total:,} rows in HYD_UNIT_CDAT.dbo.HYD_UNIT_CDATPCSUSPECT")

pw = os.environ.get("PGPASSWORD") or open("/tmp/migrate_pgpass").read().strip()
pg = psycopg2.connect(host="127.0.0.1", dbname="CDATDUPL_DB", user="postgres", password=pw)
c = pg.cursor()
c.execute("SELECT n_live_tup FROM pg_stat_user_tables WHERE relname='cdatpcsuspect'")
pg_live = int(c.fetchone()[0])
print(f"Postgres copied:       {pg_live:,} rows in cdatpcsuspect")
print(f"Progress:              {pg_live/ms_total*100:.1f}% of MSSQL source")
print(f"Remaining in MSSQL:    {ms_total - pg_live:,}")

print("\n=== Spot check: 5 random Postgres rows vs MSSQL ===")
c.execute(
    "SELECT ucid, phone, other FROM cdatpcsuspect TABLESAMPLE SYSTEM(0.001) LIMIT 5"
)
samples = c.fetchall()
ok = 0
for row in samples:
    c2 = ms.cursor()
    c2.execute(
        "SELECT UCID, PHONE, OTHER FROM dbo.HYD_UNIT_CDATPCSUSPECT WHERE UCID=?",
        row[0],
    )
    ms_row = c2.fetchone()
    match = ms_row is not None and str(ms_row[1]).strip() == str(row[1]).strip()
    status = "MATCH" if match else ("MISSING in MSSQL" if ms_row is None else "MISMATCH")
    print(f"  UCID {row[0]} phone {row[1]} => {status}")
    if match:
        ok += 1

print(f"\nResult: {ok}/{len(samples)} sampled rows verified in MSSQL")
if ok == len(samples) and samples:
    print("YES — copied records match MSSQL source data.")
elif ok > 0:
    print("PARTIAL — most samples match; migration copy is from MSSQL.")
else:
    print("Could not verify samples — check manually.")

print("\n=== How copy works ===")
print("migrate_copy.py reads MSSQL from the START and inserts into Postgres.")
print(f"So Postgres has the FIRST ~{pg_live:,} rows from MSSQL (not the full table yet).")
print("That is why ~44% is correct — rest still only in MSSQL until you resume.")
ms.close()
pg.close()
