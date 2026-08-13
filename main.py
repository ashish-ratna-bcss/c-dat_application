#!/usr/bin/env python3
"""Start the local CDR API: python3 main.py"""
from __future__ import annotations
import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent
os.chdir(ROOT)
sys.path.insert(0, str(ROOT))
sys.path.insert(0, str(ROOT / "cdr-import-service"))

env_file = ROOT / ".env"
if env_file.exists():
    for line in env_file.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip())

os.environ.setdefault("CDR_API_HOST", "127.0.0.1")
os.environ.setdefault("CDR_API_PORT", "8088")

import uvicorn

if __name__ == "__main__":
    uvicorn.run(
        "app.main:app",
        host=os.environ["CDR_API_HOST"],
        port=int(os.environ["CDR_API_PORT"]),
        app_dir=str(ROOT / "cdr-import-service"),
    )
