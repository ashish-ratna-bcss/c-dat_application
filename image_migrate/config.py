from __future__ import annotations

import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
LOG_DIR = Path(os.environ.get('IMAGE_MIGRATE_LOG_DIR', '/mnt/storage1/ITCell_DL_RTA_Data/distributed_migrate_logs'))
LOG_DIR.mkdir(parents=True, exist_ok=True)

MSSQL_DRIVER = os.environ.get('MSSQL_ODBC_DRIVER', 'ODBC Driver 17 for SQL Server')
MSSQL_HOST = os.environ.get('MSSQL_HOST', 'localhost')
MSSQL_USER = os.environ.get('MSSQL_USER', 'SA')
MSSQL_PASSWORD = os.environ.get('MSSQL_SA_PASSWORD', '')

PG_HOST = os.environ.get('CDAT_PG_HOST', os.environ.get('DIST_PG_HOST', '127.0.0.1'))
PG_PORT = os.environ.get('CDAT_PG_PORT', os.environ.get('DIST_PG_PORT', '5432'))
PG_USER = os.environ.get('CDAT_PG_USER', os.environ.get('DIST_PG_USER', 'postgres'))
PG_PASSWORD = os.environ.get('CDAT_PG_PASSWORD', os.environ.get('DIST_PG_PASSWORD', ''))
PG_DATABASE = os.environ.get('CDAT_PG_DATABASE', 'postgres')

DEFAULT_BATCH_SIZE = int(os.environ.get('IMAGE_MIGRATE_BATCH_SIZE', '500'))
IMAGE_RESTORE_BAK = os.environ.get(
    'IMAGE_MSSQL_RESTORE_BAK',
    '/var/opt/mssql/data/DOPAMS_HYD_UNIT.bak',
)
IMAGE_RESTORE_DB = os.environ.get('IMAGE_MSSQL_SOURCE_DB', 'DOPAMS_HYD_UNIT')

SUSPECT_IMAGE_JOB = {
    'job_name': 'suspect_image_table',
    'mssql_database': IMAGE_RESTORE_DB,
    'mssql_table': 'SUSPECT_IMAGE_TABLE',
    'pg_table': 'suspect_image_table',
    'mssql_key_column': 'IRKEY',
    'pg_key_column': 'irkey',
    'mssql_secondary_key': 'MOBILE',
    'pg_secondary_key': 'mobile',
    'image_column': 'IMAGE',
    'pg_image_column': 'image',
    'image_encoding': 'text_base64',
    'batch_size': DEFAULT_BATCH_SIZE,
}

IMAGE_TABLE_JOB = {
    'job_name': 'image_table',
    'mssql_database': IMAGE_RESTORE_DB,
    'mssql_table': 'IMAGE_TABLE',
    'pg_table': 'image_table',
    'mssql_key_column': 'IRKEY',
    'pg_key_column': 'irkey',
    'image_column': 'IMAGE',
    'pg_image_column': 'image',
    'image_encoding': 'bytea_or_text',
    'batch_size': DEFAULT_BATCH_SIZE,
}

MO_IMAGE_JOB = {
    'job_name': 'mo_image_table',
    'mssql_database': IMAGE_RESTORE_DB,
    'mssql_table': 'MO_IMAGE_TABLE',
    'pg_table': 'mo_image_table',
    'mssql_key_column': 'MO_KEY',
    'pg_key_column': 'mo_key',
    'image_column': 'IMAGE',
    'pg_image_column': 'image',
    'image_encoding': 'text_base64',
    'batch_size': DEFAULT_BATCH_SIZE,
}

ALL_IMAGE_JOBS = [SUSPECT_IMAGE_JOB, IMAGE_TABLE_JOB, MO_IMAGE_JOB]


def mssql_conn_str(database: str) -> str:
    return (
        f'DRIVER={{{MSSQL_DRIVER}}};'
        f'SERVER={MSSQL_HOST};'
        f'DATABASE={database};'
        f'UID={MSSQL_USER};'
        f'PWD={MSSQL_PASSWORD}'
    )


def pg_conn_kwargs(database: str | None = None) -> dict:
    return {
        'host': PG_HOST,
        'port': PG_PORT,
        'dbname': database or PG_DATABASE,
        'user': PG_USER,
        'password': PG_PASSWORD,
    }
