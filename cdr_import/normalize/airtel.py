"""
Airtel CDR normalization — Python/Postgres port of MSSQL
  import.proc_airtel_new_format

MSSQL-specific constructs intentionally replaced:
  - #temp / #temp_cdat          → in-memory CdrRecord mutation
  - REPLACE / SUBSTRING / LEFT  → Python string ops
  - ISDATE / DMY                → parser already validates starttime
  - dbo.udf_GetNumeric          → digit extraction
  - CDATDUPL.dbo.* joins        → local Postgres tables/views
  - CDATCELLTOWERAREANEW_MAX    → cdatcelltowerareanew + ORDER BY lastupdate
  - Getdate()                   → asondate set at insert time (unchanged)

Tower lookups are best-effort with a short statement_timeout so a slow FDW
cannot block or fail the whole upload. Field rules always run.
"""
from __future__ import annotations

import logging
import re
from typing import Optional

from ..models import CdrRecord
from ..enrichment import safe_tower_key

logger = logging.getLogger(__name__)

AIRTEL_OPERATOR_LABEL = 'AIRTEL_TOWER'
AIRTEL_PROVIDER_KEY = 2

# Legacy: Incoming=0 when Call_Type is in this outgoing set; else 1.
OUTGOING_CALL_TYPES = frozenset({
    'DAT', 'BSM', 'OG', 'OUT', 'SMO', 'ROC', 'SMS', 'BOC', 'BCM',
    'SMS_MOC', 'OUTROAMERCALL', 'VMS', 'VOC', 'ROAMINGCALLFORWARD',
    'FOW', 'SMS_OUT', 'DEL-SMS', 'BOV',
})

# Ordered like the MSSQL CASE — first match wins.
ROAMING_TO_STATE: tuple[tuple[str, str], ...] = (
    ('AP', 'ANDHRA PRADESH'),
    ('ASM', 'ASSAM'),
    ('AIRTELBIHAR', 'BIHAR'),
    ('CHN', 'CHENNAI'),
    ('DEL', 'DELHI'),
    ('GUJ', 'GUJARAT'),
    ('AIRTELHARYANA', 'HARYANA'),
    ('HP', 'HIMACHAL PRADESH'),
    ('JK', 'JAMMU_KASHMIR'),
    ('KER', 'KERALA'),
    ('KK', 'KARNATAKA'),
    ('KARNATAKA', 'KARNATAKA'),
    ('KOL', 'KOLKATA'),
    ('MAH', 'MAHARASHTRA'),
    ('MH', 'MAHARASHTRA'),
    ('MP', 'MADHYA PRADESH'),
    ('MUM', 'MUMBAI'),
    ('NESA', 'NORTH_EAST'),
    ('ORI', 'ORISSA'),
    ('PUN', 'PUNJAB'),
    ('RAJ', 'RAJASTHAN'),
    ('TN', 'TAMILNADU'),
    ('UPE', 'UP_EAST'),
    ('UPW', 'UP_WEST'),
    ('WB', 'WEST BENGAL'),
)

_SPECIAL_PHONE_PREFIXES = ('3006', '3005', '3007', '3008', '2728')


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


def _normalize_msisdn(value: Optional[str]) -> Optional[str]:
    """Port of Phone/Other prefix stripping from proc_airtel_new_format."""
    if value is None:
        return None
    text = _strip_quotes(value) or ''
    text = text.strip()
    if text == '':
        return ''

    # Keep non-digit-ish party labels (e.g. short codes already handled elsewhere).
    # Any letter means this is an alphanumeric sender ID ('AB-650002-P', 'VK-SBIBNK-P'),
    # not an MSISDN. Digit-extracting those leaves a bare run ('650002') that reads like
    # a subscriber number, so return the label untouched -- none of the prefix-stripping
    # below applies to it.
    raw = text
    if re.search(r'[A-Za-z]', text):
        return raw
    digits = _digits_only(text)
    working = digits if digits else raw

    if working.startswith('95') and len(working) > 10:
        working = working[2:]
    if working.startswith('00') and len(working) > 2:
        working = working[2:]
    if working.startswith('91') and len(working) > 10:
        working = working[2:]
    if working.startswith('0') and len(working) > 1:
        working = working[1:]
    if working.startswith(_SPECIAL_PHONE_PREFIXES) and len(working) > 4:
        working = working[4:]
    return working or ''


def _normalize_cell_id(value: Optional[str]) -> Optional[str]:
    """CGI / celltowerid cleanup from the Airtel proc."""
    if value is None:
        return None
    text = _strip_quotes(value) or ''
    text = text.strip()
    if text in {'', '-', '--'}:
        return '0'

    text = text.replace("'", '')
    text = text.replace('--', '')
    # MSSQL: substring(celltowerid,1,4) LIKE '40_-%'  (_ = any single char)
    # then substring(celltowerid,8,20)
    if len(text) >= 4 and text[0] == '4' and text[1] == '0' and text[3] == '-':
        text = text[7:] if len(text) >= 7 else text

    text = text.replace('--', '-')
    text = text.replace('_', '-')
    text = text.replace('NULL-NULL', '0')
    if text.startswith('-'):
        text = text[1:]
    text = text.strip()
    return text or '0'


def _map_roaming_to_state(roaming_nw: Optional[str]) -> Optional[str]:
    if not roaming_nw:
        return None
    blob = roaming_nw.upper()
    for token, state in ROAMING_TO_STATE:
        if token in blob:
            return state
    return None


def _resolve_roaming_nw(rec: CdrRecord) -> Optional[str]:
    """case when RoamNw like '%AIR%' then RoamNw else LRNTSPLSA end"""
    raw = rec.raw or {}
    roam = _strip_quotes(rec.roaming_nw) or _strip_quotes(raw.get('Roam Nw') or raw.get('RoamNw'))
    lrn = _strip_quotes(
        raw.get('LRNTSPLSA')
        or raw.get('LRN TS PLSA')
        or raw.get('LRN/TSP/LSA')
        or raw.get('LRNTS PLSA')
    )
    if roam and 'AIR' in roam.upper():
        chosen = roam
    else:
        chosen = lrn or roam
    if not chosen:
        return None
    chosen = chosen.replace(' ', '').replace('-', '')
    return chosen or None


def _set_incoming_from_call_type(rec: CdrRecord) -> None:
    ct = (rec.call_type or '').strip().upper()
    rec.incoming = 0 if ct in OUTGOING_CALL_TYPES else 1


def normalize_airtel_fields(rec: CdrRecord) -> Optional[CdrRecord]:
    """Field-only Airtel rules (no DB). Returns None if row should be dropped."""
    rec.provider_key = AIRTEL_PROVIDER_KEY

    # Quote stripping on string fields (parser already cleans most; re-apply safely).
    rec.phone = _strip_quotes(rec.phone)
    rec.other = _strip_quotes(rec.other)
    rec.call_type = _strip_quotes(rec.call_type)
    rec.calling_no = _strip_quotes(rec.calling_no) or rec.phone
    rec.called_no = _strip_quotes(rec.called_no) or rec.other
    rec.first_cellid = _strip_quotes(rec.first_cellid)
    rec.last_cellid = _strip_quotes(rec.last_cellid)
    rec.celltowerid = _strip_quotes(rec.celltowerid)
    rec.otherinfo = _strip_quotes(rec.otherinfo)

    _set_incoming_from_call_type(rec)

    # Drop: phone is null and Call_Type='dsm'
    ct = (rec.call_type or '').strip().upper()
    if (rec.phone is None or str(rec.phone).strip() == '') and ct == 'DSM':
        return None

    rec.roaming_nw = _resolve_roaming_nw(rec)

    rec.phone = _normalize_msisdn(rec.phone)
    rec.other = _normalize_msisdn(rec.other)
    if rec.calling_no:
        rec.calling_no = _normalize_msisdn(rec.calling_no) or rec.calling_no
    if rec.called_no:
        rec.called_no = _normalize_msisdn(rec.called_no) or rec.called_no

    # DSM short-phone swap: SET PHONE=OTHER, OTHER=PHONE WHERE LEN(PHONE)<10 AND CALL_TYPE='DSM'
    if ct == 'DSM' and rec.phone is not None and len(str(rec.phone)) < 10:
        rec.phone, rec.other = rec.other, rec.phone

    rec.celltowerid = _normalize_cell_id(rec.celltowerid or rec.first_cellid)
    rec.first_cellid = _normalize_cell_id(rec.first_cellid) if rec.first_cellid else rec.celltowerid
    rec.last_cellid = _normalize_cell_id(rec.last_cellid) if rec.last_cellid else None

    # IMEI hygiene
    imei_s = str(rec.imeinumber) if rec.imeinumber is not None else ''
    if imei_s in {'', '-', 'None'} or not _is_numeric(imei_s):
        rec.imeinumber = 0

    if rec.duration is None:
        rec.duration = 0

    # otherinfo from roaming circle codes (legacy CASE)
    mapped = _map_roaming_to_state(rec.roaming_nw)
    if mapped:
        rec.otherinfo = mapped

    # other = '' where null (legacy)
    if rec.other is None:
        rec.other = ''

    return rec


class AirtelDbEnricher:
    """Postgres lookups that replace MSSQL joins in the Airtel proc."""

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
            logger.warning('Airtel normalizer: could not load cdat_state_master: %s', exc)
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
                cur.execute('SAVEPOINT airtel_area_lookup')
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
                    cur.execute('RELEASE SAVEPOINT airtel_area_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT airtel_area_lookup')
                    logger.warning('Airtel normalizer: phonearea lookup failed: %s', inner_exc)
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
            logger.warning('Airtel normalizer: phonearea lookup unavailable: %s', exc)
            result = (None, None)
        self._area_by_prefix[prefix] = result
        return result

    def lookup_tower_key(self, celltowerid: Optional[str], state_name: Optional[str]) -> Optional[int]:
        if not celltowerid or celltowerid in {'0', '-'}:
            return None
        state = (state_name or '').strip()
        cache_key = (celltowerid, state.upper())
        if cache_key in self._tower_cache:
            return self._tower_cache[cache_key]

        candidates = [celltowerid]
        stripped = celltowerid.replace('-', '')
        if stripped and stripped != celltowerid:
            candidates.append(stripped)

        value: Optional[int] = None
        try:
            with self.conn.cursor() as cur:
                # Savepoint so FDW timeout cannot abort the outer import transaction.
                cur.execute('SAVEPOINT airtel_tower_lookup')
                try:
                    cur.execute("SET LOCAL statement_timeout = '1500ms'")
                    for cand in candidates:
                        # Prefer match on cell + AIRTEL_TOWER + state (legacy MAX view).
                        if state:
                            cur.execute(
                                """
                                SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                                FROM cdatcelltowerareanew
                                WHERE celltowerid = %s
                                  AND operator = %s
                                  AND state = %s
                                ORDER BY lastupdate DESC NULLS LAST
                                LIMIT 1
                                """,
                                (cand, AIRTEL_OPERATOR_LABEL, state),
                            )
                            row = cur.fetchone()
                            if row and row[0]:
                                value = safe_tower_key(row[0])
                                break
                        # Fallback: cell + operator only
                        cur.execute(
                            """
                            SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                            FROM cdatcelltowerareanew
                            WHERE celltowerid = %s
                              AND operator = %s
                            ORDER BY lastupdate DESC NULLS LAST
                            LIMIT 1
                            """,
                            (cand, AIRTEL_OPERATOR_LABEL),
                        )
                        row = cur.fetchone()
                        if row and row[0]:
                            value = safe_tower_key(row[0])
                            break
                    cur.execute('RELEASE SAVEPOINT airtel_tower_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT airtel_tower_lookup')
                    logger.warning('Airtel normalizer: tower lookup skipped: %s', inner_exc)
                    value = None
        except Exception as exc:
            logger.warning('Airtel normalizer: tower lookup unavailable: %s', exc)
            value = None

        self._tower_cache[cache_key] = value
        return value

    def enrich(self, rec: CdrRecord) -> CdrRecord:
        state_name, state_key = self.lookup_area_state(rec.phone)
        if state_name and not rec.otherinfo:
            rec.otherinfo = state_name
        if state_key is not None and rec.state_key is None:
            rec.state_key = state_key

        # Legacy: otherinfo = COALESCE(otherinfo, State_Name)
        if not rec.otherinfo and state_name:
            rec.otherinfo = state_name

        # Legacy defaults (kept, but only after enrichment attempts).
        if not rec.otherinfo:
            rec.otherinfo = 'ANDHRA PRADESH'
        if rec.state_key is None:
            mapped_key = self._state_by_name.get(str(rec.otherinfo).strip().upper())
            rec.state_key = mapped_key if mapped_key is not None else 1

        if rec.tower_key is None:
            rec.tower_key = self.lookup_tower_key(rec.celltowerid, rec.otherinfo)
        return rec


def normalize_airtel_record(rec: CdrRecord, *, conn=None, enricher: Optional[AirtelDbEnricher] = None) -> Optional[CdrRecord]:
    """Full Airtel normalization. Drop returns None (legacy DELETE)."""
    out = normalize_airtel_fields(rec)
    if out is None:
        return None

    if conn is not None:
        db = enricher or AirtelDbEnricher(conn)
        return db.enrich(out)
    # Without DB: still apply safe defaults matching proc when enrichment skipped
    if not out.otherinfo:
        mapped = _map_roaming_to_state(out.roaming_nw)
        out.otherinfo = mapped or 'ANDHRA PRADESH'
    if out.state_key is None:
        out.state_key = 1
    return out
