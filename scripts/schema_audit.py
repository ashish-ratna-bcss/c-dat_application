#!/usr/bin/env python3
"""Compare live PostgreSQL schema vs sql/*.sql for tables referenced by modules/."""
from __future__ import annotations

import os
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT))

try:
    import psycopg2
except ImportError:
    print("psycopg2 required: pip install psycopg2-binary", file=sys.stderr)
    sys.exit(1)


def load_env() -> dict[str, str]:
    env: dict[str, str] = {}
    env_file = ROOT / ".env"
    if env_file.is_file():
        for line in env_file.read_text().splitlines():
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            k, v = line.split("=", 1)
            env[k.strip()] = v.strip().strip("\"'")
    return env


def tables_from_modules() -> set[str]:
    pat = re.compile(
        r"\b(?:FROM|JOIN|INTO|UPDATE|TABLE)\s+([a-z_][a-z0-9_]*)",
        re.I,
    )
    found: set[str] = set()
    for php in (ROOT / "modules").rglob("*.php"):
        if php.name in ("sqlsrv_compat.php", "cdr_enrichment_sql.php"):
            continue
        text = php.read_text(errors="replace")
        for m in pat.finditer(text):
            name = m.group(1).lower()
            if name not in ("select", "where", "temp", "public", "case", "left", "inner", "outer"):
                found.add(name)
    return found


def columns_from_sql_files() -> dict[str, set[str]]:
    expected: dict[str, set[str]] = {}
    for sql_path in (ROOT / "sql").glob("*.sql"):
        if sql_path.name in ("fdw_setup.sql", "mssql_to_postgres_migration.sql"):
            continue
        text = sql_path.read_text(errors="replace")
        for m in re.finditer(
            r"CREATE\s+(?:TABLE|VIEW)\s+(?:IF\s+NOT\s+EXISTS\s+)?([a-z_][a-z0-9_]*)",
            text,
            re.I,
        ):
            tbl = m.group(1).lower()
            expected.setdefault(tbl, set())
        for m in re.finditer(
            r"CREATE\s+(?:TABLE|VIEW)\s+(?:IF\s+NOT\s+EXISTS\s+)?([a-z_][a-z0-9_]*)\s*\((.*?)\);",
            text,
            re.I | re.S,
        ):
            tbl = m.group(1).lower()
            body = m.group(2)
            cols = set()
            for line in body.splitlines():
                line = line.strip().rstrip(",")
                if not line or line.upper().startswith(("CONSTRAINT", "PRIMARY", "UNIQUE", "FOREIGN", "CHECK")):
                    continue
                cm = re.match(r"([a-z_][a-z0-9_]*)", line, re.I)
                if cm:
                    cols.add(cm.group(1).lower())
            if cols:
                expected[tbl] = cols
    return expected


def main() -> int:
    env = load_env()
    host = env.get("CDR_DB_HOST", "127.0.0.1")
    port = env.get("CDR_DB_PORT", "5432")
    db = env.get("CDR_DB_NAME", "CDATDUPL_DB")
    user = env.get("CDR_DB_USER", "postgres")
    password = env.get("CDR_DB_PASSWORD", "")

    module_tables = tables_from_modules()
    expected_cols = columns_from_sql_files()

    conn = psycopg2.connect(
        host=host, port=port, dbname=db, user=user, password=password, connect_timeout=15
    )
    cur = conn.cursor()

    cur.execute(
        """
        SELECT table_name, column_name, data_type
        FROM information_schema.columns
        WHERE table_schema = 'public'
        ORDER BY table_name, ordinal_position
        """
    )
    live: dict[str, dict[str, str]] = {}
    for tbl, col, dtype in cur.fetchall():
        t = tbl.lower()
        live.setdefault(t, {})[col.lower()] = dtype

    cur.execute(
        """
        SELECT foreign_table_name FROM information_schema.foreign_tables
        WHERE foreign_table_schema = 'public'
        """
    )
    fdw_tables = {r[0].lower() for r in cur.fetchall()}

    cur.execute(
        """
        SELECT column_name, data_type FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = 'cdatpcsuspect'
        AND column_name = 'ucid'
        """
    )
    ucid_row = cur.fetchone()

    print("# Schema audit report\n")
    print(f"Database: {db} @ {host}:{port}\n")

    missing = sorted(t for t in module_tables if t not in live and not t.startswith("temp_"))
    print("## Tables referenced in modules/ but missing in public schema")
    if missing:
        for t in missing:
            in_sql = "yes" if t in expected_cols else "no"
            print(f"- {t} (in sql/*.sql: {in_sql})")
    else:
        print("- (none)")

    print("\n## FDW foreign tables")
    for t in sorted(fdw_tables):
        print(f"- {t}")

    print("\n## Column type mismatches (sql/*.sql vs live, sampled)")
    mismatches = []
    for tbl, cols in sorted(expected_cols.items()):
        if tbl not in live:
            continue
        for col in cols:
            if col not in live[tbl]:
                mismatches.append((tbl, col, "missing in live", ""))
    if ucid_row:
        print(f"- cdatpcsuspect.ucid live type: {ucid_row[1]}")
    else:
        print("- cdatpcsuspect.ucid: column not found")

    for tbl, col, note, _ in mismatches[:40]:
        print(f"- {tbl}.{col}: {note}")
    if len(mismatches) > 40:
        print(f"- ... and {len(mismatches) - 40} more")

    conn.close()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
