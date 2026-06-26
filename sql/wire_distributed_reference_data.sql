-- Wire distributed_db (Citus) reference data into the CDAT postgres database.
-- Replaces empty local stubs with views over postgres_fdw foreign tables.

CREATE EXTENSION IF NOT EXISTS postgres_fdw;

DO $$ BEGIN
  CREATE SERVER distributed_db_srv FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host '127.0.0.1', port '5432', dbname 'distributed_db');
EXCEPTION WHEN duplicate_object THEN NULL;
END $$;

DO $$ BEGIN
  CREATE USER MAPPING FOR postgres SERVER distributed_db_srv
    OPTIONS (user 'postgres', password 'REPLACE_WITH_DISTRIBUTED_DB_PASSWORD');
EXCEPTION WHEN duplicate_object THEN NULL;
END $$;

CREATE SCHEMA IF NOT EXISTS dist;

-- Import new foreign tables (skip if already present)
DO $$ BEGIN
  IMPORT FOREIGN SCHEMA public
    LIMIT TO (cdataddress, cellids)
    FROM SERVER distributed_db_srv INTO dist;
EXCEPTION WHEN duplicate_table THEN
  RAISE NOTICE 'dist.cdataddress or dist.cellids already imported';
END $$;

-- Ensure core dist tables exist (idempotent re-import attempt)
DO $$ BEGIN
  IMPORT FOREIGN SCHEMA public
    LIMIT TO (rta_data, dl_data, echallan_data, tc_name, address_other_state)
    FROM SERVER distributed_db_srv INTO dist;
EXCEPTION WHEN duplicate_table THEN
  RAISE NOTICE 'dist core foreign tables already imported';
END $$;

-- Out-of-state addresses (distributed_db Citus)
DROP TABLE IF EXISTS public.address_other_state CASCADE;
DROP VIEW IF EXISTS public.address_other_state CASCADE;
CREATE VIEW public.address_other_state AS
SELECT * FROM dist.address_other_state;

-- In-state addresses (distributed_db Citus, migrates from MSSQL CDATADDRESS)
DROP TABLE IF EXISTS public.cdataddress CASCADE;
DROP VIEW IF EXISTS public.cdataddress CASCADE;
CREATE VIEW public.cdataddress AS
SELECT * FROM dist.cdataddress;

-- Cell towers (distributed_db Citus CELLIDS -> legacy cdatcelltowerareanew shape)
DROP TABLE IF EXISTS public.cdatcelltowerareanew CASCADE;
DROP VIEW IF EXISTS public.cdatcelltowerareanew CASCADE;
CREATE VIEW public.cdatcelltowerareanew AS
SELECT
    celltowerid,
    operator,
    state,
    siteaddress,
    areadescription,
    lastupdate,
    provider_key::smallint AS provider_key,
    state_key::smallint AS state_key,
    bts_id,
    lat,
    long,
    azimuth
FROM dist.cellids;

-- RTA / DL views (idempotent)
DROP VIEW IF EXISTS public.cdat_rta CASCADE;
DROP TABLE IF EXISTS public.cdat_rta CASCADE;
CREATE VIEW public.cdat_rta AS
SELECT
    vehicle_no AS regn_no,
    owner_name AS fullname,
    father_name AS fathername,
    COALESCE(address, '') || CASE WHEN city IS NOT NULL AND city <> '' THEN ', ' || city ELSE '' END AS fulladdress,
    city,
    contact_no AS phone,
    maker_class AS mkr_clas,
    colour,
    vehicle_class AS veh_class,
    CONCAT(COALESCE(maker_class, ''), ', COLOR: ', COALESCE(colour, ''), ', ', COALESCE(vehicle_class, '')) AS vehicle_type,
    engine_no AS eng_no,
    chassis_no AS chas_no,
    issue_date AS iss_dt,
    issue_date AS updated_dt
FROM dist.rta_data;

DROP VIEW IF EXISTS public.cdat_licence CASCADE;
DROP TABLE IF EXISTS public.cdat_licence CASCADE;
CREATE VIEW public.cdat_licence AS
SELECT
    contact_no AS phone,
    dl_no AS licence_no,
    first_name AS fullname,
    parent_name AS father_name,
    dob,
    COALESCE(address, '') || CASE WHEN city IS NOT NULL AND city <> '' THEN ', ' || city ELSE '' END AS fulladdress
FROM dist.dl_data;

CREATE OR REPLACE VIEW public.cdat_echallan AS SELECT * FROM dist.echallan_data;
CREATE OR REPLACE VIEW public.cdat_tc_name AS SELECT * FROM dist.tc_name;
