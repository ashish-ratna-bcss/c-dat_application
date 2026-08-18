-- =============================================================================
-- MSSQL to PostgreSQL Migration: PDACT_DB
-- Source: /Desktop/old/mssql/*.sql
-- Branch: mssql-to-postgres-migration
-- Only tables/views actually referenced by the application are included.
-- Safe to re-run: CREATE TABLE IF NOT EXISTS / CREATE OR REPLACE VIEW
-- =============================================================================

-- ------------------------------------------------------------

-- Target database: PDACT_DB

-- TABLE: PDACT_MAIN_TABLE
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pdact_main_table (
    PDACT_KEY BIGSERIAL NOT NULL,
    PDACT_CALL_KEY varchar(20) NULL,
    Name varchar(100) NULL,
    Father_Name varchar(50) NOT NULL,
    Age varchar(10) NULL,
    Dob date NULL,
    Occupation varchar(50) NULL,
    Caste varchar(50) NULL,
    Id_Proof varchar(50) NULL,
    Id_Proof_No varchar(50) NULL,
    Phone_No varchar(50) NULL,
    Irkey varchar(20) NULL,
    Present_Address varchar(1000) NULL,
    Permanent_Address varchar(1000) NULL,
    District varchar(50) NULL,
    State varchar(50) NULL,
    PD_ACT_PS varchar(50) NULL,
    Zone varchar(50) NULL,
    File_no varchar(50) NULL,
    File_No_Year varchar(50) NULL,
    Detenu_No varchar(50) NULL,
    Order_Issued_On date NULL,
    Approval_Orders_No varchar(500) NULL,
    Confirmation_Revocation_Orders varchar(500) NULL,
    Crime_Head varchar(50) NULL,
    Minor_Head varchar(50) NULL,
    ModusOperendi varchar(500) NULL,
    Police_Station varchar(50) NULL,
    Crime_No varchar(100) NULL,
    Year varchar(20) NULL,
    Sec_Of_Law varchar(250) NULL,
    Whether_Involved_In_Other_Unit_Cases varchar(250) NULL,
    Name_Of_Units varchar(500) NULL,
    No_Of_Cases varchar(50) NULL,
    Date_Of_Arrest date NULL,
    Date_Of_Release date NULL,
    Brief_Facts varchar(8000) NULL,
    ASONDATE TIMESTAMP NULL,
    IMAGE BYTEA NULL,
    CRIME_HEAD_SEARCH varchar(50) NULL,
    DIVISION varchar(50) NULL
);
