#!/usr/bin/env bash
# Local C-DAT web server (PHP built-in). No nginx required.
# Default: project root + scripts/dev_router.php (same URLs as production forwarders).
# Optional: CDAT_DOCROOT=public ./scripts/run_dev_server.sh  (Phase 4 front controller)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

HOST="${CDAT_DEV_HOST:-127.0.0.1}"
PORT="${CDAT_DEV_PORT:-8020}"
DOCROOT="${CDAT_DOCROOT:-root}"

echo "C-DAT dev server: http://${HOST}:${PORT}/HOME.html"
echo "Login page:        http://${HOST}:${PORT}/LOGIN.HTML"
echo "Health:            http://${HOST}:${PORT}/health.php"
echo "Docroot mode:      ${DOCROOT}"
echo "Ctrl+C to stop"

if [[ "$DOCROOT" == "public" ]]; then
  cd "$ROOT/public"
  exec php -d auto_prepend_file="$ROOT/sqlsrv_compat.php" \
    -d display_errors=1 \
    -S "${HOST}:${PORT}" \
    "$ROOT/public/index.php"
fi

exec php -d auto_prepend_file="$ROOT/sqlsrv_compat.php" \
  -d display_errors=1 \
  -d max_execution_time=300 \
  -d max_input_time=300 \
  -S "${HOST}:${PORT}" \
  "$ROOT/scripts/dev_router.php"
