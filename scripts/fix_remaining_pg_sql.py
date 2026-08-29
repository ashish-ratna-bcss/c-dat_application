#!/usr/bin/env python3
"""Second-pass MSSQL→PG fixes for modules/*.php SQL strings."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULES = ROOT / "modules"
SKIP = {"sqlsrv_compat.php", "cdr_enrichment_sql.php"}


def fix_sql(text: str) -> str:
    # Database/table prefixes
    text = re.sub(r"\bCDATDUPL\.(?:DBO\.)?CDAT_RTA\b", "cdat_rta", text, flags=re.I)
    text = re.sub(r"\bCDATDUPL\.\.([A-Za-z0-9_]+)\b", lambda m: m.group(1).lower(), text, flags=re.I)

    # Temp table naming
    text = re.sub(r"\bCREATE TABLE (temp_[A-Za-z0-9_]+)", r"CREATE TEMP TABLE \1", text, flags=re.I)
    text = re.sub(r"\(PHONE NVARCHAR\s*\(\s*20\s*\)\s*NULL\)", "(phone varchar(20))", text, flags=re.I)
    text = re.sub(r"\b#([A-Za-z0-9_]+)\b", r"temp_\1", text)

    # Functions
    text = re.sub(r"\bLEN\s*\(", "LENGTH(", text, flags=re.I)
    text = re.sub(
        r"CONVERT\s*\(\s*CHAR\s*\(\s*8\s*\)\s*,\s*(\w+)\s*,\s*108\s*\)",
        r"TO_CHAR(\1, 'HH24:MI:SS')",
        text,
        flags=re.I,
    )
    text = re.sub(
        r"CONVERT\s*\(\s*VARCHAR(?:\s*\(\s*\d+\s*\))?\s*,\s*([^,)]+)\s*,\s*106\s*\)",
        r"TO_CHAR(\1::timestamp, 'DD Mon YYYY')",
        text,
        flags=re.I,
    )
    text = re.sub(
        r"CONVERT\s*\(\s*VARCHAR(?:\s*\(\s*\d+\s*\))?\s*,\s*([^,)]+)\s*,\s*20\s*\)",
        r"TO_CHAR(\1::timestamp, 'YYYY-MM-DD HH24:MI:SS')",
        text,
        flags=re.I,
    )
    text = re.sub(
        r"CONVERT\s*\(\s*VARCHAR(?:\s*\(\s*\d+\s*\))?\s*,\s*([^,)]+)\s*\)",
        r"TO_CHAR(\1::timestamp, 'YYYY-MM-DD HH24:MI:SS')",
        text,
        flags=re.I,
    )
    text = re.sub(r"CONVERT\s*\(\s*IMAGE\s*,\s*(\w+)\s*\)", r"\1", text, flags=re.I)

    # String concat in SQL: 'x' + col + 'y' -> 'x' || col || 'y'
    for _ in range(8):
        new = re.sub(
            r"((?:'[^']*'|\w+(?:\.\w+)?|\([^)]*\)|COALESCE\([^)]*\)))\s*\+\s*('[^']*'|\w+(?:\.\w+)?|\([^)]*\)|COALESCE\([^)]*\))",
            r"\1 || \2",
            text,
        )
        if new == text:
            break
        text = new

    text = re.sub(r"PHONEPREFIX\s*\+\s*'%'", "phoneprefix || '%'", text, flags=re.I)
    text = re.sub(r"'00'\s*\+\s*", "'00' || ", text)
    text = re.sub(r"\bLIKE\s*'\[6-9\]%'", "LIKE '6%' OR A.OTHER LIKE '7%' OR A.OTHER LIKE '8%' OR A.OTHER LIKE '9%'", text, flags=re.I)

    # SELECT … INTO temp_x (single-line)
    text = re.sub(
        r"SELECT\s+(.+?)\s+INTO\s+(temp_\w+)\s*;",
        r"CREATE TEMP TABLE \2 AS SELECT \1;",
        text,
        flags=re.I | re.S,
    )

    # Broken inline CREATE TEMP inside CASE
    text = re.sub(
        r"CASE WHEN A\.PHONE IN \(CREATE TEMP TABLE (temp_\w+) AS SELECT PHONE FROM (temp_\w+)\)",
        r"CASE WHEN A.PHONE IN (SELECT phone FROM \2)",
        text,
        flags=re.I,
    )

    # FOR XML PATH → string_agg (common_cnts pattern)
    text = re.sub(
        r"\(SELECT PHONE \|\| ', '\s+FROM (temp_\w+) US\s+WHERE US\.OTHER = SS\.OTHER FOR XML PATH\(''\)\)",
        r"(SELECT string_agg(phone || ', ', '' ORDER BY phone) FROM \1 us WHERE us.other = ss.other)",
        text,
        flags=re.I,
    )
    text = re.sub(
        r"\(SELECT PHONE \+\s*', '\s+FROM (temp_\w+) US\s+WHERE US\.OTHER = SS\.OTHER FOR XML PATH\(''\)\)",
        r"(SELECT string_agg(phone || ', ', '' ORDER BY phone) FROM \1 us WHERE us.other = ss.other)",
        text,
        flags=re.I,
    )

    # Subquery total in sql4 broken pattern
    text = re.sub(
        r"\(CREATE TEMP TABLE common_numbertable3 AS SELECT SUM\(COUNT1\) FROM (temp_\w+) XX WHERE XX\.OTHER = SS\.OTHER\)",
        r"(SELECT SUM(count1) FROM \1 xx WHERE xx.other = ss.other)",
        text,
        flags=re.I,
    )

    return text


def main() -> None:
    n = 0
    for path in MODULES.rglob("*.php"):
        if path.name in SKIP:
            continue
        orig = path.read_text(encoding="utf-8", errors="replace")
        text = fix_sql(orig)
        if text != orig:
            path.write_text(text, encoding="utf-8")
            n += 1
            print(path.relative_to(ROOT))
    print(f"fixed {n} files")


if __name__ == "__main__":
    main()
