from __future__ import annotations
import re
from datetime import date, datetime
from decimal import Decimal
from typing import Any

def normalize_pg_column(name: str) -> str:
    name = name.strip().lower()
    name = re.sub(r'[^\w]+', '_', name)
    return re.sub(r'_+', '_', name).strip('_')


def mssql_type_to_pg(data_type: str, char_len: int | None, num_precision: int | None, num_scale: int | None = None) -> str:
    dt = (data_type or '').lower()
    if dt == 'bigint':
        return 'BIGINT'
    if dt in ('int', 'smallint', 'tinyint'):
        return 'INTEGER'
    if dt == 'bit':
        return 'BOOLEAN'
    if dt in ('datetime', 'datetime2', 'smalldatetime'):
        return 'TIMESTAMP WITHOUT TIME ZONE'
    if dt == 'date':
        return 'DATE'
    if dt in ('decimal', 'numeric'):
        prec = num_precision or 18
        scale = num_scale if num_scale is not None else 0
        return f'NUMERIC({prec},{scale})'
    if dt in ('float', 'real'):
        return 'DOUBLE PRECISION'
    if char_len and char_len > 0:
        if char_len >= 1000:
            return 'TEXT'
        return f'VARCHAR({char_len})'
    return 'TEXT'


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


def fetch_mssql_columns(cursor, table_name: str) -> list[dict[str, Any]]:
    cursor.execute(
        """
        SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH,
               NUMERIC_PRECISION, NUMERIC_SCALE, IS_NULLABLE, ORDINAL_POSITION
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = ?
        ORDER BY ORDINAL_POSITION
        """,
        table_name,
    )
    cols = []
    for row in cursor.fetchall():
        cols.append({
            'mssql_name': row[0],
            'pg_name': normalize_pg_column(row[0]),
            'data_type': row[1],
            'char_len': row[2],
            'num_precision': row[3],
            'num_scale': row[4],
            'nullable': row[5] == 'YES',
            'ordinal': row[6],
        })
    return cols
