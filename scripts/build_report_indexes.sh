#!/usr/bin/env bash
#
# Build report-query indexes on cdatpcsuspect (CONCURRENTLY, one at a time).
# Waits until no other index build is running on cdatpcsuspect (e.g. dedup index).
# Designed for cdat-report-indexes.service (runs after cdat-dedup-index.service).
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_CONFIG="${APP_DIR}/db_config.php"

read_cfg() {
    php -r '$c = require $argv[1]; echo $c[$argv[2]] ?? "";' "$DB_CONFIG" "$1"
}

export PGHOST="$(read_cfg host)"
export PGPORT="$(read_cfg port)"
export PGDATABASE="$(read_cfg database)"
export PGUSER="$(read_cfg user)"
export PGPASSWORD="$(read_cfg password)"
export PGOPTIONS="-c lock_timeout=0"

LOG_TAG="[build_report_indexes]"
DEDUP_INDEX="idx_cdatpcsuspect_phone_other_starttime"

echo "${LOG_TAG} $(date -Is) starting on ${PGHOST}:${PGPORT}/${PGDATABASE}"

index_valid() {
    local idx="$1"
    psql -tAqc "
        SELECT i.indisvalid FROM pg_class c
        JOIN pg_index i ON i.indexrelid = c.oid
        WHERE c.relname = '${idx}'
    " 2>/dev/null || echo ""
}

pcs_index_builds_running() {
    psql -tAqc "
        SELECT count(*) FROM pg_stat_progress_create_index p
        JOIN pg_class t ON t.oid = p.relid
        WHERE t.relname = 'cdatpcsuspect'
    " 2>/dev/null || echo "0"
}

wait_for_quiet_pcs() {
    echo "${LOG_TAG} waiting until cdatpcsuspect has no in-flight CREATE INDEX..."
    while [ "$(pcs_index_builds_running)" != "0" ]; do
        if systemctl is-active --quiet cdat-dedup-index 2>/dev/null; then
            echo "${LOG_TAG} cdat-dedup-index.service still running..."
        fi
        sleep 60
    done
    echo "${LOG_TAG} cdatpcsuspect index builds idle"
}

drop_invalid() {
    local idx="$1"
    if [ "$(index_valid "${idx}")" = "f" ]; then
        echo "${LOG_TAG} dropping invalid ${idx}"
        psql -v ON_ERROR_STOP=1 -c "DROP INDEX CONCURRENTLY IF EXISTS ${idx}"
    fi
}

build_one() {
    local idx="$1"
    local sql="$2"
    local label="$3"
    if [ "$(index_valid "${idx}")" = "t" ]; then
        echo "${LOG_TAG} ${idx} already valid — skip"
        return 0
    fi
    wait_for_quiet_pcs
    drop_invalid "${idx}"
    echo "${LOG_TAG} $(date -Is) building ${label} (${idx})..."
    psql -v ON_ERROR_STOP=1 -c "${sql}"
    echo "${LOG_TAG} $(date -Is) done ${label}"
    wait_for_quiet_pcs
}

# Ensure dedup composite index finished before adding more indexes on same table.
dedup_valid="$(index_valid "${DEDUP_INDEX}")"
if [ "${dedup_valid}" != "t" ]; then
    echo "${LOG_TAG} waiting for ${DEDUP_INDEX} to become valid..."
    while true; do
        dedup_valid="$(index_valid "${DEDUP_INDEX}")"
        if [ "${dedup_valid}" = "t" ]; then
            break
        fi
        if [ "$(pcs_index_builds_running)" = "0" ] && [ "${dedup_valid}" != "f" ]; then
            # Service done but index missing — dedup service may not have been run.
            echo "${LOG_TAG} ${DEDUP_INDEX} not present; starting dedup build via script..."
            "${APP_DIR}/scripts/build_dedup_index.sh"
            break
        fi
        sleep 60
    done
fi

build_one "idx_cdatpcsuspect_phone_starttime" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cdatpcsuspect_phone_starttime ON cdatpcsuspect (phone, starttime)" \
    "phone_starttime"

build_one "idx_cdatpcsuspect_other" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cdatpcsuspect_other ON cdatpcsuspect (other) WHERE other IS NOT NULL AND other::text <> ''" \
    "other"

build_one "idx_cdatpcsuspect_celltowerid" \
    "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cdatpcsuspect_celltowerid ON cdatpcsuspect (celltowerid) WHERE celltowerid IS NOT NULL AND celltowerid::text <> ''" \
    "celltowerid"

psql -v ON_ERROR_STOP=1 -c "ANALYZE cdatpcsuspect"
echo "${LOG_TAG} $(date -Is) all report indexes complete"
