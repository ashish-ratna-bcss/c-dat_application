from __future__ import annotations
from pathlib import Path
from ..config import PROVIDER_KEYS
from .airtel import AirtelParser
from .bsnl import BsnlParser
from .jio import JioParser
from .vi import ViParser
PARSERS = {'airtel': AirtelParser, 'bsnl': BsnlParser, 'vi': ViParser, 'jio': JioParser}

def get_parser(operator: str, file_path: Path, target_phone: str | None):
    cls = PARSERS[operator]
    provider_key = PROVIDER_KEYS[operator]
    return cls(file_path, target_phone, provider_key)
