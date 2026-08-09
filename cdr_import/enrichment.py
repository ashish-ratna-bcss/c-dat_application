from __future__ import annotations
import re
from typing import Optional
import psycopg2.extras
from .config import PROVIDER_KEYS
NETWORK_OPERATOR_MAP = {'airtel': 'airtel', 'jio': 'jio', 'bsnl': 'bsnl', 'vi': 'vi', 'vodafone': 'vi', 'idea': 'vi', 'all': None}

def resolve_operator(network: Optional[str], detected: Optional[str] = None) -> str:
    """Prefer the explicit Network dropdown / --operator value.

    Filename-based detection is not used. `detected` is only a fallback when
    the caller did not pass a network (e.g. CLI without --operator).
    """
    if network:
        key = network.strip().lower()
        if key not in ('all', 'auto', ''):
            mapped = NETWORK_OPERATOR_MAP.get(key)
            if mapped:
                return mapped
            if key in PROVIDER_KEYS:
                return key
            raise ValueError(f"Unknown network/operator: {network!r}")
    if detected:
        return detected
    raise ValueError(
        'Network is required. Select Airtel, Jio, Vi, or BSNL from the dropdown.'
    )

def normalize_duration(duration: int, call_type: Optional[str], service_type: Optional[str]) -> int:
    blob = f'{call_type or ''} {service_type or ''}'.lower()
    if any((token in blob for token in ('sms', 'mms', 'message', 'text', 'ussd'))):
        return 0
    if duration == 1 and 'sms' in blob:
        return 0
    return duration


# Matches public.cdatpcsuspect.tower_key NUMERIC(18,0).
TOWER_KEY_MAX_DIGITS = 18


def safe_tower_key(value) -> Optional[int]:
    """Parse bts/tower id digits into tower_key, or None if missing/overflow."""
    if value is None:
        return None
    digits = re.sub(r'\D', '', str(value))
    if not digits or len(digits) > TOWER_KEY_MAX_DIGITS:
        return None
    try:
        parsed = int(digits)
    except ValueError:
        return None
    return parsed or None

def _phone_lookup_sql(phone: str) -> tuple[str, list]:
    digits = re.sub('\\D', '', phone or '')
    if len(digits) == 10:
        candidates = [digits, f'91{digits}', f'0{digits}']
    elif len(digits) > 10:
        candidates = [digits, digits[-10:], f'0{digits[-10:]}']
    else:
        candidates = [digits] if digits else []
    if not candidates:
        return ('', [])
    clauses = []
    params: list = []
    for cand in candidates:
        clauses.append('%s LIKE phoneprefix || %s')
        params.extend([cand, '%'])
    return (f'({' OR '.join(clauses)})', params)

def normalize_record(rec) -> None:
    rec.duration = normalize_duration(rec.duration, rec.call_type, rec.otherinfo)

class CdrEnricher:

    def __init__(self, conn):
        self.conn = conn
        self._tower_cache: dict[tuple[str, int], dict] = {}
        self._area_cache: dict[str, Optional[str]] = {}

    def enrich_record(self, rec) -> None:
        rec.duration = normalize_duration(rec.duration, rec.call_type, rec.otherinfo)
        if not rec.celltowerid and rec.phone:
            network_id = self.lookup_network_id(rec.phone)
            if network_id:
                rec.celltowerid = network_id
                if not rec.first_cellid:
                    rec.first_cellid = network_id
        if rec.celltowerid:
            tower = self.lookup_tower(rec.celltowerid, rec.provider_key)
            if tower:
                if tower.get('tower_key') is not None:
                    rec.tower_key = tower['tower_key']
                if tower.get('state_key') is not None:
                    rec.state_key = tower['state_key']
                if not rec.otherinfo and tower.get('operator'):
                    rec.otherinfo = tower['operator']

    def lookup_network_id(self, phone: str) -> Optional[str]:
        if phone in self._area_cache:
            return self._area_cache[phone]
        where, params = _phone_lookup_sql(phone)
        if not where:
            self._area_cache[phone] = None
            return None
        sql = f"\n            SELECT COALESCE(NULLIF(areadescription, ''), phoneprefix) AS network_id\n            FROM cdatphonearea\n            WHERE {where}\n            ORDER BY LENGTH(phoneprefix) DESC\n            LIMIT 1\n        "
        with self.conn.cursor() as cur:
            cur.execute(sql, params)
            row = cur.fetchone()
        value = row[0] if row else None
        self._area_cache[phone] = value
        return value

    def lookup_tower(self, celltowerid: str, provider_key: int | None=None) -> dict:
        cache_key = (celltowerid, provider_key or 0)
        if cache_key in self._tower_cache:
            return self._tower_cache[cache_key]
        sql_keyed = '\n            SELECT bts_id, state_key, operator, areadescription\n            FROM cdatcelltowerareanew\n            WHERE celltowerid = %s AND provider_key = %s\n            ORDER BY lastupdate DESC NULLS LAST\n            LIMIT 1\n        '
        sql_any = '\n            SELECT bts_id, state_key, operator, areadescription\n            FROM cdatcelltowerareanew\n            WHERE celltowerid = %s\n            ORDER BY lastupdate DESC NULLS LAST\n            LIMIT 1\n        '
        with self.conn.cursor(cursor_factory=psycopg2.extras.RealDictCursor) as cur:
            row = None
            if provider_key:
                cur.execute(sql_keyed, (celltowerid, provider_key))
                row = cur.fetchone()
            if not row:
                cur.execute(sql_any, (celltowerid,))
                row = cur.fetchone()
        data = dict(row) if row else {}
        if data.get('bts_id'):
            data['tower_key'] = safe_tower_key(data['bts_id'])
        self._tower_cache[cache_key] = data
        return data
