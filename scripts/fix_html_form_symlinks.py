#!/usr/bin/env python3
"""Point .html/.HTML/.htm aliases at static form files instead of .PHP result pages."""

from __future__ import annotations

import os
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def static_form_for(base: str) -> str | None:
    for ext in (".html", ".HTML", ".htm", ".HTM"):
        candidate = ROOT / f"{base}{ext}"
        if candidate.is_file() and not candidate.is_symlink():
            return candidate.name
    return None


def main() -> int:
    fixed = 0
    for entry in ROOT.iterdir():
        if not entry.is_symlink():
            continue
        name = entry.name
        lower = name.lower()
        if not (lower.endswith(".html") or lower.endswith(".htm")):
            continue
        target = os.readlink(entry)
        if not target.lower().endswith(".php"):
            continue
        base = name.rsplit(".", 1)[0]
        static = static_form_for(base)
        if not static or os.path.normcase(target) == os.path.normcase(static):
            continue
        entry.unlink()
        entry.symlink_to(static)
        fixed += 1
        print(f"fixed {name}: {target} -> {static}")
    print(f"done, fixed {fixed} symlinks")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
