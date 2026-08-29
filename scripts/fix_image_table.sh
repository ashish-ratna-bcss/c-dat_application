#!/usr/bin/env bash
set -euo pipefail
LOGDIR="${MIGRATION_LOG_DIR:-/mnt/storage1/migration_logs}"
if [[ -n "${MIGRATE_PG_B64:-}" ]]; then
  echo "$MIGRATE_PG_B64" | base64 -d > /tmp/migrate_pgpass
  chmod 600 /tmp/migrate_pgpass
fi
export PGPASSWORD="$(cat /tmp/migrate_pgpass)"

echo "[$(date '+%F %T')] Truncate IR_DB.image_table (remove duplicate rows)"
psql -h localhost -U postgres -d IR_DB -v ON_ERROR_STOP=1 -c "TRUNCATE image_table;"

echo "[$(date '+%F %T')] Re-copy image_table from mssql_dump_ir"
python3 - <<'PY'
import os, sys
sys.path.insert(0, "/tmp")
os.chdir("/tmp")
import importlib.util
spec = importlib.util.spec_from_file_location("migrate_copy", "/tmp/migrate_copy.py")
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)
mod.copy_table("mssql_dump_ir", "IR_DB", "image_table")
PY

echo "[$(date '+%F %T')] Verify row count"
psql -h localhost -U postgres -d IR_DB -t -A -c "SELECT count(*) FROM image_table;"
