#!/usr/bin/env bash
# Create gitignored runtime directories required for uploads and CDR worker.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DIRS=(
  uploads
  var/cdr_documents/inbox/cdr
  var/cdr_documents/inbox/sdr
  var/cdr_documents/processing
  var/cdr_documents/done
  var/cdr_documents/failed
  logs
  tmp
)

for d in "${DIRS[@]}"; do
  mkdir -p "$d"
done

# Prefer group-writable dirs for PHP-FPM (www-data) when deployed under hyd-cat.
if id www-data >/dev/null 2>&1; then
  chown -R "$(whoami):www-data" uploads var/cdr_documents logs tmp 2>/dev/null || true
fi
chmod -R 775 uploads var/cdr_documents logs tmp 2>/dev/null || true

echo "Runtime directories ready under $ROOT"
