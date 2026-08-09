#!/usr/bin/env bash
# Start IMEI index build only when the host has headroom (avoids OOM during MSSQL index build).
set -euo pipefail
swap_pct=$(free | awk '/^Swap:/ {if ($2>0) printf "%.0f", $3/$2*100; else print 0}')
avail_gb=$(free -b | awk '/^Mem:/ {printf "%.1f", $7/1024/1024/1024}')

if (( swap_pct > 85 )); then
  echo "Swap at ${swap_pct}% — not starting IMEI index (run again when swap < 85%)"
  exit 1
fi
if awk "BEGIN {exit !($avail_gb < 4.0)}"; then
  echo "Only ${avail_gb} GB RAM available — not starting IMEI index"
  exit 1
fi

echo "Host OK (swap ${swap_pct}%, ${avail_gb} GB avail) — starting cdatpcsuspect-imei-index"
systemctl start cdatpcsuspect-imei-index
systemctl is-active cdatpcsuspect-imei-index
