#!/usr/bin/env python3
"""Start the local CDR worker: python3 worker.py"""
from __future__ import annotations
import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent
os.chdir(ROOT)
sys.path.insert(0, str(ROOT))

env_file = ROOT / ".env"
if env_file.exists():
    for line in env_file.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip())

from cdr_import.cli import main

if __name__ == "__main__":
    sys.argv = ["cdr_import", "worker", "--poll", "10", *sys.argv[1:]]
    raise SystemExit(main())
