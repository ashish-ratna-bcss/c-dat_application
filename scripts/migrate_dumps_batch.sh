#!/usr/bin/env bash
# Restore MSSQL dumps and copy into new Postgres DBs.
# Safe: only writes mssql_dump_* and JRMS_DB / IR_DB / ROWDY_SHEETS_DB.
# CDR is NOT started here (run separately when ready).
set -euo pipefail

LOGDIR="${MIGRATION_LOG_DIR:-/mnt/storage1/migration_logs}"
LOCK="${MIGRATION_LOCK_FILE:-/tmp/mssql_restore.lock}"
mkdir -p "$LOGDIR"

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SQLCMD=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql
  /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA")

log() { echo "[$(date '+%F %T')] $*" | tee -a "$LOGDIR/batch.log"; }

restore_bak() {
  local src="$1"
  local ctr_bak="$2"
  local dbname="$3"
  local data_file="$4"
  local log_file="$5"
  local data_logical="${6:-}"
  local log_logical="${7:-}"

  log "RESTORE start: $dbname from $src"
  (
    flock -x 200
    if docker exec mssql test -f "$ctr_bak"; then
      log "RESTORE bak already staged: $ctr_bak"
    else
      log "RESTORE copying dump into container..."
      docker cp "$src" "mssql:$ctr_bak"
    fi
    if [[ -z "$data_logical" || -z "$log_logical" ]]; then
      read -r data_logical log_logical < <(
        "${SQLCMD[@]}" -s'|' -W -h -1 -Q "RESTORE FILELISTONLY FROM DISK = N'$ctr_bak';" \
          | awk -F'|' '
              $3 ~ /^D/ && !d { d=$1; gsub(/^ +| +$/, "", d) }
              $3 ~ /^L/ && !l { l=$1; gsub(/^ +| +$/, "", l) }
              END { if (d && l) print d, l; else exit 1 }'
      )
      log "RESTORE discovered logicals: data=$data_logical log=$log_logical"
    fi
    "${SQLCMD[@]}" -Q "
SET NOCOUNT ON;
IF DB_ID(N'$dbname') IS NOT NULL
BEGIN
  PRINT 'Database $dbname already exists — skip restore';
END
ELSE
BEGIN
  RESTORE DATABASE [$dbname]
  FROM DISK = N'$ctr_bak'
  WITH
    MOVE N'$data_logical' TO N'$data_file',
    MOVE N'$log_logical' TO N'$log_file',
    RECOVERY,
    STATS = 10;
END
"
  ) 200>"$LOCK"
  log "RESTORE done: $dbname"
}

run_jrms() {
  local log="$LOGDIR/jrms.log"
  {
    log "JRMS job started"
    restore_bak \
      '/media/hyd-cat/Extreme SSD/BACKUPS 10-07-2026/JRMS' \
      '/var/opt/mssql/data/JRMS.bak' \
      'mssql_dump_jrms' \
      '/var/opt/mssql/data/mssql_dump_jrms.mdf' \
      '/var/opt/mssql/data/mssql_dump_jrms_log.ldf' \
      'JRMS_BACKUP1' \
      'JRMS_BACKUP1_log'
    python3 /tmp/migrate_copy.py jrms
    log "JRMS job finished"
  } >>"$log" 2>&1
}

run_ir() {
  local log="$LOGDIR/ir.log"
  {
    log "IR job started"
    restore_bak \
      '/media/hyd-cat/Extreme SSD/BACKUPS 10-07-2026/IRS_REPORT' \
      '/var/opt/mssql/data/IRS_REPORT.bak' \
      'mssql_dump_ir' \
      '/var/opt/mssql/data/mssql_dump_ir.mdf' \
      '/var/opt/mssql/data/mssql_dump_ir_log.ldf' \
      'FORMS' \
      'FORMS_log'
    python3 /tmp/migrate_copy.py ir
    log "IR job finished"
  } >>"$log" 2>&1
}

run_rowdy() {
  local log="$LOGDIR/rowdy.log"
  {
    log "ROWDY job started (CDATDUPL2 ~49GB restore)"
    restore_bak \
      '/media/hyd-cat/Extreme SSD/BACKUPS 10-07-2026/CDATDUPL2' \
      '/var/opt/mssql/data/CDATDUPL2.bak' \
      'mssql_dump_cdatdupl' \
      '/var/opt/mssql/data/mssql_dump_cdatdupl.mdf' \
      '/var/opt/mssql/data/mssql_dump_cdatdupl_log.ldf'
    python3 /tmp/migrate_copy.py rowdy
    log "ROWDY job finished"
  } >>"$log" 2>&1
}

main() {
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
  log "Batch migration starting (JRMS + IR + Rowdy parallel; CDR skipped)"
  run_jrms &
  pid_jrms=$!
  run_ir &
  pid_ir=$!
  run_rowdy &
  pid_rowdy=$!
  wait "$pid_jrms" && log "JRMS exit OK" || log "JRMS exit FAIL"
  wait "$pid_ir" && log "IR exit OK" || log "IR exit FAIL"
  wait "$pid_rowdy" && log "ROWDY exit OK" || log "ROWDY exit FAIL"
  log "Batch migration finished. CDR not started — run separately when ready."
}

main "$@"
