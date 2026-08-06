#!/usr/bin/env bash
# Zero-breakage smoke tests for C-DAT.
# Usage: ./scripts/smoke_test.sh [BASE_URL]
# Default BASE_URL: http://127.0.0.1:8020
set -euo pipefail

BASE_URL="${1:-http://127.0.0.1:8020}"
BASE_URL="${BASE_URL%/}"
FAIL=0

check() {
  local path="$1"
  local code
  code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 15 "${BASE_URL}${path}" || echo "000")
  if [[ "$code" == "200" || "$code" == "302" || "$code" == "301" ]]; then
    printf 'OK  %s -> %s\n' "$path" "$code"
  else
    printf 'FAIL %s -> %s\n' "$path" "$code"
    FAIL=1
  fi
}

echo "Smoke against ${BASE_URL}"
echo "--------------------------------"

# Core entry
check /HOME.html
check /LOGIN.HTML
check /LOGIN.PHP

# Assets
check /IMAGES/ANALYSIS1.jpg
check /SpryAssets/SpryMenuBar.js

# High-traffic forms (GET pages)
check /IMEISEARCH.html
check /ADDRESS.HTML
check /CELLID_SEARCH.html
check /CALLDETAILS.PHP
check /SUM_HOME.html

# Health (added in Phase 2; tolerate 404 until then)
HEALTH_CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "${BASE_URL}/health.php" || echo "000")
if [[ "$HEALTH_CODE" == "200" ]]; then
  printf 'OK  /health.php -> %s\n' "$HEALTH_CODE"
elif [[ "$HEALTH_CODE" == "404" ]]; then
  printf 'SKIP /health.php -> 404 (not deployed yet)\n'
else
  printf 'FAIL /health.php -> %s\n' "$HEALTH_CODE"
  FAIL=1
fi

echo "--------------------------------"
if [[ "$FAIL" -ne 0 ]]; then
  echo "SMOKE FAILED"
  exit 1
fi
echo "SMOKE PASSED"
exit 0
