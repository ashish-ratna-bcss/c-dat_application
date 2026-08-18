-- =============================================================================
-- postgres_fdw setup: mount IR_DB, JRMS_DB, PDACT_DB, ROWDY_SHEETS_DB
-- as foreign tables inside CDR_DB (the main application database).
--
-- Run this ONCE inside CDR_DB after all 5 databases have been created
-- and their tables loaded:
--
--   psql -h localhost -p 5432 -U sadhudinakar -d CDR_DB -f sql/fdw_setup.sql
--
-- After this, the app connects only to CDR_DB and sees all tables as local.
-- =============================================================================

-- Step 1: enable the extension (safe to re-run)
CREATE EXTENSION IF NOT EXISTS postgres_fdw;

-- =============================================================================
-- Step 2: foreign servers (one per satellite database)
-- =============================================================================

-- Drop existing servers if re-running (cascade removes dependent mappings/tables)
DROP SERVER IF EXISTS ir_server      CASCADE;
DROP SERVER IF EXISTS jrms_server    CASCADE;
DROP SERVER IF EXISTS pdact_server   CASCADE;
DROP SERVER IF EXISTS rowdy_server   CASCADE;

CREATE SERVER ir_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'localhost', port '5432', dbname 'IR_DB');

CREATE SERVER jrms_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'localhost', port '5432', dbname 'JRMS_DB');

CREATE SERVER pdact_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'localhost', port '5432', dbname 'PDACT_DB');

CREATE SERVER rowdy_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'localhost', port '5432', dbname 'ROWDY_SHEETS_DB');

-- =============================================================================
-- Step 3: user mappings (replace 'sadhudinakar' with your actual PG user)
-- =============================================================================

CREATE USER MAPPING FOR sadhudinakar
    SERVER ir_server
    OPTIONS (user 'sadhudinakar', password '');

CREATE USER MAPPING FOR sadhudinakar
    SERVER jrms_server
    OPTIONS (user 'sadhudinakar', password '');

CREATE USER MAPPING FOR sadhudinakar
    SERVER pdact_server
    OPTIONS (user 'sadhudinakar', password '');

CREATE USER MAPPING FOR sadhudinakar
    SERVER rowdy_server
    OPTIONS (user 'sadhudinakar', password '');

-- =============================================================================
-- Step 4: import all foreign tables into the public schema of CDR_DB
-- This makes every table in each satellite DB appear as a local table.
-- =============================================================================

IMPORT FOREIGN SCHEMA public
    EXCEPT (logins)
    FROM SERVER ir_server
    INTO public;

IMPORT FOREIGN SCHEMA public
    FROM SERVER jrms_server
    INTO public;

IMPORT FOREIGN SCHEMA public
    FROM SERVER pdact_server
    INTO public;

IMPORT FOREIGN SCHEMA public
    FROM SERVER rowdy_server
    INTO public;

-- =============================================================================
-- Step 5: verify — list all foreign tables now visible in CDR_DB
-- =============================================================================

SELECT
    ft.foreign_table_name AS table_name,
    fs.srvname            AS source_server
FROM information_schema.foreign_tables ft
JOIN pg_foreign_table pft ON pft.ftrelid = (
    SELECT oid FROM pg_class WHERE relname = ft.foreign_table_name LIMIT 1
)
JOIN pg_foreign_server fs ON fs.oid = pft.ftserver
ORDER BY fs.srvname, ft.foreign_table_name;
