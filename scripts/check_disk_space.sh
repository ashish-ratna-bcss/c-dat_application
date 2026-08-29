#!/usr/bin/env bash
# Estimate disk space needed for full Postgres migration copy
set -euo pipefail

if [[ -f /tmp/migrate_pgpass ]]; then export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
elif [[ -n "${PGPASSWORD:-}" ]]; then :
else
  PGPASS=$(grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/c-dat_application/.env 2>/dev/null | cut -d= -f2- | tr -d '"' || true)
  [[ -n "$PGPASS" ]] && export PGPASSWORD="$PGPASS"
fi

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W -h -1)

echo "========== DISK SPACE CHECK FOR POSTGRES COPY =========="
echo "Time: $(date)"
echo

echo "--- 1. Filesystem free space ---"
df -h /mnt/storage1 /mnt/storage2 / 2>/dev/null | grep -v tmpfs
echo

echo "--- 2. Postgres data directory ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  PGDATA=$(psql -h localhost -U postgres -d postgres -t -A -c "SHOW data_directory;")
  echo "  data_directory: $PGDATA"
  df -h "$PGDATA" 2>/dev/null | tail -1 | awk '{print "  mount: "$6"  total="$2"  used="$3"  avail="$4"  use="$5}'
fi
echo

echo "--- 3. MSSQL database sizes on disk (source data) ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT name,
       CAST(SUM(CAST(size AS bigint)*8/1024.0/1024) AS decimal(10,1)) AS size_gb
FROM sys.master_files
WHERE DB_NAME(database_id) IN ('mssql_dump_pdact','mssql_dump_jrms','mssql_dump_ir','mssql_dump_cdatdupl','HYD_UNIT_CDAT')
GROUP BY name ORDER BY size_gb DESC;"
echo

echo "--- 4. Current NEW Postgres DB sizes ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -c "
    SELECT datname, pg_size_pretty(pg_database_size(datname)) AS size,
           pg_database_size(datname) AS bytes
    FROM pg_database
    WHERE datname IN ('CDATDUPL_DB','IR_DB','JRMS_DB','PDACT_DB','ROWDY_SHEETS_DB')
    ORDER BY pg_database_size(datname) DESC;"
fi
echo

echo "--- 5. OLD Postgres DB sizes (untouched, for reference) ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -c "
    SELECT datname, pg_size_pretty(pg_database_size(datname)) AS size
    FROM pg_database
    WHERE datname IN ('postgres','cdat_db','distributed_db')
    ORDER BY pg_database_size(datname) DESC;"
fi
echo

echo "--- 6. MSSQL row counts for migration targets ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT 'PDACT_DB' target, SUM(p.rows) rows FROM mssql_dump_pdact.sys.partitions p JOIN mssql_dump_pdact.sys.tables t ON p.object_id=t.object_id WHERE t.name='PDACT_MAIN_TABLE' AND p.index_id IN (0,1)
UNION ALL SELECT 'JRMS_DB', SUM(p.rows) FROM mssql_dump_jrms.sys.partitions p JOIN mssql_dump_jrms.sys.tables t ON p.object_id=t.object_id WHERE t.name='JRMS_TOTAL' AND p.index_id IN (0,1)
UNION ALL SELECT 'IR_DB (all tables in dump)', SUM(p.rows) FROM mssql_dump_ir.sys.partitions p JOIN mssql_dump_ir.sys.tables t ON p.object_id=t.object_id WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
UNION ALL SELECT 'CDATDUPL2 lookup (13 tables)', SUM(p.rows) FROM mssql_dump_cdatdupl.sys.partitions p JOIN mssql_dump_cdatdupl.sys.tables t ON p.object_id=t.object_id WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
UNION ALL SELECT 'CDR cdatpcsuspect', SUM(p.rows) FROM HYD_UNIT_CDAT.sys.partitions p JOIN HYD_UNIT_CDAT.sys.tables t ON p.object_id=t.object_id WHERE t.name='HYD_UNIT_CDATPCSUSPECT' AND p.index_id IN (0,1);"
echo

echo "--- 7. Space estimate for FULL copy (rough) ---"
# MSSQL sizes as lower bound; Postgres often 1.0-1.3x for same data
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -t -A <<'SQL'
WITH mssql_est AS (
  SELECT 'PDACT_DB' db, 0.001::numeric AS est_gb UNION ALL
  SELECT 'JRMS_DB', 0.8 UNION ALL
  SELECT 'IR_DB', 4.5 UNION ALL
  SELECT 'ROWDY_SHEETS_DB', 0.05 UNION ALL
  SELECT 'CDATDUPL_DB (cdatpcsuspect only)', 350 UNION ALL
  SELECT 'CDATDUPL_DB (cdatdupl lookups)', 80
),
current AS (
  SELECT datname, pg_database_size(datname)/1024.0/1024/1024 AS cur_gb
  FROM pg_database
  WHERE datname IN ('CDATDUPL_DB','IR_DB','JRMS_DB','PDACT_DB','ROWDY_SHEETS_DB')
)
SELECT m.db,
       ROUND(m.est_gb, 1) AS estimated_final_gb,
       ROUND(COALESCE(c.cur_gb, 0), 2) AS current_gb,
       ROUND(GREATEST(m.est_gb - COALESCE(c.cur_gb, 0), 0), 1) AS still_needed_gb
FROM mssql_est m
LEFT JOIN current c ON (
  (m.db = 'PDACT_DB' AND c.datname = 'PDACT_DB') OR
  (m.db = 'JRMS_DB' AND c.datname = 'JRMS_DB') OR
  (m.db = 'IR_DB' AND c.datname = 'IR_DB') OR
  (m.db = 'ROWDY_SHEETS_DB' AND c.datname = 'ROWDY_SHEETS_DB') OR
  (m.db LIKE 'CDATDUPL_DB%' AND c.datname = 'CDATDUPL_DB')
)
ORDER BY still_needed_gb DESC;
SQL
fi
echo

echo "--- 8. VERDICT ---"
avail1=$(df -BG /mnt/storage1 2>/dev/null | tail -1 | awk '{gsub(/G/,"",$4); print $4}')
avail2=$(df -BG /mnt/storage2 2>/dev/null | tail -1 | awk '{gsub(/G/,"",$4); print $4}')
echo "  /mnt/storage1 free: ${avail1} GB  (Postgres + MSSQL live here)"
echo "  /mnt/storage2 free: ${avail2} GB"
echo
echo "  Small jobs (PDACT + IR remainder + Rowdy): need ~5-10 GB extra  -> OK on storage1"
echo "  CDR full copy (1B rows): may need ~300-450 GB extra            -> CHECK storage1 (${avail1} GB free)"
echo
if [[ "${avail1:-0}" -lt 300 ]]; then
  echo "  ⚠️  WARNING: storage1 likely NOT enough for full CDR copy without freeing space or using storage2"
else
  echo "  ✅ storage1 may be enough for full migration (with tight margin)"
fi
