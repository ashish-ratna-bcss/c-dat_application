from __future__ import annotations
import logging
import re
from datetime import date, datetime
from decimal import Decimal
from typing import Any, Optional
import psycopg2
import psycopg2.extras
import pyodbc
from .config import SDR_BATCH_SIZE, SDR_TABLES, mssql_conn_str, pg_conn_kwargs
logger = logging.getLogger(__name__)

class SdrMigrateError(Exception):
    pass

def normalize_pg_column(name: str) -> str:
    name = name.strip().lower()
    name = re.sub('[^\\w]+', '_', name)
    name = re.sub('_+', '_', name).strip('_')
    return name

def mssql_type_to_pg(data_type: str, char_len: int | None, num_precision: int | None) -> str:
    dt = data_type.lower()
    if dt in ('bigint',):
        return 'BIGINT'
    if dt in ('int', 'smallint', 'tinyint'):
        return 'INTEGER'
    if dt in ('bit',):
        return 'BOOLEAN'
    if dt in ('datetime', 'datetime2', 'smalldatetime'):
        return 'TIMESTAMP WITHOUT TIME ZONE'
    if dt in ('date',):
        return 'DATE'
    if dt in ('decimal', 'numeric'):
        prec = num_precision or 18
        return f'NUMERIC({prec}, 0)'
    if dt in ('float', 'real'):
        return 'DOUBLE PRECISION'
    if char_len and char_len > 0:
        if char_len >= 1000:
            return 'TEXT'
        return f'VARCHAR({char_len})'
    return 'TEXT'

def fetch_mssql_columns(cursor, table_name: str) -> list[dict[str, Any]]:
    cursor.execute('\n        SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, IS_NULLABLE, ORDINAL_POSITION\n        FROM INFORMATION_SCHEMA.COLUMNS\n        WHERE TABLE_NAME = ?\n        ORDER BY ORDINAL_POSITION\n        ', table_name)
    cols = []
    for row in cursor.fetchall():
        cols.append({'mssql_name': row[0], 'pg_name': normalize_pg_column(row[0]), 'data_type': row[1], 'char_len': row[2], 'num_precision': row[3], 'nullable': row[4] == 'YES', 'ordinal': row[5]})
    return cols

def ensure_pg_table(pg_conn, table_name: str, columns: list[dict[str, Any]]) -> list[str]:
    pg_cols = [c['pg_name'] for c in columns]
    with pg_conn.cursor() as cur:
        cur.execute("\n            SELECT column_name\n            FROM information_schema.columns\n            WHERE table_schema = 'public' AND table_name = %s\n            ", (table_name.lower(),))
        existing = {r[0] for r in cur.fetchall()}
        if not existing:
            col_defs = []
            for col in columns:
                pg_type = mssql_type_to_pg(col['data_type'], col['char_len'], col['num_precision'])
                null_sql = '' if col['nullable'] else ' NOT NULL'
                col_defs.append(f'{col['pg_name']} {pg_type}{null_sql}')
            ddl = f'CREATE TABLE {table_name} ({', '.join(col_defs)})'
            cur.execute(ddl)
            pg_conn.commit()
            return pg_cols
        for col in columns:
            if col['pg_name'] in existing:
                continue
            pg_type = mssql_type_to_pg(col['data_type'], col['char_len'], col['num_precision'])
            cur.execute(f'ALTER TABLE {table_name} ADD COLUMN IF NOT EXISTS {col['pg_name']} {pg_type}')
        pg_conn.commit()
    return pg_cols

def adapt_value(value: Any) -> Any:
    if value is None:
        return None
    if isinstance(value, Decimal):
        return value
    if isinstance(value, datetime):
        return value.replace(tzinfo=None)
    if isinstance(value, date):
        return value
    if isinstance(value, bytes):
        return value.decode('utf-8', errors='replace')
    return value

def count_mssql_rows(mssql_conn, table_name: str) -> int:
    cur = mssql_conn.cursor()
    cur.execute(f'SELECT COUNT(*) FROM {table_name}')
    return int(cur.fetchone()[0])

def migrate_table(*, mssql_database: str, mssql_table: str, pg_table: str, key_column: str, last_key: int=0, batch_size: int=SDR_BATCH_SIZE, on_batch=None) -> dict:
    mssql = pyodbc.connect(mssql_conn_str(mssql_database), autocommit=True)
    pg = psycopg2.connect(**pg_conn_kwargs())
    try:
        m_cur = mssql.cursor()
        columns = fetch_mssql_columns(m_cur, mssql_table)
        if not columns:
            raise SdrMigrateError(f'No columns found for MSSQL table {mssql_table}')
        pg_cols = ensure_pg_table(pg, pg_table, columns)
        mssql_names = [c['mssql_name'] for c in columns]
        bracketed_key = f'[{key_column}]' if ' ' in key_column else key_column
        select_cols = ', '.join((f'[{c}]' if ' ' in c else c for c in mssql_names))
        insert_cols = ', '.join(pg_cols)
        placeholders = ', '.join(['%s'] * len(pg_cols))
        total_inserted = 0
        current_key = last_key
        while True:
            query = f'\n            SELECT TOP {batch_size} {select_cols}\n            FROM {mssql_table}\n            WHERE {bracketed_key} > ?\n            ORDER BY {bracketed_key} ASC\n            '
            m_cur.execute(query, current_key)
            rows = m_cur.fetchall()
            if not rows:
                break
            adapted = [tuple((adapt_value(v) for v in row)) for row in rows]
            with pg.cursor() as p_cur:
                psycopg2.extras.execute_batch(p_cur, f'INSERT INTO {pg_table} ({insert_cols}) VALUES ({placeholders})', adapted, page_size=min(len(adapted), 1000))
            pg.commit()
            key_idx = mssql_names.index(key_column)
            current_key = int(rows[-1][key_idx])
            total_inserted += len(rows)
            if on_batch:
                on_batch(rows_committed=total_inserted, last_key=current_key)
            logger.info('Migrated %s rows to %s (last %s=%s)', total_inserted, pg_table, key_column, current_key)
        return {'table': pg_table, 'rows_inserted': total_inserted, 'last_key': current_key}
    finally:
        mssql.close()
        pg.close()

def estimate_total_rows(mssql_database: str) -> int:
    mssql = pyodbc.connect(mssql_conn_str(mssql_database), autocommit=True)
    try:
        total = 0
        for spec in SDR_TABLES:
            total += count_mssql_rows(mssql, spec['mssql_table'])
        return total
    finally:
        mssql.close()
