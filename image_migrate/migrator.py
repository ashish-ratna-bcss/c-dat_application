from __future__ import annotations

import base64
import logging
import time
from typing import Any

import psycopg2
import psycopg2.extras
import pyodbc

from .config import LOG_DIR, PG_DATABASE, SLEEP_ON_ERROR_SEC, mssql_conn_str, pg_conn_kwargs

logger = logging.getLogger(__name__)

SLEEP_ON_ERROR_SEC = int(__import__('os').environ.get('IMAGE_MIGRATE_RETRY_SEC', '15'))


class ImageMigrator:
  def __init__(self, job: dict[str, Any]) -> None:
    self.job = job
    self.job_name = job['job_name']
    self.log_file = LOG_DIR / f'{self.job_name}_migration.log'
    self._configure_logging()

  def _configure_logging(self) -> None:
    root = logging.getLogger()
    if not any(isinstance(h, logging.FileHandler) and getattr(h, 'baseFilename', '') == str(self.log_file) for h in root.handlers):
      handler = logging.FileHandler(self.log_file)
      handler.setFormatter(logging.Formatter('%(asctime)s - %(levelname)s - %(message)s'))
      root.addHandler(handler)
    if not root.handlers:
      logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
    root.setLevel(logging.INFO)

  def _ensure_checkpoint_table(self, cur) -> None:
    cur.execute(
      """
      CREATE TABLE IF NOT EXISTS image_migration_checkpoint (
          job_name        VARCHAR(64) PRIMARY KEY,
          last_key        TEXT NOT NULL DEFAULT '',
          rows_committed  BIGINT NOT NULL DEFAULT 0,
          status          VARCHAR(20) NOT NULL DEFAULT 'running',
          error_message   TEXT,
          updated_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
      )
      """
    )

  def _load_checkpoint(self, cur) -> dict[str, Any]:
    self._ensure_checkpoint_table(cur)
    cur.execute(
      'SELECT last_key, rows_committed, status FROM image_migration_checkpoint WHERE job_name = %s',
      (self.job_name,),
    )
    row = cur.fetchone()
    if not row:
      cur.execute(
        """
        INSERT INTO image_migration_checkpoint (job_name, last_key, rows_committed, status)
        VALUES (%s, '', 0, 'running')
        """,
        (self.job_name,),
      )
      return {'last_key': '', 'rows_committed': 0, 'status': 'running'}
    return {'last_key': row[0] or '', 'rows_committed': int(row[1]), 'status': row[2]}

  def _save_checkpoint(self, cur, *, last_key: str, rows_committed: int, status: str = 'running', error: str | None = None) -> None:
    cur.execute(
      """
      INSERT INTO image_migration_checkpoint (job_name, last_key, rows_committed, status, error_message, updated_at)
      VALUES (%s, %s, %s, %s, %s, NOW())
      ON CONFLICT (job_name) DO UPDATE SET
          last_key = EXCLUDED.last_key,
          rows_committed = EXCLUDED.rows_committed,
          status = EXCLUDED.status,
          error_message = EXCLUDED.error_message,
          updated_at = NOW()
      """,
      (self.job_name, last_key, rows_committed, status, error),
    )

  def _table_exists_mssql(self, cur, table_name: str) -> bool:
    cur.execute('SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?', table_name)
    return cur.fetchone() is not None

    def _ensure_pg_schema(self, pg_conn) -> None:
        job = self.job
        with pg_conn.cursor() as cur:
      if job['pg_table'] == 'mo_image_table':
        cur.execute(
          """
          CREATE TABLE IF NOT EXISTS mo_image_table (
              mo_key VARCHAR(100) NOT NULL,
              image TEXT,
              PRIMARY KEY (mo_key)
          )
          """
        )
      elif job['pg_table'] == 'suspect_image_table':
        cur.execute(
          """
          CREATE TABLE IF NOT EXISTS suspect_image_table (
              irkey VARCHAR(50) NOT NULL,
              mobile VARCHAR(50) NOT NULL DEFAULT '',
              image TEXT
          )
          """
        )
        cur.execute(
          """
          CREATE UNIQUE INDEX IF NOT EXISTS suspect_image_table_irkey_mobile_uidx
          ON suspect_image_table (irkey, mobile)
          """
        )
      elif job['pg_table'] == 'image_table':
        cur.execute(
          """
          CREATE TABLE IF NOT EXISTS image_table (
              irkey VARCHAR(50) NOT NULL PRIMARY KEY,
              image BYTEA
          )
          """
        )
      pg_conn.commit()

  def _normalize_image(self, value: Any) -> Any:
    if value is None:
      return None
    encoding = self.job.get('image_encoding', 'text_base64')
    if isinstance(value, memoryview):
      value = value.tobytes()
    if isinstance(value, bytes):
      if encoding == 'bytea_or_text':
        return psycopg2.Binary(value)
      try:
        text = value.decode('utf-8').strip()
        if text.startswith('/9j/') or text.startswith('iVBOR'):
          return text
      except UnicodeDecodeError:
        pass
      return base64.b64encode(value).decode('ascii')
    if isinstance(value, str):
      text = value.strip()
      if encoding == 'bytea_or_text' and (text.startswith('/9j/') or text.startswith('iVBOR')):
        return text
      if encoding == 'bytea_or_text':
        try:
          return psycopg2.Binary(base64.b64decode(text))
        except Exception:
          return psycopg2.Binary(text.encode('utf-8', errors='replace'))
      return text
    return value

  def _build_select_query(self, last_key: str) -> tuple[str, list[Any]]:
    job = self.job
    key_col = job['mssql_key_column']
    sec_col = job.get('mssql_secondary_key')
    image_col = job['image_column']
    batch_size = int(job.get('batch_size', 500))
    cols = [key_col]
    if sec_col:
      cols.append(sec_col)
    cols.append(image_col)
    select_cols = ', '.join(f'[{c}]' if ' ' in c else c for c in cols)
    bracketed_key = f'[{key_col}]' if ' ' in key_col else key_col

    if sec_col:
      bracketed_sec = f'[{sec_col}]' if ' ' in sec_col else sec_col
      if last_key:
        parts = last_key.split('\x1f', 1)
        last_primary = parts[0]
        last_secondary = parts[1] if len(parts) > 1 else ''
        where = (
          f'WHERE ({bracketed_key} > ?) OR ({bracketed_key} = ? AND ISNULL({bracketed_sec}, \'\') > ?)'
        )
        params = [last_primary, last_primary, last_secondary]
      else:
        where = 'WHERE 1=1'
        params = []
      order = f'ORDER BY {bracketed_key} ASC, ISNULL({bracketed_sec}, \'\') ASC'
    else:
      if last_key:
        where = f'WHERE {bracketed_key} > ?'
        params = [last_key]
      else:
        where = 'WHERE 1=1'
        params = []
      order = f'ORDER BY {bracketed_key} ASC'

    query = f"""
      SELECT TOP {batch_size} {select_cols}
      FROM {job['mssql_table']} WITH (NOLOCK)
      {where}
      {order}
    """
    return query, params

  def _row_checkpoint_key(self, row: tuple[Any, ...]) -> str:
    if self.job.get('mssql_secondary_key'):
      return f'{row[0]}\x1f{row[1] or ""}'
    return str(row[0])

  def _insert_rows(self, cur, rows: list[tuple[Any, ...]]) -> int:
    job = self.job
    inserted = 0
    sec_col = job.get('mssql_secondary_key')
    image_idx = 2 if sec_col else 1

    for row in rows:
      image_value = self._normalize_image(row[image_idx])
      if image_value is None or image_value == '':
        continue

      if job['pg_table'] == 'suspect_image_table':
        cur.execute(
          """
          INSERT INTO suspect_image_table (irkey, mobile, image)
          VALUES (%s, %s, %s)
          ON CONFLICT (irkey, mobile) DO NOTHING
          """,
          (str(row[0]), str(row[1] or ''), image_value),
        )
      elif job['pg_table'] == 'image_table':
        cur.execute(
          """
          INSERT INTO image_table (irkey, image)
          VALUES (%s, %s)
          ON CONFLICT (irkey) DO NOTHING
          """,
          (str(row[0]), image_value),
        )
      elif job['pg_table'] == 'mo_image_table':
        cur.execute(
          """
          INSERT INTO mo_image_table (mo_key, image)
          VALUES (%s, %s)
          ON CONFLICT (mo_key) DO NOTHING
          """,
          (str(row[0]), image_value),
        )
      if cur.rowcount:
        inserted += 1
    return inserted

  def run(self) -> None:
    job = self.job
    mssql = pyodbc.connect(mssql_conn_str(job['mssql_database']), autocommit=True, timeout=600)
    pg = psycopg2.connect(**pg_conn_kwargs(PG_DATABASE))
    try:
      m_cur = mssql.cursor()
      if not self._table_exists_mssql(m_cur, job['mssql_table']):
        raise RuntimeError(
          f"MSSQL table {job['mssql_database']}.dbo.{job['mssql_table']} not found — "
          f'restore {job["mssql_database"]} or set IMAGE_MSSQL_SOURCE_DB'
        )

      self._ensure_pg_schema(pg)

      with pg.cursor() as p_cur:
        checkpoint = self._load_checkpoint(p_cur)
        pg.commit()

      if checkpoint['status'] == 'completed':
        logger.info('Job %s already completed (%s rows)', self.job_name, checkpoint['rows_committed'])
        return

      last_key = checkpoint['last_key']
      total_rows = int(checkpoint['rows_committed'])
      logger.info('Resuming %s from key %r (%s rows committed)', self.job_name, last_key, total_rows)

      while True:
        query, params = self._build_select_query(last_key)
        m_cur.execute(query, params)
        rows = m_cur.fetchall()
        if not rows:
          with pg.cursor() as p_cur:
            self._save_checkpoint(p_cur, last_key=last_key, rows_committed=total_rows, status='completed')
            pg.commit()
          logger.info('DONE %s: %s rows committed', self.job_name, total_rows)
          return

        with pg.cursor() as p_cur:
          inserted = self._insert_rows(p_cur, rows)
          total_rows += inserted
          last_key = self._row_checkpoint_key(rows[-1])
          self._save_checkpoint(p_cur, last_key=last_key, rows_committed=total_rows, status='running')
          pg.commit()

        logger.info('Batch %s: +%s new rows (scanned %s), total=%s, last_key=%r', self.job_name, inserted, len(rows), total_rows, last_key)
    finally:
      mssql.close()
      pg.close()


def run_forever(job: dict[str, Any]) -> None:
  migrator = ImageMigrator(job)
  while True:
    try:
      migrator.run()
      break
    except Exception as exc:
      logger.exception('Migration %s failed: %s', job['job_name'], exc)
      try:
        pg = psycopg2.connect(**pg_conn_kwargs(PG_DATABASE))
        with pg.cursor() as cur:
          migrator._ensure_checkpoint_table(cur)
          state = migrator._load_checkpoint(cur)
          migrator._save_checkpoint(
            cur,
            last_key=str(state['last_key']),
            rows_committed=int(state['rows_committed']),
            status='running',
            error=str(exc),
          )
          pg.commit()
        pg.close()
      except Exception:
        pass
      time.sleep(SLEEP_ON_ERROR_SEC)
