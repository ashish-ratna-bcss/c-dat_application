#!/usr/bin/env bash
# Drop only cellids_db from Docker MSSQL (SSD backup kept)
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W -h -1)

echo "Confirm SSD backup exists..."
ls -lh '/media/hyd-cat/Extreme SSD/SDR_CELLID_TC_03022026_FROM_CICELL/CELLIDS_LAB5_22012026'

echo "Dropping cellids_db..."
"${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'cellids_db') IS NOT NULL
BEGIN
  ALTER DATABASE [cellids_db] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
  DROP DATABASE [cellids_db];
  PRINT 'DROPPED';
END
ELSE
  PRINT 'NOT_FOUND';
"

echo "Remaining match:"
"${SC[@]}" -Q "SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name LIKE '%cellid%';" || true

echo "Data files left:"
docker exec mssql bash -lc 'ls -lh /var/opt/mssql/data/cellids_db* 2>&1 || true'

echo "Disk:"
df -h /mnt/storage1 | tail -1
echo DONE
