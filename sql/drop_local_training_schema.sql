-- Remove local training_db schema if it was created on CDATDUPL_DB before FDW migration.
-- Safe to run on CDATDUPL_DB only. Does not touch satellite TRAINING_DB.
DROP SCHEMA IF EXISTS training_db CASCADE;

-- Drop local copies if present (FDW foreign tables replace these after apply_fdw.sh).
DROP TABLE IF EXISTS public.training_strength_particulars CASCADE;
DROP TABLE IF EXISTS public.trng_att_with_empid CASCADE;
