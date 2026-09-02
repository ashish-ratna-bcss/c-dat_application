"""Preview a CDR CSV: detect provider from content and return all columns."""
from __future__ import annotations

import csv
import logging
import re
from collections import Counter
from datetime import datetime
from decimal import Decimal, InvalidOperation
from pathlib import Path
from typing import Any, Optional

HEADER_MARKERS = {
    "airtel": re.compile(r"Target No\b", re.IGNORECASE),
    "vi": re.compile(r"Target\s*/A\s*PARTY\s*NUMBER", re.IGNORECASE),
    "bsnl": re.compile(r"\bSL_NO\b", re.IGNORECASE),
    "jio": re.compile(r"Calling Party Telephone Number", re.IGNORECASE),
}
PROVIDER_BANNERS = {
    "airtel": re.compile(r"BHARTI\s+AIRTEL|\bAIRTEL\b", re.IGNORECASE),
    "vi": re.compile(r"VODAFONE\s*IDEA|\bVODAFONE\b", re.IGNORECASE),
    "bsnl": re.compile(r"\bBSNL\b|BHARAT\s+SANCHAR", re.IGNORECASE),
    "jio": re.compile(r"RELIANCE\s+JIO|\bJIO\b", re.IGNORECASE),
}
HEADER_TEXT = {
    "airtel": "Target No",
    "vi": "Target /A PARTY NUMBER",
    "bsnl": "SL_NO",
    "jio": "Calling Party Telephone Number",
}
_CONTENT_SCAN_LINES = 25
_SKIP_PREFIXES = ("***", "cdr count", "this is system generated", "disclaimer", "call_forward")
_PREVIEW_ROW_LIMIT = 0
_EMPTY = {"", "-", "---", "-----", "n/a", "na", "null"}
logger = logging.getLogger("dataUpload.cdr.preview")


def decode_csv_bytes(raw: bytes) -> str:
    return raw.decode("utf-8-sig", errors="replace")


def detect_provider_from_text(text: str) -> Optional[str]:
    head = text.splitlines()[:_CONTENT_SCAN_LINES]
    blob = "\n".join(head)
    marker_hits = [op for op, pattern in HEADER_MARKERS.items() if pattern.search(blob)]
    if len(marker_hits) == 1:
        return marker_hits[0]
    banner_hits = [op for op, pattern in PROVIDER_BANNERS.items() if pattern.search(blob)]
    if len(banner_hits) == 1:
        return banner_hits[0]
    if marker_hits and banner_hits:
        common = [op for op in marker_hits if op in banner_hits]
        if len(common) == 1:
            return common[0]
    return None


def _clean_cell(value: str) -> str:
    text = (value or "").strip()
    if len(text) >= 3 and text[0] == "=" and text[1] in {'"', "'"} and text.endswith(text[1]):
        text = text[2:-1].strip()
    elif text.startswith("=") and len(text) > 1:
        text = text[1:].strip()
    while len(text) >= 2 and (
        (text.startswith("'") and text.endswith("'"))
        or (text.startswith('"') and text.endswith('"'))
    ):
        text = text[1:-1].strip()
    return text.strip()


def _extract_metadata(lines: list[str]) -> dict[str, str]:
    blob = "\n".join(lines[:40])
    meta = {
        "subscriber_name": "",
        "subscriber_address": "",
        "target_phone": "",
        "connection_type": "",
        "date_from": "",
        "date_to": "",
        "city": "",
        "state": "",
        "nickname": "",
        "category": "",
        "imei": "",
    }
    kv_aliases = {
        "name": "subscriber_name",
        "subscriber name": "subscriber_name",
        "customer name": "subscriber_name",
        "user name": "subscriber_name",
        "address": "subscriber_address",
        "local address": "subscriber_address",
        "permanent address": "subscriber_address",
        "customer_type": "connection_type",
        "connection type": "connection_type",
        "customer type": "connection_type",
        "city": "city",
        "state": "state",
        "imei": "imei",
    }
    for line in lines[:40]:
        parsed = next(csv.reader([line]), [])
        if len(parsed) >= 2:
            key = _clean_cell(parsed[0]).lower().rstrip(":- ").replace("_", " ")
            value = _clean_cell(", ".join(parsed[1:])).strip(" ,")
            field = kv_aliases.get(key)
            if field and value and not meta[field]:
                meta[field] = value
        m = re.match(r"^\s*([^:,]{2,40})\s*[:-]+\s*(.+)$", line)
        if m:
            key = m.group(1).strip().lower().replace("_", " ")
            value = _clean_cell(m.group(2)).strip(" ,")
            field = kv_aliases.get(key)
            if field and value and not meta[field]:
                meta[field] = value

    m = re.search(r"Search Value\s*:\s*(\d{8,15})", blob, re.IGNORECASE)
    if m:
        meta["target_phone"] = m.group(1)
    m = re.search(r"Call Details of Mobile No\s+'?(\d{8,15})", blob, re.IGNORECASE)
    if m and not meta["target_phone"]:
        meta["target_phone"] = m.group(1)
    m = re.search(r"Input Value.*?['\"](\d{8,15})['\"]", blob, re.IGNORECASE)
    if m and not meta["target_phone"]:
        meta["target_phone"] = m.group(1)
    m = re.search(r"MSISDN\s*:?\s*-?\s*(\d{8,15})", blob, re.IGNORECASE)
    if m and not meta["target_phone"]:
        meta["target_phone"] = m.group(1)
    m = re.search(
        r"from\s+'?([\d\-]+)'?\s+to\s+'?([\d\-]+)'?",
        blob,
        re.IGNORECASE,
    )
    if m:
        meta["date_from"], meta["date_to"] = m.group(1), m.group(2)
    return meta


def _lookup_subscriber(phone: str) -> dict[str, str]:
    out = {
        "subscriber_name": "",
        "subscriber_address": "",
        "city": "",
        "state": "",
        "nickname": "",
        "category": "",
        "pin": "",
        "role": "",
        "inc_officer": "",
        "connection_type": "",
    }
    if not phone:
        return out
    try:
        from db import db_connection

        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT nickname, fname, lname, address, city, state, pin,
                           category, role, inc_officer
                    FROM cdatsuspect
                    WHERE phone = %s
                    LIMIT 1
                    """,
                    (phone,),
                )
                row = cur.fetchone()
                if row:
                    nick, fname, lname, address, city, state, pin, category, role, officer = row
                    name = " ".join(part for part in (fname, lname) if part).strip()
                    out["subscriber_name"] = name or (nick or "")
                    out["nickname"] = nick or ""
                    out["subscriber_address"] = address or ""
                    out["city"] = city or ""
                    out["state"] = state or ""
                    out["pin"] = pin or ""
                    out["category"] = category or ""
                    out["role"] = role or ""
                    out["inc_officer"] = officer or ""
                cur.execute(
                    """
                    SELECT fullname, fulladdress, category_type, connection_type
                    FROM cdataddress
                    WHERE phone = %s AND eff_to_date IS NULL
                    ORDER BY eff_from_date DESC NULLS LAST
                    LIMIT 1
                    """,
                    (phone,),
                )
                addr = cur.fetchone()
                if addr:
                    fullname, fulladdress, category_type, connection_type = addr
                    if fullname and not out["subscriber_name"]:
                        out["subscriber_name"] = fullname
                    if fulladdress:
                        out["subscriber_address"] = fulladdress
                    if category_type and not out["category"]:
                        out["category"] = category_type
                    if connection_type:
                        out["connection_type"] = connection_type
    except Exception as exc:
        logger.warning("CDR preview subscriber lookup failed: %s", exc)
    return out


def _is_skip_row(row: list[str]) -> bool:
    joined = " ".join(_clean_cell(c) for c in row).strip()
    if not joined:
        return True
    if set(joined) <= {"-", ",", " "}:
        return True
    lower = joined.lower()
    return any(lower.startswith(p) for p in _SKIP_PREFIXES)


def _phone_from_filename(name: str) -> str:
    matches = re.findall(r"\d{10,15}", Path(name).stem)
    return matches[-1] if matches else ""


def _row_get(row: dict[str, str], *names: str) -> str:
    if not names:
        return ""
    exact = {k: v for k, v in row.items()}
    lower = {k.lower().replace(" ", ""): v for k, v in row.items()}
    for name in names:
        if name in exact and exact[name] != "":
            return exact[name]
        compact = name.lower().replace(" ", "")
        if compact in lower and lower[compact] != "":
            return lower[compact]
    for name in names:
        if name in exact:
            return exact[name]
        compact = name.lower().replace(" ", "")
        if compact in lower:
            return lower[compact]
    return ""


def _to_int(value: str, default: int = 0) -> int:
    text = (value or "").strip().replace(",", "")
    if not text or text.lower() in _EMPTY:
        return default
    try:
        return int(Decimal(text))
    except (InvalidOperation, ValueError):
        digits = re.sub(r"\D", "", text)
        return int(digits) if digits else default


def _to_phone(value: str) -> Optional[str]:
    text = (value or "").strip()
    if not text or text.lower() in _EMPTY:
        return None
    if re.search(r"[A-Za-z]", text):
        return None
    text = re.sub(r"[^\d+]", "", text)
    if text.startswith("91") and len(text) > 10:
        text = text[2:]
    return text or None


def _parse_datetime(date_part: str, time_part: str) -> Optional[datetime]:
    date_s = (date_part or "").strip()
    time_s = (time_part or "").strip()
    if not date_s:
        return None
    combos = [f"{date_s} {time_s}".strip(), f"{date_s}T{time_s}".strip()]
    formats = (
        "%d/%m/%Y %H:%M:%S",
        "%d-%m-%Y %H:%M:%S",
        "%d/%m/%Y %H:%M",
        "%d-%m-%Y %H:%M",
        "%Y-%m-%d %H:%M:%S",
        "%Y-%m-%d %H:%M",
    )
    for combo in combos:
        for fmt in formats:
            try:
                return datetime.strptime(combo, fmt)
            except ValueError:
                continue
    return None


DupKey = tuple[str, Optional[str], datetime, int, int]


def _norm_key(phone: str, other: Optional[str], starttime: datetime, duration: int, incoming: int) -> DupKey:
    if getattr(starttime, "tzinfo", None) is not None:
        starttime = starttime.replace(tzinfo=None)
    if getattr(starttime, "microsecond", 0):
        starttime = starttime.replace(microsecond=0)
    other_s = None if other is None or str(other).strip() == "" else str(other).strip()
    return (str(phone), other_s, starttime, int(duration), int(incoming))


PROVIDER_KEYS = {"airtel": 2, "bsnl": 4, "vi": 12, "jio": 15}


def _clip(value: Optional[str], width: int) -> Optional[str]:
    if value is None:
        return None
    text = str(value).strip()
    if not text:
        return None
    return text[:width]


def _map_cdr_record(provider: Optional[str], row: dict[str, str], target_phone: str) -> Optional[dict[str, Any]]:
    other_raw = ""
    incoming = 0
    call_type = ""
    first_cell = ""
    last_cell = ""
    roam = ""
    otherinfo = ""
    calling_no = None
    called_no = None
    if provider == "airtel":
        phone = _to_phone(_row_get(row, "Target No", "TargetNo")) or target_phone
        other_raw = _row_get(row, "B Party No", "BPartyNo")
        starttime = _parse_datetime(_row_get(row, "Date"), _row_get(row, "Time"))
        duration = _to_int(_row_get(row, "Dur(s)", "Dur", "Duration"))
        call_type = _row_get(row, "Call Type", "CallType")
        incoming = 1 if call_type.upper() == "IN" else 0
        first_cell = _row_get(row, "First CGI", "FirstCGI", "First BTS")
        last_cell = _row_get(row, "Last CGI", "LastCGI", "Last BTS")
        roam = _row_get(row, "Roam Nw", "RoamNw") or _row_get(row, "LRN TSP-LSA", "LRNTSPLSA")
        otherinfo = _row_get(row, "Service Type", "ServiceType")
        calling_no = phone
        called_no = _to_phone(other_raw) or other_raw
    elif provider == "bsnl":
        phone = _to_phone(_row_get(row, "Mobile_No", "mobile_no")) or target_phone
        other_raw = _row_get(row, "Other_Party_No", "other_party_no")
        starttime = _parse_datetime(
            _row_get(row, "Call_Date", "call_date"),
            _row_get(row, "Call_Time", "call_time", "Call_Initiation_Time(CIT)", "Call_Initiation_Time"),
        )
        duration = _to_int(_row_get(row, "Call_Duration", "call_duration"))
        call_type = _row_get(row, "Call_Type", "call_type")
        incoming = 1 if call_type.upper() == "IN" else 0
        first_cell = _row_get(row, "First_Cell_id", "first_cell_id", "First_Cell_ID")
        last_cell = _row_get(row, "Last_Cell_ID", "last_cell_id", "Last_Cell_id")
        roam = _row_get(row, "Circle_NW", "circle_nw", "Circle_Nw")
        otherinfo = _row_get(row, "Service_Type", "service_type", "Service Type")
        calling_no = phone
        called_no = _to_phone(other_raw) or other_raw
    elif provider == "jio":
        calling_raw = _row_get(row, "Calling Party Telephone Number", "CallingNo", "Calling No")
        called_raw = _row_get(row, "Called Party Telephone Number", "CalledNo", "Called No")
        calling = _to_phone(calling_raw) or (calling_raw or None)
        called = _to_phone(called_raw) or (called_raw or None)
        call_type = _row_get(row, "Call Type", "Call_Type", "CALL_TYPE")
        incoming = 0 if "out" in call_type.lower() else 1
        phone = (calling if incoming == 0 else called) or target_phone
        other_raw = (called_raw if incoming == 0 else calling_raw) or ""
        starttime = _parse_datetime(
            _row_get(row, "Call Date", "Call_Date"),
            _row_get(row, "Call Time", "Call_Time"),
        )
        duration = _to_int(_row_get(row, "Call Duration", "Call_Dur", "Call Dur"))
        first_cell = _row_get(row, "First Cell ID", "FIRST_CELLID", "First_Cellid").strip("'")
        last_cell = _row_get(row, "Last Cell ID", "LAST_CELLID", "Last_Cellid").strip("'")
        roam = _row_get(row, "Roaming Circle Name", "roam_nw", "Roam_NW")
        calling_no = calling
        called_no = called
    else:
        phone = _to_phone(
            _row_get(row, "Target /A PARTY NUMBER", "Target/A PARTY NUMBER", "Target No", "TargetNo")
        ) or target_phone
        other_raw = _row_get(row, "B PARTY NUMBER", "B Party No", "BPartyNo", "BPARTY NUMBER")
        starttime = _parse_datetime(
            _row_get(row, "Call date", "Date", "Call_Date"),
            _row_get(row, "Call Initiation Time", "Time", "Call_Time"),
        )
        duration = _to_int(_row_get(row, "Call Duration", "Dur", "Duration"))
        call_type = _row_get(row, "CALL_TYPE", "Call Type", "CallType")
        incoming = 0 if call_type.upper().startswith("OUTGOING") else 1
        first_cell = _row_get(row, "First Cell Global Id", "FirstCGI", "First CGI")
        last_cell = _row_get(row, "Last Cell Global Id", "LastCGI", "Last CGI")
        roam = _row_get(row, "Roaming Network", "Roam Nw", "RoamNw")
        calling_no = phone
        called_no = _to_phone(other_raw) or other_raw

    if not phone or starttime is None:
        return None
    other = _to_phone(other_raw) or (other_raw.strip() or None)
    if other and other.lower() in _EMPTY:
        other = None
    imei = _to_int(_row_get(row, "IMEI", "Imei", "imei", "Imeinumber"))
    if imei > 10**15 - 1:
        imei = int(str(imei)[-15:])
    imsi = _to_int(_row_get(row, "IMSI", "Imsi", "imsi", "Imsinumber"), default=0) or None
    if imsi and imsi > 10**18 - 1:
        imsi = int(str(imsi)[-18:])
    return {
        "phone": str(phone)[:25],
        "other": (other or "")[:50],
        "starttime": starttime,
        "duration": min(int(duration), 99999),
        "incoming": int(incoming),
        "imeinumber": imei,
        "imsinumber": imsi,
        "celltowerid": _clip(first_cell, 50),
        "otherinfo": _clip(otherinfo, 50),
        "tower_key": None,
        "provider_key": PROVIDER_KEYS.get(provider or "", 0),
        "state_key": None,
        "first_cellid": _clip(first_cell, 50),
        "last_cellid": _clip(last_cell, 50),
        "roaming_nw": _clip(roam, 50),
        "call_type": _clip(call_type, 25),
        "calling_no": _clip(str(calling_no) if calling_no else None, 50),
        "called_no": _clip(str(called_no) if called_no else None, 50),
        "asondate": datetime.now().replace(microsecond=0),
    }


def _dup_key(provider: Optional[str], row: dict[str, str], target_phone: str) -> Optional[DupKey]:
    mapped = _map_cdr_record(provider, row, target_phone)
    if not mapped:
        return None
    return _norm_key(
        mapped["phone"],
        mapped["other"] or None,
        mapped["starttime"],
        mapped["duration"],
        mapped["incoming"],
    )


_SKIP_STAGING_TABLES = {"cdatpcsuspect", "cdr_pipeline_jobs"}
_KEY_EXISTS_SQL = """
    SELECT k.phone, k.other, k.starttime, k.duration, k.incoming
    FROM _cdr_preview_keys k
    WHERE EXISTS (
        SELECT 1
        FROM {table} t
        WHERE t.phone = k.phone
          AND NULLIF(BTRIM(COALESCE(t.other, '')), '')
              IS NOT DISTINCT FROM NULLIF(BTRIM(COALESCE(k.other, '')), '')
          AND t.starttime = k.starttime
          AND t.duration IS NOT DISTINCT FROM k.duration
          AND t.incoming IS NOT DISTINCT FROM k.incoming
    )
"""


def _fetch_matching_keys(cur: Any, table_sql: str) -> set[DupKey]:
    found: set[DupKey] = set()
    cur.execute(_KEY_EXISTS_SQL.format(table=table_sql))
    for phone, other, starttime, duration, incoming in cur.fetchall():
        found.add(_norm_key(str(phone), other, starttime, int(duration), int(incoming)))
    return found


def _staging_data_tables(cur: Any, schema: str) -> list[str]:
    cur.execute(
        """
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = %s AND table_type = 'BASE TABLE'
        ORDER BY table_name
        """,
        (schema,),
    )
    names: list[str] = []
    for (name,) in cur.fetchall():
        if name in _SKIP_STAGING_TABLES:
            continue
        if re.fullmatch(r"[A-Za-z_][A-Za-z0-9_]*", name):
            names.append(name)
    return names


def _existing_keys(keys: list[DupKey]) -> tuple[set[DupKey], set[DupKey], bool, str]:
    """Return (in live DB, in staging tables, ok, message)."""
    if not keys:
        return set(), set(), True, ""
    try:
        from config import settings
        from db import _ident, db_connection
        from psycopg2.extras import execute_values

        unique = list(dict.fromkeys(keys))
        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute(
                    """
                    SELECT 1
                    FROM information_schema.tables
                    WHERE table_schema = 'public' AND table_name = 'cdatpcsuspect'
                    """
                )
                if cur.fetchone() is None:
                    return set(), set(), False, "public.cdatpcsuspect was not found."
                cur.execute(
                    """
                    CREATE TEMP TABLE _cdr_preview_keys (
                        phone varchar(25) NOT NULL,
                        other varchar(50),
                        starttime timestamp NOT NULL,
                        duration numeric(5, 0) NOT NULL,
                        incoming smallint NOT NULL
                    ) ON COMMIT DROP
                    """
                )
                execute_values(
                    cur,
                    "INSERT INTO _cdr_preview_keys (phone, other, starttime, duration, incoming) VALUES %s",
                    unique,
                    page_size=1000,
                )
                in_db = _fetch_matching_keys(cur, f"{_ident('public')}.{_ident('cdatpcsuspect')}")
                in_staging: set[DupKey] = set()
                schema_sql = _ident(settings.pcsuspect_schema)
                for table in _staging_data_tables(cur, settings.pcsuspect_schema):
                    in_staging |= _fetch_matching_keys(cur, f"{schema_sql}.{_ident(table)}")
        return in_db, in_staging, True, ""
    except Exception as exc:
        logger.warning("CDR preview compare failed: %s", exc)
        return set(), set(), False, "Could not compare with staging or the live database."


def _record_key(record: dict[str, Any]) -> DupKey:
    return _norm_key(
        str(record.get("phone") or ""),
        record.get("other"),
        record["starttime"],
        int(record.get("duration") or 0),
        int(record.get("incoming") or 0),
    )


def filter_new_cdr_records(records: list[dict[str, Any]]) -> tuple[list[dict[str, Any]], dict[str, int]]:
    """Keep rows that appear once in the file and are not in live DB or existing staging."""
    rec_keys = [_record_key(rec) for rec in records]
    freq = Counter(rec_keys)
    dup_keys = {key for key, count in freq.items() if count > 1}
    duplicates = sum(count for key, count in freq.items() if count > 1)

    unique_once: list[dict[str, Any]] = []
    unique_keys: list[DupKey] = []
    for rec, key in zip(records, rec_keys):
        if key in dup_keys:
            continue
        unique_once.append(rec)
        unique_keys.append(key)

    in_db, in_staging, ok, message = _existing_keys(unique_keys)
    if not ok:
        raise ValueError(message or "Could not compare with staging or the live database.")

    kept: list[dict[str, Any]] = []
    already_in_db = 0
    already_in_staging = 0
    for rec, key in zip(unique_once, unique_keys):
        if key in in_db:
            already_in_db += 1
            continue
        if key in in_staging:
            already_in_staging += 1
            continue
        kept.append(rec)
    return kept, {
        "source": len(records),
        "duplicates": duplicates,
        "already_in_db": already_in_db,
        "already_in_staging": already_in_staging,
        "new": len(kept),
    }


def load_csv_rows(text: str, *, filename: str = "upload.csv") -> dict[str, Any]:
    """Parse a CDR CSV into provider, columns, raw rows, and header metadata."""
    if not text.strip():
        raise ValueError("Uploaded file is empty.")

    lines = text.splitlines()
    provider = detect_provider_from_text(text)

    header_idx: Optional[int] = None
    columns: list[str] = []
    marker = HEADER_TEXT.get(provider or "", "")
    for i, line in enumerate(lines):
        if marker and marker in line:
            header_idx = i
            columns = [c.strip() for c in next(csv.reader([line]))]
            break
        for op, pattern in HEADER_MARKERS.items():
            if pattern.search(line):
                header_idx = i
                columns = [c.strip() for c in next(csv.reader([line]))]
                provider = provider or op
                break
        if header_idx is not None:
            break

    if header_idx is None or not columns:
        raise ValueError(
            "Could not find a CDR header row. Upload an Airtel, BSNL, Jio, or Vi CSV export."
        )

    metadata = _extract_metadata(lines[: header_idx + 1])
    if not metadata.get("target_phone"):
        metadata["target_phone"] = _phone_from_filename(filename)

    all_rows: list[dict[str, str]] = []
    for line in lines[header_idx + 1 :]:
        if not line.strip():
            continue
        parsed = next(csv.reader([line]))
        if _is_skip_row(parsed):
            continue
        cells = [_clean_cell(parsed[i] if i < len(parsed) else "") for i in range(len(columns))]
        all_rows.append(dict(zip(columns, cells)))

    return {
        "provider": provider,
        "columns": columns,
        "rows": all_rows,
        "metadata": metadata,
        "header_idx": header_idx,
        "filename": Path(filename).name,
    }


def mapped_cdr_records(text: str, *, filename: str = "upload.csv") -> list[dict[str, Any]]:
    parsed = load_csv_rows(text, filename=filename)
    provider = parsed["provider"]
    target_phone = parsed["metadata"].get("target_phone") or ""
    records: list[dict[str, Any]] = []
    for row in parsed["rows"]:
        mapped = _map_cdr_record(provider, row, target_phone)
        if mapped:
            records.append(mapped)
    return records


def preview_csv_bytes(
    raw: bytes,
    *,
    filename: str = "upload.csv",
    row_limit: int = _PREVIEW_ROW_LIMIT,
) -> dict[str, Any]:
    return preview_csv_text(decode_csv_bytes(raw), filename=filename, row_limit=row_limit)


def preview_csv_text(
    text: str,
    *,
    filename: str = "upload.csv",
    row_limit: int = _PREVIEW_ROW_LIMIT,
) -> dict[str, Any]:
    parsed = load_csv_rows(text, filename=filename)
    provider = parsed["provider"]
    columns = parsed["columns"]
    all_rows = parsed["rows"]
    metadata = parsed["metadata"]
    header_idx = parsed["header_idx"]

    if all_rows and not metadata.get("imei"):
        metadata["imei"] = _row_get(all_rows[0], "IMEI", "Imei", "imei")

    target_phone = metadata.get("target_phone") or ""
    if not metadata.get("subscriber_name") or not metadata.get("subscriber_address"):
        profile = _lookup_subscriber(target_phone)
        for field, value in profile.items():
            if value and not metadata.get(field):
                metadata[field] = value
    if metadata.get("city") or metadata.get("state") or metadata.get("pin"):
        loc = ", ".join(part for part in (metadata.get("city"), metadata.get("state"), metadata.get("pin")) if part)
        if loc and loc not in (metadata.get("subscriber_address") or ""):
            addr = metadata.get("subscriber_address") or ""
            metadata["subscriber_address"] = f"{addr}, {loc}".strip(", ") if addr else loc

    keys = [_dup_key(provider, row, target_phone) for row in all_rows]
    freq = Counter(key for key in keys if key is not None)
    dup_keys = {key for key, count in freq.items() if count > 1}
    unique_keys = list(freq.keys())
    existing, staged, db_checked, db_message = _existing_keys(unique_keys)

    def _status_for(key: Optional[DupKey]) -> str:
        if key and key in existing:
            return "in_db"
        if key and key in staged:
            return "in_staging"
        if key and key in dup_keys:
            return "duplicate"
        return "new"

    all_status = [_status_for(key) for key in keys]
    already_in_db = all_status.count("in_db")
    already_in_staging = all_status.count("in_staging")
    in_file_duplicates = all_status.count("duplicate")
    new_records = all_status.count("new")

    rows = all_rows if row_limit <= 0 else all_rows[:row_limit]
    blank = {"", "-", "--", "---", "-----"}
    display_columns = [
        col for col in columns
        if any((row.get(col) or "").strip() not in blank for row in rows)
    ] or columns
    row_status = all_status if row_limit <= 0 else all_status[:row_limit]

    labels = {"airtel": "Airtel", "bsnl": "BSNL", "jio": "Jio", "vi": "Vi"}
    payload = {
        "ok": True,
        "provider": provider,
        "provider_label": labels.get(provider or "", provider or "Unknown"),
        "filename": Path(filename).name,
        "header_line": header_idx + 1,
        "columns": display_columns,
        "all_columns": columns,
        "rows": [{col: row.get(col, "") for col in display_columns} for row in rows],
        "row_status": row_status,
        "preview_count": len(rows),
        "total_records": len(all_rows),
        "truncated": len(all_rows) > len(rows),
        "in_file_duplicates": in_file_duplicates,
        "unique_records": sum(1 for count in freq.values() if count == 1),
        "already_in_db": already_in_db,
        "already_in_staging": already_in_staging,
        "new_records": new_records,
        "db_checked": db_checked,
        "db_message": db_message,
        **metadata,
    }
    try:
        from cdr.staging import json_schema_row, schema_columns

        spec = schema_columns()
        names = [item["name"] for item in spec]
        sample_rows: list[dict[str, Any]] = []
        sample_status: list[str] = []
        for index, row in enumerate(rows):
            mapped = _map_cdr_record(provider, row, target_phone)
            if not mapped:
                continue
            sample_rows.append(json_schema_row(mapped, index + 1))
            sample_status.append(row_status[index])
        payload["csv_columns"] = display_columns
        payload["columns"] = names
        payload["rows"] = sample_rows
        payload["row_status"] = sample_status
        payload["preview_count"] = len(sample_rows)
        payload["schema_columns"] = spec
        payload["schema_rows"] = sample_rows
    except Exception as exc:
        logger.warning("Could not attach schema sample: %s", exc)
    return payload
