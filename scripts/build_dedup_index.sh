#!/usr/bin/env bash
#
# Build the CDR dedup composite index on cdatpcsuspect WITHOUT blocking writes.
# Uses CREATE INDEX CONCURRENTLY + lock_timeout=0. Idempotent and resumable:
# if a previous CONCURRENTLY build left an INVALID index, it is dropped first.
#
# Designed to run as a systemd oneshot service (cdat-dedup-index.service) so the
# build survives SSH disconnects.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_CONFIG="${APP_DIR}/db_config.php"

# Pull connection details from db_config.php (no secrets committed in this script).
read_cfg() {
    php -r '$c = require $argv[1]; echo $c[$argv[2]] ?? "";' "$DB_CONFIG" "$1"
}
PGHOST="$(read_cfg host)"
PGPORT="$(read_cfg port)"
PGDATABASE="$(read_cfg database)"
PGUSER="$(read_cfg user)"
PGPASSWORD="$(read_cfg password)"
# lock_timeout must be set via connection options, NOT a separate "SET" statement:
# CREATE INDEX CONCURRENTLY cannot run inside the implicit transaction that
# psql -c "SET ...; CREATE ..." would create.
PGOPTIONS="-c lock_timeout=0"
export PGHOST PGPORT PGDATABASE PGUSER PGPASSWORD PGOPTIONS

INDEX_NAME="idx_cdatpcsuspect_phone_other_starttime"

echo "[build_dedup_index] $(date -Is) starting on ${PGHOST}:${PGPORT}/${PGDATABASE}"

# If the index already exists and is valid, we're done.
VALID=$(psql -tAqc "SELECT i.indisvalid FROM pg_class c JOIN pg_index i ON i.indexrelid=c.oid WHERE c.relname='${INDEX_NAME}'" || true)
if [ "${VALID}" = "t" ]; then
    echo "[build_dedup_index] index ${INDEX_NAME} already exists and is valid. Nothing to do."
    exit 0
fi
if [ "${VALID}" = "f" ]; then
    echo "[build_dedup_index] dropping leftover INVALID index from a prior failed build."
    psql -v ON_ERROR_STOP=1 -c "DROP INDEX CONCURRENTLY IF EXISTS ${INDEX_NAME}"
fi

echo "[build_dedup_index] building CONCURRENTLY (this can take a long time on large tables)..."
psql -v ON_ERROR_STOP=1 -c "CREATE INDEX CONCURRENTLY IF NOT EXISTS ${INDEX_NAME} ON cdatpcsuspect (phone, other, starttime);"

echo "[build_dedup_index] running ANALYZE so the planner picks up the new index..."
psql -v ON_ERROR_STOP=1 -c "ANALYZE cdatpcsuspect (phone, other, starttime);"

echo "[build_dedup_index] $(date -Is) done."
