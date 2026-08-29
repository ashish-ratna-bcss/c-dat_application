#!/usr/bin/env bash
# After 3-DB backups are verified:
#  - DROP cdat_db, distributed_db, postgres
#  - CREATE empty postgres (so server tools still work)
#  - START pcsuspect copy
set -euo pipefail

BACKUP_ROOT="${BACKUP_ROOT:-/mnt/storage2/pg_db_backups}"
ENV_FILE="${ENV_FILE:-/mnt/storage1/c-dat_application/.env}"
MIGRATE_PY="${MIGRATE_PY:-/home/hyd-cat/migrate_copy.py}"
LOG="$BACKUP_ROOT/continue_after_backup.log"

mkdir -p "$BACKUP_ROOT"
exec > >(tee -a "$LOG") 2>&1

echo "=============================================="
echo "Continue-after-backup started: $(date)"
echo "=============================================="

if [[ -f "$ENV_FILE" ]]; then
  export PGPASSWORD="$(grep -E '^CDR_DB_PASSWORD=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')"
fi

echo "Waiting for ALL BACKUPS OK..."
while true; do
  if grep -q "ALL BACKUPS OK" "$BACKUP_ROOT"/pipeline_*.log 2>/dev/null; then
    break
  fi
  if grep -q "PIPELINE_COMPLETE\|PIPELINE_PARTIAL_BACKUP_ONLY\|ERROR:" "$BACKUP_ROOT"/pipeline_*.log 2>/dev/null \
     && ! pgrep -f "pg_dump -h 127.0.0.1" >/dev/null 2>&1 \
     && ! pgrep -f "backup_then_pcsuspect.sh" >/dev/null 2>&1; then
    # backup process ended; check if dumps exist
    break
  fi
  sleep 60
done

# Find latest stamp dir with 3 dumps
OUT_DIR="$(ls -1dt "$BACKUP_ROOT"/20* 2>/dev/null | head -1 || true)"
if [[ -z "$OUT_DIR" ]]; then
  echo "ERROR: no backup directory found"
  exit 1
fi
echo "Using backup dir: $OUT_DIR"

for db in postgres distributed_db cdat_db; do
  f="$OUT_DIR/${db}.dump"
  echo "Verify $f"
  [[ -s "$f" ]] || { echo "ERROR: missing/empty $f"; exit 1; }
  pg_restore -l "$f" >/dev/null || { echo "ERROR: corrupt $f"; exit 1; }
done
echo "ALL THREE DUMPS VERIFIED"

echo "=== Dropping old DBs (cdat_db, distributed_db, postgres) ==="
psql -h 127.0.0.1 -U postgres -d template1 -v ON_ERROR_STOP=1 <<'SQL'
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname IN ('cdat_db', 'distributed_db', 'postgres')
  AND pid <> pg_backend_pid();

DROP DATABASE IF EXISTS cdat_db;
DROP DATABASE IF EXISTS distributed_db;
DROP DATABASE IF EXISTS postgres;

CREATE DATABASE postgres OWNER postgres;
SQL

echo "=== Free space after drop ==="
df -h /mnt/storage1 | tail -1

echo "=== Starting pcsuspect copy ==="
export MIGRATE_BATCH_SIZE="${MIGRATE_BATCH_SIZE:-2000}"
export CDR_MSSQL_DB=HYD_UNIT_CDAT
export CDR_MSSQL_TABLE=HYD_UNIT_CDATPCSUSPECT
nohup nice -n 10 ionice -c2 -n7 python3 "$MIGRATE_PY" pcsuspect \
  > /tmp/migrate_pcsuspect.log 2>&1 &
echo "pcsuspect PID=$! log=/tmp/migrate_pcsuspect.log"
echo "CONTINUE_COMPLETE $(date)"
