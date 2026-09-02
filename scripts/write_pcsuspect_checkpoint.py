#!/usr/bin/env python3
"""Write pcsuspect migration checkpoint from log + Postgres MAX(ucid)."""
from __future__ import annotations

import json
import os
import re
import sys
from datetime import datetime, timezone
from pathlib import Path

import psycopg2

LOG = Path("/tmp/migrate_pcsuspect.log")
CP = Path("/tmp/migrate_checkpoint_cdatdupl_db_cdatpcsuspect.json")


def last_log_rows() -> int:
    if not LOG.is_file():
        return 0
    last = 0
    pat = re.compile(r"cdatpcsuspect:\s*(\d+)\s*rows")
    for line in LOG.read_text(errors="replace").splitlines():
        m = pat.search(line)
        if m:
            last = int(m.group(1))
    return last


def main() -> None:
    pw = os.environ.get("PGPASSWORD") or Path("/tmp/migrate_pgpass").read_text().strip()
    conn = psycopg2.connect(
        host="127.0.0.1", dbname="CDATDUPL_DB", user="postgres", password=pw
    )
    cur = conn.cursor()
    cur.execute("SET statement_timeout = '7200s'")
    cur.execute("SELECT COALESCE(MAX(ucid), 0) FROM cdatpcsuspect")
    max_ucid = cur.fetchone()[0]
    conn.close()

    rows = last_log_rows()
    cp = {
        "pg_db": "CDATDUPL_DB",
        "pg_table": "cdatpcsuspect",
        "mssql_db": os.environ.get("CDR_MSSQL_DB", "HYD_UNIT_CDAT"),
        "mssql_table": os.environ.get("CDR_MSSQL_TABLE", "HYD_UNIT_CDATPCSUSPECT"),
        "resume_key": "ucid",
        "resume_from": max_ucid,
        "last_ucid": max_ucid,
        "pg_rows_at_start": rows,
        "pg_total_rows": rows,
        "rows_this_run": 0,
        "status": "stopped",
        "updated_at": datetime.now(timezone.utc).isoformat(),
        "note": "checkpoint before manual stop",
    }
    CP.write_text(json.dumps(cp, indent=2) + "\n")
    print(f"checkpoint: {CP}")
    print(f"log_rows: {rows:,}")
    print(f"max_ucid: {max_ucid}")


if __name__ == "__main__":
    main()
