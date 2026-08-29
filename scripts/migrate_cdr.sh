#!/usr/bin/env bash
# Restore CDATPCSUSPECT dump as mssql_dump_cdr, and copy into Postgres CDATDUPL_DB.
# Does not touch old Postgres DBs (postgres, cdat_db, distributed_db).
set -euo pipefail
LOGDIR="${MIGRATION_LOG_DIR:-/mnt/storage1/migration_logs}"
LOCK="${MIGRATION_LOCK_FILE:-/tmp/mssql_restore.lock}"
mkdir -p "$LOGDIR"
exec >>"$LOGDIR/cdr.log" 2>&1

log() { echo "[$(date '+%F %T')] $*"; }

if [[ -n "${MIGRATE_PG_B64:-}" ]]; then
  echo "$MIGRATE_PG_B64" | base64 -d > /tmp/migrate_pgpass
  chmod 600 /tmp/migrate_pgpass
fi
if [[ -z "${PGPASSWORD:-}" && -f /tmp/migrate_pgpass ]]; then
  export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
fi
if [[ -z "${PGPASSWORD:-}" ]]; then
  echo "PGPASSWORD or MIGRATE_PG_B64 required" >&2
  exit 1
fi

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SQLCMD=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql
  /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA")

SRC='/media/hyd-cat/Extreme SSD/CDAT_BACKUP/HYD_UNIT_CDATPCSUSPECT_22042026.BAK'
CTR_BAK='/var/opt/mssql/data/HYD_UNIT_CDATPCSUSPECT_22042026.BAK'
DBNAME='mssql_dump_cdr'
DATA_FILE='/var/opt/mssql/data/mssql_dump_cdr.mdf'
LOG_FILE='/var/opt/mssql/data/mssql_dump_cdr_log.ldf'

log "CDR job started"

log "Widen CDATDUPL_DB.cdatpcsuspect columns for dump data"
psql -h 127.0.0.1 -U postgres -d CDATDUPL_DB -v ON_ERROR_STOP=1 <<'SQL'
ALTER TABLE cdatpcsuspect ALTER COLUMN ucid TYPE bigint;
ALTER TABLE cdatpcsuspect ALTER COLUMN phone TYPE varchar(25);
ALTER TABLE cdatpcsuspect ALTER COLUMN other TYPE varchar(50);
SQL

hyd_has_cdr=$("${SQLCMD[@]}" -h -1 -W -Q "SET NOCOUNT ON; USE [HYD_UNIT_CDAT]; SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME IN ('CDATPCSUSPECT','cdatpcsuspect');" | tr -d '[:space:]' || true)
log "HYD_UNIT_CDAT cdatpcsuspect tables=$hyd_has_cdr"

restore_mssql_dump_cdr() {
  (
    flock -x 200
    if docker exec mssql test -f "$CTR_BAK"; then
      log "bak already staged: $CTR_BAK"
    else
      log "copying CDR bak into MSSQL data dir"
      docker cp "$SRC" "mssql:$CTR_BAK"
    fi
    read -r data_logical log_logical < <(
      "${SQLCMD[@]}" -s'|' -W -h -1 -Q "RESTORE FILELISTONLY FROM DISK = N'$CTR_BAK';" \
        | awk -F'|' '
            $3 ~ /^D/ && !d { d=$1; gsub(/^ +| +$/, "", d) }
            $3 ~ /^L/ && !l { l=$1; gsub(/^ +| +$/, "", l) }
            END { if (d && l) print d, l; else exit 1 }'
    )
    log "logicals data=$data_logical log=$log_logical"
    "${SQLCMD[@]}" -Q "
SET NOCOUNT ON;
IF DB_ID(N'$DBNAME') IS NOT NULL
BEGIN
  PRINT 'Database $DBNAME already exists — skip restore';
END
ELSE
BEGIN
  RESTORE DATABASE [$DBNAME]
  FROM DISK = N'$CTR_BAK'
  WITH
    MOVE N'$data_logical' TO N'$DATA_FILE',
    MOVE N'$log_logical' TO N'$LOG_FILE',
    RECOVERY,
    STATS = 10;
END
"
  ) 200>"$LOCK"
}

log "Starting mssql_dump_cdr restore in background"
restore_mssql_dump_cdr &
restore_pid=$!

if [[ "$hyd_has_cdr" == "1" ]]; then
  log "Copying CDR tables from existing HYD_UNIT_CDAT into Postgres CDATDUPL_DB (read-only on MSSQL)"
  CDR_MSSQL_DB=HYD_UNIT_CDAT python3 /tmp/migrate_copy.py cdr
  wait "$restore_pid" && log "RESTORE mssql_dump_cdr OK" || log "RESTORE mssql_dump_cdr FAIL"
else
  wait "$restore_pid" && log "RESTORE mssql_dump_cdr OK" || { log "RESTORE mssql_dump_cdr FAIL"; exit 1; }
  export CDR_MSSQL_DB="$DBNAME"
  python3 /tmp/migrate_copy.py cdr
fi
log "CDR job finished"
