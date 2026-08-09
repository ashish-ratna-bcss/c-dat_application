-- Run during a quiet window: SET lock_timeout = 0; then apply.
-- CREATE INDEX CONCURRENTLY cannot run inside a transaction block.
CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_cdatpcsuspect_phone_other_starttime
    ON cdatpcsuspect (phone, other, starttime);
