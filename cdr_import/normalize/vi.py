"""
Vi / Vodafone CDR normalization — Python/Postgres port of MSSQL
  import.proc_vodafone_new_FORMAT

MSSQL-specific constructs intentionally replaced:
  - #TEMP / #TEMP_CDAT          → in-memory CdrRecord mutation
  - REPLACE / SUBSTRING / LEFT  → Python string ops
  - ISNUMERIC / ISDATE / DMY    → digit checks; parser validates starttime
  - dbo.udf_GetNumeric          → digit extraction
  - CDATDUPL.dbo.* joins        → local Postgres tables/views
  - MNC_CODES                   → optional local table (soft-fail if missing)
  - CDATCELLTOWERAREANEW_MAX    → cdatcelltowerareanew + ORDER BY lastupdate
  - Getdate()                   → asondate set at insert time (unchanged)

Legacy MSSQL hardcodes Provider_Key='2' (Airtel). Local provider master maps
VODAFONE_TOWER → 12; we use 12 so Vi rows are not tagged as Airtel.

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

VI_PROVIDER_KEY = 12
VI_OPERATOR_LABEL = 'VODAFONE_TOWER'

# Ordered like the MSSQL CASE — first match wins.
ROAMING_TO_STATE: tuple[tuple[str, str], ...] = (
    ('ANDHRA', 'ANDHRA PRADESH'),
    ('AP-VODAFONE-INDIA', 'ANDHRA PRADESH'),
    ('WB', 'WEST BENGAL'),
    ('KOL', 'KOLKATA'),
    ('BIH', 'BIHAR'),
    ('ORI', 'ORISSA'),
    ('MP', 'MADHYA PRADESH'),
    ('MUM', 'MUMBAI'),
    ('DEL', 'DELHI'),
    ('RAJ', 'RAJASTHAN'),
    ('UPE', 'UP_EAST'),
    ('UPW', 'UP_WEST'),
    ('CHN', 'CHENNAI'),
    ('TN', 'TAMILNADU'),
    ('KER', 'KERALA'),
    ('KAR', 'KARNATAKA'),
    ('GUJ', 'GUJARAT'),
    ('MAG', 'MAHARASHTRA'),
    ('HAR', 'HARYANA'),
    ('PUNJ', 'PUNJAB'),
    ('JK', 'JAMMU_KASHMIR'),
    ('BLDESH', 'BANGLADESH'),
    ('THAILAND', 'THAILAND'),
    ('SINGAPORE', 'SINGAPORE'),
    ('NEPAL', 'NEPAL'),
    ('INDON', 'INDONESIA'),
    ('BRAZIL', 'BRAZIL'),
    ('CHINA', 'CHINA'),
    ('MALAYSIA', 'MALAYSIA'),
    ('HKG', 'HONGKONG'),
    ('MALTA', 'MALTA'),
    ('OMAN', 'OMAN'),
    ('KUWAIT', 'KUWAIT'),
    ('SAUDI', 'SAUDIAREBIA'),
    ('UAE', 'UAE'),
    ('USA', 'USA'),
)

# Roaming values that become 'ROAMING_' + original (legacy).
ROAMING_PREFIX_AS_IS = ('HONDURAS', 'MACAU')

_LONG_ROAM_RENAMES = {
    'AP-VODAFONE-ANDHRAPRADESH-INDIA_SISTEMA SHYAM TELESERVICES':
        'AP-Vodafone-AP-INDIA_SISTEMA SHYAM',
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


def _normalize_vi_msisdn(value: Optional[str], *, is_phone: bool = False) -> Optional[str]:
    """Port of Phone/Other prefix stripping from proc_vodafone_new_FORMAT."""
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip()
    if text == '':
        return ''

    if re.search(r'[A-Za-z]', text):
        return text

    working = _digits_only(text) or text

    if is_phone:
        # Phone: 91, 95, 00, leading 0 x2, then 91 again (legacy sequential UPDATEs).
        if working[:2] == '91' and len(working) > 10:
            working = working[2:]
        if working[:2] == '95' and len(working) > 10:
            working = working[2:]
        if working[:2] == '00' and len(working) > 10:
            working = working[2:]
        if working.startswith('0') and len(working) > 1:
            working = working[1:]
        if working.startswith('0') and len(working) > 1:
            working = working[1:]
        if working[:2] == '91' and len(working) > 10:
            working = working[2:]
        # Special long prefixes
        if len(working) > 15 and working[:6] in ('613064', '614109', '153064'):
            working = working[6:]
        if len(working) > 10 and working[:4] in ('3064', '3076'):
            working = working[4:]
    else:
        # Other: 91, 95, leading 0 x2
        if working[:2] == '91' and len(working) > 10:
            working = working[2:]
        if working[:2] == '95' and len(working) > 10:
            working = working[2:]
        if working.startswith('0') and len(working) > 1:
            working = working[1:]
        if working.startswith('0') and len(working) > 1:
            working = working[1:]
    return working or ''


def _normalize_celltowerid(value: Optional[str]) -> str:
    if value is None:
        return '0'
    text = (_strip_quotes(value) or '').strip()
    if text in {'', '---', '----', '-', '--'}:
        return '0'
    # Collapse padded zero segments: -0 / -00 / -000 / -00000 → -
    for pad in ('-00000', '-000', '-00', '-0'):
        text = text.replace(pad, '-')
    if len(text) < 10:
        text = text.replace('-', '')
    if text in {'', '---', '----'}:
        return '0'
    return text or '0'


def _apply_operator_cell_transform(celltowerid: str, mnc_operator: Optional[str]) -> str:
    op = (mnc_operator or '').strip().upper()
    if not celltowerid or celltowerid == '0':
        return celltowerid
    if op == 'IDEA' and len(celltowerid) >= 4:
        # REPLACE(LEFT(cell,4),'-','') + SUBSTRING(cell,5,30)
        return celltowerid[:4].replace('-', '') + celltowerid[4:34]
    if op == 'AIRTEL' and '-' in celltowerid:
        # SUBSTRING(cell,8,30) when contains '-'
        return celltowerid[7:37] if len(celltowerid) >= 8 else celltowerid
    return celltowerid


def _map_roaming_to_state(roaming_nw: Optional[str]) -> Optional[str]:
    if not roaming_nw:
        return None
    blob = roaming_nw.upper()
    for token in ROAMING_PREFIX_AS_IS:
        if token in blob:
            return f'ROAMING_{roaming_nw}'
    for token, state in ROAMING_TO_STATE:
        if token in blob:
            return state
    return roaming_nw


def _combined_call_type(rec: CdrRecord) -> str:
    raw = rec.raw or {}
    ct = (_strip_quotes(rec.call_type) or '').strip()
    # If parser already combined CallType_ServiceType, keep it.
    st = ''
    for key in ('Service Type', 'ServiceType', 'Service_Type', 'SERVICE_TYPE'):
        val = raw.get(key)
        if val is not None and str(val).strip() != '':
            st = (_strip_quotes(str(val)) or '').strip()
            break
    # Avoid double-appending if call_type already ends with service type.
    if st and st.upper() not in ct.upper() and f'_{st}' not in ct and not ct.upper().endswith(st.upper()):
        # If call_type looks like bare CallType (no underscore), combine.
        if '_' not in ct.replace(' ', ''):
            ct = f'{ct}_{st}' if ct else st
    return ct.replace(' ', '')


def normalize_vi_fields(rec: CdrRecord) -> Optional[CdrRecord]:
    """Field-only Vi/Vodafone rules (no DB). Returns None if row should be dropped."""
    rec.provider_key = VI_PROVIDER_KEY

    rec.phone = _strip_quotes(rec.phone)
    rec.other = _strip_quotes(rec.other)
    rec.call_type = _strip_quotes(rec.call_type)
    rec.calling_no = _strip_quotes(rec.calling_no)
    rec.called_no = _strip_quotes(rec.called_no)
    rec.first_cellid = _strip_quotes(rec.first_cellid)
    rec.last_cellid = _strip_quotes(rec.last_cellid)
    rec.celltowerid = _strip_quotes(rec.celltowerid)
    rec.roaming_nw = _strip_quotes(rec.roaming_nw)
    if rec.roaming_nw:
        rec.roaming_nw = rec.roaming_nw.replace(' ', '')
    if rec.first_cellid:
        rec.first_cellid = rec.first_cellid.replace(' ', '')

    rec.call_type = _combined_call_type(rec)
    # Incoming: Call_Type LIKE 'OUTGOING%' → 0 else 1
    rec.incoming = 0 if (rec.call_type or '').upper().startswith('OUTGOING') else 1

    # Preserve calling/called as Target/BParty (legacy).
    if not rec.calling_no:
        rec.calling_no = rec.phone
    if not rec.called_no:
        rec.called_no = rec.other

    rec.phone = _normalize_vi_msisdn(rec.phone, is_phone=True)
    rec.other = _normalize_vi_msisdn(rec.other, is_phone=False)

    # Drop when both non-numeric, or both null.
    phone_s = (rec.phone or '').strip()
    other_s = (rec.other or '').strip()
    if phone_s == '' and other_s == '':
        return None
    phone_ok = phone_s != '' and _is_numeric(phone_s)
    other_ok = other_s != '' and _is_numeric(other_s)
    if not phone_ok and not other_ok:
        return None

    if phone_s and other_s and phone_s == other_s:
        return None

    # SMS: truncate other to 15
    if 'sms' in (rec.call_type or '').lower() and rec.other:
        rec.other = rec.other[:15]

    # Duration hygiene
    if rec.duration is None or str(rec.duration).strip() in {'', '-', ' '}:
        rec.duration = 0
    else:
        dur_digits = _digits_only(str(rec.duration))
        rec.duration = int(dur_digits) if dur_digits else 0

    # IMEI / IMSI
    imei_s = str(rec.imeinumber) if rec.imeinumber is not None else ''
    if (
        imei_s in {'', '-', ' ', 'None'}
        or '-' in imei_s
        or not _is_numeric(imei_s.replace('.', '', 1))
    ):
        rec.imeinumber = 0
    else:
        digits = _digits_only(imei_s)
        rec.imeinumber = int(digits[:15]) if digits else 0

    if rec.imsinumber is None:
        rec.imsinumber = 0
    else:
        imsi_s = str(rec.imsinumber).strip()
        if imsi_s in {'', '-', ' '} or not _is_numeric(imsi_s):
            rec.imsinumber = 0

    raw_first = rec.first_cellid or rec.celltowerid
    rec.celltowerid = _normalize_celltowerid(raw_first)
    # Re-apply -0 pads after other transforms (legacy does this twice).
    if rec.celltowerid and rec.celltowerid != '0':
        for pad in ('-000', '-00', '-0'):
            rec.celltowerid = rec.celltowerid.replace(pad, '-')

    if rec.otherinfo:
        rec.otherinfo = str(rec.otherinfo).replace('ROAMING_', '')

    # Clear service-type parking; enricher sets state otherinfo.
    # Keep temporarily None so roaming CASE can fill.
    rec.otherinfo = None
    return rec


class ViDbEnricher:
    """Postgres lookups that replace MSSQL joins in proc_vodafone_new_FORMAT."""

    def __init__(self, conn):
        self.conn = conn
        self._state_by_name: dict[str, int] = {}
        self._area_by_prefix: dict[str, tuple[Optional[str], Optional[int]]] = {}
        self._tower_cache: dict[str, Optional[int]] = {}
        self._tower_by_bts_cache: dict[str, Optional[int]] = {}
        self._mnc_cache: dict[str, tuple[Optional[str], Optional[str]]] = {}
        self._mnc_available: Optional[bool] = None
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
            logger.warning('Vi normalizer: could not load cdat_state_master: %s', exc)
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
                cur.execute('SAVEPOINT vi_area_lookup')
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
                    cur.execute('RELEASE SAVEPOINT vi_area_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT vi_area_lookup')
                    logger.warning('Vi normalizer: phonearea lookup failed: %s', inner_exc)
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
            logger.warning('Vi normalizer: phonearea lookup unavailable: %s', exc)
            result = (None, None)
        self._area_by_prefix[prefix] = result
        return result

    def lookup_mnc(self, first_cellid: Optional[str]) -> tuple[Optional[str], Optional[str]]:
        """Optional MNC_CODES join → (state, operators). Soft-fails if table missing."""
        if not first_cellid or first_cellid[:3] not in ('404', '405'):
            return (None, None)
        if first_cellid in self._mnc_cache:
            return self._mnc_cache[first_cellid]
        if self._mnc_available is False:
            return (None, None)

        result: tuple[Optional[str], Optional[str]] = (None, None)
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT vi_mnc_lookup')
                try:
                    if self._mnc_available is None:
                        cur.execute(
                            """
                            SELECT 1 FROM information_schema.tables
                            WHERE table_name IN ('mnc_codes', 'MNC_CODES')
                            LIMIT 1
                            """
                        )
                        self._mnc_available = cur.fetchone() is not None
                    if not self._mnc_available:
                        cur.execute('RELEASE SAVEPOINT vi_mnc_lookup')
                        self._mnc_cache[first_cellid] = result
                        return result
                    cur.execute(
                        """
                        SELECT state,
                               COALESCE(
                                   NULLIF(trim(operators), ''),
                                   NULLIF(trim(operator), '')
                               )
                        FROM mnc_codes
                        WHERE left(%s, 3) IN ('404', '405')
                          AND %s LIKE replace(mcc_mnc, '-', '') || '%%'
                        LIMIT 1
                        """,
                        (first_cellid, first_cellid),
                    )
                    row = cur.fetchone()
                    cur.execute('RELEASE SAVEPOINT vi_mnc_lookup')
                    if row:
                        result = (row[0], row[1])
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT vi_mnc_lookup')
                    self._mnc_available = False
                    logger.info('Vi normalizer: mnc_codes unavailable (%s); skipping', inner_exc)
        except Exception as exc:
            self._mnc_available = False
            logger.info('Vi normalizer: mnc_codes unavailable (%s); skipping', exc)

        self._mnc_cache[first_cellid] = result
        return result

    def lookup_tower_key(self, celltowerid: Optional[str]) -> Optional[int]:
        if not celltowerid or celltowerid in {'0', '-'}:
            return None
        if celltowerid in self._tower_cache:
            return self._tower_cache[celltowerid]

        value: Optional[int] = None
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT vi_tower_lookup')
                try:
                    cur.execute("SET LOCAL statement_timeout = '1500ms'")
                    cur.execute(
                        """
                        SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                        FROM cdatcelltowerareanew
                        WHERE celltowerid = %s
                        ORDER BY
                          CASE
                            WHEN provider_key = %s THEN 0
                            WHEN upper(COALESCE(operator, '')) = %s THEN 1
                            ELSE 2
                          END,
                          lastupdate DESC NULLS LAST
                        LIMIT 1
                        """,
                        (celltowerid, VI_PROVIDER_KEY, VI_OPERATOR_LABEL),
                    )
                    row = cur.fetchone()
                    if row and row[0]:
                        value = safe_tower_key(row[0])
                    cur.execute('RELEASE SAVEPOINT vi_tower_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT vi_tower_lookup')
                    logger.warning('Vi normalizer: tower lookup skipped: %s', inner_exc)
                    value = None
        except Exception as exc:
            logger.warning('Vi normalizer: tower lookup unavailable: %s', exc)
            value = None

        self._tower_cache[celltowerid] = value
        return value

    def lookup_tower_key_by_bts(self, celltowerid: Optional[str]) -> Optional[int]:
        """Fallback: join MAX view on BTS_ID = celltowerid."""
        if not celltowerid or celltowerid in {'0', '-'}:
            return None
        if celltowerid in self._tower_by_bts_cache:
            return self._tower_by_bts_cache[celltowerid]

        value: Optional[int] = None
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT vi_tower_bts')
                try:
                    cur.execute("SET LOCAL statement_timeout = '1500ms'")
                    cur.execute(
                        """
                        SELECT NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')
                        FROM cdatcelltowerareanew
                        WHERE bts_id = %s
                           OR regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g') = %s
                        ORDER BY lastupdate DESC NULLS LAST
                        LIMIT 1
                        """,
                        (celltowerid, _digits_only(celltowerid)),
                    )
                    row = cur.fetchone()
                    if row and row[0]:
                        value = safe_tower_key(row[0])
                    cur.execute('RELEASE SAVEPOINT vi_tower_bts')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT vi_tower_bts')
                    logger.warning('Vi normalizer: bts tower lookup skipped: %s', inner_exc)
                    value = None
        except Exception as exc:
            logger.warning('Vi normalizer: bts tower lookup unavailable: %s', exc)
            value = None

        self._tower_by_bts_cache[celltowerid] = value
        return value

    def enrich(self, rec: CdrRecord) -> CdrRecord:
        state_name, state_key = self.lookup_area_state(rec.phone)
        mnc_state, mnc_operator = self.lookup_mnc(rec.first_cellid)

        # Legacy: OTHERINFO from MNC state; State_Name from phonearea.
        if mnc_state:
            rec.otherinfo = str(mnc_state)[:50]
        mnc_op = (mnc_operator or 'VODAFONE').strip()

        # Clear OTHERINFO when celltowerid is numeric and roaming doesn't start with '-'
        roam = rec.roaming_nw or ''
        if (
            rec.otherinfo
            and _is_numeric(rec.celltowerid)
            and (not roam or roam[0] != '-')
        ):
            rec.otherinfo = None

        # Re-apply -0 pads (legacy second pass)
        if rec.celltowerid and rec.celltowerid != '0':
            for pad in ('-000', '-00', '-0'):
                rec.celltowerid = rec.celltowerid.replace(pad, '-')

        if not rec.otherinfo:
            mapped = _map_roaming_to_state(rec.roaming_nw)
            if mapped:
                rec.otherinfo = mapped

        # state_key from State_Name (phonearea state)
        if state_key is not None:
            rec.state_key = state_key
        elif state_name:
            rec.state_key = self._state_by_name.get(str(state_name).strip().upper())

        # Operator-specific celltower transforms
        rec.celltowerid = _apply_operator_cell_transform(rec.celltowerid or '0', mnc_op)

        # Append operator to roaming when not plain VODAFONE
        if mnc_op.upper() != 'VODAFONE' and rec.roaming_nw:
            rec.roaming_nw = f'{rec.roaming_nw}_{mnc_op}'

        if rec.tower_key is None:
            rec.tower_key = self.lookup_tower_key(rec.celltowerid)
        if rec.tower_key is None and rec.celltowerid:
            rec.tower_key = self.lookup_tower_key_by_bts(rec.celltowerid)

        # Special roaming renames + truncate
        roam_key = (rec.roaming_nw or '').upper()
        for src, dst in _LONG_ROAM_RENAMES.items():
            if roam_key == src.upper():
                rec.roaming_nw = dst
                break
        if rec.roaming_nw and len(rec.roaming_nw) > 50:
            rec.roaming_nw = rec.roaming_nw[:50]

        if rec.other is None:
            rec.other = ''
        return rec


def normalize_vi_record(
    rec: CdrRecord,
    *,
    conn=None,
    enricher: Optional[ViDbEnricher] = None,
) -> Optional[CdrRecord]:
    """Full Vi/Vodafone normalization. Drop returns None (legacy DELETE)."""
    out = normalize_vi_fields(rec)
    if out is None:
        return None

    if conn is not None:
        db = enricher or ViDbEnricher(conn)
        return db.enrich(out)

    if not out.otherinfo:
        out.otherinfo = _map_roaming_to_state(out.roaming_nw)
    if out.other is None:
        out.other = ''
    if out.roaming_nw and len(out.roaming_nw) > 50:
        out.roaming_nw = out.roaming_nw[:50]
    return out
