-- Link upload audit rows to background document processing jobs.
ALTER TABLE upload_activity_logs
    ADD COLUMN IF NOT EXISTS document_job_id BIGINT;

CREATE INDEX IF NOT EXISTS idx_upload_logs_document_job_id
    ON upload_activity_logs (document_job_id);
