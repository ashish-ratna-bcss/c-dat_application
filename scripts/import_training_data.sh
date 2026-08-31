#!/usr/bin/env bash
# Create satellite TRAINING_DB, apply schema, load data, then FDW-mount into CDATDUPL_DB.
#
# Usage:
#   bash scripts/import_training_data.sh
#   TRAINING_DUMP=/path/to/training.dump bash scripts/import_training_data.sh
#   TRAINING_CSV_DIR=/path/to/csvs bash scripts/import_training_data.sh
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
: "${TRAINING_DB_NAME:=TRAINING_DB}"

export PGPASSWORD="${CDR_DB_PASSWORD}"

PSQL=(psql -h "$CDR_DB_HOST" -p "$CDR_DB_PORT" -U "$CDR_DB_USER" -v ON_ERROR_STOP=1)
TRAINING_PSQL=(psql -h "$CDR_DB_HOST" -p "$CDR_DB_PORT" -U "$CDR_DB_USER" -d "$TRAINING_DB_NAME" -v ON_ERROR_STOP=1)

echo "Ensuring database $TRAINING_DB_NAME exists..."
exists=$("${PSQL[@]}" -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname = '$TRAINING_DB_NAME'")
if [[ "$exists" != "1" ]]; then
  "${PSQL[@]}" -d postgres -c "CREATE DATABASE \"$TRAINING_DB_NAME\";"
fi

echo "Applying training schema on $TRAINING_DB_NAME..."
"${TRAINING_PSQL[@]}" -f "$ROOT/sql/training_db.sql"

if [[ -n "${TRAINING_DUMP:-}" && -f "$TRAINING_DUMP" ]]; then
  echo "Restoring from dump: $TRAINING_DUMP"
  pg_restore -h "$CDR_DB_HOST" -p "$CDR_DB_PORT" -U "$CDR_DB_USER" -d "$TRAINING_DB_NAME" \
    --no-owner --no-privileges --data-only "$TRAINING_DUMP" || true
fi

if [[ -n "${TRAINING_CSV_DIR:-}" && -d "$TRAINING_CSV_DIR" ]]; then
  if [[ -f "$TRAINING_CSV_DIR/training_strength_particulars.csv" ]]; then
    echo "Loading training_strength_particulars.csv..."
    "${TRAINING_PSQL[@]}" -c "TRUNCATE training_strength_particulars;"
    "${TRAINING_PSQL[@]}" -c "\copy training_strength_particulars FROM '$TRAINING_CSV_DIR/training_strength_particulars.csv' CSV HEADER"
  fi
  if [[ -f "$TRAINING_CSV_DIR/trng_att_with_empid.csv" ]]; then
    echo "Loading trng_att_with_empid.csv..."
    "${TRAINING_PSQL[@]}" -c "TRUNCATE trng_att_with_empid;"
    "${TRAINING_PSQL[@]}" -c "\copy trng_att_with_empid FROM '$TRAINING_CSV_DIR/trng_att_with_empid.csv' CSV HEADER"
  fi
fi

echo "Removing stale local training copies from $CDR_DB_NAME (if any)..."
"${PSQL[@]}" -d "$CDR_DB_NAME" -f "$ROOT/sql/drop_local_training_schema.sql"

echo "Mounting $TRAINING_DB_NAME into $CDR_DB_NAME via FDW..."
bash "$ROOT/sql/apply_fdw.sh"

echo "Training satellite DB ready."
"${TRAINING_PSQL[@]}" -c "SELECT 'training_strength_particulars' AS tbl, COUNT(*) FROM training_strength_particulars
UNION ALL SELECT 'trng_att_with_empid', COUNT(*) FROM trng_att_with_empid;"
