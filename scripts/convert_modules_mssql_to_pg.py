#!/usr/bin/env python3
"""One-time MSSQL -> PostgreSQL string cleanup for modules/*.php."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MODULES = ROOT / "modules"

SKIP = {"sqlsrv_compat.php", "cdr_enrichment_sql.php"}


def convert_sql(sql: str) -> str:
    q = sql
    q = re.sub(r"\bJRMS\.\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bFORMS\.\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bCDATDUPL\.\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bCDATDUPL\.DBO\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bCDATDUPL\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bdbo\.([A-Za-z0-9_]+)", r"\1", q, flags=re.I)
    q = re.sub(r"\bWITH\s*\(\s*NOLOCK\s*\)", "", q, flags=re.I)
    q = re.sub(r"\bISNULL\s*\(", "COALESCE(", q, flags=re.I)
    q = re.sub(r"\bGETDATE\s*\(\s*\)", "CURRENT_TIMESTAMP", q, flags=re.I)
    q = re.sub(r"\bLEN\s*\(", "LENGTH(", q, flags=re.I)
    q = re.sub(
        r"CHARINDEX\s*\(\s*'([^']*)'\s*,\s*([^)]+)\)",
        r"POSITION('\1' IN \2)",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"CONVERT\s*\(\s*IMAGE\s*,\s*([^)]+)\)",
        r"\1",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"CONVERT\s*\(\s*VARCHAR\s*\(\s*20\s*\)\s*,\s*CONVERT\s*\(\s*DATE\s*,\s*([^)]+)\)",
        r"TO_CHAR(\1::date, 'YYYY-MM-DD')",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"CONVERT\s*\(\s*VARCHAR\s*\(\s*20\s*\)\s*,\s*([^)]+)\)",
        r"(\1)::varchar",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"CONVERT\s*\(\s*DATE\s*,\s*([^)]+)\)",
        r"(\1)::date",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"ISNUMERIC\s*\(\s*([^)]+)\s*\)\s*=\s*'1'",
        r"\1 ~ '^[0-9]+$'",
        q,
        flags=re.I,
    )
    q = re.sub(r"^\s*SET\s+DATEFORMAT\s+\w+\s*", "", q, flags=re.I)
    q = re.sub(r"\bSELECT\s+TOP\s+(\d+)\b", r"SELECT", q, flags=re.I)
    q = re.sub(
        r"OFFSET\s+\?\s+ROWS\s+FETCH\s+NEXT\s+\?\s+ROWS\s+ONLY",
        "LIMIT ? OFFSET ?",
        q,
        flags=re.I,
    )
    q = re.sub(r"#([A-Za-z0-9_]+)", r"temp_\1", q)
    q = re.sub(r"\[dbo\]\.", "", q, flags=re.I)
    q = re.sub(r"\[([A-Za-z0-9_]+)\]", r"\1", q)
    q = re.sub(
        r"convert\s*\(\s*char\s*\(\s*10\s*\)\s*,\s*([^,]+)\s*,\s*121\s*\)\s+between",
        r"(\1)::date BETWEEN",
        q,
        flags=re.I,
    )
    q = re.sub(
        r"INTO\s+temp_([A-Za-z0-9_]+)\s+FROM",
        r"INTO temp_\1 FROM",
        q,
        flags=re.I,
    )
    return q


def convert_file(path: Path) -> bool:
    text = path.read_text(encoding="utf-8", errors="replace")
    orig = text

    text = re.sub(
        r"\n\s*\$serverName\s*=.*?;\s*\n\s*\$connectionInfo\s*=.*?;\s*\n",
        "\n",
        text,
    )
    text = re.sub(
        r"(require_once|include_once|require|include)\s+[^;]*sqlsrv_compat\.php['\"];\s*\n?",
        "",
        text,
        flags=re.I,
    )
    text = re.sub(
        r"if\s*\(\s*\$conn\s*===\s*false\s*\)\s*\{[^}]+\}\s*\n",
        "",
        text,
    )

    def repl_string(m: re.Match[str]) -> str:
        full = m.group(0)
        quote = full[0]
        body = full[1:-1]
        if not re.search(
            r"SELECT|INSERT|UPDATE|DELETE|CREATE|FROM|JOIN|WHERE",
            body,
            re.I,
        ):
            return full
        return quote + convert_sql(body) + quote

    text = re.sub(r'("(?:\\.|[^"\\])*")', repl_string, text)
    text = re.sub(r"('(?:\\.|[^'\\])*')", repl_string, text)

    if text != orig:
        path.write_text(text, encoding="utf-8")
        return True
    return False


def main() -> None:
    changed = []
    for path in sorted(MODULES.rglob("*.php")):
        if path.name in SKIP:
            continue
        if convert_file(path):
            changed.append(path.relative_to(ROOT))
    print(f"Updated {len(changed)} files")
    for p in changed:
        print(f"  {p}")


if __name__ == "__main__":
    main()
