from __future__ import annotations
import hashlib
from contextlib import contextmanager

def _lock_key(module: str, file_hash: str) -> int:
    digest = hashlib.sha256(f'{module}:{file_hash}'.encode()).digest()
    return int.from_bytes(digest[:8], byteorder='big', signed=True)

@contextmanager
def module_promote_lock(conn, module: str):
    with conn.cursor() as cur:
        cur.execute('SELECT pg_advisory_lock(hashtext(%s))', (f'cdat_module_promote:{module.lower()}',))
    try:
        yield
    finally:
        with conn.cursor() as cur:
            cur.execute('SELECT pg_advisory_unlock(hashtext(%s))', (f'cdat_module_promote:{module.lower()}',))

@contextmanager
def content_lock(conn, module: str, file_hash: str):
    key = _lock_key(module, file_hash)
    with conn.cursor() as cur:
        cur.execute('SELECT pg_advisory_lock(%s)', (key,))
    try:
        yield
    finally:
        with conn.cursor() as cur:
            cur.execute('SELECT pg_advisory_unlock(%s)', (key,))
