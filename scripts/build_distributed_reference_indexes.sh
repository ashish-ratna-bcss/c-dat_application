#!/usr/bin/env bash
#
# Build reference-table indexes on distributed_db (Citus).
# Idempotent; safe to run as cdat-distributed-indexes.service.
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_CONFIG="${APP_DIR}/db_config.php"

read_cfg() {
    php -r '$c = require $argv[1]; echo $c[$argv[2]] ?? "";' "$DB_CONFIG" "$1"
}

export PGHOST="$(read_cfg host)"
export PGPORT="$(read_cfg port)"
export PGUSER="$(read_cfg user)"
export PGPASSWORD="$(read_cfg password)"
export PGDATABASE="distributed_db"
export PGOPTIONS="-c lock_timeout=0"

LOG_TAG="[build_distributed_indexes]"
echo "${LOG_TAG} $(date -Is) starting on ${PGHOST}:${PGPORT}/${PGDATABASE}"

index_valid() {
    local idx="$1"
    psql -tAqc "
        SELECT i.indisvalid FROM pg_class c
        JOIN pg_index i ON i.indexrelid = c.oid
        WHERE c.relname = '${idx}'
    " 2>/dev/null || echo ""
}

drop_invalid() {
    local idx="$1"
    local valid
    valid="$(index_valid "${idx}")"
    if [ "${valid}" = "f" ]; then
        echo "${LOG_TAG} dropping invalid ${idx}"
        psql -v ON_ERROR_STOP=1 -c "DROP INDEX CONCURRENTLY IF EXISTS ${idx}"
    fi
}

build_one() {
    local idx="$1"
    local sql="$2"
    local valid
    valid="$(index_valid "${idx}")"
    if [ "${valid}" = "t" ]; then
        echo "${LOG_TAG} ${idx} already valid — skip"
        return 0
    fi
    drop_invalid "${idx}"
    echo "${LOG_TAG} $(date -Is) building ${idx}..."
    psql -v ON_ERROR_STOP=1 -c "${sql}"
    echo "${LOG_TAG} $(date -Is) done ${idx}"
}

wait_for_index_workers() {
    local table="$1"
    while true; do
        local n
        n="$(psql -tAqc "
            SELECT count(*) FROM pg_stat_progress_create_index p
            JOIN pg_class t ON t.oid = p.relid
            WHERE t.relname = '${table}'
        " 2>/dev/null || echo 0)"
        if [ "${n}" = "0" ]; then
            return 0
        fi
        echo "${LOG_TAG} waiting for in-flight index build on ${table}..."
        sleep 30
    done
}

build_one "idx_cdataddress_phone_active" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cdataddress_phone_active ON cdataddress (phone) WHERE eff_to_date IS NULL"

wait_for_index_workers "cdataddress"

build_one "idx_address_other_state_phone_active" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_address_other_state_phone_active ON address_other_state (phone) WHERE eff_to_date IS NULL"

wait_for_index_workers "address_other_state"

build_one "idx_cellids_celltowerid_provider" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cellids_celltowerid_provider ON cellids (celltowerid, provider_key)"

wait_for_index_workers "cellids"

build_one "idx_cellids_lat_long" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cellids_lat_long ON cellids (lat, long) WHERE lat IS NOT NULL AND long IS NOT NULL"

psql -v ON_ERROR_STOP=1 -c "ANALYZE cdataddress"
psql -v ON_ERROR_STOP=1 -c "ANALYZE address_other_state"
psql -v ON_ERROR_STOP=1 -c "ANALYZE cellids"

echo "${LOG_TAG} $(date -Is) all distributed reference indexes complete"
