-- cdatphonearea reference table setup.
--
-- cdatphonearea maps a leading phone prefix to a human-readable area/network description.
-- It is consulted by:
--   * legacy report pages (e.g. address.php) via  phone LIKE PHONEPREFIX || '%'
--   * CDR staging enrichment (upload_verification_service.php::enrichCdrStaging) to fill the
--     Cellular/network ID when celltowerid is empty.
--
-- This table has no upstream/distributed source; it is operator-maintained reference data.
-- This migration only guarantees the schema + a supporting index. Populate it from a CSV
-- (two columns: phoneprefix,areadescription) using the \copy command at the bottom.

CREATE TABLE IF NOT EXISTS cdatphonearea (
    phoneprefix     VARCHAR(20),
    areadescription TEXT
);

-- Longer prefixes win (most specific match); btree on the prefix supports ordering and
-- speeds equality/range probes used during prefix resolution.
CREATE INDEX IF NOT EXISTS idx_cdatphonearea_phoneprefix
    ON cdatphonearea (phoneprefix);

CREATE INDEX IF NOT EXISTS idx_cdatphonearea_phoneprefix_len
    ON cdatphonearea (LENGTH(phoneprefix) DESC);

-- To load operator-provided prefix data (run as the postgres user):
--   \copy cdatphonearea (phoneprefix, areadescription) FROM '/path/to/phonearea.csv' WITH (FORMAT csv, HEADER true)

-- Seed common India mobile prefixes (idempotent).
INSERT INTO cdatphonearea (phoneprefix, areadescription)
SELECT phoneprefix, areadescription
FROM (VALUES
    ('70', 'Reliance Jio (70x)'),
    ('80', 'BSNL / MTNL (80x)'),
    ('81', 'BSNL (81x)'),
    ('82', 'BSNL (82x)'),
    ('83', 'Reliance Jio (83x)'),
    ('84', 'Vodafone Idea (84x)'),
    ('85', 'Reliance Jio (85x)'),
    ('86', 'Reliance Jio (86x)'),
    ('87', 'Reliance Jio (87x)'),
    ('88', 'Reliance Jio (88x)'),
    ('89', 'Reliance Jio (89x)'),
    ('90', 'Airtel (90x)'),
    ('91', 'Airtel (91x)'),
    ('92', 'Airtel (92x)'),
    ('93', 'Airtel (93x)'),
    ('94', 'Airtel (94x)'),
    ('95', 'Airtel (95x)'),
    ('96', 'Reliance Jio (96x)'),
    ('97', 'Airtel (97x)'),
    ('98', 'Airtel (98x)'),
    ('99', 'Reliance Jio / Vi (99x)'),
    ('600', 'Reliance Jio'),
    ('601', 'Reliance Jio'),
    ('700', 'Airtel'),
    ('701', 'Airtel'),
    ('702', 'Airtel'),
    ('800', 'BSNL'),
    ('801', 'BSNL'),
    ('900', 'Airtel'),
    ('901', 'Airtel'),
    ('987', 'Airtel Delhi NCR'),
    ('988', 'Airtel'),
    ('989', 'Airtel'),
    ('990', 'Airtel'),
    ('991', 'Airtel'),
    ('992', 'Airtel'),
    ('993', 'Airtel'),
    ('994', 'Airtel'),
    ('995', 'Airtel'),
    ('996', 'Airtel'),
    ('997', 'Airtel'),
    ('998', 'Airtel'),
    ('999', 'Airtel')
) AS seed(phoneprefix, areadescription)
WHERE NOT EXISTS (SELECT 1 FROM cdatphonearea LIMIT 1);
