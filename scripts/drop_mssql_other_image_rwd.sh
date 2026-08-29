#!/usr/bin/env bash
# Drop only mssql_dump_other_image_rwd from Docker MSSQL
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W -h -1)

echo "Dropping mssql_dump_other_image_rwd..."
"${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'mssql_dump_other_image_rwd') IS NOT NULL
BEGIN
  ALTER DATABASE [mssql_dump_other_image_rwd] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
  DROP DATABASE [mssql_dump_other_image_rwd];
  PRINT 'DROPPED';
END
ELSE
  PRINT 'NOT_FOUND';
"

echo "Cleanup bak/workdir..."
rm -f /mnt/storage1/mssql/data/OTHER_IMAGE_RWD.bak 2>/dev/null || true
rm -f /mnt/storage1/mssql_restore_other/OTHER_IMAGE_RWD.bak 2>/dev/null || true
rm -rf /mnt/storage1/mssql_restore_other 2>/dev/null || true
docker exec mssql bash -lc 'rm -f /var/opt/mssql/data/OTHER_IMAGE_RWD.bak 2>/dev/null; ls /var/opt/mssql/data/mssql_dump_other_image_rwd* 2>&1 || true' || true

echo "Remaining:"
"${SC[@]}" -Q "SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name LIKE 'mssql_dump_other%';" || true
df -h /mnt/storage1 | tail -1
echo DONE
