#!/usr/bin/env bash
# Static security regression checks for C-DAT.
# Exit 0 if checks pass; non-zero on failure.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
FAIL=0

pass() { echo "PASS  $1"; }
fail() { echo "FAIL  $1"; FAIL=1; }

# 1) Layout must enforce session on include
if grep -q 'audit_require_session' modules/common/includes/layout.php; then
  pass "layout.php enforces audit_require_session"
else
  fail "layout.php missing session guard"
fi

# 2) Plaintext login must not be default
if grep -q "CDAT_ALLOW_PLAINTEXT_MIGRATION" modules/common/login.php \
   && ! grep -Pzo 'else \{\n\s*\$valid = hash_equals\(\$stored, \$PASSWORD\);' modules/common/login.php >/dev/null 2>&1; then
  pass "plaintext password login gated"
else
  # Accept if migration flag is required before hash_equals on password
  if grep -A5 'CDAT_ALLOW_PLAINTEXT_MIGRATION' modules/common/login.php | grep -q 'hash_equals'; then
    pass "plaintext password login gated by migration flag"
  else
    fail "plaintext password path not gated"
  fi
fi

# 3) FastAPI auth always applied
if grep -q 'auth_deps = \[Depends(verify_api_key)\]' dataUpload/main.py; then
  pass "FastAPI always requires verify_api_key"
else
  fail "FastAPI auth_deps optional/open"
fi

# 4) Upload proxy present
if [[ -f modules/data-upload/upload_api_proxy.php ]] && grep -q 'api/data-upload' main.php; then
  pass "PHP upload API proxy wired"
else
  fail "upload API proxy missing"
fi

# 5) Cascading APIs require session
for f in get_ps.php get_year.php get_crno.php get_division.php; do
  if grep -q 'audit_require_session' "modules/common/$f"; then
    pass "$f requires session"
  else
    fail "$f missing session guard"
  fi
done

# 6) Investigative modules include layout (central gate)
MISSING=0
while IFS= read -r f; do
  case "$f" in
    */common/*|*/administration/*|*/data-upload/*|*/dashboard/*) continue ;;
  esac
  if ! grep -q 'includes/layout.php' "$f"; then
    echo "WARN  $f does not include layout.php"
    MISSING=$((MISSING+1))
  fi
done < <(find modules -name '*.php' -type f)
if [[ "$MISSING" -eq 0 ]]; then
  pass "module pages include layout.php"
else
  fail "$MISSING module page(s) omit layout.php"
fi

# 7) prod API default loopback
if grep -q 'DATA_UPLOAD_HOST 127.0.0.1' deploy/prod.sh; then
  pass "prod.sh defaults API to loopback"
else
  fail "prod.sh API host default not loopback"
fi

# 8) Blocked sensitive paths in main.php
if grep -q "str_starts_with(\$path, '/dataUpload')" main.php; then
  pass "main.php blocks /dataUpload"
else
  fail "main.php missing /dataUpload block"
fi

if [[ "$FAIL" -ne 0 ]]; then
  echo "Security regression checks FAILED"
  exit 1
fi
echo "Security regression checks PASSED"
exit 0
