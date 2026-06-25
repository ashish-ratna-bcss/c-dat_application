from __future__ import annotations
import csv
import re
from abc import ABC, abstractmethod
from datetime import datetime
from decimal import Decimal, InvalidOperation
from pathlib import Path
from typing import Any, Optional
from ..models import CdrRecord, ParseResult
EMPTY_MARKERS = {'', '-', '---', '-----', 'N/A', 'NA', 'NULL', 'null'}

def clean(value: Any) -> str:
    if value is None:
        return ''
    text = str(value).strip()
    while len(text) >= 2 and (text.startswith("'") and text.endswith("'") or (text.startswith('"') and text.endswith('"'))):
        text = text[1:-1].strip()
    text = text.replace("''", "'")
    return text.strip()

def is_empty(value: Any) -> bool:
    return clean(value) in EMPTY_MARKERS

def to_int(value: Any, default: int=0) -> int:
    text = clean(value)
    if not text or text in EMPTY_MARKERS:
        return default
    text = text.replace(',', '')
    if re.fullmatch('-?\\d+\\.0+', text):
        text = text.split('.', 1)[0]
    if is_scientific_notation(text):
        try:
            return int(Decimal(text))
        except (InvalidOperation, ValueError):
            return default
    if not re.search('\\d', text):
        return default
    try:
        return int(float(text))
    except (ValueError, TypeError):
        digits = re.sub('\\D', '', text)
        return int(digits) if digits else default

def to_bigint_optional(value: Any) -> Optional[int]:
    text = clean(value)
    if not text or text in EMPTY_MARKERS:
        return None
    return to_int(text, default=0)

def is_scientific_notation(text: str) -> bool:
    return bool(re.fullmatch('[+-]?\\d+(?:\\.\\d+)?[eE][+-]?\\d+', text.strip()))

def to_phone(value: Any) -> Optional[str]:
    text = clean(value)
    if not text or text in EMPTY_MARKERS:
        return None
    if is_scientific_notation(text):
        text = str(int(Decimal(text)))
    text = re.sub('[^\\d+]', '', text)
    if text.startswith('91') and len(text) > 10:
        text = text[2:]
    return text or None

def parse_datetime(date_part: str, time_part: str) -> datetime:
    date_s = clean(date_part)
    time_s = clean(time_part)
    combos = [f'{date_s} {time_s}', f'{date_s}T{time_s}']
    formats = ['%d/%m/%Y %H:%M:%S', '%d-%m-%Y %H:%M:%S', '%d/%m/%Y %H:%M', '%d-%m-%Y %H:%M', '%d-%m-%Y %H:%M:%S', '%Y-%m-%d %H:%M:%S', '%Y-%m-%d %H:%M']
    for combo in combos:
        for fmt in formats:
            try:
                return datetime.strptime(combo, fmt)
            except ValueError:
                continue
    raise ValueError(f'Unparseable date/time: date={date_part!r} time={time_part!r}')

def is_separator_row(row: list[str]) -> bool:
    joined = ''.join((clean(c) for c in row))
    return not joined or set(joined) <= {'-'}

class BaseCdrParser(ABC):
    operator: str

    def __init__(self, file_path: Path, target_phone: Optional[str], provider_key: int):
        self.file_path = file_path
        self.target_phone = target_phone
        self.provider_key = provider_key

    def parse(self) -> ParseResult:
        lines = self.file_path.read_text(encoding='utf-8', errors='replace').splitlines()
        header_idx, header = self.find_header(lines)
        records: list[CdrRecord] = []
        warnings: list[str] = []
        row_no = 0
        for line in lines[header_idx + 1:]:
            if not line.strip():
                continue
            row = next(csv.reader([line]))
            if is_separator_row(row):
                continue
            if self.should_skip_row(row):
                continue
            if self.is_skippable_data_row(row, header):
                continue
            row_no += 1
            try:
                record = self.map_row(row, header, row_no)
                if record is not None:
                    records.append(record)
            except Exception as exc:
                warnings.append(f'Row {row_no}: {exc}')
        return ParseResult(operator=self.operator, target_phone=self.target_phone, header_line_no=header_idx + 1, records=records, warnings=warnings)

    def find_header(self, lines: list[str]) -> tuple[int, list[str]]:
        for i, line in enumerate(lines):
            marker = self.header_marker()
            if marker in line:
                return (i, next(csv.reader([line])))
        raise ValueError(f'{self.operator}: header row not found in {self.file_path.name}')

    @abstractmethod
    def header_marker(self) -> str:
        raise NotImplementedError

    def should_skip_row(self, row: list[str]) -> bool:
        first = clean(row[0]) if row else ''
        return first.lower().startswith('disclaimer')

    @abstractmethod
    def map_row(self, row: list[str], header: list[str], source_row_number: int) -> Optional[CdrRecord]:
        raise NotImplementedError

    def is_skippable_data_row(self, row: list[str], header: list[str]) -> bool:
        return False

    def row_dict(self, row: list[str], header: list[str]) -> dict[str, str]:
        data: dict[str, str] = {}
        for idx, col in enumerate(header):
            key = clean(col)
            if not key:
                continue
            data[key] = row[idx] if idx < len(row) else ''
        return data

    def base_record(self, *, phone: Optional[str], other: Optional[str], starttime: datetime, duration: int, incoming: int, imeinumber: int, source_row_number: int, raw: dict[str, str], imsinumber: Optional[int]=None, celltowerid: Optional[str]=None, first_cellid: Optional[str]=None, last_cellid: Optional[str]=None, roaming_nw: Optional[str]=None, call_type: Optional[str]=None, calling_no: Optional[str]=None, called_no: Optional[str]=None, otherinfo: Optional[str]=None) -> CdrRecord:
        return CdrRecord(phone=phone, other=other, starttime=starttime, duration=duration, incoming=incoming, imeinumber=imeinumber, imsinumber=imsinumber, celltowerid=celltowerid or first_cellid, otherinfo=otherinfo, provider_key=self.provider_key, first_cellid=first_cellid, last_cellid=last_cellid, roaming_nw=roaming_nw, call_type=call_type, calling_no=calling_no, called_no=called_no, source_row_number=source_row_number, raw=raw)
