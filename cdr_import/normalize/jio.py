"""
Jio CDR normalization — Python/Postgres port of MSSQL
  import.proc_rel_jio

MSSQL-specific constructs intentionally replaced:
  - #temp / #temp_cdat          → in-memory CdrRecord mutation
  - REPLACE / SUBSTRING / LEFT  → Python string ops
  - ISNUMERIC / ISDATE / DMY    → digit checks; parser validates starttime
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

logger = logging.getLogger(__name__)

JIO_PROVIDER_KEY = 15
JIO_OPERATOR_LABEL = 'JIO_TOWER'
DEFAULT_OTHERINFO = 'ANDHRA PRADESH'


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


def _is_outgoing(call_type: Optional[str]) -> bool:
    return 'out' in (call_type or '').lower()


def _normalize_jio_msisdn(value: Optional[str]) -> Optional[str]:
    """Port of Phone/Other prefix stripping from proc_rel_jio."""
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip()
    if text == '':
        return ''

    # Keep alphanumeric party labels (e.g. A2P SMS senders); only strip digit MSISDNs.
    if re.search(r'[A-Za-z]', text):
        return text

    digits = _digits_only(text)
    working = digits if digits else text

    if working[:2] in ('00', '95') and len(working) > 10:
        working = working[2:]
    if working[:2] == '91' and len(working) > 10:
        working = working[2:]
    if working.startswith('0') and len(working) > 1:
        working = working[1:]
    # Legacy: LEN>15 AND LEFT=34500 → SUBSTRING(OTHER,6,15)
    if len(working) > 15 and working.startswith('34500'):
        working = working[5:20]
    return working or ''


def _normalize_call_fw(value: Optional[str]) -> Optional[str]:
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip()
    if not text:
        return ''
    digits = _digits_only(text) or text
    working = digits
    if working[:2] == '91' and len(working) > 10:
        working = working[2:]
    if working.startswith('0') and len(working) > 1:
        working = working[1:]
    return working or ''


def _normalize_celltowerid(value: Optional[str]) -> str:
    if value is None:
        return '0'
    text = (_strip_quotes(value) or '').strip()
    if text in {'', '-', '--'}:
        return '0'
    text = text.replace('--', '-')
    if len(text) > 15:
        text = text[:15]
    return text or '0'


def _truncate_cell(value: Optional[str]) -> Optional[str]:
    if value is None:
        return None
    text = (_strip_quotes(value) or '').strip()
    if not text:
        return None
    return text[:15] if len(text) > 15 else text


def _assign_phone_other(rec: CdrRecord) -> None:
    """Legacy: OUT → phone=CallingNo, other=CalledNo; else reverse."""
    calling = rec.calling_no
    called = rec.called_no
    if _is_outgoing(rec.call_type):
        rec.phone = calling
        rec.other = called
        rec.incoming = 0
    else:
        rec.phone = called
        rec.other = calling
        rec.incoming = 1


def normalize_jio_fields(rec: CdrRecord) -> Optional[CdrRecord]:
    """Field-only Jio rules (no DB). Returns None if row should be dropped."""
    rec.provider_key = JIO_PROVIDER_KEY

    # Quote stripping
    rec.phone = _strip_quotes(rec.phone)
    rec.other = _strip_quotes(rec.other)
    rec.call_type = _strip_quotes(rec.call_type)
    rec.calling_no = _strip_quotes(rec.calling_no)
    rec.called_no = _strip_quotes(rec.called_no)
    rec.first_cellid = _strip_quotes(rec.first_cellid)
    rec.last_cellid = _strip_quotes(rec.last_cellid)
    rec.celltowerid = _strip_quotes(rec.celltowerid)
    rec.roaming_nw = _strip_quotes(rec.roaming_nw)

    # Re-apply phone/other from calling/called + direction (proc_rel_jio).
    if rec.calling_no is not None or rec.called_no is not None:
        _assign_phone_other(rec)
    else:
        rec.incoming = 0 if _is_outgoing(rec.call_type) else 1

    raw = rec.raw or {}
    call_fw = raw.get('CF') or raw.get('Call_FW') or raw.get('CALL_FW')
    if call_fw is not None:
        raw['_call_fw_normalized'] = _normalize_call_fw(str(call_fw))

    rec.phone = _normalize_jio_msisdn(rec.phone)
    rec.other = _normalize_jio_msisdn(rec.other)
    if rec.calling_no:
        rec.calling_no = _normalize_jio_msisdn(rec.calling_no) or rec.calling_no
    if rec.called_no:
        rec.called_no = _normalize_jio_msisdn(rec.called_no) or rec.called_no

    # Drop only when BOTH phone and other are non-numeric (legacy AND).
    phone_ok = rec.phone is not None and str(rec.phone).strip() != '' and _is_numeric(str(rec.phone))
    other_ok = rec.other is not None and str(rec.other).strip() != '' and _is_numeric(str(rec.other))
    if not phone_ok and not other_ok:
        return None

    # IMEI hygiene: null / '-' / len<14 / non-numeric → 0
    imei_s = str(rec.imeinumber) if rec.imeinumber is not None else ''
    if imei_s in {'', '-', 'None'} or not _is_numeric(imei_s) or len(_digits_only(imei_s)) < 14:
        rec.imeinumber = 0
    else:
        rec.imeinumber = int(_digits_only(imei_s))

    if rec.imsinumber is not None:
        imsi_s = str(rec.imsinumber)
        if not _is_numeric(imsi_s):
            rec.imsinumber = 0
    else:
        rec.imsinumber = 0

    if rec.duration is None:
        rec.duration = 0

    raw_first = rec.first_cellid or rec.celltowerid
    rec.celltowerid = _normalize_celltowerid(raw_first)
    rec.first_cellid = _truncate_cell(raw_first)
    rec.last_cellid = _truncate_cell(rec.last_cellid)

    # Default otherinfo; enricher overwrites from phonearea / roaming.
    if not rec.otherinfo:
        rec.otherinfo = DEFAULT_OTHERINFO

    return rec


class JioDbEnricher:
    """Postgres lookups that replace MSSQL joins in proc_rel_jio."""

    def __init__(self, conn):
        self.conn = conn
        self._state_by_name: dict[str, int] = {}
        self._area_by_prefix: dict[str, tuple[Optional[str], Optional[int]]] = {}
        self._tower_cache: dict[str, Optional[int]] = {}
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
            logger.warning('Jio normalizer: could not load cdat_state_master: %s', exc)
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
                cur.execute('SAVEPOINT jio_area_lookup')
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
                    cur.execute('RELEASE SAVEPOINT jio_area_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT jio_area_lookup')
                    logger.warning('Jio normalizer: phonearea lookup failed: %s', inner_exc)
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
            logger.warning('Jio normalizer: phonearea lookup unavailable: %s', exc)
            result = (None, None)
        self._area_by_prefix[prefix] = result
        return result

    def lookup_tower_key(self, celltowerid: Optional[str]) -> Optional[int]:
        """Legacy joins CDATCELLTOWERAREANEW_MAX on celltowerid only."""
        if not celltowerid or celltowerid in {'0', '-'}:
            return None
        if celltowerid in self._tower_cache:
            return self._tower_cache[celltowerid]

        value: Optional[int] = None
        try:
            with self.conn.cursor() as cur:
                cur.execute('SAVEPOINT jio_tower_lookup')
                try:
                    cur.execute("SET LOCAL statement_timeout = '1500ms'")
                    # Prefer JIO_TOWER / provider_key=15 when present; else any match.
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
                        (celltowerid, JIO_PROVIDER_KEY, JIO_OPERATOR_LABEL),
                    )
                    row = cur.fetchone()
                    if row and row[0]:
                        value = int(row[0])
                    # Undo SET LOCAL statement_timeout so later import/dedup is not capped at 1.5s.
                    cur.execute('ROLLBACK TO SAVEPOINT jio_tower_lookup')
                    cur.execute('RELEASE SAVEPOINT jio_tower_lookup')
                except Exception as inner_exc:
                    cur.execute('ROLLBACK TO SAVEPOINT jio_tower_lookup')
                    logger.warning('Jio normalizer: tower lookup skipped: %s', inner_exc)
                    value = None
        except Exception as exc:
            logger.warning('Jio normalizer: tower lookup unavailable: %s', exc)
            value = None

        self._tower_cache[celltowerid] = value
        return value

    def enrich(self, rec: CdrRecord) -> CdrRecord:
        state_name, state_key = self.lookup_area_state(rec.phone)
        # Legacy: Otherinfo=C.STATE, State_Name=B.state (same phone prefix join).
        if state_name:
            rec.otherinfo = state_name

        # If still null and roam_nw set: OTHERINFO from state_master where ROAM_NW=STATE
        if not rec.otherinfo and rec.roaming_nw:
            roam = str(rec.roaming_nw).strip()
            if roam.upper() in self._state_by_name:
                rec.otherinfo = roam

        # Typo fix: CHATTISGARH → CHHATTISGARH
        if (rec.roaming_nw or '').strip().upper() == 'CHATTISGARH':
            rec.otherinfo = 'CHHATTISGARH'

        # OTHERINFO = State_Name WHERE OTHERINFO IS NULL
        if not rec.otherinfo and state_name:
            rec.otherinfo = state_name
        if not rec.otherinfo:
            rec.otherinfo = DEFAULT_OTHERINFO

        # Legacy: state_key joined only on State_Name (phonearea), never from Otherinfo
        # (Otherinfo may be roaming / CHHATTISGARH / default AP).
        if state_key is not None:
            rec.state_key = state_key
        elif state_name:
            rec.state_key = self._state_by_name.get(str(state_name).strip().upper())

        if rec.tower_key is None:
            rec.tower_key = self.lookup_tower_key(rec.celltowerid)

        # Final insert: ISNULL(other,0)
        if rec.other is None or str(rec.other).strip() == '':
            rec.other = '0'
        return rec


def normalize_jio_record(
    rec: CdrRecord,
    *,
    conn=None,
    enricher: Optional[JioDbEnricher] = None,
) -> Optional[CdrRecord]:
    """Full Jio normalization. Drop returns None (legacy DELETE)."""
    out = normalize_jio_fields(rec)
    if out is None:
        return None

    if conn is not None:
        db = enricher or JioDbEnricher(conn)
        return db.enrich(out)

    if not out.otherinfo:
        out.otherinfo = DEFAULT_OTHERINFO
    if out.other is None or str(out.other).strip() == '':
        out.other = '0'
    if (out.roaming_nw or '').strip().upper() == 'CHATTISGARH':
        out.otherinfo = 'CHHATTISGARH'
    return out
