-- =============================================================================
-- MSSQL to PostgreSQL Migration: JRMS_DB
-- Source: /Desktop/old/mssql/*.sql
-- Branch: mssql-to-postgres-migration
-- Only tables/views actually referenced by the application are included.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS / CREATE OR REPLACE VIEW
-- =============================================================================

-- ------------------------------------------------------------

-- Target database: JRMS_DB

-- TABLE: JRMS_TOTAL_2012_TO_2017
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS jrms_total_2012_to_2017 (
    CIN bigint NULL,
    PSArrested TEXT NULL,
    Name TEXT NOT NULL,
    PrisonerNo TEXT NULL,
    Gender TEXT NULL,
    TypeofRelease TEXT NULL,
    Photo TEXT NULL,
    JailName TEXT NULL,
    Admission_to_Jail TEXT NULL,
    ReleaseDt TEXT NULL,
    Addr_DuringRelease TEXT NULL,
    HeadofCrime TEXT NOT NULL,
    IdentificationMark TEXT NOT NULL,
    PlaceofIdentificationMark TEXT NOT NULL,
    RlDtOrder TEXT NULL,
    CrimeNos TEXT NULL,
    FathersName TEXT NOT NULL,
    MobileNo TEXT NULL,
    JailRefId TEXT NULL,
    DISTRICT varchar(500) NULL,
    UNIQUE_KEY varchar(25) NULL,
    IRKEY varchar(25) NULL,
    ASONDATE date NULL,
    APP_OR_MANUAL varchar(20) NULL,
    DOB_AGE date NULL,
    IDPROOF_TYPE varchar(100) NULL,
    IDPROOF_NO varchar(20) NULL,
    SEC_OF_LAW varchar(250) NULL,
    REMARKS varchar(250) NULL,
    AUTO_KEY BIGSERIAL NOT NULL,
    ID_PROOF varchar(50) NULL
);

-- ------------------------------------------------------------

