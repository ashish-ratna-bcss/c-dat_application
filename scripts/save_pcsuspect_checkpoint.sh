#!/bin/bash
set -euo pipefail
python3 <<'PY'
import json
import re
from datetime import datetime, timezone
from pathlib import Path

log = Path("/tmp/migrate_pcsuspect.log").read_text(errors="replace")
matches = re.findall(r"cdatpcsuspect:\s*(\d+)\s*rows", log)
rows = int(matches[-1]) if matches else 0
cp = {
    "pg_db": "CDATDUPL_DB",
    "pg_table": "cdatpcsuspect",
    "mssql_db": "HYD_UNIT_CDAT",
    "mssql_table": "HYD_UNIT_CDATPCSUSPECT",
    "resume_key": "ucid",
    "pg_total_rows": rows,
    "pg_rows_at_start": rows,
    "rows_this_run": 0,
    "status": "stopped",
    "updated_at": datetime.now(timezone.utc).isoformat(),
    "note": "stopped 2026-09-02; resume with MIGRATE_RESUME=1",
}
path = Path("/tmp/migrate_checkpoint_cdatdupl_db_cdatpcsuspect.json")
path.write_text(json.dumps(cp, indent=2) + "\n")
print(f"checkpoint saved: {rows:,} rows")
print(path.read_text())
PY
