#!/usr/bin/env bash
# Install and enable CDAT index build systemd units (run with sudo).
set -euo pipefail
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
UNIT_DIR="${APP_DIR}/scripts/systemd"

chmod +x "${APP_DIR}/scripts/build_dedup_index.sh"
chmod +x "${APP_DIR}/scripts/build_report_indexes.sh"
chmod +x "${APP_DIR}/scripts/build_distributed_reference_indexes.sh"

for unit in cdat-dedup-index.service cdat-distributed-indexes.service cdat-report-indexes.service cdat-index-pipeline.service; do
    install -m 0644 "${UNIT_DIR}/${unit}" "/etc/systemd/system/${unit}"
done

systemctl daemon-reload
systemctl enable cdat-dedup-index.service cdat-distributed-indexes.service cdat-report-indexes.service cdat-index-pipeline.service

echo "Installed units. Start full pipeline:"
echo "  sudo systemctl start cdat-index-pipeline.service"
echo "Or individually:"
echo "  sudo systemctl start cdat-distributed-indexes.service"
echo "  sudo systemctl start cdat-dedup-index.service   # if not already running"
echo "  sudo systemctl start cdat-report-indexes.service  # after dedup completes"
echo "Logs:"
echo "  /tmp/cdatpcsuspect_index.log"
echo "  /tmp/cdat_distributed_indexes.log"
echo "  /tmp/cdat_report_indexes.log"
