#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PY="${MIGRATION_PYTHON:-/home/hyd-cat/migration_env/bin/python}"
export PYTHONPATH="$ROOT:${PYTHONPATH:-}"
export DIST_MIGRATE_LOG_DIR="${DIST_MIGRATE_LOG_DIR:-/mnt/storage1/ITCell_DL_RTA_Data/distributed_migrate_logs}"
export MSSQL_SA_PASSWORD="${MSSQL_SA_PASSWORD:?MSSQL_SA_PASSWORD must be set (e.g. via the systemd EnvironmentFile)}"
export DIST_PG_PASSWORD="${DIST_PG_PASSWORD:?DIST_PG_PASSWORD must be set (e.g. via the systemd EnvironmentFile)}"
mkdir -p "$DIST_MIGRATE_LOG_DIR"
exec "$PY" -m distributed_migrate "$@"
