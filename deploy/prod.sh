#!/usr/bin/env bash
# Production: dataUpload API on 127.0.0.1:8090 (PHP is Nginx + PHP-FPM).
# Usage:
#   ./deploy/prod.sh start
#   ./deploy/prod.sh stop
#   ./deploy/prod.sh status
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

API_DIR="$ROOT/dataUpload"
VENV="$API_DIR/env"
RUN_DIR="$ROOT/var/deploy"
PID_FILE="$RUN_DIR/dataupload.pid"
LOG_FILE="$RUN_DIR/dataupload.log"
API_URL="${DATA_UPLOAD_URL:-http://127.0.0.1:8090}"
ACTION="${1:-start}"

mkdir -p "$RUN_DIR"

api_pid() {
  if [[ -f "$PID_FILE" ]]; then
    local pid
    pid="$(cat "$PID_FILE")"
    if kill -0 "$pid" 2>/dev/null; then
      echo "$pid"
      return 0
    fi
    rm -f "$PID_FILE"
  fi
  return 1
}

status() {
  local pid
  if pid="$(api_pid)"; then
    echo "dataUpload API running (pid $pid)  $API_URL/health"
    curl -fsS "$API_URL/health" || true
    echo
  else
    echo "dataUpload API is not running."
    return 1
  fi
}

stop() {
  local pid
  if pid="$(api_pid)"; then
    kill "$pid"
    sleep 1
    kill -0 "$pid" 2>/dev/null && kill -9 "$pid" 2>/dev/null || true
    rm -f "$PID_FILE"
    echo "Stopped dataUpload API (pid $pid)."
  else
    echo "dataUpload API was not running."
  fi
}

start() {
  if [[ ! -f "$ROOT/.env" ]]; then
    echo "Missing $ROOT/.env — copy .env.example and set production credentials." >&2
    exit 1
  fi

  export CDAT_SQL_CONSOLE="${CDAT_SQL_CONSOLE:-0}"
  export DATA_UPLOAD_HOST="${DATA_UPLOAD_HOST:-127.0.0.1}"
  export DATA_UPLOAD_PORT="${DATA_UPLOAD_PORT:-8090}"

  if [[ ! -x "$VENV/bin/python" ]]; then
    echo "Creating dataUpload venv…"
    python3 -m venv "$VENV"
    "$VENV/bin/pip" install -r "$API_DIR/requirements.txt"
  fi

  if pid="$(api_pid)"; then
    echo "Already running (pid $pid)."
    status
    return 0
  fi

  echo "Starting dataUpload API on ${DATA_UPLOAD_HOST}:${DATA_UPLOAD_PORT}…"
  (
    cd "$API_DIR"
    exec "$VENV/bin/python" main.py
  ) >>"$LOG_FILE" 2>&1 &
  echo $! >"$PID_FILE"
  sleep 2

  if ! api_pid >/dev/null; then
    echo "API failed to start. Last log lines:" >&2
    tail -n 40 "$LOG_FILE" >&2
    exit 1
  fi

  status
  echo
  echo "PHP is served by Nginx + PHP-FPM (see cdat-web.nginx.conf)."
  echo "Set DATA_UPLOAD_URL=/ in .env so the browser uses this proxy, not :8090."
  echo "Log: $LOG_FILE"
}

case "$ACTION" in
  start) start ;;
  stop) stop ;;
  status) status ;;
  restart) stop; start ;;
  *)
    echo "Usage: $0 {start|stop|status|restart}" >&2
    exit 1
    ;;
esac
