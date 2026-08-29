#!/usr/bin/env bash
# Show EXACTLY which databases/tables and sizes are used in space calculation
set -euo pipefail

if [[ -f /tmp/migrate_pgpass ]]; then export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
elif [[ -n "${PGPASSWORD:-}" ]]; then :
else
  PGPASS=$(grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/c-dat_application/.env 2>/dev/null | cut -d= -f2- | tr -d '"' || true)
  [[ -n "$PGPASS" ]] && export PGPASSWORD="$PGPASS"
fi

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W)

echo "========== WHICH SIZES ARE WE CALCULATING? =========="
echo

echo "--- A. DISK FREE (where Postgres writes) ---"
df -h /mnt/storage1 /mnt/storage2 | grep -v tmpfs
echo "  ^ Postgres data dir: /mnt/storage1/postgres"
echo

echo "--- B. MSSQL: each restored dump DB - file size on disk ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT
  DB_NAME(database_id) AS mssql_database,
  type_desc AS file_type,
  name AS logical_file,
  CAST(size*8.0/1024/1024 AS decimal(10,2)) AS size_gb
FROM sys.master_files
WHERE DB_NAME(database_id) IN (
  'mssql_dump_pdact','mssql_dump_jrms','mssql_dump_ir',
  'mssql_dump_cdatdupl','HYD_UNIT_CDAT','mssql_dump_cdr'
)
ORDER BY DB_NAME(database_id), type_desc;"
echo

echo "--- C. MSSQL: total per dump DB (data + log) ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT
  DB_NAME(database_id) AS mssql_database,
  CAST(SUM(size)*8.0/1024/1024 AS decimal(10,2)) AS total_gb
FROM sys.master_files
WHERE DB_NAME(database_id) IN (
  'mssql_dump_pdact','mssql_dump_jrms','mssql_dump_ir',
  'mssql_dump_cdatdupl','HYD_UNIT_CDAT','mssql_dump_cdr'
)
GROUP BY DB_NAME(database_id)
ORDER BY total_gb DESC;"
echo

echo "--- D. MSSQL: top tables BY ROW COUNT (what we copy) ---"
show_top_tables() {
  local label="$1" db="$2"
  echo "[$label] database: $db"
  "${SC[@]}" -Q "SET NOCOUNT ON; USE [$db];
  SELECT TOP 10 t.name AS table_name, SUM(p.rows) AS row_count
  FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
  WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
  GROUP BY t.name ORDER BY SUM(p.rows) DESC;"
  echo
}
show_top_tables "PDACT -> PDACT_DB" mssql_dump_pdact
show_top_tables "JRMS -> JRMS_DB" mssql_dump_jrms
show_top_tables "IR -> IR_DB" mssql_dump_ir
show_top_tables "CDATDUPL2 -> CDATDUPL_DB lookups" mssql_dump_cdatdupl
show_top_tables "CDR -> CDATDUPL_DB calls" HYD_UNIT_CDAT

echo "--- E. POSTGRES: current NEW DB sizes (already on disk) ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -c "
    SELECT datname AS postgres_db,
           pg_size_pretty(pg_database_size(datname)) AS current_size,
           ROUND(pg_database_size(datname)/1024.0/1024/1024, 2) AS gb
    FROM pg_database
    WHERE datname IN ('PDACT_DB','JRMS_DB','IR_DB','ROWDY_SHEETS_DB','CDATDUPL_DB')
    ORDER BY pg_database_size(datname) DESC;"
fi
echo

echo "--- F. POSTGRES: per-table size in NEW DBs (what is already copied) ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  for db in PDACT_DB JRMS_DB IR_DB ROWDY_SHEETS_DB CDATDUPL_DB; do
    echo "[$db tables]"
    psql -h localhost -U postgres -d "$db" -c "
      SELECT relname AS table_name,
             pg_size_pretty(pg_total_relation_size(relid)) AS size,
             n_live_tup AS rows
      FROM pg_stat_user_tables
      ORDER BY pg_total_relation_size(relid) DESC
      LIMIT 15;" 2>/dev/null || echo "  (empty or missing)"
    echo
  done
fi

echo "--- G. WHAT GOES WHERE (migration map) ---"
cat <<'MAP'
  USB dump              MSSQL source              Postgres target        What data
  ─────────────────────────────────────────────────────────────────────────────────
  PDACT                 mssql_dump_pdact          PDACT_DB               PDACT_MAIN_TABLE (1,205 rows)
  JRMS                  mssql_dump_jrms           JRMS_DB                JRMS_TOTAL (93,119 rows)
  IRS_REPORT            mssql_dump_ir             IR_DB                  12 IR tables (~722K rows total)
  (rowdy in IR dump)    mssql_dump_ir             ROWDY_SHEETS_DB        rowdy tables from IR
  CDATDUPL2             mssql_dump_cdatdupl       CDATDUPL_DB            towers, civil, gas, passport...
  HYD_UNIT_CDATPCSUSPECT HYD_UNIT_CDAT            CDATDUPL_DB            CDATPCSUSPECT (~1.05B rows)
MAP
echo

echo "--- H. SPACE CALCULATION BREAKDOWN ---"
echo "  For small DBs (PDACT/JRMS/IR/Rowdy):"
echo "    Size used = actual Postgres DB size after copy (already mostly done for JRMS)"
echo "    IR still needs ~10 tables -> estimate from mssql_dump_ir total ~4-7 GB"
echo
echo "  For CDR (the big one):"
echo "    Source = HYD_UNIT_CDAT on MSSQL (see section C for exact GB on disk)"
echo "    Plus CDATDUPL2 lookups from mssql_dump_cdatdupl (see section C)"
echo "    Postgres usually needs similar or slightly MORE than MSSQL source size"
echo "    Because: indexes + Postgres storage overhead + WAL during insert"
echo
echo "--- I. MSSQL HYD_UNIT_CDAT + mssql_dump_cdatdupl ONLY (CDR sources) ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT DB_NAME(database_id) AS db,
       SUM(CASE WHEN type_desc='ROWS' THEN size ELSE 0 END)*8/1024/1024 AS data_gb,
       SUM(CASE WHEN type_desc='LOG' THEN size ELSE 0 END)*8/1024/1024 AS log_gb,
       SUM(size)*8/1024/1024 AS total_gb
FROM sys.master_files
WHERE DB_NAME(database_id) IN ('HYD_UNIT_CDAT','mssql_dump_cdatdupl')
GROUP BY DB_NAME(database_id);"
