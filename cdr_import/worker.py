from __future__ import annotations
import logging
import shutil
from pathlib import Path
from .config import UPLOAD_DONE_DIR, UPLOAD_FAILED_DIR, UPLOAD_INBOX_DIR, UPLOAD_PROCESSING_DIR
from .importer import CdrImportError, import_file
logger = logging.getLogger(__name__)

def run_once(*, commit: bool=False, batch_size: int=500) -> bool:
    inbox_files = sorted((f for f in UPLOAD_INBOX_DIR.iterdir() if f.is_file() and (not f.name.startswith('.'))))
    if not inbox_files:
        return False
    source = inbox_files[0]
    processing = UPLOAD_PROCESSING_DIR / source.name
    shutil.move(str(source), processing)
    logger.info('Processing %s', processing.name)
    try:
        result = import_file(processing, dry_run=not commit, batch_size=batch_size, resume=True)
        dest_dir = UPLOAD_DONE_DIR if result.get('status') in ('validated', 'completed') else UPLOAD_FAILED_DIR
        shutil.move(str(processing), dest_dir / processing.name)
        logger.info('Finished %s status=%s records=%s committed=%s', processing.name, result.get('status'), result.get('total_records'), result.get('rows_committed'))
        return True
    except CdrImportError as exc:
        logger.error('Import failed for %s: %s', processing.name, exc)
        if processing.exists():
            shutil.move(str(processing), UPLOAD_FAILED_DIR / processing.name)
        return True
    except Exception as exc:
        logger.exception('Unexpected error for %s: %s', processing.name, exc)
        if processing.exists():
            shutil.move(str(processing), UPLOAD_FAILED_DIR / processing.name)
        return True
