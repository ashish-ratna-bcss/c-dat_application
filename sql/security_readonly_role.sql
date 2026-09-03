-- C-DAT: optional read-only PostgreSQL role for the admin SQL console
-- Run as a PostgreSQL superuser after reviewing grants for your environment.
-- Application login (CDR_DB_USER) should NOT be a superuser.
-- Prefer a dedicated role for the SQL console connection if you enable it.

-- Example (adjust database / schema names):
-- CREATE ROLE cdat_sql_readonly LOGIN PASSWORD 'REPLACE_ME_STRONG';
-- GRANT CONNECT ON DATABASE "CDATDUPL_DB" TO cdat_sql_readonly;
-- GRANT USAGE ON SCHEMA public TO cdat_sql_readonly;
-- GRANT SELECT ON ALL TABLES IN SCHEMA public TO cdat_sql_readonly;
-- ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO cdat_sql_readonly;
--
-- Deny dangerous functions where present:
-- REVOKE EXECUTE ON FUNCTION pg_read_file(text) FROM PUBLIC;
-- REVOKE EXECUTE ON FUNCTION pg_read_file(text, bigint, bigint) FROM PUBLIC;
-- REVOKE EXECUTE ON FUNCTION pg_ls_dir(text) FROM PUBLIC;
--
-- Do NOT grant INSERT/UPDATE/DELETE/TRUNCATE/CREATE to cdat_sql_readonly.
-- Keep CDAT_SQL_CONSOLE=0 in production unless a documented need exists.

SELECT 'Apply grants manually after review — this file is documentation only.' AS notice;
