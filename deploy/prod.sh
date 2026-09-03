#!/usr/bin/env bash
# Production: PM2 runs PHP + dataUpload API.
# Ports come from .env (PHP_PORT, DATA_UPLOAD_HOST, DATA_UPLOAD_PORT).
# Usage: ./deploy/prod.sh start|stop|status|restart
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

API_DIR="$ROOT/dataUpload"
VENV="$API_DIR/env"
PHP_APP="cdat-v2-php"
API_APP="cdat-v2-api"
ACTION="${1:-start}"

env_get() {
  local key="$1" default="${2:-}" line value
  [[ -f "$ROOT/.env" ]] || { echo "$default"; return 0; }
  line="$(grep -E "^${key}=" "$ROOT/.env" | tail -n 1 || true)"
  value="${line#*=}"
  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"
  echo "${value:-$default}"
}

PHP_HOST="${PHP_HOST:-$(env_get PHP_HOST 0.0.0.0)}"
PHP_PORT="${PHP_PORT:-$(env_get PHP_PORT 8022)}"
# FastAPI must stay on loopback; the PHP upload proxy reaches it locally.
API_HOST="$(env_get DATA_UPLOAD_HOST 127.0.0.1)"
API_PORT="$(env_get DATA_UPLOAD_PORT 5022)"
API_URL="$(env_get DATA_UPLOAD_URL "http://127.0.0.1:${API_PORT}")"
API_KEY="$(env_get DATA_UPLOAD_API_KEY "")"

if [[ "${API_HOST}" != "127.0.0.1" && "${API_HOST}" != "localhost" && "${API_HOST}" != "::1" ]]; then
  if [[ -z "${API_KEY}" ]]; then
    echo "DATA_UPLOAD_HOST=${API_HOST} requires DATA_UPLOAD_API_KEY (or bind API to 127.0.0.1)." >&2
    exit 1
  fi
  echo "WARNING: DATA_UPLOAD_HOST=${API_HOST} is not loopback. Prefer 127.0.0.1 + PHP proxy." >&2
fi

stop_pidfiles() {
  local pidfile pid
  for pidfile in "$ROOT/var/deploy/php.pid" "$ROOT/var/deploy/dataupload.pid"; do
    if [[ -f "$pidfile" ]]; then
      pid="$(cat "$pidfile")"
      if kill -0 "$pid" 2>/dev/null; then
        kill "$pid" 2>/dev/null || true
        sleep 1
        kill -0 "$pid" 2>/dev/null && kill -9 "$pid" 2>/dev/null || true
      fi
      rm -f "$pidfile"
    fi
  done
}

status() {
  pm2 status "$PHP_APP" "$API_APP"
  echo
  curl -sS -o /dev/null -w "PHP  HTTP %{http_code}  http://127.0.0.1:${PHP_PORT}/login\n" \
    "http://127.0.0.1:${PHP_PORT}/login" || true
  curl -sS -w "\nAPI  HTTP %{http_code}  ${API_URL}/health\n" \
    "http://127.0.0.1:${API_PORT}/health" || true
}

stop() {
  pm2 delete "$PHP_APP" "$API_APP" >/dev/null 2>&1 || true
  stop_pidfiles
  pm2 save >/dev/null 2>&1 || true
  echo "Stopped $PHP_APP and $API_APP"
}

start() {
  if [[ ! -f "$ROOT/.env" ]]; then
    echo "Missing $ROOT/.env — copy .env.example and set production credentials." >&2
    exit 1
  fi
  if ! command -v pm2 >/dev/null 2>&1; then
    echo "pm2 is not installed or not on PATH." >&2
    exit 1
  fi

  export CDAT_SQL_CONSOLE="${CDAT_SQL_CONSOLE:-0}"
  mkdir -p "$ROOT/logs" "$API_DIR/logs" "$ROOT/var/deploy"

  if [[ ! -x "$VENV/bin/python" ]]; then
    echo "Creating dataUpload venv…"
    python3 -m venv "$VENV"
    "$VENV/bin/pip" install -r "$API_DIR/requirements.txt"
  fi

  stop_pidfiles
  pm2 delete "$PHP_APP" "$API_APP" >/dev/null 2>&1 || true

  pm2 start php --name "$PHP_APP" --cwd "$ROOT" --interpreter none \
    --output "$ROOT/logs/php-server.log" --error "$ROOT/logs/php-error.log" --merge-logs \
    -- -S "${PHP_HOST}:${PHP_PORT}" "$ROOT/main.php"

  pm2 start "$VENV/bin/python" --name "$API_APP" --cwd "$API_DIR" --interpreter none \
    --output "$API_DIR/logs/dataupload.log" --error "$API_DIR/logs/dataupload-error.log" --merge-logs \
    -- main.py

  pm2 save
  sleep 3
  echo "PHP  ${PHP_HOST}:${PHP_PORT}"
  echo "API  ${API_HOST}:${API_PORT}  (${API_URL})"
  status
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
