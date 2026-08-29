#!/usr/bin/env bash
# Drop only CDATADDRESS table from address_db (keep address_other_state + DB)
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W -h -1)

echo "=== Before ==="
"${SC[@]}" -Q "SET NOCOUNT ON; USE address_db;
SELECT t.name, SUM(p.rows) AS row_count
FROM sys.tables t
JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
  AND t.name IN (N'CDATADDRESS', N'ADDRESS_OTHER_STATE')
GROUP BY t.name;"

echo "=== Drop CDATADDRESS only ==="
"${SC[@]}" -Q "SET NOCOUNT ON; USE address_db;
IF OBJECT_ID(N'dbo.CDATADDRESS', N'U') IS NOT NULL
BEGIN
  DROP TABLE dbo.CDATADDRESS;
  PRINT 'DROPPED CDATADDRESS';
END
ELSE
  PRINT 'CDATADDRESS NOT_FOUND';
"

echo "=== After ==="
"${SC[@]}" -Q "SET NOCOUNT ON; USE address_db;
SELECT t.name, SUM(p.rows) AS row_count
FROM sys.tables t
JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
GROUP BY t.name
ORDER BY row_count DESC;"

echo "=== Disk (may reclaim after shrink; drop frees space gradually) ==="
df -h /mnt/storage1 | tail -1
echo DONE
