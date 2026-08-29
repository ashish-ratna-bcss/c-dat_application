#!/usr/bin/env bash
# Drop tc_backup_db and back_up_2022 from MSSQL only; keep SSD backups
set -euo pipefail

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W -h -1)

echo "=== Confirm SSD backups exist ==="
ls -lh '/media/hyd-cat/Extreme SSD/SDR_CELLID_TC_03022026_FROM_CICELL/TC_BACKUP_03022026.BAK'
ls -lh '/media/hyd-cat/Extreme SSD/2022_back_up/' 2>/dev/null || true

drop_one() {
  local db="$1"
  echo "=== Dropping $db ==="
  "${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'$db') IS NOT NULL
BEGIN
  ALTER DATABASE [$db] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
  DROP DATABASE [$db];
  PRINT 'DROPPED $db';
END
ELSE
  PRINT 'NOT_FOUND $db';
"
}

drop_one "tc_backup_db"
drop_one "back_up_2022"

echo "=== Remaining match ==="
"${SC[@]}" -Q "SET NOCOUNT ON; SELECT name FROM sys.databases WHERE name IN (N'tc_backup_db', N'back_up_2022');" || true

echo "=== Data files ==="
docker exec mssql bash -lc 'ls -lh /var/opt/mssql/data/tc_backup* /var/opt/mssql/data/back_up_2022* 2>&1 || true'

echo "=== Disk ==="
df -h /mnt/storage1 | tail -1
echo DONE
