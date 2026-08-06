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

def _load_dotenv(path: Path) -> None:
    if not path.is_file():
        return
    for line in path.read_text(encoding='utf-8', errors='replace').splitlines():
        line = line.strip()
        if not line or line.startswith('#') or '=' not in line:
            continue
        key, value = line.split('=', 1)
        key = key.strip()
        value = value.strip().strip('"').strip("'")
        if key and key not in os.environ:
            os.environ[key] = value


def load_db_config() -> dict[str, str]:
    _load_dotenv(BASE_DIR / '.env')
    cfg = {
        'host': os.environ.get('CDR_DB_HOST', '127.0.0.1'),
        'port': os.environ.get('CDR_DB_PORT', '5432'),
        'database': os.environ.get('CDR_DB_NAME', 'postgres'),
        'user': os.environ.get('CDR_DB_USER', 'postgres'),
        'password': os.environ.get('CDR_DB_PASSWORD', ''),
    }
    php_cfg = BASE_DIR / 'db_config.php'
    if php_cfg.exists():
        text = php_cfg.read_text(encoding='utf-8', errors='replace')
        for key in ('host', 'port', 'database', 'user', 'password'):
            marker = f"'{key}' => '"
            start = text.find(marker)
            if start == -1:
                continue
            start += len(marker)
            end = text.find("'", start)
            if end != -1 and (not cfg.get(key)):
                cfg[key] = text[start:end]
            elif end != -1 and key == 'password' and (not os.environ.get('CDR_DB_PASSWORD')):
                cfg[key] = text[start:end]
    if not cfg['password'] or cfg['password'] in ('your_password_here',):
        raise RuntimeError('Database password not configured (.env CDR_DB_PASSWORD or db_config.php).')
    return cfg
