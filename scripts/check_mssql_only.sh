#!/usr/bin/env bash
# MSSQL restore status only — no Postgres checks.
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W)

echo "========== MSSQL RESTORE STATUS =========="
echo

echo "--- Restored databases (mssql_dump_* + HYD_UNIT_CDAT) ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT name, state_desc, create_date
FROM sys.databases
WHERE name LIKE 'mssql_dump_%' OR name = 'HYD_UNIT_CDAT'
ORDER BY name;"

echo
echo "--- Expected dump -> MSSQL mapping ---"
echo "PDACT.BAK              -> mssql_dump_pdact"
echo "JRMS.BAK               -> mssql_dump_jrms"
echo "IRS_REPORT.BAK         -> mssql_dump_ir"
echo "CDATDUPL2.BAK          -> mssql_dump_cdatdupl"
echo "HYD_UNIT_CDATPCSUSPECT -> mssql_dump_cdr (or HYD_UNIT_CDAT if pre-existing)"
echo

echo "--- Row counts per restored DB ---"

echo "[PDACT] mssql_dump_pdact"
"${SC[@]}" -Q "SET NOCOUNT ON; USE mssql_dump_pdact;
SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;"

echo "[JRMS] mssql_dump_jrms"
"${SC[@]}" -Q "SET NOCOUNT ON; USE mssql_dump_jrms;
SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;"

echo "[IR] mssql_dump_ir (main tables)"
"${SC[@]}" -Q "SET NOCOUNT ON; USE mssql_dump_ir;
SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;"

echo "[CDATDUPL2] mssql_dump_cdatdupl"
"${SC[@]}" -Q "SET NOCOUNT ON; USE mssql_dump_cdatdupl;
SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;"

echo "[CDR] mssql_dump_cdr (if exists)"
"${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'mssql_dump_cdr') IS NULL
  SELECT 'mssql_dump_cdr' AS db, 'NOT RESTORED' AS status;
ELSE BEGIN
  USE mssql_dump_cdr;
  SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
  WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;
END"

echo "[CDR alt] HYD_UNIT_CDAT (pre-existing on server)"
"${SC[@]}" -Q "SET NOCOUNT ON; USE HYD_UNIT_CDAT;
SELECT t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0 GROUP BY t.name ORDER BY cnt DESC;"

echo
echo "--- USB .BAK files (source dumps) ---"
for dir in "/media/hyd-cat/Extreme SSD" "/media/hyd-cat/Extreme SSD/CDAT_BACKUP" "/media/hyd-cat/Extreme SSD/JRMS_DATA" "/media/hyd-cat/Extreme SSD/pcsuspect_dump"; do
  if [[ -d "$dir" ]]; then
    echo "== $dir =="
    find "$dir" -maxdepth 2 -iname '*.bak' -printf '%s %p\n' 2>/dev/null | sort -rn | head -20 | awk '{printf "%.1f GB  %s\n", $1/1024/1024/1024, $2}'
  fi
done

echo
echo "--- Summary ---"
"${SC[@]}" -Q "SET NOCOUNT ON;
SELECT
  CASE WHEN DB_ID(N'mssql_dump_pdact') IS NOT NULL THEN 'YES' ELSE 'NO' END AS pdact,
  CASE WHEN DB_ID(N'mssql_dump_jrms') IS NOT NULL THEN 'YES' ELSE 'NO' END AS jrms,
  CASE WHEN DB_ID(N'mssql_dump_ir') IS NOT NULL THEN 'YES' ELSE 'NO' END AS ir,
  CASE WHEN DB_ID(N'mssql_dump_cdatdupl') IS NOT NULL THEN 'YES' ELSE 'NO' END AS cdatdupl2,
  CASE WHEN DB_ID(N'mssql_dump_cdr') IS NOT NULL THEN 'YES' ELSE 'NO' END AS cdr_bak_restore,
  CASE WHEN DB_ID(N'HYD_UNIT_CDAT') IS NOT NULL THEN 'YES' ELSE 'NO' END AS hyd_unit_cdat_alt;"
