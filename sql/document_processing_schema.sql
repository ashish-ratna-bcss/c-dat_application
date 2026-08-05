
CREATE TABLE IF NOT EXISTS document_jobs (
    job_id                  BIGSERIAL PRIMARY KEY,
    module                  VARCHAR(20) NOT NULL,
    source_file             TEXT NOT NULL,
    source_basename         TEXT NOT NULL,
    file_path               TEXT NOT NULL,
    file_sha256             CHAR(64) NOT NULL,
    status                  VARCHAR(30) NOT NULL DEFAULT 'queued',
    phase                   VARCHAR(40),
    operator                VARCHAR(20),
    target_phone            VARCHAR(25),
    mssql_database          VARCHAR(128),
    total_rows_estimated    BIGINT,
    rows_committed          BIGINT NOT NULL DEFAULT 0,
    last_checkpoint_key     BIGINT NOT NULL DEFAULT 0,
    last_source_row_no      BIGINT NOT NULL DEFAULT 0,
    batch_size              INTEGER NOT NULL DEFAULT 500,
    phase_state             JSONB NOT NULL DEFAULT '{}'::jsonb,
    error_message           TEXT,
    dry_run                 BOOLEAN NOT NULL DEFAULT FALSE,
    created_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at              TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    completed_at            TIMESTAMPTZ,
    UNIQUE (module, source_file, file_sha256)
);

CREATE INDEX IF NOT EXISTS idx_document_jobs_status ON document_jobs (status);
CREATE INDEX IF NOT EXISTS idx_document_jobs_module ON document_jobs (module, status);

DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = 'public' AND table_name = 'cdr_import_jobs' AND table_type = 'BASE TABLE'
    ) THEN
        INSERT INTO document_jobs (
            module, source_file, source_basename, file_path, file_sha256,
            operator, target_phone, status, total_rows_estimated, rows_committed,
            last_source_row_no, batch_size, error_message, dry_run,
            created_at, updated_at, completed_at, phase
        )
        SELECT
            'cdr', source_file, source_basename, source_file, file_sha256,
            operator, target_phone, status, total_rows_estimated, rows_committed,
            last_source_row_no, batch_size, error_message, dry_run,
            created_at, updated_at, completed_at, 'import'
        FROM cdr_import_jobs
        ON CONFLICT (module, source_file, file_sha256) DO NOTHING;

        ALTER TABLE cdatpcsuspect_staging DROP CONSTRAINT IF EXISTS cdatpcsuspect_staging_import_job_id_fkey;
        DROP TABLE cdr_import_jobs;
    END IF;
END $$;

CREATE OR REPLACE VIEW cdr_import_jobs AS
SELECT
    job_id,
    source_file,
    source_basename,
    file_sha256,
    COALESCE(operator, '') AS operator,
    target_phone,
    status,
    NULL::INTEGER AS header_line_no,
    total_rows_estimated::INTEGER AS total_rows_estimated,
    rows_committed,
    last_source_row_no,
    batch_size,
    error_message,
    dry_run,
    created_at,
    updated_at,
    completed_at
FROM document_jobs
WHERE module = 'cdr';
