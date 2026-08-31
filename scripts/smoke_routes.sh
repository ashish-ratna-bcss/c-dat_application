#!/usr/bin/env bash
# Smoke-test GET routes (expect HTTP 200, not 500).
set -euo pipefail

: "${SMOKE_BASE_URL:=http://127.0.0.1:8020}"
: "${SMOKE_USER:=}"
: "${SMOKE_PASS:=}"

COOKIE_JAR="$(mktemp)"
trap 'rm -f "$COOKIE_JAR"' EXIT

fail=0

if [[ -n "$SMOKE_USER" && -n "$SMOKE_PASS" ]]; then
  code=$(curl -sS -o /dev/null -w '%{http_code}' -c "$COOKIE_JAR" -b "$COOKIE_JAR" \
    -X POST "$SMOKE_BASE_URL/login" \
    -d "USERNAME=$(printf '%s' "$SMOKE_USER" | sed 's/@/%40/g')" \
    -d "PASSWORD=$SMOKE_PASS" \
    -d 'ajax=1' || echo 000)
  if [[ "$code" != "200" ]]; then
    echo "FAIL: login HTTP $code" >&2
    fail=1
  else
    echo "OK: login"
  fi
fi

routes=(
  /login
  /health
  /dashboard
  /summary/total
  /call-details/movements
  /others/cell-id
  /others/trainings
  /others/vehicle
  /jrms/name
  /interrogation-reports/name
  /pd-act/name
  /imei-search/phones-by-imei
  /day-night-location/top-10
  /cdat/contacts
  /administration/user-activity
  /api/police-stations
  /api/divisions
  /api/years
)

for route in "${routes[@]}"; do
  code=$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE_JAR" "$SMOKE_BASE_URL$route" || echo 000)
  if [[ "$code" == "500" ]] || [[ ! "$code" =~ ^[0-9]{3}$ ]] || [[ "$code" == "000" ]]; then
    echo "FAIL: $route HTTP $code" >&2
    fail=1
  else
    echo "OK: $route HTTP $code"
  fi
done

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi
echo 'Smoke routes passed.'
