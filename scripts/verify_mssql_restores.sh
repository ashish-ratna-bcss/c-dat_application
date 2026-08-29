#!/usr/bin/env bash
# Verify MSSQL restores vs USB .BAK files
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W -h -1)

echo "========== VERIFICATION: MSSQL RESTORES =========="
echo

echo "--- 1. All mssql_dump_* databases ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT name, state_desc, CONVERT(varchar(19), create_date, 120) AS created
FROM sys.databases WHERE name LIKE 'mssql_dump_%' ORDER BY name;"

echo
echo "--- 2. Quick health: table count + total rows per dump DB ---"
for db in mssql_dump_pdact mssql_dump_jrms mssql_dump_ir mssql_dump_cdatdupl mssql_dump_cdr; do
  echo -n "[$db] "
  "${SC[@]}" -Q "SET NOCOUNT ON;
  IF DB_ID(N'$db') IS NULL BEGIN SELECT 'MISSING'; END
  ELSE BEGIN
    DECLARE @sql nvarchar(max) = N'USE [$db]; SELECT CAST(COUNT(DISTINCT t.object_id) AS varchar) + '' tables, '' + CAST(SUM(p.rows) AS varchar) + '' total rows'' FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0;';
    EXEC sp_executesql @sql;
  END" 2>/dev/null | tr -d '\r' | head -1
done

echo
echo "[HYD_UNIT_CDAT - CDR alternative]"
"${SC[@]}" -Q "SET NOCOUNT ON; USE HYD_UNIT_CDAT;
SELECT CAST(COUNT(DISTINCT t.object_id) AS varchar) + ' tables, ' + CAST(SUM(p.rows) AS varchar) + ' total rows'
FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0;"

echo
echo "--- 3. Key table spot-check (expected main table per dump) ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
USE mssql_dump_pdact;  SELECT 'PDACT' dump, 'PDACT_MAIN_TABLE' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='PDACT_MAIN_TABLE' AND p.index_id IN (0,1);
USE mssql_dump_jrms;   SELECT 'JRMS' dump, 'JRMS_TOTAL' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='JRMS_TOTAL' AND p.index_id IN (0,1);
USE mssql_dump_ir;     SELECT 'IR' dump, 'IR_PARTICULARS' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='IR_PARTICULARS' AND p.index_id IN (0,1);
USE mssql_dump_cdatdupl; SELECT 'CDATDUPL2' dump, 'CDAT_TOWERDATA_ALL' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='CDAT_TOWERDATA_ALL' AND p.index_id IN (0,1);
IF DB_ID(N'mssql_dump_cdr') IS NOT NULL
  EXEC(N'USE mssql_dump_cdr; SELECT ''CDR'' dump, ''CDATPCSUSPECT'' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name LIKE ''%CDATPCSUSPECT%'' AND p.index_id IN (0,1);');
ELSE SELECT 'CDR' dump, 'mssql_dump_cdr' tbl, NULL cnt;
USE HYD_UNIT_CDAT; SELECT 'CDR-alt' dump, 'HYD_UNIT_CDATPCSUSPECT' tbl, SUM(p.rows) cnt FROM sys.partitions p JOIN sys.tables t ON p.object_id=t.object_id WHERE t.name='HYD_UNIT_CDATPCSUSPECT' AND p.index_id IN (0,1);"

echo
echo "--- 4. USB .BAK files (full list with sizes) ---"
find "/media/hyd-cat/Extreme SSD" -iname '*.bak' -type f 2>/dev/null \
  | while read -r f; do
    sz=$(stat -c%s "$f" 2>/dev/null || stat -f%z "$f" 2>/dev/null)
    gb=$(echo "scale=1; $sz/1024/1024/1024" | bc)
    printf "%6.1f GB  %s\n" "$gb" "$f"
  done | sort -rn

echo
echo "--- 5. FINAL VERDICT ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT
  'mssql_dump_pdact' AS db,
  CASE WHEN DB_ID(N'mssql_dump_pdact') IS NOT NULL AND (SELECT state FROM sys.databases WHERE name='mssql_dump_pdact')=0 THEN 'RESTORED OK' ELSE 'MISSING/FAIL' END AS status
UNION ALL SELECT 'mssql_dump_jrms',
  CASE WHEN DB_ID(N'mssql_dump_jrms') IS NOT NULL AND (SELECT state FROM sys.databases WHERE name='mssql_dump_jrms')=0 THEN 'RESTORED OK' ELSE 'MISSING/FAIL' END
UNION ALL SELECT 'mssql_dump_ir',
  CASE WHEN DB_ID(N'mssql_dump_ir') IS NOT NULL AND (SELECT state FROM sys.databases WHERE name='mssql_dump_ir')=0 THEN 'RESTORED OK' ELSE 'MISSING/FAIL' END
UNION ALL SELECT 'mssql_dump_cdatdupl',
  CASE WHEN DB_ID(N'mssql_dump_cdatdupl') IS NOT NULL AND (SELECT state FROM sys.databases WHERE name='mssql_dump_cdatdupl')=0 THEN 'RESTORED OK' ELSE 'MISSING/FAIL' END
UNION ALL SELECT 'mssql_dump_cdr (from .BAK)',
  CASE WHEN DB_ID(N'mssql_dump_cdr') IS NOT NULL THEN 'RESTORED OK' ELSE 'NOT RESTORED' END
UNION ALL SELECT 'HYD_UNIT_CDAT (CDR alt)',
  CASE WHEN DB_ID(N'HYD_UNIT_CDAT') IS NOT NULL THEN 'EXISTS (use for CDR)' ELSE 'MISSING' END;"
