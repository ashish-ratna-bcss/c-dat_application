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
_executor = ThreadPoolExecutor(max_workers=int(os.environ.get('CDR_IMPORT_WORKERS', '4')))
_lock = threading.Lock()
_running: set[int] = set()
UPLOAD_DIR = Path(os.environ.get('CDR_API_UPLOAD_DIR', str(UPLOAD_INBOX_DIR)))

def ensure_runtime_dirs() -> None:
    UPLOAD_DIR.mkdir(parents=True, exist_ok=True)
    from document_processing.resumable_upload import ensure_sessions_dir
    ensure_sessions_dir()
    # Schema is normally already applied; don't block API startup on lock contention.
    try:
        with db_connection() as conn:
            with conn.cursor() as cur:
                cur.execute("SET LOCAL lock_timeout TO '5s'")
                cur.execute("SET LOCAL statement_timeout TO '30s'")
            ensure_schema(conn)
    except Exception as exc:
        logger.warning('Schema ensure skipped at startup (will retry on first job): %s', exc)

def save_upload(filename: str, content: bytes, *, module: str) -> Path:
    safe_name = Path(filename).name
    module_dir = UPLOAD_DIR / module
    module_dir.mkdir(parents=True, exist_ok=True)
    dest = module_dir / f'{uuid.uuid4().hex}_{safe_name}'
    dest.write_bytes(content)
    return dest

async def save_upload_stream(upload, *, module: str, chunk_size: int = 8 * 1024 * 1024) -> Path:
    """Stream a large upload to disk without loading it entirely into memory."""
    safe_name = Path(upload.filename or 'upload').name
    module_dir = UPLOAD_DIR / module
    module_dir.mkdir(parents=True, exist_ok=True)
    dest = module_dir / f'{uuid.uuid4().hex}_{safe_name}'
    with dest.open('wb') as out:
        while True:
            chunk = await upload.read(chunk_size)
            if not chunk:
                break
            out.write(chunk)
    return dest

def _run_job(job_id: int) -> None:
    with _lock:
        if job_id in _running:
            return
        _running.add(job_id)
    try:
        execute_document_job(job_id, resume=True)
    except Exception as exc:
        logger.exception('Background document job failed for job %s', job_id)
        try:
            from document_processing.db import db_connection, update_document_job
            with db_connection() as conn:
                update_document_job(conn, job_id, status='failed', error_message=str(exc))
        except Exception:
            logger.exception('Could not mark job %s as failed', job_id)
    finally:
        with _lock:
            _running.discard(job_id)

def submit_background_document(file_path: Path, *, module: str, batch_size: int, dry_run: bool=False, operator: Optional[str] = None) -> dict:
    queued = enqueue_document(file_path, module=module, batch_size=batch_size, dry_run=dry_run, operator=operator)
    job_id = queued.get('job_id')
    if job_id and (not dry_run):
        _executor.submit(_run_job, int(job_id))
    return queued

def resume_background_job(job_id: int) -> dict:
    _executor.submit(_run_job, job_id)
    return get_document_job_status(job_id)

def validate_upload(file_path: Path, module: str, operator: Optional[str] = None) -> dict:
    return validate_document(file_path, module, operator=operator)

def fetch_jobs(*, module: str | None=None, limit: int=50, offset: int=0) -> list[dict]:
    with db_connection() as conn:
        return list_document_jobs(conn, module=module, limit=limit, offset=offset)
CdrImportError = DocumentProcessingError

def submit_background_import(file_path: Path, *, batch_size: int) -> dict:
    return submit_background_document(file_path, module='cdr', batch_size=batch_size)

def get_job_status(job_id: int) -> dict:
    return get_document_job_status(job_id)
