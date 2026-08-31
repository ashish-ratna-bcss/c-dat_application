#!/usr/bin/env bash
# Check server impact before starting Postgres copy
set -euo pipefail

echo "========== SERVER IMPACT CHECK =========="
echo "Time: $(date)"
echo

echo "--- 1. CPU / Memory / Load ---"
uptime
free -h | head -3
echo

echo "--- 2. Disk space ---"
df -h /mnt/storage1 /mnt/storage2 / 2>/dev/null | grep -v tmpfs
echo

echo "--- 3. All PostgreSQL databases ---"
if [[ -f /tmp/migrate_pgpass ]]; then export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
elif [[ -n "${PGPASSWORD:-}" ]]; then :
else
  PGPASS=$(grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/c-dat_application/.env 2>/dev/null | cut -d= -f2- | tr -d '"' || true)
  [[ -n "$PGPASS" ]] && export PGPASSWORD="$PGPASS"
fi
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -c "
    SELECT datname,
           pg_size_pretty(pg_database_size(datname)) AS size
    FROM pg_database
    WHERE datistemplate = false
    ORDER BY pg_database_size(datname) DESC;"
else
  echo "(skip - no postgres password)"
fi
echo

echo "--- 4. NEW migration target DBs (safe to write) ---"
for db in CDATDUPL_DB IR_DB JRMS_DB PDACT_DB ROWDY_SHEETS_DB; do
  if [[ -n "${PGPASSWORD:-}" ]]; then
    sz=$(psql -h localhost -U postgres -d postgres -t -A -c "SELECT pg_size_pretty(pg_database_size('$db'));" 2>/dev/null || echo "missing")
    echo "  $db -> $sz"
  fi
done
echo

echo "--- 5. OLD / existing DBs (must NOT touch) ---"
for db in postgres cdat_db distributed_db; do
  if [[ -n "${PGPASSWORD:-}" ]]; then
    exists=$(psql -h localhost -U postgres -d postgres -t -A -c "SELECT CASE WHEN EXISTS(SELECT 1 FROM pg_database WHERE datname='$db') THEN 'EXISTS' ELSE 'missing' END;" 2>/dev/null)
    if [[ "$exists" == "EXISTS" ]]; then
      sz=$(psql -h localhost -U postgres -d postgres -t -A -c "SELECT pg_size_pretty(pg_database_size('$db'));" 2>/dev/null)
      echo "  $db -> $exists, size $sz (LIVE - do not migrate into)"
    fi
  fi
done
echo

echo "--- 6. Active Postgres connections (who is using DB now) ---"
if [[ -n "${PGPASSWORD:-}" ]]; then
  psql -h localhost -U postgres -d postgres -c "
    SELECT datname, usename, count(*) AS connections, state
    FROM pg_stat_activity
    WHERE datname IS NOT NULL
    GROUP BY datname, usename, state
    ORDER BY connections DESC, datname
    LIMIT 25;"
fi
echo

echo "--- 7. Running web/app processes ---"
ps aux | grep -iE 'apache|nginx|php-fpm|httpd|node|python.*migrate' | grep -v grep | head -20 || echo "  (none found in ps)"
echo

echo "--- 8. Docker MSSQL container ---"
docker stats mssql --no-stream 2>/dev/null || echo "  docker stats unavailable"
echo

echo "--- 9. Any background import workers running? ---"
ps aux | grep -E 'worker\.py|main\.py|sdr_import|cdr_import' | grep -v grep || echo "  none"
echo
