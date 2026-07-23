from __future__ import annotations
import json
import logging
import os
import threading
import uuid
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Optional
from cdr_import.config import UPLOAD_INBOX_DIR
logger = logging.getLogger(__name__)
DEFAULT_CHUNK_SIZE = int(os.environ.get('SDR_UPLOAD_CHUNK_SIZE', str(16 * 1024 * 1024)))
SESSION_TTL_HOURS = int(os.environ.get('SDR_UPLOAD_SESSION_TTL_HOURS', '168'))
SESSIONS_ROOT = Path(os.environ.get('SDR_RESUMABLE_SESSIONS_DIR', str(UPLOAD_INBOX_DIR / 'sdr' / '.resumable')))
_lock = threading.Lock()

class ResumableUploadError(Exception):
    pass

def _utcnow() -> str:
    return datetime.now(timezone.utc).isoformat()

def _session_path(upload_id: str) -> Path:
    return SESSIONS_ROOT / f'{upload_id}.json'

def _partial_path(upload_id: str) -> Path:
    return SESSIONS_ROOT / f'{upload_id}.partial'

def _index_path() -> Path:
    return SESSIONS_ROOT / 'index.json'

def ensure_sessions_dir() -> None:
    SESSIONS_ROOT.mkdir(parents=True, exist_ok=True)

def _load_index() -> dict[str, str]:
    path = _index_path()
    if not path.is_file():
        return {}
    try:
        return json.loads(path.read_text(encoding='utf-8'))
    except (OSError, json.JSONDecodeError):
        return {}

def _save_index(index: dict[str, str]) -> None:
    _index_path().write_text(json.dumps(index, indent=2), encoding='utf-8')

def _load_session(upload_id: str) -> dict[str, Any]:
    path = _session_path(upload_id)
    if not path.is_file():
        raise ResumableUploadError(f'Upload session {upload_id} not found.')
    try:
        return json.loads(path.read_text(encoding='utf-8'))
    except (OSError, json.JSONDecodeError) as exc:
        raise ResumableUploadError(f'Corrupt upload session {upload_id}.') from exc

def _save_session(session: dict[str, Any]) -> None:
    session['updated_at'] = _utcnow()
    _session_path(session['upload_id']).write_text(json.dumps(session, indent=2), encoding='utf-8')

def _sync_bytes_received(session: dict[str, Any]) -> int:
    partial = _partial_path(session['upload_id'])
    size = partial.stat().st_size if partial.is_file() else 0
    session['bytes_received'] = size
    return size

def _find_session_by_file_key(file_key: str) -> Optional[dict[str, Any]]:
    index = _load_index()
    upload_id = index.get(file_key)
    if not upload_id:
        return None
    try:
        session = _load_session(upload_id)
    except ResumableUploadError:
        return None
    if session.get('status') not in ('uploading', 'failed'):
        return None
    if session.get('file_key') != file_key:
        return None
    return session

def init_upload(*, filename: str, file_size: int, file_key: str, chunk_size: int=DEFAULT_CHUNK_SIZE) -> dict[str, Any]:
    if file_size <= 0:
        raise ResumableUploadError('file_size must be positive.')
    if not filename or Path(filename).name != filename:
        raise ResumableUploadError('Invalid filename.')
    if not file_key:
        raise ResumableUploadError('file_key is required for resume support.')
    ensure_sessions_dir()
    chunk_size = max(1024 * 1024, min(chunk_size, 64 * 1024 * 1024))
    with _lock:
        existing = _find_session_by_file_key(file_key)
        if existing:
            if int(existing['file_size']) != int(file_size):
                raise ResumableUploadError('A partial upload exists for this file key but file size differs. Use a new file or cancel the old session.')
            offset = _sync_bytes_received(existing)
            existing['chunk_size'] = chunk_size
            existing['status'] = 'uploading'
            _save_session(existing)
            return _public_session(existing, resumed=True)
        upload_id = uuid.uuid4().hex
        partial = _partial_path(upload_id)
        partial.touch()
        session = {'upload_id': upload_id, 'module': 'sdr', 'filename': filename, 'file_size': int(file_size), 'file_key': file_key, 'chunk_size': chunk_size, 'bytes_received': 0, 'status': 'uploading', 'created_at': _utcnow(), 'updated_at': _utcnow()}
        _save_session(session)
        index = _load_index()
        index[file_key] = upload_id
        _save_index(index)
        return _public_session(session, resumed=False)

def get_upload_status(upload_id: str) -> dict[str, Any]:
    with _lock:
        session = _load_session(upload_id)
        _sync_bytes_received(session)
        _save_session(session)
        return _public_session(session, resumed=session.get('bytes_received', 0) > 0)

def append_chunk(upload_id: str, offset: int, data: bytes) -> dict[str, Any]:
    if offset < 0:
        raise ResumableUploadError('offset must be non-negative.')
    if not data:
        raise ResumableUploadError('Empty chunk rejected.')
    with _lock:
        session = _load_session(upload_id)
        if session['status'] not in ('uploading', 'failed'):
            raise ResumableUploadError(f'Upload is not accepting chunks (status={session['status']}).')
        partial = _partial_path(upload_id)
        current = partial.stat().st_size if partial.is_file() else 0
        if offset != current:
            raise ResumableUploadError(f'Offset mismatch: expected {current}, got {offset}. Fetch upload status and resume from the reported offset.')
        expected_total = int(session['file_size'])
        if offset + len(data) > expected_total:
            raise ResumableUploadError('Chunk exceeds declared file size.')
        with partial.open('ab') as fh:
            fh.write(data)
        session['bytes_received'] = offset + len(data)
        session['status'] = 'uploading'
        session['last_error'] = None
        _save_session(session)
        return _public_session(session, resumed=False)

def complete_upload(upload_id: str) -> Path:
    with _lock:
        session = _load_session(upload_id)
        received = _sync_bytes_received(session)
        expected = int(session['file_size'])
        if received != expected:
            raise ResumableUploadError(f'Upload incomplete: received {received} of {expected} bytes.')
        partial = _partial_path(upload_id)
        if not partial.is_file():
            raise ResumableUploadError('Partial file missing.')
        module_dir = UPLOAD_INBOX_DIR / 'sdr'
        module_dir.mkdir(parents=True, exist_ok=True)
        dest = module_dir / f'{upload_id}_{session['filename']}'
        partial.rename(dest)
        session['status'] = 'complete'
        session['final_path'] = str(dest)
        _save_session(session)
        index = _load_index()
        if session.get('file_key') in index:
            del index[session['file_key']]
            _save_index(index)
        return dest

def cancel_upload(upload_id: str) -> None:
    with _lock:
        session = _load_session(upload_id)
        partial = _partial_path(upload_id)
        if partial.is_file():
            partial.unlink()
        session['status'] = 'cancelled'
        _save_session(session)
        index = _load_index()
        file_key = session.get('file_key')
        if file_key and index.get(file_key) == upload_id:
            del index[file_key]
            _save_index(index)

def _public_session(session: dict[str, Any], *, resumed: bool) -> dict[str, Any]:
    total = int(session['file_size'])
    received = int(session.get('bytes_received', 0))
    progress = round(received / total * 100.0, 2) if total > 0 else 0.0
    return {'upload_id': session['upload_id'], 'module': session.get('module', 'sdr'), 'filename': session['filename'], 'file_size': total, 'chunk_size': int(session.get('chunk_size', DEFAULT_CHUNK_SIZE)), 'offset': received, 'bytes_received': received, 'progress_percent': progress, 'status': session['status'], 'resumed': resumed, 'complete': received >= total and session['status'] == 'complete'}
