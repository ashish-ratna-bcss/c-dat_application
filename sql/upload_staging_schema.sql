
CREATE TABLE IF NOT EXISTS upload_staging_batches (
    batch_id            BIGSERIAL PRIMARY KEY,
    document_job_id     BIGINT NOT NULL UNIQUE REFERENCES document_jobs (job_id) ON DELETE CASCADE,
    upload_log_id       BIGINT,
    module              VARCHAR(20) NOT NULL,
    staging_tables      JSONB NOT NULL DEFAULT '{}'::jsonb,
    verification_status VARCHAR(30) NOT NULL DEFAULT 'pending',
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    verified_at         TIMESTAMPTZ,
    verified_by         VARCHAR(100)
);

CREATE INDEX IF NOT EXISTS idx_upload_staging_batches_status
    ON upload_staging_batches (verification_status);

ALTER TABLE upload_activity_logs
    ADD COLUMN IF NOT EXISTS staging_batch_id BIGINT;

ALTER TABLE upload_activity_logs
    ADD COLUMN IF NOT EXISTS verification_status VARCHAR(30);

CREATE INDEX IF NOT EXISTS idx_upload_logs_staging_batch
    ON upload_activity_logs (staging_batch_id);
