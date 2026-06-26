from __future__ import annotations
import os
from pathlib import Path
from cdr_import.config import load_db_config
BASE_DIR = Path(__file__).resolve().parent.parent
MSSQL_CONTAINER = os.environ.get('MSSQL_CONTAINER', 'mssql')
MSSQL_SA_PASSWORD = os.environ.get('MSSQL_SA_PASSWORD', '')
MSSQL_DATABASE = os.environ.get('SDR_MSSQL_DATABASE', 'address_db')
MSSQL_DATA_HOST_DIR = Path(os.environ.get('MSSQL_DATA_HOST_DIR', '/mnt/storage1/mssql/data'))
MSSQL_DATA_CONTAINER_DIR = Path(os.environ.get('MSSQL_DATA_CONTAINER_DIR', '/var/opt/mssql/data'))
SDR_BATCH_SIZE = int(os.environ.get('SDR_IMPORT_BATCH_SIZE', '10000'))
SDR_PG_DATABASE = os.environ.get('SDR_PG_DATABASE', 'postgres')
SDR_TABLES = ({'mssql_table': 'CDATADDRESS', 'pg_table': 'cdataddress', 'key_column': 'CDAT_SDR_KEY', 'phase': 'migrate_cdataddress'}, {'mssql_table': 'ADDRESS_OTHER_STATE', 'pg_table': 'address_other_state', 'key_column': 'OTH_SDR_KEY', 'phase': 'migrate_address_other_state'})

def mssql_conn_str(database: str | None=None) -> str:
    db = database or MSSQL_DATABASE
    return f'DRIVER={{ODBC Driver 17 for SQL Server}};SERVER=localhost;DATABASE={db};UID=SA;PWD={MSSQL_SA_PASSWORD}'

def pg_conn_kwargs(database: str | None=None) -> dict:
    cfg = load_db_config()
    return {'host': cfg['host'], 'port': cfg['port'], 'dbname': database or SDR_PG_DATABASE, 'user': cfg['user'], 'password': cfg['password']}
