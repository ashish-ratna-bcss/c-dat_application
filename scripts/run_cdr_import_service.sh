#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT/cdr-import-service"

export PYTHONPATH="$ROOT:${PYTHONPATH:-}"
export CDR_API_HOST="${CDR_API_HOST:-0.0.0.0}"
export CDR_API_PORT="${CDR_API_PORT:-8088}"

if ! python3 -c "import fastapi, uvicorn" 2>/dev/null; then
  python3 -m pip install --break-system-packages -r requirements.txt
fi

exec python3 -m uvicorn app.main:app --host "$CDR_API_HOST" --port "$CDR_API_PORT"
