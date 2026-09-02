#!/usr/bin/env python3
"""Fast MSSQL vs Postgres pcsuspect verification (approx count + UCID spot checks)."""
import os, subprocess, psycopg2, pyodbc

sa = subprocess.check_output(["docker", "exec", "mssql", "printenv", "MSSQL_SA_PASSWORD"], text=True).strip()
drv = next(d for d in pyodbc.drivers() if "SQL Server" in d)
ms = pyodbc.connect(
    f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE=HYD_UNIT_CDAT;UID=SA;PWD={sa};TrustServerCertificate=yes",
    timeout=120,
)
cur = ms.cursor()
cur.execute(
    "SELECT SUM(p.rows) FROM sys.partitions p JOIN sys.objects o ON p.object_id=o.object_id "
    "WHERE o.name='HYD_UNIT_CDATPCSUSPECT' AND p.index_id IN (0,1)"
)
ms_approx = int(cur.fetchone()[0] or 0)
print(f"MSSQL approx total rows: {ms_approx:,}")

cur.execute("SELECT TOP 1 UCID, PHONE, OTHER FROM dbo.HYD_UNIT_CDATPCSUSPECT ORDER BY UCID")
ms_first = cur.fetchone()
cur.execute("SELECT TOP 1 UCID, PHONE, OTHER FROM dbo.HYD_UNIT_CDATPCSUSPECT ORDER BY UCID DESC")
ms_last = cur.fetchone()
print(f"MSSQL first row: {ms_first}")
print(f"MSSQL last row:  {ms_last}")
ms.close()

pw = os.environ.get("PGPASSWORD") or open("/tmp/migrate_pgpass").read().strip()
pg = psycopg2.connect(host="127.0.0.1", dbname="CDATDUPL_DB", user="postgres", password=pw)
c = pg.cursor()
c.execute("SELECT n_live_tup FROM pg_stat_user_tables WHERE relname='cdatpcsuspect'")
pg_live = int(c.fetchone()[0])
print(f"Postgres live rows:     {pg_live:,}")

c.execute("SELECT ucid, phone, other FROM cdatpcsuspect ORDER BY ucid ASC LIMIT 1")
pg_first = c.fetchone()
c.execute("SELECT ucid, phone, other FROM cdatpcsuspect ORDER BY ucid DESC LIMIT 1")
pg_last = c.fetchone()
print(f"Postgres first row: {pg_first}")
print(f"Postgres last row:  {pg_last}")

if pg_last:
    ms2 = pyodbc.connect(
        f"DRIVER={{{drv}}};SERVER=127.0.0.1,1433;DATABASE=HYD_UNIT_CDAT;UID=SA;PWD={sa};TrustServerCertificate=yes",
        timeout=120,
    )
    c2 = ms2.cursor()
    c2.execute("SELECT UCID, PHONE, OTHER FROM dbo.HYD_UNIT_CDATPCSUSPECT WHERE UCID=?", pg_last[0])
    ms_match = c2.fetchone()
    ms2.close()
    print(f"\nSpot check highest Postgres UCID {pg_last[0]} in MSSQL:")
    print(f"  MSSQL:    {ms_match}")
    print(f"  Postgres: {pg_last}")
    if ms_match and str(ms_match[1]).strip() == str(pg_last[1]).strip():
        print("  => PHONE MATCHES — data copied correctly from MSSQL")
    else:
        print("  => Check phone/other manually")

pct = pg_live / ms_approx * 100 if ms_approx else 0
print(f"\nSummary: {pg_live:,} of ~{ms_approx:,} MSSQL rows copied ({pct:.1f}%)")
print(f"Remaining in MSSQL: ~{max(ms_approx - pg_live, 0):,}")
pg.close()
