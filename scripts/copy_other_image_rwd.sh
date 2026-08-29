#!/usr/bin/env bash
# Ensure ROWDY_SHEETS_DB.rowdy_sheeter_data1 exists, then copy OTHER_IMAGE_RWD tables
set -euo pipefail

export PGPASSWORD="$(grep -E '^CDR_DB_PASSWORD=' /mnt/storage1/c-dat_application/.env | cut -d= -f2- | tr -d '"')"
export MIGRATE_BATCH_SIZE="${MIGRATE_BATCH_SIZE:-2000}"

echo "=== Create rowdy_sheeter_data1 in ROWDY_SHEETS_DB if missing ==="
psql -U postgres -h localhost -d ROWDY_SHEETS_DB <<'SQL'
CREATE TABLE IF NOT EXISTS rowdy_sheeter_data1 (
    RWD_ID varchar(8000) NULL,
    IRKEY varchar(8000) NULL,
    PDACT_KEY varchar(8000) NULL,
    LATEST_ARREST varchar(8000) NULL,
    POLICE_STATION varchar(8000) NULL,
    DATE_OF_OPENING_RWD varchar(8000) NULL,
    RWD_YEAR varchar(8000) NULL,
    NAME varchar(8000) NULL,
    AGE varchar(8000) NULL,
    FATHER_NAME varchar(8000) NULL,
    PRESENT_ADDRESS varchar(8000) NULL,
    LAT_P varchar(8000) NULL,
    LONG_P varchar(8000) NULL,
    PERMANENT_ADDRESS varchar(8000) NULL,
    LAT varchar(8000) NULL,
    LONG varchar(8000) NULL,
    PHONE varchar(8000) NULL,
    ID_PROOF_TYPE varchar(8000) NULL,
    ID_NO varchar(8000) NULL,
    COMMUNAL_NONCOMMUNAL varchar(8000) NULL,
    ACTIVE_INACTIVE varchar(8000) NULL,
    LATEST_BIND_OVER_DATE varchar(8000) NULL,
    LBO_YEAR varchar(8000) NULL,
    PRESENT_ACTIVITY varchar(8000) NULL,
    PHOTO_ID varchar(8000) NULL,
    remarks varchar(8000) NULL,
    PS_TRANSFER_STATUS varchar(8000) NULL,
    COUNT_OF_INVD_CASES varchar(8000) NULL
);
SQL

echo "=== Copy OTHER_IMAGE_RWD -> Postgres ==="
python3 /home/hyd-cat/migrate_copy.py other_rwd

echo "=== Verify ==="
psql -U postgres -h localhost -d ROWDY_SHEETS_DB -c "SELECT 'ROWDY_SHEETS_DB.rowdy_sheeter_data1' AS t, count(*) FROM rowdy_sheeter_data1;"
psql -U postgres -h localhost -d CDATDUPL_DB -c "SELECT 'CDATDUPL_DB.suspect_image_table' AS t, count(*) FROM suspect_image_table;"
echo DONE
