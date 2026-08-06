#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT/cdr-import-service"

export PYTHONPATH="$ROOT:${PYTHONPATH:-}"
export CDR_API_HOST="${CDR_API_HOST:-0.0.0.0}"
export CDR_API_PORT="${CDR_API_PORT:-8088}"
export CDR_STAGING_TABLE="${CDR_STAGING_TABLE:-cdatpcsuspect}"

# Local Mac/dev defaults (override with env vars for production Linux paths)
LOCAL_DOC_ROOT="${CDR_DOCUMENTS_ROOT:-$ROOT/var/cdr_documents}"
export CDR_UPLOAD_INBOX="${CDR_UPLOAD_INBOX:-$LOCAL_DOC_ROOT/inbox}"
export CDR_UPLOAD_PROCESSING="${CDR_UPLOAD_PROCESSING:-$LOCAL_DOC_ROOT/processing}"
export CDR_UPLOAD_DONE="${CDR_UPLOAD_DONE:-$LOCAL_DOC_ROOT/done}"
export CDR_UPLOAD_FAILED="${CDR_UPLOAD_FAILED:-$LOCAL_DOC_ROOT/failed}"
export CDR_API_UPLOAD_DIR="${CDR_API_UPLOAD_DIR:-$CDR_UPLOAD_INBOX}"

mkdir -p "$CDR_UPLOAD_INBOX" "$CDR_UPLOAD_PROCESSING" "$CDR_UPLOAD_DONE" "$CDR_UPLOAD_FAILED"

if ! python3 -c "import fastapi, uvicorn" 2>/dev/null; then
  python3 -m pip install --break-system-packages -r requirements.txt
fi

echo "CDR document API on :${CDR_API_PORT}  inbox=${CDR_UPLOAD_INBOX}"
exec python3 -m uvicorn app.main:app --host "$CDR_API_HOST" --port "$CDR_API_PORT"
