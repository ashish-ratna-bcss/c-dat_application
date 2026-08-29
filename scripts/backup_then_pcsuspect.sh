#!/usr/bin/env bash
# Sequential job:
#  1) Backup postgres + distributed_db + cdat_db to /mnt/storage2 (compressed)
#  2) Verify backups
#  3) Start cdatpcsuspect copy HYD_UNIT_CDAT -> CDATDUPL_DB
set -euo pipefail

OUT_ROOT="${BACKUP_ROOT:-/mnt/storage2/pg_db_backups}"
STAMP="$(date +%Y%m%d_%H%M%S)"
OUT_DIR="$OUT_ROOT/$STAMP"
LOG="$OUT_ROOT/pipeline_${STAMP}.log"
MIGRATE_PY="${MIGRATE_PY:-/home/hyd-cat/migrate_copy.py}"
ENV_FILE="${ENV_FILE:-/mnt/storage1/c-dat_application/.env}"
MIN_FREE_GB_FOR_PCSUSPECT="${MIN_FREE_GB_FOR_PCSUSPECT:-500}"

mkdir -p "$OUT_DIR"
exec > >(tee -a "$LOG") 2>&1

echo "=============================================="
echo "Pipeline started: $(date)"
echo "Backup dir: $OUT_DIR"
echo "Log: $LOG"
echo "=============================================="

if [[ -f "$ENV_FILE" ]]; then
  export PGPASSWORD="$(grep -E '^CDR_DB_PASSWORD=' "$ENV_FILE" | cut -d= -f2- | tr -d '"')"
fi
if [[ -z "${PGPASSWORD:-}" && -f /tmp/migrate_pgpass ]]; then
  export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
fi
if [[ -z "${PGPASSWORD:-}" ]]; then
  echo "ERROR: PGPASSWORD not available"
  exit 1
fi

free_gb() {
  df -BG "$1" | awk 'NR==2{gsub(/G/,"",$4); print $4}'
}

backup_one() {
  local db="$1"
  local out="$OUT_DIR/${db}.dump"
  echo "----- BACKUP $db -> $out -----"
  # custom compressed format (-Fc); no owner/acl for easier restore
  pg_dump -h 127.0.0.1 -U postgres -d "$db" -Fc -Z 6 -f "$out"
  ls -lh "$out"
  # basic verify: list TOC
  pg_restore -l "$out" >/dev/null
  echo "OK verified $db"
}

echo "=== STEP 1/3: Backup 3 databases ==="
backup_one "postgres"
backup_one "distributed_db"
backup_one "cdat_db"

echo "=== Writing checksums ==="
(
  cd "$OUT_DIR"
  sha256sum *.dump > SHA256SUMS.txt
  ls -lh
  cat SHA256SUMS.txt
)

echo "=== STEP 2/3: Verify all dumps present ==="
for db in postgres distributed_db cdat_db; do
  f="$OUT_DIR/${db}.dump"
  [[ -s "$f" ]] || { echo "ERROR: missing/empty $f"; exit 1; }
  pg_restore -l "$f" >/dev/null || { echo "ERROR: corrupt $f"; exit 1; }
done
echo "ALL BACKUPS OK"

FREE1="$(free_gb /mnt/storage1)"
FREE2="$(free_gb /mnt/storage2)"
echo "Free storage1=${FREE1}G storage2=${FREE2}G"

if [[ "${FREE1}" -lt "${MIN_FREE_GB_FOR_PCSUSPECT}" ]] || [[ "${AUTO_DROP_OLD_DBS:-1}" == "1" ]]; then
  echo "Dropping old DBs after verified backup (cdat_db, distributed_db, postgres)..."
  echo "Will recreate empty postgres database for system use."
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
  FREE1="$(free_gb /mnt/storage1)"
  echo "After drop, storage1 free=${FREE1}G"
fi

if [[ "${FREE1}" -lt "${MIN_FREE_GB_FOR_PCSUSPECT}" ]]; then
  echo "WARNING: storage1 free ${FREE1}G still < ${MIN_FREE_GB_FOR_PCSUSPECT}G after drop"
  echo "PIPELINE_PARTIAL_BACKUP_ONLY $(date)"
  exit 0
fi

echo "=== STEP 3/3: Copy pcsuspect ==="
export MIGRATE_BATCH_SIZE="${MIGRATE_BATCH_SIZE:-2000}"
export CDR_MSSQL_DB=HYD_UNIT_CDAT
export CDR_MSSQL_TABLE=HYD_UNIT_CDATPCSUSPECT
nice -n 10 ionice -c2 -n7 python3 "$MIGRATE_PY" pcsuspect

echo "PIPELINE_COMPLETE $(date)"
