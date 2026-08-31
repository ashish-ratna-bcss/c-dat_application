-- One-time migration: switch CDR import UCIDs from negative to positive.
-- Auto-detects start point from production (not a fixed number).
-- Safe to re-run.

ALTER SEQUENCE IF EXISTS cdr_import_ucid_seq INCREMENT BY 1;

SELECT setval(
    'cdr_import_ucid_seq',
    GREATEST(
        COALESCE((SELECT MAX(ucid) FROM cdatpcsuspect WHERE ucid > 0), 0),
        COALESCE((SELECT last_value FROM cdr_import_ucid_seq), 0)
    ),
    true
);
