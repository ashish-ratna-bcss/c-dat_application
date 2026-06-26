-- CDAT PostgreSQL guardrails (web DB only; migrations use distributed_db).
-- Safe to re-run.

-- Web app connections to postgres: cap idle locks and runaway transactions.
ALTER ROLE postgres IN DATABASE postgres SET idle_in_transaction_session_timeout = '60s';
ALTER ROLE postgres IN DATABASE postgres SET lock_timeout = '30s';

-- Background workers on distributed_db may run long batches.
ALTER ROLE postgres IN DATABASE distributed_db SET idle_in_transaction_session_timeout = '300s';
ALTER ROLE postgres IN DATABASE distributed_db SET lock_timeout = '60s';

-- Reload settings for existing sessions on next connect.
SELECT pg_reload_conf();
