from __future__ import annotations
import os
from pathlib import Path
BASE_DIR = Path(__file__).resolve().parent.parent
UPLOAD_INBOX_DIR = Path(os.environ.get('CDR_UPLOAD_INBOX', '/mnt/storage1/cdr_documents/inbox'))
UPLOAD_PROCESSING_DIR = Path(os.environ.get('CDR_UPLOAD_PROCESSING', '/mnt/storage1/cdr_documents/processing'))
UPLOAD_DONE_DIR = Path(os.environ.get('CDR_UPLOAD_DONE', '/mnt/storage1/cdr_documents/done'))
UPLOAD_FAILED_DIR = Path(os.environ.get('CDR_UPLOAD_FAILED', '/mnt/storage1/cdr_documents/failed'))
STAGING_TABLE = os.environ.get('CDR_STAGING_TABLE', 'cdatpcsuspect_staging')
JOBS_TABLE = os.environ.get('CDR_JOBS_TABLE', 'document_jobs')
TARGET_TABLE = os.environ.get('CDR_TARGET_TABLE', 'cdatpcsuspect')
PRODUCTION_INSERT_COLUMNS = (
    'ucid', 'phone', 'other', 'starttime', 'duration', 'incoming', 'imeinumber', 'imsinumber',
    'celltowerid', 'otherinfo', 'tower_key', 'provider_key', 'state_key', 'first_cellid',
    'last_cellid', 'roaming_nw', 'call_type', 'calling_no', 'called_no', 'asondate',
)
DEFAULT_BATCH_SIZE = int(os.environ.get('CDR_IMPORT_BATCH_SIZE', '5000'))
PROGRESS_UPDATE_EVERY_BATCHES = int(os.environ.get('CDR_PROGRESS_UPDATE_EVERY', '5'))
WORKER_POLL_SECONDS = int(os.environ.get('CDR_WORKER_POLL_SECONDS', '10'))
PROVIDER_KEYS = {'airtel': int(os.environ.get('CDR_PROVIDER_KEY_AIRTEL', '2')), 'bsnl': int(os.environ.get('CDR_PROVIDER_KEY_BSNL', '4')), 'vi': int(os.environ.get('CDR_PROVIDER_KEY_VI', '12')), 'jio': int(os.environ.get('CDR_PROVIDER_KEY_JIO', '15'))}

_DB_ENV = {
    'host': 'CDR_DB_HOST',
    'port': 'CDR_DB_PORT',
    'database': 'CDR_DB_NAME',
    'user': 'CDR_DB_USER',
    'password': 'CDR_DB_PASSWORD',
}
_DB_DEFAULTS = {'host': '127.0.0.1', 'port': '5432', 'database': 'postgres', 'user': 'postgres'}


def load_db_config() -> dict[str, str]:
    """Database settings, in order of precedence:

        1. environment variables      (deployment overrides)
        2. controller/db_config.php   (what the web app itself uses)
        3. built-in defaults          (host/port/db/user only, never a password)

    The env defaults used to be applied in step 1, which meant they were never
    empty and so step 2 could not fill host, port, database or user -- only the
    password. The service therefore had to be handed CDR_DB_* explicitly or it
    would connect to port 5432 / database "postgres" without complaint, and the
    launcher scripts duplicated the credentials to work around it. One of them
    ended up carrying the live password as a literal.
    """
    cfg: dict[str, str] = {}
    for key, env in _DB_ENV.items():
        value = os.environ.get(env, '')
        if value:
            cfg[key] = value

    php_cfg = BASE_DIR / 'controller' / 'db_config.php'
    if php_cfg.exists():
        text = php_cfg.read_text(encoding='utf-8', errors='replace')
        for key in _DB_ENV:
            if cfg.get(key):
                continue                      # the environment already said
            marker = f"'{key}' => '"
            start = text.find(marker)
            if start == -1:
                continue
            start += len(marker)
            end = text.find("'", start)
            if end != -1:
                cfg[key] = text[start:end]

    for key, fallback in _DB_DEFAULTS.items():
        cfg.setdefault(key, fallback)

    if not cfg.get('password'):
        raise RuntimeError('Database password not configured (db_config.php or CDR_DB_PASSWORD).')
    return cfg
