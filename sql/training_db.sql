-- =============================================================================
-- PostgreSQL schema for satellite database TRAINING_DB
-- Source: legacy MSSQL TRAINING_DB (PWDMS + training attendance)
--
-- Apply on the TRAINING_DB database (not CDATDUPL_DB):
--   bash scripts/import_training_data.sh
--
-- Then mount into CDATDUPL_DB via FDW:
--   bash sql/apply_fdw.sh
-- =============================================================================

CREATE TABLE IF NOT EXISTS training_strength_particulars (
    employee_id    varchar(50),
    name           varchar(250),
    rank           varchar(50),
    role           varchar(100),
    general_no     varchar(50),
    wing_name      varchar(100),
    zone_name      varchar(100),
    division_name  varchar(100),
    police_station varchar(100)
);

CREATE INDEX IF NOT EXISTS idx_training_strength_employee_id
    ON training_strength_particulars (employee_id);
CREATE INDEX IF NOT EXISTS idx_training_strength_general_no
    ON training_strength_particulars (general_no);
CREATE INDEX IF NOT EXISTS idx_training_strength_name
    ON training_strength_particulars (name);

CREATE TABLE IF NOT EXISTS trng_att_with_empid (
    employee_id   varchar(50),
    general_no    varchar(50),
    names         varchar(250),
    ps_name       varchar(100),
    ph_no         varchar(50),
    zone          varchar(100),
    rank          varchar(50),
    course_name   varchar(250),
    start_date    date,
    end_date      date
);

CREATE INDEX IF NOT EXISTS idx_trng_att_employee_id
    ON trng_att_with_empid (employee_id);
CREATE INDEX IF NOT EXISTS idx_trng_att_general_no
    ON trng_att_with_empid (general_no);
CREATE INDEX IF NOT EXISTS idx_trng_att_names
    ON trng_att_with_empid (names);
