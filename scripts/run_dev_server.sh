#!/usr/bin/env bash
# Local C-DAT web server (PHP built-in). No nginx required.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

HOST="${CDAT_DEV_HOST:-127.0.0.1}"
PORT="${CDAT_DEV_PORT:-8020}"

# DB settings are loaded by db_config.php from .env (avoids shell metachar issues).

echo "C-DAT dev server: http://${HOST}:${PORT}/HOME.html"
echo "Login page:        http://${HOST}:${PORT}/LOGIN.HTML"
echo "Ctrl+C to stop"
exec php -d auto_prepend_file="$ROOT/sqlsrv_compat.php" \
  -d display_errors=1 \
  -S "${HOST}:${PORT}" \
  "$ROOT/scripts/dev_router.php"
