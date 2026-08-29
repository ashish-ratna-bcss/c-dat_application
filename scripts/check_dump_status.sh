#!/usr/bin/env bash
set -euo pipefail

if [[ -f /tmp/migrate_pgpass ]]; then
  export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
elif [[ -z "${PGPASSWORD:-}" ]]; then
  echo "No /tmp/migrate_pgpass and no PGPASSWORD" >&2
  exit 1
fi

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W -h -1)

echo "=== MSSQL DATABASES ==="
"${SC[@]}" -Q "SET NOCOUNT ON; SELECT name, state_desc FROM sys.databases WHERE name LIKE 'mssql_dump_%' OR name='HYD_UNIT_CDAT' ORDER BY name;"

echo
echo "=== MSSQL KEY ROW COUNTS ==="
"${SC[@]}" -Q "SET NOCOUNT ON;
USE mssql_dump_pdact; SELECT 'PDACT_MAIN_TABLE', SUM(p.rows) FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='PDACT_MAIN_TABLE' AND p.index_id IN (0,1);
USE mssql_dump_jrms; SELECT 'JRMS_TOTAL', SUM(p.rows) FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='JRMS_TOTAL' AND p.index_id IN (0,1);
USE mssql_dump_ir; SELECT 'IMAGE_TABLE', SUM(p.rows) FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='IMAGE_TABLE' AND p.index_id IN (0,1);
USE mssql_dump_cdatdupl; SELECT 'ROWDY_SHEETER_COMPLETE_DATA', SUM(p.rows) FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='ROWDY_SHEETER_COMPLETE_DATA' AND p.index_id IN (0,1);
USE HYD_UNIT_CDAT; SELECT 'HYD_UNIT_CDATPCSUSPECT', SUM(p.rows) FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='HYD_UNIT_CDATPCSUSPECT' AND p.index_id IN (0,1);"

echo
echo "=== POSTGRES ROW COUNTS ==="
psql -h localhost -U postgres -d PDACT_DB -t -A -c "SELECT 'PDACT_DB.pdact_main_table', COUNT(*) FROM pdact_main_table;"
psql -h localhost -U postgres -d JRMS_DB -t -A -c "SELECT 'JRMS_DB.jrms_total_2012_to_2017', COUNT(*) FROM jrms_total_2012_to_2017;"
psql -h localhost -U postgres -d IR_DB -t -A -c "SELECT relname || '|' || n_live_tup FROM pg_stat_user_tables ORDER BY relname;"
psql -h localhost -U postgres -d ROWDY_SHEETS_DB -t -A -c "SELECT 'ROWDY_SHEETS_DB.rowdy_sheeter_complete_data', COUNT(*) FROM rowdy_sheeter_complete_data;" 2>&1 || true
psql -h localhost -U postgres -d CDATDUPL_DB -t -A -c "SELECT 'CDATDUPL_DB.cdatpcsuspect', COUNT(*) FROM cdatpcsuspect;"

echo
echo "=== RUNNING MIGRATION PROCESSES ==="
ps aux | grep -E 'migrate_copy|migrate_cdr|migrate_dumps|check_dump' | grep -v grep || echo "none"
