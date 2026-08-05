#!/usr/bin/env bash
# Print status of CDAT index build services and key indexes.
set -euo pipefail
APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DB_CONFIG="${APP_DIR}/db_config.php"
read_cfg() { php -r '$c = require $argv[1]; echo $c[$argv[2]] ?? "";' "$DB_CONFIG" "$1"; }
export PGHOST="$(read_cfg host)" PGPORT="$(read_cfg port)" PGUSER="$(read_cfg user)" PGPASSWORD="$(read_cfg password)"

echo "=== systemd ==="
for u in cdat-dedup-index cdat-distributed-indexes cdat-report-indexes cdat-index-pipeline; do
    state="$(systemctl is-active "${u}.service" 2>/dev/null || true)"
    [ -z "${state}" ] && state="unknown"
    printf "  %-30s %s\n" "${u}.service" "${state}"
done

echo ""
echo "=== postgres.cdatpcsuspect indexes ==="
PGDATABASE="$(read_cfg database)" psql -c "
SELECT c.relname AS index_name,
       pg_size_pretty(pg_relation_size(c.oid)) AS size,
       i.indisvalid AS valid,
       CASE WHEN p.phase IS NOT NULL THEN p.phase || ' ' ||
            round(100.0 * p.blocks_done / nullif(p.blocks_total, 0), 1) || '%'
            ELSE 'idle' END AS build_progress
FROM pg_class c
JOIN pg_index i ON i.indexrelid = c.oid
JOIN pg_class t ON t.oid = i.indrelid
LEFT JOIN pg_stat_progress_create_index p ON p.index_relid = c.oid
WHERE t.relname = 'cdatpcsuspect'
ORDER BY c.relname;
"

echo ""
echo "=== distributed_db reference indexes ==="
PGDATABASE=distributed_db psql -c "
SELECT indexname, indexdef FROM pg_indexes
WHERE schemaname = 'public'
  AND tablename IN ('cdataddress', 'address_other_state', 'cellids')
ORDER BY tablename, indexname;
" 2>/dev/null || echo "  (distributed_db unavailable)"

echo ""
echo "Logs: /tmp/cdatpcsuspect_index.log /tmp/cdat_distributed_indexes.log /tmp/cdat_report_indexes.log"
