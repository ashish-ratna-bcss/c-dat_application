-- Geo helpers and filtered tower view for NEAREST_CELLIDS / NEAR_BY_CELLTOWERIDS pages.

CREATE OR REPLACE FUNCTION calculatedistance(
    lon1 double precision,
    lat1 double precision,
    lon2 double precision,
    lat2 double precision
) RETURNS double precision
LANGUAGE sql IMMUTABLE AS $$
    SELECT 6371 * acos(
        LEAST(1.0, GREATEST(-1.0,
            cos(radians(lat1)) * cos(radians(lat2)) * cos(radians(lon2) - radians(lon1))
            + sin(radians(lat1)) * sin(radians(lat2))
        ))
    );
$$;

CREATE OR REPLACE FUNCTION getbearing(
    lat1 double precision,
    lon1 double precision,
    lat2 double precision,
    lon2 double precision
) RETURNS double precision
LANGUAGE sql IMMUTABLE AS $$
    SELECT degrees(atan2(
        sin(radians(lon2 - lon1)) * cos(radians(lat2)),
        cos(radians(lat1)) * sin(radians(lat2))
          - sin(radians(lat1)) * cos(radians(lat2)) * cos(radians(lon2 - lon1))
    ));
$$;

DROP VIEW IF EXISTS public.celltowerfiltered CASCADE;
CREATE VIEW public.celltowerfiltered AS
SELECT *
FROM cdatcelltowerareanew
WHERE lat IS NOT NULL
  AND long IS NOT NULL
  AND lat::text ~ '^-?[0-9]+(\.[0-9]+)?$'
  AND long::text ~ '^-?[0-9]+(\.[0-9]+)?$';
