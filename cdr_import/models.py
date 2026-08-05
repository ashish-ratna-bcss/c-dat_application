from __future__ import annotations
from dataclasses import dataclass, field
from datetime import datetime
from typing import Any, Optional

@dataclass
class CdrRecord:
    phone: Optional[str]
    other: Optional[str]
    starttime: datetime
    duration: int
    incoming: int
    imeinumber: int
    imsinumber: Optional[int] = None
    celltowerid: Optional[str] = None
    otherinfo: Optional[str] = None
    tower_key: Optional[int] = None
    provider_key: int = 0
    state_key: Optional[int] = None
    first_cellid: Optional[str] = None
    last_cellid: Optional[str] = None
    roaming_nw: Optional[str] = None
    call_type: Optional[str] = None
    calling_no: Optional[str] = None
    called_no: Optional[str] = None
    asondate: Optional[datetime] = None
    source_row_number: int = 0
    raw: dict[str, Any] = field(default_factory=dict)

@dataclass
class ParseResult:
    operator: str
    target_phone: Optional[str]
    header_line_no: int
    records: list[CdrRecord]
    warnings: list[str] = field(default_factory=list)
