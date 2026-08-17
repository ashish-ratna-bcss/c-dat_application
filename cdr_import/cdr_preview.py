#!/usr/bin/env python3
"""Parse and normalize a CDR file for read-only upload preview (JSON to stdout)."""
from __future__ import annotations
import json
import sys
from pathlib import Path

_REPO_ROOT = Path(__file__).resolve().parents[1]
if str(_REPO_ROOT) not in sys.path:
    sys.path.insert(0, str(_REPO_ROOT))

from cdr_import.service import CdrImportError, preview_file


def main() -> int:
    if len(sys.argv) < 2:
        print('Usage: cdr_preview.py <file.csv> [operator] [limit]', file=sys.stderr)
        return 2
    src = Path(sys.argv[1]).resolve()
    operator = None
    limit = 150
    if len(sys.argv) >= 3 and sys.argv[2] not in ('', 'auto', 'AUTO'):
        operator = sys.argv[2]
    if len(sys.argv) >= 4:
        limit = max(1, min(500, int(sys.argv[3])))
    try:
        result = preview_file(src, operator=operator, limit=limit)
    except (CdrImportError, ValueError) as exc:
        print(json.dumps({'ok': False, 'error': str(exc)}))
        return 1
    print(json.dumps({'ok': True, **result}, default=str))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
