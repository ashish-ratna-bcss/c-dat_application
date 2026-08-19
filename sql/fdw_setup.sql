-- =============================================================================
-- postgres_fdw setup: mount satellite databases into CDATDUPL_DB.
--
-- Do not run this file directly. Apply it from .env:
--
--   bash sql/apply_fdw.sh
--
-- After this, the app connects only to CDR_DB_NAME and sees all tables as local.
-- =============================================================================

\if :{?ir_db}
\else
\echo 'Run: bash sql/apply_fdw.sh  (database names come from .env)'
\quit 1
\endif

CREATE EXTENSION IF NOT EXISTS postgres_fdw;

DROP SERVER IF EXISTS ir_server      CASCADE;
DROP SERVER IF EXISTS jrms_server    CASCADE;
DROP SERVER IF EXISTS pdact_server   CASCADE;
DROP SERVER IF EXISTS rowdy_server   CASCADE;

CREATE SERVER ir_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host :'db_host', port :'db_port', dbname :'ir_db');

CREATE SERVER jrms_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host :'db_host', port :'db_port', dbname :'jrms_db');

CREATE SERVER pdact_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host :'db_host', port :'db_port', dbname :'pdact_db');

CREATE SERVER rowdy_server
    FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host :'db_host', port :'db_port', dbname :'rowdy_db');

CREATE USER MAPPING FOR CURRENT_USER
    SERVER ir_server
    OPTIONS (user :'db_user', password :'db_password');

CREATE USER MAPPING FOR CURRENT_USER
    SERVER jrms_server
    OPTIONS (user :'db_user', password :'db_password');

CREATE USER MAPPING FOR CURRENT_USER
    SERVER pdact_server
    OPTIONS (user :'db_user', password :'db_password');

CREATE USER MAPPING FOR CURRENT_USER
    SERVER rowdy_server
    OPTIONS (user :'db_user', password :'db_password');

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

SELECT
    ft.foreign_table_name AS table_name,
    fs.srvname            AS source_server
FROM information_schema.foreign_tables ft
JOIN pg_foreign_table pft ON pft.ftrelid = (
    SELECT oid FROM pg_class WHERE relname = ft.foreign_table_name LIMIT 1
)
JOIN pg_foreign_server fs ON fs.oid = pft.ftserver
ORDER BY fs.srvname, ft.foreign_table_name;
