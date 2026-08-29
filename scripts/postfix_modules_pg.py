#!/usr/bin/env python3
"""Fix artifacts from convert_modules_mssql_to_pg.py."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULES = ROOT / "modules"

REPLACEMENTS = [
    (r"TO_CHAR\((ADMISSION_TO_JAIL)::date, 'YYYY-MM-DD'\)\)", r"TO_CHAR(\1::date, 'YYYY-MM-DD') AS add_to_jail"),
    (r"TO_CHAR\((RELEASEDT)::date, 'YYYY-MM-DD'\)\)", r"TO_CHAR(\1::date, 'YYYY-MM-DD') AS release_date"),
    (r"\bJRMS_TOTAL_2012_TO_2017\b", "jrms_total_2012_to_2017"),
    (r"\bIR_PARTICULARS\b", "ir_particulars"),
    (r"\bPDACT_MAIN_TABLE\b", "pdact_main_table"),
    (r"\bIMAGE_TABLE\b", "image_table"),
    (r"CREATE TEMP TABLE COUNT AS", "CREATE TEMP TABLE temp_jrms_count AS"),
    (r"FROM temp_TEMP\b", "FROM temp_jrms_temp"),
    (r"JOIN temp_COUNT\b", "JOIN temp_jrms_count"),
    (r"CREATE TEMP TABLE temp_TEMP AS", "CREATE TEMP TABLE temp_jrms_temp AS"),
    (r"CREATE TEMP TABLE TEMP AS", "CREATE TEMP TABLE temp_jrms_temp AS"),
    (r"from temp_TEMP\b", "FROM temp_jrms_temp"),
    (r"NO_OF_TIMES_RELEASED\s+NO_OF_TIMES_RELEASED", "B.no_of_times_released"),
    (r",PHOTO PHOTO,", ", photo,"),
    (r"require_once CDAT_COMMON \. '/cdr_enrichment_sql\.php';\n", ""),
]


def main() -> None:
    n = 0
    for path in MODULES.rglob("*.php"):
        if path.name == "sqlsrv_compat.php":
            continue
        text = path.read_text(encoding="utf-8", errors="replace")
        orig = text
        for pat, repl in REPLACEMENTS:
            text = re.sub(pat, repl, text, flags=re.I)
        if text != orig:
            path.write_text(text, encoding="utf-8")
            n += 1
    print(f"post-fixed {n} files")


if __name__ == "__main__":
    main()
