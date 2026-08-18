-- =============================================================================
-- MSSQL to PostgreSQL Migration: ROWDY_SHEETS_DB
-- Source: /Desktop/old/mssql/*.sql
-- Branch: mssql-to-postgres-migration
-- Only tables/views actually referenced by the application are included.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS / CREATE OR REPLACE VIEW
-- =============================================================================

-- ------------------------------------------------------------

-- Target database: ROWDY_SHEETS_DB

-- TABLE: ROWDY_SHEETER_COMPLETE_DATA
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rowdy_sheeter_complete_data (
    RWD_ID varchar(8000) NULL,
    Irkey varchar(8000) NULL,
    PDAct_Key varchar(8000) NULL,
    latest_arrest varchar(8000) NULL,
    police_station varchar(8000) NULL,
    date_of_rwd varchar(8000) NULL,
    YEAR varchar(8000) NULL,
    name varchar(8000) NULL,
    Age varchar(8000) NULL,
    father_name varchar(8000) NULL,
    present_address varchar(8000) NULL,
    latitude1 varchar(8000) NULL,
    langitude1 varchar(8000) NULL,
    permanent_address varchar(8000) NULL,
    Latitude2 varchar(8000) NULL,
    langitude2 varchar(8000) NULL,
    phone varchar(8000) NULL,
    idproof varchar(8000) NULL,
    ID_No varchar(8000) NULL,
    communal_noncommunal varchar(8000) NULL,
    ACTIVE_STATUS varchar(8000) NULL,
    latest_bind_over varchar(8000) NULL,
    year2 varchar(8000) NULL,
    present_activity varchar(8000) NULL,
    photo_id varchar(8000) NULL,
    remarks varchar(8000) NULL,
    TRANSFER_PS varchar(8000) NULL,
    COUNT_OF_INVOLVED_CASES varchar(8000) NULL,
    ZONE varchar(10) NULL,
    DIVISION varchar(30) NULL
);

-- ------------------------------------------------------------

