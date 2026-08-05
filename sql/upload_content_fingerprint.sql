
ALTER TABLE upload_activity_logs
    ADD COLUMN IF NOT EXISTS content_fingerprint VARCHAR(64);

CREATE INDEX IF NOT EXISTS idx_upload_logs_content_fingerprint
    ON upload_activity_logs (table_name, content_fingerprint)
    WHERE content_fingerprint IS NOT NULL AND upload_status = 'Success';
