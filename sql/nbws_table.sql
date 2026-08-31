-- NBWS pending cases for modules/interrogation-reports/ir.php
-- Apply on CDATDUPL_DB (local table) or IR_DB + FDW re-import.
-- Load data: bash scripts/import_nbws_table.sh

CREATE TABLE IF NOT EXISTS public.nbws_verify_data_important (
    irkey                  numeric(18, 0) NOT NULL,
    first_hearing_date     date NULL,
    decision_date          date NULL,
    case_status            varchar(500) NULL,
    next_hearing_date      date NULL,
    nature_of_disposal     varchar(500) NULL,
    court_number_and_judge varchar(500) NULL,
    stage_of_case          varchar(500) NULL,
    petitioner_respondent  varchar(1000) NULL,
    act_and_sec            varchar(500) NULL
);

CREATE INDEX IF NOT EXISTS idx_nbws_verify_irkey
    ON public.nbws_verify_data_important (irkey);
CREATE INDEX IF NOT EXISTS idx_nbws_verify_case_status
    ON public.nbws_verify_data_important (case_status);
