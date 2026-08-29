#!/usr/bin/env bash
# Rename Postgres database CDR_DB -> CDATDUPL_DB
set -euo pipefail

PGPASS=$(grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/systemd-install/cdat-web/.env 2>/dev/null | cut -d= -f2- | tr -d '"' \
  || grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/c-dat_application/.env 2>/dev/null | cut -d= -f2- | tr -d '"' \
  || true)
if [[ -z "$PGPASS" && -f /tmp/migrate_pgpass ]]; then
  PGPASS=$(cat /tmp/migrate_pgpass)
fi
if [[ -z "$PGPASS" ]]; then
  echo "ERROR: no postgres password found" >&2
  exit 1
fi
export PGPASSWORD="$PGPASS"

echo "=== Before rename ==="
psql -h localhost -U postgres -d postgres -t -A -c \
  "SELECT datname FROM pg_database WHERE datname IN ('CDR_DB','CDATDUPL_DB') ORDER BY datname;"

if psql -h localhost -U postgres -d postgres -t -A -c \
  "SELECT 1 FROM pg_database WHERE datname='CDATDUPL_DB';" | grep -q 1; then
  echo "CDATDUPL_DB already exists — nothing to do"
  exit 0
fi

if ! psql -h localhost -U postgres -d postgres -t -A -c \
  "SELECT 1 FROM pg_database WHERE datname='CDR_DB';" | grep -q 1; then
  echo "ERROR: CDR_DB not found" >&2
  exit 1
fi

echo "Terminating connections to CDR_DB..."
psql -h localhost -U postgres -d postgres -c \
  "SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname='CDR_DB' AND pid <> pg_backend_pid();"

echo "Renaming CDR_DB -> CDATDUPL_DB..."
psql -h localhost -U postgres -d postgres -c \
  'ALTER DATABASE "CDR_DB" RENAME TO "CDATDUPL_DB";'

echo "=== After rename ==="
psql -h localhost -U postgres -d postgres -t -A -c \
  "SELECT datname FROM pg_database WHERE datname IN ('CDR_DB','CDATDUPL_DB') ORDER BY datname;"
psql -h localhost -U postgres -d CDATDUPL_DB -c '\dt' | head -20
echo "Done."
