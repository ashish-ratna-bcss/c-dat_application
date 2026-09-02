"""PostgreSQL access for dataUpload. Credentials come from the repo-root .env."""
from __future__ import annotations

import logging
import re
from contextlib import contextmanager
from pathlib import Path
from typing import Any, Generator, Optional

import psycopg2
from psycopg2.extensions import ISOLATION_LEVEL_AUTOCOMMIT
from psycopg2.extensions import connection as PgConnection

from config import REPO_ROOT, load_db_config, settings

logger = logging.getLogger("dataUpload.db")

_ADMIN_DB = "postgres"
_IDENT_RE = re.compile(r"^[A-Za-z_][A-Za-z0-9_]*$")

_SCHEMA_FILES = (
    ("db_name", "cdr_db.sql"),
    ("db_name", "cdatdbschema.sql"),
    ("ir_db_name", "ir_db.sql"),
    ("jrms_db_name", "jrms_db.sql"),
    ("pdact_db_name", "pdact_db.sql"),
    ("rowdy_sheets_db_name", "rowdy_sheets_db.sql"),
    ("training_db_name", "training_db.sql"),
)


def _ident(name: str) -> str:
    if not _IDENT_RE.fullmatch(name):
        raise ValueError(f"Invalid database name: {name!r}")
    return '"' + name.replace('"', '""') + '"'


def _ensure_columns(cur: Any, schema: str, table: str, columns: tuple[tuple[str, str], ...]) -> None:
    for name, ddl in columns:
        cur.execute(
            """
            SELECT 1
            FROM information_schema.columns
            WHERE table_schema = %s AND table_name = %s AND column_name = %s
            """,
            (schema, table, name),
        )
        if cur.fetchone() is None:
            cur.execute(
                f"ALTER TABLE {_ident(schema)}.{_ident(table)} ADD COLUMN {_ident(name)} {ddl}"
            )
            logger.info("Added %s.%s.%s", schema, table, name)


def connect(dbname: Optional[str] = None) -> PgConnection:
    cfg = load_db_config()
    return psycopg2.connect(
        host=cfg["host"],
        port=cfg["port"],
        dbname=dbname or cfg["database"],
        user=cfg["user"],
        password=cfg["password"],
        connect_timeout=8,
    )


@contextmanager
def db_connection(dbname: Optional[str] = None) -> Generator[PgConnection, None, None]:
    conn = connect(dbname)
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def _schema_targets() -> list[tuple[str, Path]]:
    sql_dir = REPO_ROOT / "sql"
    targets: list[tuple[str, Path]] = []
    for attr, filename in _SCHEMA_FILES:
        dbname = getattr(settings, attr)
        path = sql_dir / filename
        if dbname and path.is_file():
            targets.append((dbname, path))
    return targets


def _database_exists(name: str) -> bool:
    conn = connect(_ADMIN_DB)
    conn.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)
    try:
        with conn.cursor() as cur:
            cur.execute("SELECT 1 FROM pg_database WHERE datname = %s", (name,))
            return cur.fetchone() is not None
    finally:
        conn.close()


def _create_database(name: str) -> None:
    conn = connect(_ADMIN_DB)
    conn.set_isolation_level(ISOLATION_LEVEL_AUTOCOMMIT)
    try:
        with conn.cursor() as cur:
            cur.execute(f"CREATE DATABASE {_ident(name)}")
    finally:
        conn.close()
    logger.info("Created database %s", name)


def _sql_statements(script: str) -> list[str]:
    statements: list[str] = []
    buf: list[str] = []
    for raw in script.splitlines():
        stripped = raw.strip()
        if not stripped or stripped.startswith("--"):
            continue
        buf.append(raw)
        if stripped.endswith(";"):
            text = "\n".join(buf).strip()
            if text:
                statements.append(text)
            buf = []
    leftover = "\n".join(buf).strip()
    if leftover:
        statements.append(leftover)
    return statements


def _apply_sql_file(dbname: str, path: Path) -> int:
    statements = _sql_statements(path.read_text(encoding="utf-8"))
    with db_connection(dbname) as conn:
        with conn.cursor() as cur:
            for stmt in statements:
                cur.execute(stmt)
    return len(statements)


_OLD_PCSUSPECT_SCHEMA = "cdatpcsuspectdb"
_LOGS_TABLE = "upload_activity_logs"
_JOBS_TABLE = "cdr_pipeline_jobs"


def _move_table_to_schema(cur: Any, table: str, dest_schema: str) -> Optional[str]:
    cur.execute(
        """
        SELECT table_schema
        FROM information_schema.tables
        WHERE table_name = %s
        ORDER BY CASE table_schema
            WHEN %s THEN 0
            WHEN 'public' THEN 1
            ELSE 2
        END
        """,
        (table, dest_schema),
    )
    rows = cur.fetchall()
    if not rows:
        return None
    current = str(rows[0][0])
    if current == dest_schema:
        return dest_schema
    cur.execute(
        """
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = %s AND table_name = %s
        """,
        (dest_schema, table),
    )
    if cur.fetchone():
        return dest_schema
    cur.execute(
        f"ALTER TABLE {_ident(current)}.{_ident(table)} SET SCHEMA {_ident(dest_schema)}"
    )
    logger.info("Moved %s.%s to schema %s", current, table, dest_schema)
    return dest_schema


def ensure_upload_schema() -> dict[str, Any]:
    """Jobs and upload history live in schema cdatupload, not public or staging."""
    schema = settings.upload_schema
    sql_path = REPO_ROOT / "sql" / "cdatdbschema.sql"
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(f"CREATE SCHEMA IF NOT EXISTS {_ident(schema)}")
            cur.execute("DROP TABLE IF EXISTS public.upload_approval_queue CASCADE")
            cur.execute("DROP TABLE IF EXISTS public.upload_staging_batches CASCADE")
            _move_table_to_schema(cur, _LOGS_TABLE, schema)
            _move_table_to_schema(cur, _JOBS_TABLE, schema)
            if sql_path.is_file():
                for stmt in _sql_statements(sql_path.read_text(encoding="utf-8")):
                    cur.execute(stmt)
            _ensure_columns(
                cur,
                schema,
                _LOGS_TABLE,
                (
                    ("source_records", "BIGINT NOT NULL DEFAULT 0"),
                    ("duplicate_records", "BIGINT NOT NULL DEFAULT 0"),
                    ("already_in_db", "BIGINT NOT NULL DEFAULT 0"),
                    ("already_in_staging", "BIGINT NOT NULL DEFAULT 0"),
                    ("new_records", "BIGINT NOT NULL DEFAULT 0"),
                    ("staging_dropped", "BOOLEAN NOT NULL DEFAULT FALSE"),
                    ("completed_at", "TIMESTAMPTZ"),
                ),
            )
    logger.info("Ensured schema %s in database %s", schema, settings.db_name)
    return {"ok": True, "database": settings.db_name, "schema": schema}


def ensure_cdatpcsuspect_schema() -> dict[str, Any]:
    """Create schema cdatpcsuspectstagingdb inside CDATDUPL_DB if it is missing."""
    schema = settings.pcsuspect_schema
    renamed = False
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT 1 FROM information_schema.schemata WHERE schema_name = %s",
                (schema,),
            )
            exists = cur.fetchone() is not None
            if not exists and schema != _OLD_PCSUSPECT_SCHEMA:
                cur.execute(
                    "SELECT 1 FROM information_schema.schemata WHERE schema_name = %s",
                    (_OLD_PCSUSPECT_SCHEMA,),
                )
                if cur.fetchone() is not None:
                    cur.execute(
                        f"ALTER SCHEMA {_ident(_OLD_PCSUSPECT_SCHEMA)} RENAME TO {_ident(schema)}"
                    )
                    renamed = True
                    exists = True
            if not exists:
                cur.execute(f"CREATE SCHEMA IF NOT EXISTS {_ident(schema)}")
                exists = True
    logger.info(
        "Ensured schema %s in database %s%s",
        schema,
        settings.db_name,
        " (renamed from cdatpcsuspectdb)" if renamed else "",
    )
    return {
        "ok": exists,
        "database": settings.db_name,
        "schema": schema,
        "renamed_from": _OLD_PCSUSPECT_SCHEMA if renamed else None,
    }


def ensure_databases_and_schema() -> dict[str, Any]:
    """Create missing databases from .env, then apply sql/*.sql (IF NOT EXISTS)."""
    created: list[str] = []
    applied: list[dict[str, Any]] = []
    for dbname, sql_path in _schema_targets():
        if not _database_exists(dbname):
            _create_database(dbname)
            created.append(dbname)
        count = _apply_sql_file(dbname, sql_path)
        applied.append({"database": dbname, "schema": sql_path.name, "statements": count})
        logger.info("Applied %s to %s (%s statements)", sql_path.name, dbname, count)
    pcsuspect = ensure_cdatpcsuspect_schema()
    upload = ensure_upload_schema()
    return {
        "created_databases": created,
        "applied_schemas": applied,
        "pcsuspect_schema": pcsuspect,
        "upload_schema": upload,
    }


def ping() -> dict[str, Any]:
    """Open a short-lived connection and return server identity (no secrets)."""
    with db_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT current_database(), current_user, inet_server_addr()::text, inet_server_port()"
            )
            db_name, db_user, server_addr, server_port = cur.fetchone()
    return {
        "ok": True,
        "host": settings.db_host,
        "port": int(settings.db_port),
        "name": db_name,
        "user": db_user,
        "server_addr": server_addr,
        "server_port": server_port,
        "satellites": {
            "ir": settings.ir_db_name,
            "jrms": settings.jrms_db_name,
            "pdact": settings.pdact_db_name,
            "rowdy_sheets": settings.rowdy_sheets_db_name,
            "training": settings.training_db_name,
        },
        "pcsuspect_schema": settings.pcsuspect_schema,
        "upload_schema": settings.upload_schema,
    }
