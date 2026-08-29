#!/usr/bin/env bash
# Re-run Postgres copy only (after MSSQL restore already done).
set -euo pipefail
LOGDIR="${MIGRATION_LOG_DIR:-/mnt/storage1/migration_logs}"
mkdir -p "$LOGDIR"
if [[ -n "${MIGRATE_PG_B64:-}" ]]; then
  echo "$MIGRATE_PG_B64" | base64 -d > /tmp/migrate_pgpass
  chmod 600 /tmp/migrate_pgpass
fi
if [[ -z "${PGPASSWORD:-}" && -f /tmp/migrate_pgpass ]]; then
  export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
fi
if [[ -z "${PGPASSWORD:-}" ]]; then
  echo "Set MIGRATE_PG_B64 or PGPASSWORD" >&2
  exit 1
fi
python3 /tmp/migrate_copy.py jrms >>"$LOGDIR/jrms_copy.log" 2>&1
python3 /tmp/migrate_copy.py ir >>"$LOGDIR/ir_copy.log" 2>&1
if docker exec mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$(docker exec mssql printenv MSSQL_SA_PASSWORD)" -Q "SET NOCOUNT ON; IF DB_ID(N'mssql_dump_cdatdupl') IS NOT NULL SELECT 1" -h -1 2>/dev/null | grep -q 1; then
  python3 /tmp/migrate_copy.py rowdy >>"$LOGDIR/rowdy_copy.log" 2>&1
fi
echo "copy-only finished $(date)" >>"$LOGDIR/batch.log"
