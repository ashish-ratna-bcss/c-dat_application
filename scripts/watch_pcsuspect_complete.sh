#!/usr/bin/env bash
# Keep the heap copy running until done, then indexes + swap. Do not drop MSSQL.
# Safe to start while copy_pcsuspect_heap.py is already running.
set -u
cd /home/hyd-cat
export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
export PYTHONUNBUFFERED=1
LOG=/tmp/copy_pcsuspect_new.log
DONE=/tmp/pcsuspect_MIGRATION_DONE
CP=/tmp/migrate_checkpoint_cdatdupl_db_cdatpcsuspect_new.json

echo "[$(date -Is)] watch_pcsuspect_complete started pid=$$" >> "$LOG"

status_of() {
  python3 - <<'PY' 2>/dev/null || true
import json, pathlib
p = pathlib.Path("/tmp/migrate_checkpoint_cdatdupl_db_cdatpcsuspect_new.json")
if not p.is_file():
    print("missing")
else:
    try:
        print(json.loads(p.read_text()).get("status") or "unknown")
    except Exception:
        print("bad")
PY
}

while true; do
  if [[ -f "$DONE" ]] && grep -q swapped "$DONE"; then
    echo "[$(date -Is)] watch: migration already swapped; exit" >> "$LOG"
    exit 0
  fi
  st="$(status_of)"
  if [[ "$st" == "swapped" ]]; then
    echo "[$(date -Is)] watch: checkpoint swapped; exit" >> "$LOG"
    exit 0
  fi
  if [[ "$st" == "copied" ]]; then
    echo "[$(date -Is)] watch: copy complete; starting indexes + swap" >> "$LOG"
    python3 -u /home/hyd-cat/finish_pcsuspect_swap.py >> "$LOG" 2>&1
    rc=$?
    echo "[$(date -Is)] watch: finish_pcsuspect_swap exit=$rc" >> "$LOG"
    if [[ -f "$DONE" ]] && grep -q swapped "$DONE"; then
      exit 0
    fi
    sleep 120
    continue
  fi
  if ! pgrep -f '/home/hyd-cat/copy_pcsuspect_heap.py' >/dev/null; then
    echo "[$(date -Is)] watch: copy not running (status=$st); restarting" >> "$LOG"
    nohup python3 -u /home/hyd-cat/copy_pcsuspect_heap.py >> "$LOG" 2>&1 &
    echo "[$(date -Is)] watch: restarted copy pid=$!" >> "$LOG"
  fi
  sleep 120
done
