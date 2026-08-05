-- Load CDAT reference masters into postgres (CDAT operational DB).
-- Source files were pipe-delimited exports (not SQL dumps).
-- Goals: complete columns, no exact/whitespace duplicates, no key mismatches.

BEGIN;

-- Allow reload: drop FKs that would block TRUNCATE of masters
ALTER TABLE IF EXISTS public.cdatphonearea DROP CONSTRAINT IF EXISTS fk_cdatphonearea_state;
ALTER TABLE IF EXISTS public.cdatphonearea DROP CONSTRAINT IF EXISTS fk_cdatphonearea_provider;

-- -------------------------------------------------------------------------
-- cdat_state_master
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.cdat_state_master (
    state_key   integer PRIMARY KEY,
    state       text,
    capital     text,
    description text
);

TRUNCATE public.cdat_state_master;

\copy public.cdat_state_master (state_key, state, capital, description) FROM '/mnt/storage1/cdat-web/sql/data/loads/cdat_state_master.csv' WITH (FORMAT csv, NULL '')

-- -------------------------------------------------------------------------
-- cdat_provider_master
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.cdat_provider_master (
    provider_key  integer PRIMARY KEY,
    provider      text NOT NULL,
    provider_name text NOT NULL
);

TRUNCATE public.cdat_provider_master;

\copy public.cdat_provider_master (provider_key, provider, provider_name) FROM '/mnt/storage1/cdat-web/sql/data/loads/cdat_provider_master.csv' WITH (FORMAT csv, NULL '')

-- -------------------------------------------------------------------------
-- cdatphonearea: expand to full source columns, reload complete data
-- Keep phoneprefix + areadescription for existing app queries.
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS public.cdatphonearea (
    phoneprefix     varchar(20),
    areadescription text
);

-- Preserve existing seed rows (short prefixes) temporarily
CREATE TEMP TABLE _cdatphonearea_seed AS
SELECT phoneprefix, areadescription
FROM public.cdatphonearea;

ALTER TABLE public.cdatphonearea
    ADD COLUMN IF NOT EXISTS state           text,
    ADD COLUMN IF NOT EXISTS numberlength    integer,
    ADD COLUMN IF NOT EXISTS pplen           integer,
    ADD COLUMN IF NOT EXISTS ph_type         text,
    ADD COLUMN IF NOT EXISTS asondate        timestamp without time zone,
    ADD COLUMN IF NOT EXISTS state_key       integer,
    ADD COLUMN IF NOT EXISTS state_code      varchar(8),
    ADD COLUMN IF NOT EXISTS provider_name   text,
    ADD COLUMN IF NOT EXISTS provider_key    integer,
    ADD COLUMN IF NOT EXISTS mobile_network  text,
    ADD COLUMN IF NOT EXISTS state1          text;

TRUNCATE public.cdatphonearea;

\copy public.cdatphonearea (phoneprefix, areadescription, state, numberlength, pplen, ph_type, asondate, state_key, state_code, provider_name, provider_key, mobile_network, state1) FROM '/mnt/storage1/cdat-web/sql/data/loads/cdatphonearea.csv' WITH (FORMAT csv, NULL '')

-- Re-add short seed prefixes only when not already present (no duplication)
INSERT INTO public.cdatphonearea (phoneprefix, areadescription)
SELECT s.phoneprefix, s.areadescription
FROM _cdatphonearea_seed s
WHERE NOT EXISTS (
    SELECT 1 FROM public.cdatphonearea p WHERE p.phoneprefix = s.phoneprefix
);

-- Uniqueness: source allows same phoneprefix under different providers
CREATE UNIQUE INDEX IF NOT EXISTS uq_cdatphonearea_prefix_provider
    ON public.cdatphonearea (phoneprefix, provider_key)
    WHERE provider_key IS NOT NULL;

CREATE UNIQUE INDEX IF NOT EXISTS uq_cdatphonearea_seed_prefix
    ON public.cdatphonearea (phoneprefix)
    WHERE provider_key IS NULL;

CREATE INDEX IF NOT EXISTS idx_cdatphonearea_phoneprefix
    ON public.cdatphonearea (phoneprefix);

CREATE INDEX IF NOT EXISTS idx_cdatphonearea_phoneprefix_len
    ON public.cdatphonearea (length(phoneprefix::text) DESC);

CREATE INDEX IF NOT EXISTS idx_cdatphonearea_state_key
    ON public.cdatphonearea (state_key);

CREATE INDEX IF NOT EXISTS idx_cdatphonearea_provider_key
    ON public.cdatphonearea (provider_key);

-- Soft FK checks as constraints (state_key 0 is ISD placeholder in master)
ALTER TABLE public.cdatphonearea DROP CONSTRAINT IF EXISTS fk_cdatphonearea_state;
ALTER TABLE public.cdatphonearea
    ADD CONSTRAINT fk_cdatphonearea_state
    FOREIGN KEY (state_key) REFERENCES public.cdat_state_master (state_key);

ALTER TABLE public.cdatphonearea DROP CONSTRAINT IF EXISTS fk_cdatphonearea_provider;
ALTER TABLE public.cdatphonearea
    ADD CONSTRAINT fk_cdatphonearea_provider
    FOREIGN KEY (provider_key) REFERENCES public.cdat_provider_master (provider_key);

COMMIT;

-- Verification
SELECT 'cdat_state_master' AS table_name, COUNT(*) AS rows FROM public.cdat_state_master
UNION ALL
SELECT 'cdat_provider_master', COUNT(*) FROM public.cdat_provider_master
UNION ALL
SELECT 'cdatphonearea', COUNT(*) FROM public.cdatphonearea
UNION ALL
SELECT 'cdatphonearea_with_provider', COUNT(*) FROM public.cdatphonearea WHERE provider_key IS NOT NULL
UNION ALL
SELECT 'cdatphonearea_seed_only', COUNT(*) FROM public.cdatphonearea WHERE provider_key IS NULL;

SELECT COUNT(*) AS orphan_state
FROM public.cdatphonearea p
LEFT JOIN public.cdat_state_master s ON s.state_key = p.state_key
WHERE p.state_key IS NOT NULL AND s.state_key IS NULL;

SELECT COUNT(*) AS orphan_provider
FROM public.cdatphonearea p
LEFT JOIN public.cdat_provider_master m ON m.provider_key = p.provider_key
WHERE p.provider_key IS NOT NULL AND m.provider_key IS NULL;

SELECT phoneprefix, COUNT(*) AS c
FROM public.cdatphonearea
WHERE provider_key IS NOT NULL
GROUP BY phoneprefix, provider_key
HAVING COUNT(*) > 1
LIMIT 5;
