#!/usr/bin/env python3
"""Rebuild scripts/nginx-url-alias-map.conf from docs/url_alias_manifest.csv."""
from __future__ import annotations

import csv
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
MANIFEST = ROOT / "docs" / "url_alias_manifest.csv"
OUT = ROOT / "scripts" / "nginx-url-alias-map.conf"


def main() -> int:
    rows = list(csv.DictReader(MANIFEST.open()))
    entries: list[tuple[str, str]] = []
    seen: set[str] = set()
    for r in rows:
        if r.get("status") != "ok":
            continue
        sp = r["symlink_path"]
        canon = r["canonical"]
        if "/" in sp or "/" in canon:
            continue
        if not sp.lower().endswith((".html", ".htm", ".php")):
            continue
        key = "/" + sp
        val = "/" + canon
        if key == val or key in seen:
            continue
        seen.add(key)
        entries.append((key, val))
    entries.sort()
    lines = [
        "# Auto-generated from docs/url_alias_manifest.csv — do not edit by hand.",
        "# In nginx http {}: include .../scripts/nginx-url-alias-map.conf;",
        "map $uri $cdat_canonical_uri {",
        '    default "";',
    ]
    for key, val in entries:
        lines.append(f"    {key} {val};")
    lines.append("}")
    OUT.write_text("\n".join(lines) + "\n")
    print(f"wrote {OUT} entries={len(entries)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
