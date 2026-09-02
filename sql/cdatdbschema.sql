-- =============================================================================
-- CDAT upload schemas inside CDATDUPL_DB
-- Safe to re-run: CREATE SCHEMA / TABLE / INDEX IF NOT EXISTS
-- Per-file CDR staging tables are created at runtime in cdatpcsuspectstagingdb.
-- =============================================================================

-- Target database: CDATDUPL_DB

CREATE SCHEMA IF NOT EXISTS cdatupload;
CREATE SCHEMA IF NOT EXISTS cdatpcsuspectstagingdb;

-- ------------------------------------------------------------
-- TABLE: cdatupload.cdr_pipeline_jobs
-- One row per Staging / Insert DB run.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatupload.cdr_pipeline_jobs (
    job_id              BIGSERIAL PRIMARY KEY,
    username            VARCHAR(100) NOT NULL DEFAULT 'user',
    filename            TEXT NOT NULL,
    file_path           TEXT,
    file_size           BIGINT NOT NULL DEFAULT 0,
    ip_address          VARCHAR(45),
    module_name         VARCHAR(50) NOT NULL DEFAULT 'CDR',
    staging_table       VARCHAR(63),
    staging_dropped     BOOLEAN NOT NULL DEFAULT FALSE,
    phase               VARCHAR(30) NOT NULL DEFAULT 'queued',
    progress            SMALLINT NOT NULL DEFAULT 0,
    progress_label      VARCHAR(80) NOT NULL DEFAULT 'Queued',
    source_records      BIGINT NOT NULL DEFAULT 0,
    duplicate_records   BIGINT NOT NULL DEFAULT 0,
    already_in_db       BIGINT NOT NULL DEFAULT 0,
    already_in_staging  BIGINT NOT NULL DEFAULT 0,
    new_records         BIGINT NOT NULL DEFAULT 0,
    total_records       BIGINT NOT NULL DEFAULT 0,
    inserted_records    BIGINT NOT NULL DEFAULT 0,
    failed_records      BIGINT NOT NULL DEFAULT 0,
    error_message       TEXT,
    log_id              BIGINT,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at          TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    completed_at        TIMESTAMPTZ
);

ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS staging_dropped BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS source_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS duplicate_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS already_in_db BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS already_in_staging BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS new_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.cdr_pipeline_jobs ADD COLUMN IF NOT EXISTS completed_at TIMESTAMPTZ;

CREATE INDEX IF NOT EXISTS idx_cdr_pipeline_jobs_phase
    ON cdatupload.cdr_pipeline_jobs (phase);
CREATE INDEX IF NOT EXISTS idx_cdr_pipeline_jobs_log
    ON cdatupload.cdr_pipeline_jobs (log_id);

-- ------------------------------------------------------------
-- TABLE: cdatupload.upload_activity_logs
-- Upload History rows. document_job_id points at cdr_pipeline_jobs.job_id.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cdatupload.upload_activity_logs (
    id                  BIGSERIAL PRIMARY KEY,
    user_id             BIGINT,
    username            VARCHAR(100),
    module_name         VARCHAR(150) NOT NULL,
    file_name           TEXT NOT NULL,
    file_size           BIGINT NOT NULL DEFAULT 0,
    source_records      BIGINT NOT NULL DEFAULT 0,
    duplicate_records   BIGINT NOT NULL DEFAULT 0,
    already_in_db       BIGINT NOT NULL DEFAULT 0,
    already_in_staging  BIGINT NOT NULL DEFAULT 0,
    new_records         BIGINT NOT NULL DEFAULT 0,
    total_records       BIGINT NOT NULL DEFAULT 0,
    inserted_records    BIGINT NOT NULL DEFAULT 0,
    failed_records      BIGINT NOT NULL DEFAULT 0,
    upload_status       VARCHAR(30) NOT NULL DEFAULT 'Processing',
    error_reason        TEXT,
    ip_address          VARCHAR(45),
    db_name             VARCHAR(128),
    table_name          VARCHAR(128),
    staging_dropped     BOOLEAN NOT NULL DEFAULT FALSE,
    is_new_table        VARCHAR(10),
    content_fingerprint VARCHAR(128),
    document_job_id     BIGINT,
    staging_batch_id    BIGINT,
    verification_status VARCHAR(30),
    uploaded_at         TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    completed_at        TIMESTAMPTZ
);

ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS source_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS duplicate_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS already_in_db BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS already_in_staging BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS new_records BIGINT NOT NULL DEFAULT 0;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS staging_dropped BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE cdatupload.upload_activity_logs ADD COLUMN IF NOT EXISTS completed_at TIMESTAMPTZ;

CREATE INDEX IF NOT EXISTS idx_cdatupload_logs_document_job
    ON cdatupload.upload_activity_logs (document_job_id);
CREATE INDEX IF NOT EXISTS idx_cdatupload_logs_uploaded_at
    ON cdatupload.upload_activity_logs (uploaded_at DESC);
