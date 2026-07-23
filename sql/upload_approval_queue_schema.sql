

CREATE TABLE IF NOT EXISTS upload_approval_queue (
    queue_id          BIGSERIAL PRIMARY KEY,
    batch_id          BIGINT NOT NULL REFERENCES upload_staging_batches (batch_id) ON DELETE CASCADE,
    module            VARCHAR(20) NOT NULL,
    username          VARCHAR(100) NOT NULL DEFAULT '',
    status            VARCHAR(20) NOT NULL DEFAULT 'queued',
    inserted_records  BIGINT,
    error_message     TEXT,
    queued_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    started_at        TIMESTAMPTZ,
    completed_at      TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_upload_approval_queue_module_status
    ON upload_approval_queue (module, status, queued_at);

CREATE UNIQUE INDEX IF NOT EXISTS idx_upload_approval_queue_batch_active
    ON upload_approval_queue (batch_id)
    WHERE status IN ('queued', 'running');
