#!/usr/bin/env bash
# Fail if PHP modules build SQL by string-interpolating request variables.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

fail=0

# Patterns: $_POST/$_GET/$_REQUEST inside double-quoted SQL strings
if rg -n \
  --glob 'modules/**/*.php' \
  --glob '!modules/administration/admin_sql_console.php' \
  -e '\$_(POST|GET|REQUEST)\[[^\]]+\][^;]*["'\''][^"'\'']*\$_(POST|GET|REQUEST)' \
  modules/ 2>/dev/null; then
  echo 'FAIL: request variable concatenated into SQL string' >&2
  fail=1
fi

# WHERE col='$var' or "$var" with PHP variable (legacy interpolation)
if rg -n \
  --glob 'modules/**/*.php' \
  --glob '!modules/administration/admin_sql_console.php' \
  -e "WHERE\\s+\\w+\\s*=\\s*['\"]\\s*\\.\\s*\\\$" \
  modules/ 2>/dev/null; then
  echo 'FAIL: WHERE clause string interpolation' >&2
  fail=1
fi

if rg -n \
  --glob 'modules/**/*.php' \
  --glob '!modules/administration/admin_sql_console.php' \
  -e "WHERE\\s+\\w+\\s*=\\s*['\"]\\\$\\w+['\"]" \
  modules/ 2>/dev/null; then
  echo 'FAIL: WHERE col='\''\$var'\'' pattern' >&2
  fail=1
fi

# $conn->query("...$var...") without prepare (exclude one-line safe queries)
if rg -n \
  --glob 'modules/**/*.php' \
  --glob '!modules/administration/admin_sql_console.php' \
  --glob '!modules/data-upload/upload_verification_service.php' \
  -e '->query\s*\(\s*["'\''][^"'\'']*\$' \
  modules/ 2>/dev/null; then
  echo 'FAIL: PDO->query() with interpolated variable' >&2
  fail=1
fi

if [[ "$fail" -ne 0 ]]; then
  echo 'SQL injection audit failed.' >&2
  exit 1
fi

echo 'SQL injection audit passed (modules/).'
