#!/usr/bin/env bash
# Copy address_db.CDATADDRESS -> CDATDUPL_DB.cdataddress (safe batch size for live server)
set -euo pipefail

LOG="/tmp/migrate_cdataddress.log"
PY="/home/hyd-cat/migrate_copy.py"

if [[ -f /tmp/migrate_pgpass ]]; then
  export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
fi

export MIGRATE_BATCH_SIZE="${MIGRATE_BATCH_SIZE:-2000}"

LOCK="/tmp/migrate_cdataddress.lock"
if [[ -f "$LOCK" ]] && kill -0 "$(cat "$LOCK")" 2>/dev/null; then
  echo "cdataddress copy already running (PID $(cat "$LOCK"))"
  exit 0
fi

if [[ -z "${PGPASSWORD:-}" ]] && [[ -f /mnt/storage1/c-dat_application/.env ]]; then
  PGPASSWORD="$(grep -E '^CDR_DB_PASSWORD=' /mnt/storage1/c-dat_application/.env | cut -d= -f2- | tr -d '"')"
  export PGPASSWORD
fi

nohup nice -n 10 ionice -c2 -n7 python3 "$PY" address >>"$LOG" 2>&1 &
echo $! >"$LOCK"
echo "Started cdataddress copy PID=$! log=$LOG batch=$MIGRATE_BATCH_SIZE"
