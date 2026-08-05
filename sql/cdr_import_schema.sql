
CREATE TABLE IF NOT EXISTS cdatpcsuspect_staging (
    staging_id          BIGSERIAL PRIMARY KEY,
    import_job_id       BIGINT NOT NULL REFERENCES document_jobs (job_id),
    source_row_number   BIGINT NOT NULL,
    ucid                BIGINT NOT NULL,
    phone               VARCHAR(25),
    other               VARCHAR(50),
    starttime           TIMESTAMP WITHOUT TIME ZONE NOT NULL,
    duration            NUMERIC(5,0) NOT NULL,
    incoming            SMALLINT NOT NULL,
    imeinumber          NUMERIC(18,0) NOT NULL,
    imsinumber          NUMERIC(18,0),
    celltowerid         VARCHAR(50),
    otherinfo           VARCHAR(50),
    tower_key           NUMERIC(18,0),
    provider_key        SMALLINT NOT NULL,
    state_key           SMALLINT,
    first_cellid        VARCHAR(50),
    last_cellid         VARCHAR(50),
    roaming_nw          VARCHAR(50),
    call_type           VARCHAR(25),
    calling_no          VARCHAR(50),
    called_no           VARCHAR(50),
    asondate            TIMESTAMP WITHOUT TIME ZONE,
    operator            VARCHAR(20) NOT NULL,
    source_file         TEXT NOT NULL,
    imported_at         TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_cdatpcsuspect_staging_job
    ON cdatpcsuspect_staging (import_job_id, source_row_number);

CREATE SEQUENCE IF NOT EXISTS cdr_import_ucid_seq START WITH -1 INCREMENT BY -1;
