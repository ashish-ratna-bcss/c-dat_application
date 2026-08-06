"""
BSNL CDR normalization — Python/Postgres port of MSSQL
  dbo.BSNL_STORED

MSSQL-specific constructs intentionally replaced:
  - #temp / #temp1..4           → in-memory CdrRecord mutation
  - REPLACE / SUBSTRING / LEFT  → Python string ops
  - ISNUMERIC / DMY             → digit checks; parser validates starttime
  - CDATDUPL.dbo.* joins        → local Postgres tables/views
  - RANK() over tower_key       → ORDER BY bts_id DESC LIMIT 1
  - Getdate()                   → asondate set at insert time (unchanged)

Tower lookups are best-effort with a short statement_timeout so a slow FDW
cannot block or fail the whole upload. Field rules always run.
"""
from __future__ import annotations

import logging
import re
from typing import Optional

from ..models import CdrRecord

logger = logging.getLogger(__name__)

BSNL_PROVIDER_KEY = 4

# Legacy TYPE = call_type + '_' + service_type; these map to incoming=1.
INCOMING_TYPES = frozenset({
    'IN_SMS',
    'IN_VOICE',
    'CALLFORWARD',
    'VIDEO_INCOMING',
})

# Roaming_nw → state_key overrides (applied after phonearea/state_master).
ROAMING_STATE_KEY_OVERRIDES: tuple[tuple[str, int], ...] = (
    ('karna', 12),
    ('tamilnad', 24),
    ('chennai', 36),
)


# Exact roaming_nw matches (case-insensitive).
ROAMING_STATE_KEY_EXACT: dict[str, int] = {
    'BSNL_AP': 1,
    'INDGJ': 7,
    'INDOR': 20,
    'INDRJ': 22,
    'INDMH': 15,
    'BSNL_KERALA': 13,
}


def _strip_quotes(value: Optional[str]) -> Optional[str]:
    if value is None:
        return None
    text = str(value)
    while len(text) >= 2 and (
        (text.startswith("'") and text.endswith("'"))
        or (text.startswith('"') and text.endswith('"'))
    ):
        text = text[1:-1]
    return text.replace("''", "'")


def _digits_only(value: Optional[str]) -> str:
    return re.sub(r'\D', '', str(value or ''))


def _is_numeric(value: Optional[str]) -> bool:
    text = str(value or '').strip()
    if not text or text in {'-', '--'}:
        return False
    return bool(re.fullmatch(r'[+-]?\d+(?:\.\d+)?', text))


def _normalize_bsnl_msisdn(value: Optional[str], *, is_phone: bool = False) -> Optional[str]:
    """Port of tel/other prefix stripping from BSNL_STORED.

    Legacy differences phone vs other:
      - both: strip leading 00/95/91 when LEN>10 via SUBSTRING(...,3,20)
      - other only: when LEFT=0, SUBSTRING(...,3,20) applied twice (drops 2 chars
        each pass — preserved for SQL parity; not a single-digit strip)
      - phone/tel: no bare leading-0 strip
    """
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip()
    if text == '':
        return ''

    digits = _digits_only(text)
    working = digits if digits else text

    if working[:2] in ('00', '95', '91') and len(working) > 10:
        working = working[2:22]  # SUBSTRING(...,3,20)
    if not is_phone:
        # Legacy: update other=SUBSTRING(other,3,20) where LEFT(other,1)='0' (x2)
        if working[:1] == '0':
            working = working[2:22]
        if working[:1] == '0':
            working = working[2:22]
    return working or ''


def _format_bsnl_cell_id(value: Optional[str]) -> Optional[str]:
    """Format CGI into lac-ci style used by CDATCELLTOWERAREANEW joins."""
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip().replace(',', '')
    if text in {'', '-', '--'}:
        return '0'
    if len(text) < 6:
        return text
    # MSSQL 1-based: len=14 → substr(6,4)+'-'+substr(10,…); else substr(6,3)+'-'+substr(9,…)
    if len(text) == 14:
        return f'{text[5:9]}-{text[9:]}'
    return f'{text[5:8]}-{text[8:]}'


def _service_type(rec: CdrRecord) -> str:
    raw = rec.raw or {}
    for key in (
        'Service_Type',
        'Service Type',
        'service_type',
        'SERVICE_TYPE',
    ):
        val = raw.get(key)
        if val is not None and str(val).strip() != '':
            return (_strip_quotes(str(val)) or '').strip()
    return ''


def _combined_type(rec: CdrRecord) -> str:
    ct = (_strip_quotes(rec.call_type) or '').strip()
    st = _service_type(rec)
    if ct and st:
        return f'{ct}_{st}'
    return ct or st


def _set_incoming_from_type(rec: CdrRecord) -> None:
    """Legacy: CASE WHEN TYPE IN ('IN_SMS','IN_VOICE','Callforward','Video_Incoming').

    No extras — bare 'IN' or other IN_* values are outgoing (0) when not in the set.
    """
    combined = _combined_type(rec).upper()
    ct = (_strip_quotes(rec.call_type) or '').strip().upper()
    if combined in INCOMING_TYPES or ct in INCOMING_TYPES:
        rec.incoming = 1
    else:
        rec.incoming = 0


def _apply_roaming_state_overrides(rec: CdrRecord) -> None:
    roam = (rec.roaming_nw or '').strip()
    if not roam:
        return
    exact = ROAMING_STATE_KEY_EXACT.get(roam.upper())
    if exact is not None:
        rec.state_key = exact
        return
    lower = roam.lower()
    for token, key in ROAMING_STATE_KEY_OVERRIDES:
        if token in lower:
            rec.state_key = key
            return


def normalize_bsnl_fields(rec: CdrRecord) -> Optional[CdrRecord]:
    """Field-only BSNL rules (no DB). Returns None if row should be dropped."""
    rec.provider_key = BSNL_PROVIDER_KEY

    rec.phone = _strip_quotes(rec.phone)
    rec.other = _strip_quotes(rec.other)
    rec.call_type = _strip_quotes(rec.call_type)
    rec.roaming_nw = _strip_quotes(rec.roaming_nw)
    rec.first_cellid = _strip_quotes(rec.first_cellid)
    rec.last_cellid = _strip_quotes(rec.last_cellid)
    rec.celltowerid = _strip_quotes(rec.celltowerid)

    # Legacy keeps original mobile_no / other_party_no as clg / cld (pre-strip).
    if not rec.calling_no:
        rec.calling_no = rec.phone
    if not rec.called_no:
        rec.called_no = rec.other

    raw_first = rec.first_cellid or rec.celltowerid
    raw_last = rec.last_cellid

    # Duration: strip commas; null → 0
    if rec.duration is None:
        rec.duration = 0

    rec.phone = _normalize_bsnl_msisdn(rec.phone, is_phone=True)
    rec.other = _normalize_bsnl_msisdn(rec.other, is_phone=False)

    # Drop non-numeric / null phones and null other (legacy DELETE).
    if rec.phone is None or str(rec.phone).strip() == '' or not _is_numeric(str(rec.phone)):
        return None
    if rec.other is None or str(rec.other).strip() == '':
        return None
    if str(rec.phone) == str(rec.other):
        return None

    _set_incoming_from_type(rec)

    # Formatted lac-ci for tower join; first_cellid stays raw (legacy).
    rec.celltowerid = _format_bsnl_cell_id(raw_first) or '0'
    rec.first_cellid = raw_first
    rec.last_cellid = _format_bsnl_cell_id(raw_last) if raw_last else None

    # IMEI / IMSI hygiene
    imei_s = str(rec.imeinumber) if rec.imeinumber is not None else ''
    if imei_s in {'', '-', 'None'} or not _is_numeric(imei_s):
        rec.imeinumber = 0
    else:
        digits = _digits_only(imei_s)
        rec.imeinumber = int(digits[:15]) if digits else 0

    if rec.imsinumber is not None:
        imsi_s = str(rec.imsinumber)
        if not _is_numeric(imsi_s):
            rec.imsinumber = 0

    if rec.roaming_nw and len(rec.roaming_nw) > 15:
        rec.roaming_nw = rec.roaming_nw[:15]

    # Clear service-type parking; DB enricher sets state otherinfo.
    rec.otherinfo = None
    return rec


class BsnlDbEnricher:
    """Postgres lookups that replace MSSQL joins in BSNL_STORED."""

    def __init__(self, conn):
        self.conn = conn
        self._state_by_name: dict[str, int] = {}
        self._area_by_prefix: dict[str, tuple[Optional[str], Optional[int]]] = {}
        self._tower_cache: dict[tuple[str, str], Optional[int]] = {}
        self._load_state_master()

    def _load_state_master(self) -> None:
        try:
            with self.conn.cursor() as cur:
                cur.execute(
                    'SELECT state_key, state FROM cdat_state_master WHERE state IS NOT NULL'
                )
                for state_key, state in cur.fetchall():
                    if state:
                        self._state_by_name[str(state).strip().upper()] = int(state_key)
        except Exception as exc:
            logger.warning('BSNL normalizer: could not load cdat_state_master: %s', exc)
            try:
                self.conn.rollback()
            except Exception:
                pass

    def lookup_area_state(self, phone: Optional[str]) -> tuple[Optional[str], Optional[int]]:
        digits = _digits_only(phone)
        if len(digits) < 5:
            return (None, None)
        prefix = digits[:5]
        if prefix in self._area_by_prefix:
            return self._area_by_prefix[prefix]
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT bsnl_area_lookup')
                try:
                    cur.execute(
                        """
                        SELECT state, state_key
                        FROM cdatphonearea
                        WHERE phoneprefix = %s
                        ORDER BY CASE WHEN state_key IS NOT NULL THEN 0 ELSE 1 END
                        LIMIT 1
                        """,
                        (prefix,),
                    )
                    row = cur.fetchone()
                    cur.execute('RELEASE SAVEPOINT bsnl_area_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT bsnl_area_lookup')
                    logger.warning('BSNL normalizer: phonearea lookup failed: %s', inner_exc)
                    row = None
            if row:
                state_name = row[0]
                state_key = int(row[1]) if row[1] is not None else None
                if state_key is None and state_name:
                    state_key = self._state_by_name.get(str(state_name).strip().upper())
                result = (state_name, state_key)
            else:
                result = (None, None)
        except Exception as exc:
            logger.warning('BSNL normalizer: phonearea lookup unavailable: %s', exc)
            result = (None, None)
        self._area_by_prefix[prefix] = result
        return result

    def lookup_roaming_area_state(self, roaming_nw: Optional[str]) -> Optional[str]:
        """When LEN(roaming_nw)=12, join phonearea on substring(roaming_nw,3,5)."""
        if not roaming_nw or len(roaming_nw) != 12:
            return None
        prefix = roaming_nw[2:7]  # MSSQL substring(roaming_nw,3,5)
        if not prefix:
            return None
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT bsnl_roam_area')
                try:
                    cur.execute(
                        """
                        SELECT state
                        FROM cdatphonearea
                        WHERE phoneprefix = %s
                        LIMIT 1
                        """,
                        (prefix,),
                    )
                    row = cur.fetchone()
                    cur.execute('RELEASE SAVEPOINT bsnl_roam_area')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT bsnl_roam_area')
                    logger.warning('BSNL normalizer: roaming phonearea failed: %s', inner_exc)
                    row = None
            return row[0] if row else None
        except Exception as exc:
            logger.warning('BSNL normalizer: roaming phonearea unavailable: %s', exc)
            return None

    def lookup_tower_key(self, celltowerid: Optional[str], state_name: Optional[str]) -> Optional[int]:
        if not celltowerid or celltowerid in {'0', '-'}:
            return None
        state = (state_name or '').strip()
        cache_key = (celltowerid, state.upper())
        if cache_key in self._tower_cache:
            return self._tower_cache[cache_key]

        value: Optional[int] = None
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT bsnl_tower_lookup')
                try:
                    cur.execute("SET LOCAL statement_timeout = '1500ms'")
                    if state:
                        # Legacy: join on CELLTOWERID + PROVIDER_KEY + STATE; prefer highest tower_key.
                        cur.execute(
                            """
                            SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                            FROM cdatcelltowerareanew
                            WHERE celltowerid = %s
                              AND provider_key = %s
                              AND state = %s
                            ORDER BY
                              NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')::numeric
                                DESC NULLS LAST,
                              lastupdate DESC NULLS LAST
                            LIMIT 1
                            """,
                            (celltowerid, BSNL_PROVIDER_KEY, state),
                        )
                        row = cur.fetchone()
                        if row and row[0]:
                            value = int(row[0])
                    if value is None:
                        cur.execute(
                            """
                            SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                            FROM cdatcelltowerareanew
                            WHERE celltowerid = %s
                              AND provider_key = %s
                            ORDER BY
                              NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')::numeric
                                DESC NULLS LAST,
                              lastupdate DESC NULLS LAST
                            LIMIT 1
                            """,
                            (celltowerid, BSNL_PROVIDER_KEY),
                        )
                        row = cur.fetchone()
                        if row and row[0]:
                            value = int(row[0])
                    # Undo SET LOCAL statement_timeout so later import/dedup is not capped at 1.5s.
                    cur.execute('ROLLBACK TO SAVEPOINT bsnl_tower_lookup')
                    cur.execute('RELEASE SAVEPOINT bsnl_tower_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT bsnl_tower_lookup')
                    logger.warning('BSNL normalizer: tower lookup skipped: %s', inner_exc)
                    value = None
        except Exception as exc:
            logger.warning('BSNL normalizer: tower lookup unavailable: %s', exc)
            value = None

        self._tower_cache[cache_key] = value
        return value

    def enrich(self, rec: CdrRecord) -> CdrRecord:
        state1, state_key = self.lookup_area_state(rec.phone)
        state2 = self.lookup_roaming_area_state(rec.roaming_nw)

        # Legacy: case when state1=state2 then state1 when state2 is null then state else state2
        if state2 is None:
            rec.otherinfo = state1
        else:
            rec.otherinfo = state2

        # state_key from phone-area state (state_master on A.state), then roaming overrides.
        if state_key is not None:
            rec.state_key = state_key
        elif state1:
            rec.state_key = self._state_by_name.get(str(state1).strip().upper())

        if rec.tower_key is None:
            rec.tower_key = self.lookup_tower_key(rec.celltowerid, rec.otherinfo)

        _apply_roaming_state_overrides(rec)

        if rec.celltowerid is None:
            rec.celltowerid = '0'
        return rec


def normalize_bsnl_record(
    rec: CdrRecord,
    *,
    conn=None,
    enricher: Optional[BsnlDbEnricher] = None,
) -> Optional[CdrRecord]:
    """Full BSNL normalization. Drop returns None (legacy DELETE)."""
    out = normalize_bsnl_fields(rec)
    if out is None:
        return None

    if conn is not None:
        db = enricher or BsnlDbEnricher(conn)
        return db.enrich(out)

    _apply_roaming_state_overrides(out)
    if out.celltowerid is None:
        out.celltowerid = '0'
    return out
