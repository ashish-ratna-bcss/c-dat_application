from __future__ import annotations
import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent
LOG_DIR = Path(os.environ.get('DIST_MIGRATE_LOG_DIR', '/mnt/storage1/ITCell_DL_RTA_Data/distributed_migrate_logs'))
LOG_DIR.mkdir(parents=True, exist_ok=True)

MSSQL_DRIVER = os.environ.get('MSSQL_ODBC_DRIVER', 'ODBC Driver 17 for SQL Server')
MSSQL_HOST = os.environ.get('MSSQL_HOST', 'localhost')
MSSQL_USER = os.environ.get('MSSQL_USER', 'SA')
MSSQL_PASSWORD = os.environ.get('MSSQL_SA_PASSWORD', '')

PG_HOST = os.environ.get('DIST_PG_HOST', '127.0.0.1')
PG_PORT = os.environ.get('DIST_PG_PORT', '5432')
PG_USER = os.environ.get('DIST_PG_USER', 'postgres')
PG_PASSWORD = os.environ.get('DIST_PG_PASSWORD', '')
PG_DATABASE = os.environ.get('DIST_PG_DATABASE', 'distributed_db')

MAIN_PG_DATABASE = os.environ.get('CDAT_PG_DATABASE', 'postgres')

DEFAULT_BATCH_SIZE = int(os.environ.get('DIST_MIGRATE_BATCH_SIZE', '50000'))
SLEEP_ON_ERROR_SEC = int(os.environ.get('DIST_MIGRATE_RETRY_SEC', '10'))

CDATADDRESS_JOB = {
    'job_name': 'cdataddress',
    'mssql_database': 'address_db',
    'mssql_table': 'CDATADDRESS',
    'pg_table': 'cdataddress',
    'mssql_key_column': 'CDAT_SDR_KEY',
    'mssql_key_index': 'idx_cdat_sdr_key',
    'pg_key_column': 'cdat_sdr_key',
    'distribution_column': 'phone',
    'colocate_with': 'address_other_state',
    'batch_size': DEFAULT_BATCH_SIZE,
}

CELLIDS_JOB = {
    'job_name': 'cellids',
    'mssql_database': 'cellids_db',
    'mssql_table': 'CELLIDS',
    'pg_table': 'cellids',
    'mssql_key_column': 'TOWER_KEY',
    'mssql_key_index': 'idx_tower_key',
    'pg_key_column': 'tower_key',
    'distribution_column': 'celltowerid',
    'colocate_with': None,
    'batch_size': DEFAULT_BATCH_SIZE,
}


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
