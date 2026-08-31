#!/usr/bin/env bash
# Import nbws_verify_data_important into CDATDUPL_DB.
#
# Usage:
#   bash scripts/import_nbws_table.sh
#   NBWS_DUMP=/path/to/nbws.dump bash scripts/import_nbws_table.sh
#   NBWS_CSV=/path/to/nbws_verify_data_important.csv bash scripts/import_nbws_table.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$ROOT/.env"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing $ENV_FILE" >&2
  exit 1
fi

set -a
# shellcheck disable=SC1090
source "$ENV_FILE"
set +a

: "${CDR_DB_HOST:=localhost}"
: "${CDR_DB_PORT:=5432}"
: "${CDR_DB_NAME:=CDATDUPL_DB}"
: "${CDR_DB_USER:=postgres}"
: "${CDR_DB_PASSWORD:=}"

export PGPASSWORD="${CDR_DB_PASSWORD}"

PSQL=(psql -h "$CDR_DB_HOST" -p "$CDR_DB_PORT" -U "$CDR_DB_USER" -d "$CDR_DB_NAME" -v ON_ERROR_STOP=1)

echo "Applying NBWS DDL..."
"${PSQL[@]}" -f "$ROOT/sql/nbws_table.sql"

if [[ -n "${NBWS_DUMP:-}" && -f "$NBWS_DUMP" ]]; then
  echo "Restoring from dump: $NBWS_DUMP"
  pg_restore -h "$CDR_DB_HOST" -p "$CDR_DB_PORT" -U "$CDR_DB_USER" -d "$CDR_DB_NAME" \
    --no-owner --no-privileges --data-only "$NBWS_DUMP" || true
fi

if [[ -n "${NBWS_CSV:-}" && -f "$NBWS_CSV" ]]; then
  echo "Loading CSV: $NBWS_CSV"
  "${PSQL[@]}" -c "TRUNCATE public.nbws_verify_data_important;"
  "${PSQL[@]}" -c "\copy public.nbws_verify_data_important FROM '$NBWS_CSV' CSV HEADER"
fi

echo "NBWS import complete."
"${PSQL[@]}" -c "SELECT COUNT(*) AS nbws_rows FROM public.nbws_verify_data_important;"
