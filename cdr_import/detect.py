from __future__ import annotations
import re
from pathlib import Path
OPERATOR_PATTERNS = {'jio': re.compile('jio', re.IGNORECASE), 'bsnl': re.compile('bsnl', re.IGNORECASE), 'vi': re.compile('(?:^|[_\\-.])vi(?:[_\\-.]|\\.|$)|vodafone\\s*idea', re.IGNORECASE), 'airtel': re.compile('airtel', re.IGNORECASE)}

# Structural column-header strings unique to each operator's export. These are the most
# reliable in-file signal because real CDR files are named after the target phone, not the
# operator. Kept in sync with each parser's header_marker().
HEADER_MARKERS = {
    'airtel': re.compile(r'Target No\b', re.IGNORECASE),
    'vi': re.compile(r'Target\s*/A\s*PARTY\s*NUMBER', re.IGNORECASE),
    'bsnl': re.compile(r'\bSL_NO\b', re.IGNORECASE),
    'jio': re.compile(r'Calling Party Telephone Number', re.IGNORECASE),
}
# Provider banner text printed at the top of each export, used as a secondary signal.
PROVIDER_BANNERS = {
    'airtel': re.compile(r'BHARTI\s+AIRTEL|\bAIRTEL\b', re.IGNORECASE),
    'vi': re.compile(r'VODAFONE\s*IDEA|\bVODAFONE\b', re.IGNORECASE),
    'bsnl': re.compile(r'\bBSNL\b|BHARAT\s+SANCHAR', re.IGNORECASE),
    'jio': re.compile(r'RELIANCE\s+JIO|\bJIO\b', re.IGNORECASE),
}
_CONTENT_SCAN_LINES = 25


def detect_operator_from_content(file_path: str | Path) -> str | None:
    """Detect the operator by inspecting the file's header section.

    Returns the operator key, or None if it cannot be determined. Only the first
    few lines are scanned so data rows (which may mention other carriers) cannot
    cause false positives.
    """
    path = Path(file_path)
    try:
        with path.open('r', encoding='utf-8', errors='replace') as fh:
            head = [next(fh) for _ in range(_CONTENT_SCAN_LINES)]
    except StopIteration:
        head = locals().get('head', [])
    except OSError:
        return None
    blob = '\n'.join(head)
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


def detect_operator(file_path: str | Path) -> str:
    name = Path(file_path).name.lower()
    matches = [op for op, pattern in OPERATOR_PATTERNS.items() if pattern.search(name)]
    if len(matches) == 1:
        return matches[0]
    if len(matches) > 1:
        raise ValueError(f"Ambiguous operator in filename '{name}': {matches}")
    # Real exports are named after the target phone, so fall back to the file contents.
    from_content = detect_operator_from_content(file_path)
    if from_content:
        return from_content
    return 'airtel'

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
