#!/usr/bin/env bash
# Quick CDAT stack health check. Exit 0 = OK, 1 = warnings, 2 = critical.
set -euo pipefail

WARN=0
CRIT=0
say() { echo "$*"; }

# Memory / swap
read -r _ total used _ avail _ < <(free -b | awk '/^Mem:/ {print}')
swap_used_pct=$(free | awk '/^Swap:/ {if ($2>0) printf "%.0f", $3/$2*100; else print 0}')
avail_gb=$(awk "BEGIN {printf \"%.1f\", $avail/1024/1024/1024}")
say "RAM available: ${avail_gb} GB | swap used: ${swap_used_pct}%"
if (( swap_used_pct > 95 )); then say "CRITICAL: swap almost full — OOM risk"; CRIT=1; elif (( swap_used_pct > 80 )); then say "WARN: swap pressure high"; WARN=1; fi
if awk "BEGIN {exit !($avail_gb < 2.0)}"; then say "CRITICAL: less than 2 GB RAM free"; CRIT=1; fi

# Disk
for mount in /mnt/storage1 /; do
  pct=$(df "$mount" | awk 'NR==2 {print $5}' | tr -d '%')
  say "Disk $mount: ${pct}% used"
  if (( pct > 95 )); then say "CRITICAL: disk $mount nearly full"; CRIT=1
  elif (( pct > 88 )); then say "WARN: disk $mount getting full"; WARN=1; fi
done

# Core services
for svc in nginx php8.3-fpm postgresql docker; do
  if systemctl is-active --quiet "$svc"; then say "OK  $svc"; else say "CRITICAL: $svc not running"; CRIT=1; fi
done

# CDAT background jobs
for svc in cdataddress-citus-migration cellids-citus-migration cdatpcsuspect-imei-index; do
  if systemctl is-active --quiet "$svc" 2>/dev/null; then say "OK  $svc"; else say "WARN: $svc not active"; WARN=1; fi
done

# HTTP smoke test
if curl -sf -m 10 -o /dev/null http://127.0.0.1:8020/HOME.html; then say "OK  HTTP :8020"; else say "CRITICAL: CDAT web not responding"; CRIT=1; fi

# PostgreSQL
if PGPASSWORD="${DIST_PG_PASSWORD:?DIST_PG_PASSWORD must be set}" psql -h 127.0.0.1 -U postgres -d postgres -c "SELECT 1" -q >/dev/null 2>&1; then
  say "OK  PostgreSQL"
else
  say "CRITICAL: PostgreSQL unreachable"; CRIT=1
fi

# IMEI index
imei_valid=$(PGPASSWORD="${DIST_PG_PASSWORD}" psql -h 127.0.0.1 -U postgres -d postgres -Atc \
  "SELECT COALESCE((SELECT indisvalid::text FROM pg_index WHERE indexrelid='idx_cdatpcsuspect_imeinumber'::regclass),'missing')" 2>/dev/null || echo missing)
say "IMEI index valid: $imei_valid"

# Migration checkpoint
PGPASSWORD="${DIST_PG_PASSWORD}" psql -h 127.0.0.1 -U postgres -d distributed_db -c \
  "SELECT job_name, rows_committed, status FROM distributed_migration_checkpoint ORDER BY 1;" 2>/dev/null || true

if (( CRIT )); then exit 2; elif (( WARN )); then exit 1; fi
say "All checks passed."
exit 0
