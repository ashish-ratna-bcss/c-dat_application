#!/usr/bin/env bash
# Unzip OTHER_IMAGE_RWD and restore into MSSQL as mssql_dump_other_image_rwd
set -euo pipefail

ZIP='/media/hyd-cat/Extreme SSD/CDAT_BACKUP/OTHER_IMAGE_RWD.zip'
WORKDIR='/mnt/storage1/mssql_restore_other'
HOST_BAK="$WORKDIR/OTHER_IMAGE_RWD.bak"
CTR_BAK='/var/opt/mssql/data/OTHER_IMAGE_RWD.bak'
DBNAME='mssql_dump_other_image_rwd'

mkdir -p "$WORKDIR"
echo "=== Unzip ==="
if [[ ! -f "$HOST_BAK" ]]; then
  unzip -o "$ZIP" -d "$WORKDIR"
  # file inside zip has no .bak extension
  if [[ -f "$WORKDIR/OTHER_IMAGE_RWD" && ! -f "$HOST_BAK" ]]; then
    mv "$WORKDIR/OTHER_IMAGE_RWD" "$HOST_BAK"
  fi
fi
ls -lh "$HOST_BAK"

echo "=== Copy into MSSQL container data dir ==="
# Prefer host mount if present
if [[ -d /mnt/storage1/mssql/data ]]; then
  cp -f "$HOST_BAK" /mnt/storage1/mssql/data/OTHER_IMAGE_RWD.bak
else
  docker cp "$HOST_BAK" "mssql:$CTR_BAK"
fi
docker exec mssql ls -lh "$CTR_BAK"

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -No -W)

echo "=== FILELISTONLY ==="
"${SC[@]}" -Q "RESTORE FILELISTONLY FROM DISK = N'$CTR_BAK'"

echo "=== Drop if exists ==="
"${SC[@]}" -Q "SET NOCOUNT ON;
IF DB_ID(N'$DBNAME') IS NOT NULL
BEGIN
  ALTER DATABASE [$DBNAME] SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
  DROP DATABASE [$DBNAME];
END"

echo "=== Restore ==="
# Logical names from FILELISTONLY; fallback common pattern
LOGICAL_DATA=$("${SC[@]}" -h -1 -Q "SET NOCOUNT ON; RESTORE FILELISTONLY FROM DISK = N'$CTR_BAK';" | awk 'NR==1{print $1}')
LOGICAL_LOG=$("${SC[@]}" -h -1 -Q "SET NOCOUNT ON; RESTORE FILELISTONLY FROM DISK = N'$CTR_BAK';" | awk 'NR==2{print $1}')
echo "Logical data=$LOGICAL_DATA log=$LOGICAL_LOG"

"${SC[@]}" -Q "RESTORE DATABASE [$DBNAME]
FROM DISK = N'$CTR_BAK'
WITH MOVE N'$LOGICAL_DATA' TO N'/var/opt/mssql/data/${DBNAME}.mdf',
     MOVE N'$LOGICAL_LOG' TO N'/var/opt/mssql/data/${DBNAME}_log.ldf',
     REPLACE, STATS=10;"

echo "=== Tables ==="
"${SC[@]}" -Q "SET NOCOUNT ON; USE [$DBNAME];
SELECT t.name AS table_name, SUM(p.rows) AS row_count
FROM sys.tables t
JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1) AND t.is_ms_shipped=0
GROUP BY t.name
ORDER BY t.name;"

echo DONE
