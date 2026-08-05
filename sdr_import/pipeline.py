from __future__ import annotations
import logging
from pathlib import Path
from typing import Any, Callable, Optional
from document_processing.db import db_connection, fetch_document_job, update_document_job
from document_processing.staging import register_staging_batch
from sdr_import.config import MSSQL_DATABASE, SDR_BATCH_SIZE, SDR_TABLES
from sdr_import.migrate import SdrMigrateError, estimate_total_rows, migrate_table
from sdr_import.mssql_restore import SdrRestoreError, restore_database_from_bak
logger = logging.getLogger(__name__)

class SdrPipelineError(Exception):
    pass

def _phase_state(job: dict) -> dict:
    state = job.get('phase_state') or {}
    return dict(state) if isinstance(state, dict) else {}

def _save_phase_state(conn, job_id: int, phase: str, phase_state: dict, **kwargs) -> None:
    update_document_job(conn, job_id, phase=phase, phase_state=phase_state, **kwargs)

def run_sdr_job(job_id: int, *, resume: bool=True) -> dict:
    with db_connection() as conn:
        job = fetch_document_job(conn, job_id)
    if not job:
        raise SdrPipelineError(f'Job {job_id} not found')
    if job['module'] != 'sdr':
        raise SdrPipelineError(f'Job {job_id} is not an SDR job')
    if job['status'] in ('completed', 'pending_verification'):
        return _response(job, 'Job already completed.')
    if job.get('dry_run'):
        return _response(job, 'Dry-run SDR job: validation only, no restore/migrate executed.')
    path = Path(job['file_path'])
    phase_state = _phase_state(job)
    phase = job.get('phase') or 'restore_mssql'
    mssql_db = job.get('mssql_database') or MSSQL_DATABASE
    batch_size = int(job.get('batch_size') or SDR_BATCH_SIZE)
    rows_committed = int(job.get('rows_committed') or 0)
    try:
        with db_connection() as conn:
            update_document_job(conn, job_id, status='running', error_message=None)
        if phase == 'restore_mssql' or not phase_state.get('restore_mssql', {}).get('status'):
            logger.info('Job %s: restoring MSSQL from %s', job_id, path)
            restore_info = restore_database_from_bak(path, replace=True)
            mssql_db = restore_info.get('database', mssql_db)
            phase_state['restore_mssql'] = {'status': 'completed', **restore_info}
            est_kwargs = {}
            try:
                est_total = estimate_total_rows(mssql_db)
                if est_total:
                    est_kwargs['total_rows_estimated'] = est_total
            except Exception as est_exc:
                logger.warning('Job %s: could not estimate total rows: %s', job_id, est_exc)
            with db_connection() as conn:
                _save_phase_state(conn, job_id, phase='migrate_cdataddress', phase_state=phase_state, mssql_database=mssql_db, last_checkpoint_key=0, **est_kwargs)
            phase = 'migrate_cdataddress'
        if phase in ('migrate_cdataddress', 'migrate_address_other_state'):
            start_index = 0
            if phase == 'migrate_address_other_state':
                start_index = 1
            elif phase == 'migrate_cdataddress' and phase_state.get('migrate_cdataddress', {}).get('status') == 'completed':
                start_index = 1
            staging_tables: dict[str, str] = dict(phase_state.get('staging_tables') or {})
            for spec in SDR_TABLES[start_index:]:
                table_phase = spec['phase']
                table_state = phase_state.get(table_phase, {})
                if table_state.get('status') == 'completed' and resume:
                    rows_committed = max(rows_committed, int(table_state.get('rows_inserted', 0)))
                    if table_state.get('staging_table'):
                        staging_tables[spec['pg_table']] = table_state['staging_table']
                    continue
                last_key = int(table_state.get('last_key', job.get('last_checkpoint_key') or 0))
                if not resume:
                    last_key = 0

                def on_batch(rows_committed_batch: int, last_key_batch: int, *, jp=job_id, tp=table_phase, ps=phase_state):
                    ps[tp] = {'status': 'running', 'rows_inserted': rows_committed_batch, 'last_key': last_key_batch}
                    with db_connection() as conn:
                        total_est = job.get('total_rows_estimated')
                        update_document_job(conn, jp, phase=tp, phase_state=ps, rows_committed=rows_committed + rows_committed_batch, last_checkpoint_key=last_key_batch, total_rows_estimated=total_est, status='running')
                result = migrate_table(
                    mssql_database=mssql_db,
                    mssql_table=spec['mssql_table'],
                    pg_table=spec['pg_table'],
                    key_column=spec['key_column'],
                    last_key=last_key,
                    batch_size=batch_size,
                    on_batch=on_batch,
                    job_id=job_id,
                    use_staging=True,
                )
                rows_committed += int(result['rows_inserted'])
                staging_tables[spec['pg_table']] = result['table']
                phase_state[table_phase] = {
                    'status': 'completed',
                    'rows_inserted': result['rows_inserted'],
                    'last_key': result['last_key'],
                    'staging_table': result['table'],
                }
                next_phase = 'migrate_address_other_state' if table_phase == 'migrate_cdataddress' else 'pending_verification'
                with db_connection() as conn:
                    if next_phase == 'pending_verification':
                        phase_state['staging_tables'] = staging_tables
                        batch_id = register_staging_batch(
                            conn,
                            job_id=job_id,
                            module='sdr',
                            staging_tables=staging_tables,
                        )
                        phase_state['staging_batch_id'] = batch_id
                        update_document_job(
                            conn,
                            job_id,
                            phase='pending_verification',
                            phase_state=phase_state,
                            rows_committed=rows_committed,
                            last_checkpoint_key=int(result['last_key']),
                            status='pending_verification',
                        )
                    else:
                        phase_state['staging_tables'] = staging_tables
                        _save_phase_state(conn, job_id, phase=next_phase, phase_state=phase_state, rows_committed=rows_committed, last_checkpoint_key=0, status='running')
        with db_connection() as conn:
            job = fetch_document_job(conn, job_id)
        return _response(job, 'SDR data loaded into staging for manual verification.')
    except (SdrRestoreError, SdrMigrateError, SdrPipelineError) as exc:
        with db_connection() as conn:
            update_document_job(conn, job_id, status='failed', error_message=str(exc), phase_state=phase_state)
            job = fetch_document_job(conn, job_id)
        raise SdrPipelineError(str(exc)) from exc
    except Exception as exc:
        with db_connection() as conn:
            update_document_job(conn, job_id, status='failed', error_message=str(exc), phase_state=phase_state)
            job = fetch_document_job(conn, job_id)
        raise

def analyze_sdr_upload(file_path: Path) -> dict:
    path = Path(file_path).resolve()
    if not path.is_file():
        raise SdrPipelineError(f'File not found: {path}')
    if path.suffix.lower() != '.bak':
        raise SdrPipelineError('SDR module requires a .bak file.')
    return {'module': 'sdr', 'basename': path.name, 'file': str(path), 'mssql_database': MSSQL_DATABASE, 'message': 'Valid SDR .bak file.'}

def _response(job: dict, message: str='') -> dict:
    return {'job_id': job['job_id'], 'module': job['module'], 'status': job['status'], 'phase': job.get('phase'), 'basename': job.get('source_basename'), 'file': job.get('file_path'), 'mssql_database': job.get('mssql_database'), 'total_records': job.get('total_rows_estimated'), 'rows_committed': job.get('rows_committed', 0), 'last_checkpoint_key': job.get('last_checkpoint_key', 0), 'phase_state': job.get('phase_state'), 'error_message': job.get('error_message'), 'dry_run': job.get('dry_run'), 'message': message, 'created_at': job.get('created_at'), 'updated_at': job.get('updated_at'), 'completed_at': job.get('completed_at')}
