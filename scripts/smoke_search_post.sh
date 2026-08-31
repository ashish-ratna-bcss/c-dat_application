#!/usr/bin/env bash
# One POST smoke test per search module (requires VPN/test data env vars).
set -euo pipefail

: "${SMOKE_BASE_URL:=http://127.0.0.1:8020}"
: "${SMOKE_USER:=}"
: "${SMOKE_PASS:=}"
: "${SMOKE_PHONE:=7569422355}"
: "${SMOKE_IRKEY:=}"

COOKIE_JAR="$(mktemp)"
trap 'rm -f "$COOKIE_JAR"' EXIT
fail=0

login() {
  [[ -n "$SMOKE_USER" && -n "$SMOKE_PASS" ]] || return 0
  curl -sS -c "$COOKIE_JAR" -b "$COOKIE_JAR" -X POST "$SMOKE_BASE_URL/login" \
    -d "USERNAME=$SMOKE_USER" -d "PASSWORD=$SMOKE_PASS" -d 'ajax=1' >/dev/null
}

post_ok() {
  local name="$1" path="$2" data="$3"
  local code
  code=$(curl -sS -o /dev/null -w '%{http_code}' -b "$COOKIE_JAR" \
    -X POST "$SMOKE_BASE_URL$path" -H 'X-Requested-With: XMLHttpRequest' $data || echo 000)
  if [[ "$code" == "500" || "$code" == "000" ]]; then
    echo "FAIL: $name HTTP $code" >&2
    fail=1
  else
    echo "OK: $name HTTP $code"
  fi
}

login

post_ok 'summary' '/summary/total' "-d PHONE_NO=$SMOKE_PHONE -d BTN_CDAT=1"
post_ok 'movements' '/call-details/movements' "-d PHONE_NO=$SMOKE_PHONE -d BTN_CDAT=1"
post_ok 'cell-id' '/others/cell-id' "-d CELLID=40401 -d BTN_SUM=1"
post_ok 'vehicle' '/others/vehicle' "-d VEHICLE_NO=TS -d BTN_CDAT=1"
post_ok 'jrms' '/jrms/name' "-d NAME=TEST -d BTN_CDAT=1"
post_ok 'ir-search' '/interrogation-reports/name' "-d NAME=TEST -d BTN_CDAT=1"

if [[ -n "$SMOKE_IRKEY" ]]; then
  post_ok 'ir-detail' "/interrogation-reports/detail?IRKEY=$SMOKE_IRKEY" "-d IR_NO=$SMOKE_IRKEY -d BTN_CDAT=1"
fi

post_ok 'pdact' '/pd-act/name' "-d NAME=TEST -d BTN_CDAT=1"
post_ok 'trainings' '/others/trainings' "-d EMPLOYEE_SEARCH=NAME -d EMPLOYEE_SEARCH_NO=TEST -d BTN_CDAT=1"

if [[ "$fail" -ne 0 ]]; then
  exit 1
fi
echo 'Smoke search POST passed.'
