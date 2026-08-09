#!/usr/bin/env bash
# Apply CDAT stability hardening (requires sudo for nginx/php-fpm).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if [[ $EUID -ne 0 ]]; then
  echo "Re-run with sudo: sudo $0" >&2
  exit 1
fi

echo "==> PostgreSQL guardrails"
sudo -u postgres psql -d postgres -f "$ROOT/sql/postgres_cdat_stability.sql"

echo "==> PHP-FPM pool limits (conservative for shared 62GB host)"
cp "$ROOT/scripts/php-fpm-cdat.conf" /etc/php/8.3/fpm/pool.d/zz-cdat.conf
systemctl reload php8.3-fpm

echo "==> Nginx CDAT site"
cp "$ROOT/cdat-web.nginx.conf" /etc/nginx/sites-available/cdat-web
nginx -t
systemctl reload nginx

echo "==> Systemd resource limits for background workers"
for unit in cdataddress-citus-migration cellids-citus-migration cdatpcsuspect-imei-index; do
  src="$ROOT/scripts/systemd/${unit}.service"
  dest="/etc/systemd/system/${unit}.service"
  if [[ -f "$src" ]]; then
    cp "$src" "$dest"
    systemctl daemon-reload
    systemctl enable "$unit" 2>/dev/null || true
    systemctl restart "$unit" 2>/dev/null || true
  fi
done

echo "==> Health check"
sudo -u hyd-cat "$ROOT/scripts/cdat-health-check.sh" || true

echo "Done. Review swap/RAM — consider stopping ollama/mongodb during heavy index builds if swap > 90%."
