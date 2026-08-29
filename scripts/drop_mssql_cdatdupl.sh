#!/usr/bin/env bash
# Drop only mssql_dump_cdatdupl from Docker MSSQL
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W -h -1)

echo "Dropping mssql_dump_cdatdupl..."
"${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'mssql_dump_cdatdupl') IS NOT NULL
BEGIN
  ALTER DATABASE [mssql_dump_cdatdupl] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
  DROP DATABASE [mssql_dump_cdatdupl];
  PRINT 'DROPPED';
END
ELSE
  PRINT 'NOT_FOUND';
"

echo "Remaining match:"
"${SC[@]}" -Q "SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name LIKE 'mssql_dump_cdat%';" || true

echo "Files left:"
docker exec mssql bash -lc 'ls -lh /var/opt/mssql/data/mssql_dump_cdatdupl* 2>&1 || true'

echo "Disk:"
df -h /mnt/storage1 | tail -1
echo DONE
