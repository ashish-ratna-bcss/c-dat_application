#!/usr/bin/env bash
# Full heap copy into cdatpcsuspect_new. Does not touch live cdatpcsuspect.
set -euo pipefail
cd /home/hyd-cat
export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
export PYTHONUNBUFFERED=1
LOG=/tmp/copy_pcsuspect_new.log

if pgrep -f '/home/hyd-cat/copy_pcsuspect_heap.py' >/dev/null; then
  echo "copy_pcsuspect_heap already running"
  pgrep -af copy_pcsuspect_heap.py
  tail -n 15 "$LOG" || true
  exit 0
fi
if pgrep -f 'python3 /home/hyd-cat/migrate_copy.py pcsuspect' >/dev/null; then
  echo "old ucid resume is still running; stop it before starting the heap copy"
  exit 1
fi

echo "[$(date -Is)] starting copy_pcsuspect_heap.py" | tee -a "$LOG"
nohup python3 -u /home/hyd-cat/copy_pcsuspect_heap.py >> "$LOG" 2>&1 &
echo "pid=$!" | tee -a "$LOG"
sleep 8
pgrep -af copy_pcsuspect_heap.py || echo "not running"
tail -n 25 "$LOG"
