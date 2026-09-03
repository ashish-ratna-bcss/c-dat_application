#!/usr/bin/env bash
# Local development: PHP on 8020 + dataUpload API on 8090.
# Usage: ./deploy/dev.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PHP_HOST="${PHP_HOST:-localhost}"
PHP_PORT="${PHP_PORT:-8020}"
API_DIR="$ROOT/dataUpload"
VENV="$API_DIR/env"

if [[ ! -f "$ROOT/.env" ]]; then
  if [[ -f "$ROOT/.env.example" ]]; then
    cp "$ROOT/.env.example" "$ROOT/.env"
    echo "Created .env from .env.example — set CDR_DB_PASSWORD before uploading."
  else
    echo "Missing $ROOT/.env" >&2
    exit 1
  fi
fi

if [[ ! -x "$VENV/bin/python" ]]; then
  echo "Creating dataUpload venv…"
  python3 -m venv "$VENV"
  "$VENV/bin/pip" install -r "$API_DIR/requirements.txt"
fi

mkdir -p "$ROOT/logs" "$API_DIR/logs"

echo "PHP  http://${PHP_HOST}:${PHP_PORT}/login"
echo "API  http://127.0.0.1:8090/health"
echo "CDR  http://${PHP_HOST}:${PHP_PORT}/data-upload/cdr"
echo "Logs PHP  $ROOT/logs/application.log"
echo "Logs PHP  $ROOT/logs/php-server.log"
echo "Logs API  $API_DIR/logs/dataupload.log"
echo "Stop with Ctrl+C"
echo

php -S "${PHP_HOST}:${PHP_PORT}" "$ROOT/main.php" >>"$ROOT/logs/php-server.log" 2>&1 &
PHP_PID=$!

(
  cd "$API_DIR"
  exec "$VENV/bin/python" main.py
) &
API_PID=$!

cleanup() {
  kill "$PHP_PID" "$API_PID" 2>/dev/null || true
  wait "$PHP_PID" "$API_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

wait "$PHP_PID" "$API_PID"
