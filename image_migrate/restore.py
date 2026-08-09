from __future__ import annotations

import logging
import re
import subprocess
import time
from pathlib import Path

from .config import IMAGE_RESTORE_BAK, IMAGE_RESTORE_DB, LOG_DIR, MSSQL_PASSWORD

logger = logging.getLogger(__name__)
MSSQL_CONTAINER = __import__('os').environ.get('MSSQL_CONTAINER', 'mssql')


def _sqlcmd(args: str, *, timeout: int | None = None) -> tuple[int, str]:
  inner = (
    f'/opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$MSSQL_SA_PASSWORD" {args}'
  )
  cmd = ['docker', 'exec', '-e', f'MSSQL_SA_PASSWORD={MSSQL_PASSWORD}', MSSQL_CONTAINER, 'bash', '-c', inner]
  proc = subprocess.run(cmd, capture_output=True, text=True, timeout=timeout)
  out = (proc.stdout or '') + ('\n' + proc.stderr if proc.stderr else '')
  return proc.returncode, out


def database_state(name: str) -> str | None:
  rc, out = _sqlcmd(f"-Q \"SELECT state_desc FROM sys.databases WHERE name = N'{name.replace(chr(39), chr(39)+chr(39))}'\"", timeout=60)
  if rc != 0:
    raise RuntimeError(out)
  for line in out.splitlines():
    line = line.strip()
    if line and line.lower() not in {'statedesc', '---', '(0 rows affected)', '(1 rows affected)'}:
      if set(line) <= {'-', ' '}:
        continue
      return line
  return None


def ensure_source_database() -> None:
  """Restore IMAGE_MSSQL_SOURCE_DB from IMAGE_MSSQL_RESTORE_BAK when missing."""
  state = database_state(IMAGE_RESTORE_DB)
  if state == 'ONLINE':
    logger.info('MSSQL database %s already ONLINE', IMAGE_RESTORE_DB)
    return
  if state == 'RESTORING':
    logger.info('MSSQL database %s RESTORING — resuming', IMAGE_RESTORE_DB)
    rc, out = _sqlcmd(
      f"-Q \"RESTORE DATABASE [{IMAGE_RESTORE_DB}] FROM DISK = N'{IMAGE_RESTORE_BAK}' WITH RESTART, STATS = 5;\"",
      timeout=None,
    )
    if rc != 0:
      raise RuntimeError(f'RESTORE RESTART failed:\n{out}')
    return

  logger.info('Restoring MSSQL database %s from %s', IMAGE_RESTORE_DB, IMAGE_RESTORE_BAK)
  rc, out = _sqlcmd(f"-Q \"RESTORE FILELISTONLY FROM DISK = N'{IMAGE_RESTORE_BAK}'\"", timeout=600)
  if rc != 0:
    raise RuntimeError(f'FILELISTONLY failed:\n{out}')

  logical_data = None
  logical_log = None
  for line in out.splitlines():
    if '|' not in line:
      continue
    parts = [p.strip() for p in line.split('|')]
    if len(parts) < 3 or parts[0] in {'LogicalName', '-----------'}:
      continue
    if parts[2] == 'D':
      logical_data = parts[0]
    elif parts[2] == 'L':
      logical_log = parts[0]
  if not logical_data or not logical_log:
    match = re.search(r'^(\S+)\s+\S+\s+D\s', out, re.MULTILINE)
    if match:
      logical_data = match.group(1)
    match = re.search(r'^(\S+)\s+\S+\s+L\s', out, re.MULTILINE)
    if match:
      logical_log = match.group(1)
  if not logical_data or not logical_log:
    raise RuntimeError(f'Could not parse FILELISTONLY:\n{out[:2000]}')

  data_file = f'/var/opt/mssql/data/{IMAGE_RESTORE_DB}.mdf'
  log_file = f'/var/opt/mssql/data/{IMAGE_RESTORE_DB}_log.ldf'
  restore_sql = f"""
SET NOCOUNT ON;
RESTORE DATABASE [{IMAGE_RESTORE_DB}]
FROM DISK = N'{IMAGE_RESTORE_BAK}'
WITH
  MOVE N'{logical_data}' TO N'{data_file}',
  MOVE N'{logical_log}' TO N'{log_file}',
  RECOVERY,
  STATS = 5,
  BUFFERCOUNT = 64,
  MAXTRANSFERSIZE = 4194304;
"""
  rc, out = _sqlcmd(f"-Q \"{restore_sql.replace(chr(34), chr(92)+chr(34))}\"", timeout=None)
  if rc != 0:
    # stdin is safer for multiline
    inner = '/opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$MSSQL_SA_PASSWORD" -w 65535 -i /dev/stdin'
    cmd = ['docker', 'exec', '-i', '-e', f'MSSQL_SA_PASSWORD={MSSQL_PASSWORD}', MSSQL_CONTAINER, 'bash', '-c', inner]
    proc = subprocess.run(cmd, input=restore_sql, text=True, capture_output=True, timeout=None)
    out = (proc.stdout or '') + ('\n' + proc.stderr if proc.stderr else '')
    if proc.returncode != 0:
      state = database_state(IMAGE_RESTORE_DB)
      if state == 'RESTORING':
        logger.warning('Restore in progress; will resume on next run')
        return
      raise RuntimeError(f'RESTORE failed:\n{out}')

  final = database_state(IMAGE_RESTORE_DB)
  if final != 'ONLINE':
    if final == 'RESTORING':
      logger.warning('Restore still RESTORING; service will resume')
      return
    raise RuntimeError(f'Expected ONLINE after restore, got {final!r}')


def ensure_source_database_forever() -> None:
  while True:
    try:
      ensure_source_database()
      if database_state(IMAGE_RESTORE_DB) == 'ONLINE':
        return
      time.sleep(60)
    except Exception as exc:
      logger.exception('Source restore failed: %s', exc)
      time.sleep(60)
