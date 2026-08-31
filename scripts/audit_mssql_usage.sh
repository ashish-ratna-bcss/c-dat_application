#!/usr/bin/env bash
# Fail if MSSQL/T-SQL patterns appear in runtime PHP web application code.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

PATTERNS=(
  'sqlsrv_'
  'WITH \(NOLOCK\)'
  'GETDATE\(\)'
  'ISNULL\('
  'CDATDUPL\.dbo'
  'CDATDUPL\.\.'
  'SELECT TOP '
  'FOR XML'
  'SCOPE_IDENTITY'
  '@@IDENTITY'
)

EXCLUDE=(
  '--glob' '!docs/**'
  '--glob' '!sdr_import/**'
)

SCAN_PATHS=(modules public routes sql)

SCAN_GLOBS=(-g '*.php' -g '*.sql')

fail=0
for pat in "${PATTERNS[@]}"; do
  if rg -n -i "$pat" "${EXCLUDE[@]}" "${SCAN_GLOBS[@]}" "${SCAN_PATHS[@]}" 2>/dev/null; then
    echo "FAIL: MSSQL pattern matched: $pat" >&2
    fail=1
  fi
done

# Cross-database MSSQL .. syntax (e.g. DB..TABLE) in PHP modules only
if rg -n '\.\.[A-Z_][A-Z0-9_]*' -g '*.php' modules/ 2>/dev/null; then
  echo 'FAIL: MSSQL cross-database .. syntax in modules/' >&2
  fail=1
fi

if [[ "$fail" -ne 0 ]]; then
  echo 'MSSQL audit failed. Runtime web code must use PostgreSQL only.' >&2
  exit 1
fi

echo 'MSSQL audit passed (modules/, public/, routes/, sql/).'
