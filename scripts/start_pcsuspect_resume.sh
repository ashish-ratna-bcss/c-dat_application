#!/bin/bash
set -euo pipefail

export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
export MIGRATE_RESUME=1

LOG=/tmp/resume_pcsuspect.out
echo "[$(date -Is)] cancel index build if any" >> "$LOG"
psql -h 127.0.0.1 -U postgres -d CDATDUPL_DB -At -qc \
  "SELECT pg_cancel_backend(pid) FROM pg_stat_progress_create_index WHERE relid='cdatpcsuspect'::regclass;" \
  >> "$LOG" 2>&1 || true

pkill -f resume_pcsuspect_migration.sh 2>/dev/null || true
pkill -f 'migrate_copy.py pcsuspect' 2>/dev/null || true

echo "[$(date -Is)] computing MAX(ucid) for checkpoint" >> "$LOG"
python3 /home/hyd-cat/write_pcsuspect_checkpoint.py >> "$LOG" 2>&1

echo "[$(date -Is)] starting migrate_copy.py pcsuspect" >> "$LOG"
nohup python3 /home/hyd-cat/migrate_copy.py pcsuspect >> /tmp/migrate_pcsuspect.log 2>&1 &
echo "migrate_pid=$!" >> "$LOG"

sleep 5
pgrep -af 'migrate_copy.py pcsuspect' >> "$LOG" 2>&1 || echo "not running yet" >> "$LOG"
tail -10 /tmp/migrate_pcsuspect.log >> "$LOG" 2>&1 || true
