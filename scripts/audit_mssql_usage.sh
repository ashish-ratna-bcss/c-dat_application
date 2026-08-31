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
  '--glob' '!all_mssql_queries_extracted.sql'
  '--glob' '!all_postgress_queries_created.sql'
  '--glob' '!docs/**'
  '--glob' '!scripts/convert_modules_mssql_to_pg.py'
  '--glob' '!scripts/fix_remaining_pg_sql.py'
  '--glob' '!scripts/postfix_modules_pg.py'
  '--glob' '!scripts/compare_pg_mssql_tables.py'
  '--glob' '!scripts/migrate_copy.py'
  '--glob' '!scripts/drop_mssql_*'
  '--glob' '!scripts/check_*mssql*'
  '--glob' '!scripts/verify_mssql_*'
  '--glob' '!sql/mssql_to_postgres_migration.sql'
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
