#!/usr/bin/env bash
# Apply sql/fdw_setup.sql using database names from project-root .env
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
: "${IR_DB_NAME:=IR_DB}"
: "${JRMS_DB_NAME:=JRMS_DB}"
: "${PDACT_DB_NAME:=PDACT_DB}"
: "${ROWDY_SHEETS_DB_NAME:=ROWDY_SHEETS_DB}"
: "${TRAINING_DB_NAME:=TRAINING_DB}"

export PGPASSWORD="${CDR_DB_PASSWORD}"

psql \
  -h "$CDR_DB_HOST" \
  -p "$CDR_DB_PORT" \
  -U "$CDR_DB_USER" \
  -d "$CDR_DB_NAME" \
  -v ON_ERROR_STOP=1 \
  -v db_host="$CDR_DB_HOST" \
  -v db_port="$CDR_DB_PORT" \
  -v db_user="$CDR_DB_USER" \
  -v db_password="$CDR_DB_PASSWORD" \
  -v ir_db="$IR_DB_NAME" \
  -v jrms_db="$JRMS_DB_NAME" \
  -v pdact_db="$PDACT_DB_NAME" \
  -v rowdy_db="$ROWDY_SHEETS_DB_NAME" \
  -v training_db="$TRAINING_DB_NAME" \
  -f "$ROOT/sql/fdw_setup.sql"
