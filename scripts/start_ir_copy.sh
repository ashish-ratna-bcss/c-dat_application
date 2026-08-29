#!/usr/bin/env bash
# Start IR-only MSSQL -> Postgres copy in background
set -euo pipefail
LOGDIR="${MIGRATION_LOG_DIR:-/mnt/storage1/migration_logs}"
mkdir -p "$LOGDIR"
if [[ -n "${MIGRATE_PG_B64:-}" ]]; then
  echo "$MIGRATE_PG_B64" | base64 -d > /tmp/migrate_pgpass
  chmod 600 /tmp/migrate_pgpass
fi
export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
echo "[$(date '+%F %T')] IR copy started" >>"$LOGDIR/ir_copy.log"
python3 /tmp/migrate_copy.py ir >>"$LOGDIR/ir_copy.log" 2>&1
echo "[$(date '+%F %T')] IR copy finished" >>"$LOGDIR/ir_copy.log"
