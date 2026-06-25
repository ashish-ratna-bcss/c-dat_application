from __future__ import annotations
import re
from pathlib import Path
OPERATOR_PATTERNS = {'jio': re.compile('jio', re.IGNORECASE), 'bsnl': re.compile('bsnl', re.IGNORECASE), 'vi': re.compile('(?:^|[_\\-.])vi(?:[_\\-.]|\\.|$)|vodafone\\s*idea', re.IGNORECASE), 'airtel': re.compile('airtel', re.IGNORECASE)}

def detect_operator(file_path: str | Path) -> str:
    name = Path(file_path).name.lower()
    matches = [op for op, pattern in OPERATOR_PATTERNS.items() if pattern.search(name)]
    if len(matches) == 1:
        return matches[0]
    if len(matches) > 1:
        raise ValueError(f"Ambiguous operator in filename '{name}': {matches}")
    raise ValueError(f"Cannot detect operator from filename '{name}'. Expected one of: jio, bsnl, vi, airtel.")

def extract_phone_from_filename(file_path: str | Path) -> str | None:
    name = Path(file_path).stem
    m = re.match('^(\\d{10,15})', name)
    if m:
        return m.group(1)
    m = re.search('_(\\d{10})_', name)
    if m:
        return m.group(1)
    parts = re.findall('\\d{10,15}', name)
    if parts:
        return parts[-1]
    return None
