from __future__ import annotations
import logging
import os
import sys
import threading
import uuid
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
_REPO_ROOT = Path(__file__).resolve().parents[2]
if str(_REPO_ROOT) not in sys.path:
    sys.path.insert(0, str(_REPO_ROOT))
from cdr_import.config import DEFAULT_BATCH_SIZE, UPLOAD_INBOX_DIR
from document_processing.db import db_connection, ensure_schema, list_document_jobs
from document_processing.orchestrator import DocumentProcessingError, enqueue_document, execute_document_job, get_document_job_status, validate_document
logger = logging.getLogger(__name__)
_executor = ThreadPoolExecutor(max_workers=int(os.environ.get('CDR_IMPORT_WORKERS', '2')))
_lock = threading.Lock()
_running: set[int] = set()
UPLOAD_DIR = Path(os.environ.get('CDR_API_UPLOAD_DIR', str(UPLOAD_INBOX_DIR)))

def ensure_runtime_dirs() -> None:
    UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
    with db_connection() as conn:
        ensure_schema(conn)

def save_upload(filename: str, content: bytes, *, module: str) -> Path:
    safe_name = Path(filename).name
    module_dir = UPLOAD_DIR / module
    module_dir.mkdir(parents=True, exist_ok=True)
    dest = module_dir / f'{uuid.uuid4().hex}_{safe_name}'
    dest.write_bytes(content)
    return dest

def _run_job(job_id: int) -> None:
    with _lock:
        if job_id in _running:
            return
        _running.add(job_id)
    try:
        execute_document_job(job_id, resume=True)
    except Exception:
        logger.exception('Background document job failed for job %s', job_id)
    finally:
        with _lock:
            _running.discard(job_id)

def submit_background_document(file_path: Path, *, module: str, batch_size: int, dry_run: bool=False) -> dict:
    queued = enqueue_document(file_path, module=module, batch_size=batch_size, dry_run=dry_run)
    job_id = queued.get('job_id')
    if job_id and (not dry_run):
        _executor.submit(_run_job, int(job_id))
    return queued

def resume_background_job(job_id: int) -> dict:
    _executor.submit(_run_job, job_id)
    return get_document_job_status(job_id)

def validate_upload(file_path: Path, module: str) -> dict:
    return validate_document(file_path, module)

def fetch_jobs(*, module: str | None=None, limit: int=50, offset: int=0) -> list[dict]:
    with db_connection() as conn:
        return list_document_jobs(conn, module=module, limit=limit, offset=offset)
CdrImportError = DocumentProcessingError

def submit_background_import(file_path: Path, *, batch_size: int) -> dict:
    return submit_background_document(file_path, module='cdr', batch_size=batch_size)

def get_job_status(job_id: int) -> dict:
    return get_document_job_status(job_id)
