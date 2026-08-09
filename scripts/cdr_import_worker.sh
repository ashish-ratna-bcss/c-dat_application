#!/usr/bin/env bash

set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! python3 -c "import psycopg2" 2>/dev/null; then
  python3 -m pip install -r requirements-cdr-import.txt
fi

COMMIT_FLAG=""
if [[ "${1:-}" == "--commit" ]]; then
  COMMIT_FLAG="--commit"
fi

exec python3 -m cdr_import.cli worker --poll 10 $COMMIT_FLAG
