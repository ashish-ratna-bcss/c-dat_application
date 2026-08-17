from __future__ import annotations
import os
from pathlib import Path
BASE_DIR = Path(__file__).resolve().parent.parent
# Production Linux uses /mnt/storage1. Local Mac/dev uses var/cdr_documents.
_PROD_DOCS = Path('/mnt/storage1/cdr_documents')
_LOCAL_DOCS = BASE_DIR / 'var' / 'cdr_documents'
_DOCS_ROOT = _PROD_DOCS if _PROD_DOCS.exists() else _LOCAL_DOCS
UPLOAD_INBOX_DIR = Path(os.environ.get('CDR_UPLOAD_INBOX', str(_DOCS_ROOT / 'inbox')))
UPLOAD_PROCESSING_DIR = Path(os.environ.get('CDR_UPLOAD_PROCESSING', str(_DOCS_ROOT / 'processing')))
UPLOAD_DONE_DIR = Path(os.environ.get('CDR_UPLOAD_DONE', str(_DOCS_ROOT / 'done')))
UPLOAD_FAILED_DIR = Path(os.environ.get('CDR_UPLOAD_FAILED', str(_DOCS_ROOT / 'failed')))
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
        2. config/db_config.php           (what the web app itself uses)
        3. built-in defaults          (host/port/db/user only, never a password)
    """
    # Auto-load .env file if it exists so we don't rely on the terminal
    env_file = BASE_DIR / '.env'
    if env_file.exists():
        for line in env_file.read_text(encoding='utf-8', errors='ignore').splitlines():
            line = line.strip()
            if line and not line.startswith('#') and '=' in line:
                k, v = line.split('=', 1)
                # Override terminal environment in case it has \r formatting issues from bad exports
                os.environ[k.strip()] = v.strip()

    cfg: dict[str, str] = {}
    for key, env in _DB_ENV.items():
        if env in os.environ:
            cfg[key] = os.environ.get(env, '')

    php_cfg = BASE_DIR / 'config' / 'db_config.php'
    if php_cfg.exists():
        text = php_cfg.read_text(encoding='utf-8', errors='replace')
        for key in _DB_ENV:
            if key in cfg:
                continue
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
    cfg.setdefault('password', '')
    return cfg
