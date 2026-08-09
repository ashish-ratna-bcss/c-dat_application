from __future__ import annotations
import logging
import time
from typing import Any, Callable

import psycopg2
import psycopg2.extras
import pyodbc

from .config import LOG_DIR, PG_DATABASE, SLEEP_ON_ERROR_SEC, mssql_conn_str, pg_conn_kwargs
from .schema import adapt_value, fetch_mssql_columns, mssql_type_to_pg

logger = logging.getLogger(__name__)


class CitusMigrator:
    def __init__(self, job: dict[str, Any]) -> None:
        self.job = job
        self.job_name = job['job_name']
        self.log_file = LOG_DIR / f'{self.job_name}_migration.log'
        self._configure_logging()

    def _configure_logging(self) -> None:
        root = logging.getLogger()
        if not root.handlers:
            logging.basicConfig(
                level=logging.INFO,
                format='%(asctime)s - %(levelname)s - %(message)s',
                handlers=[
                    logging.FileHandler(self.log_file),
                    logging.StreamHandler(),
                ],
            )

    def _ensure_checkpoint_table(self, cur) -> None:
        cur.execute(
            """
            CREATE TABLE IF NOT EXISTS distributed_migration_checkpoint (
                job_name        VARCHAR(64) PRIMARY KEY,
                last_key        BIGINT NOT NULL DEFAULT 0,
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
            """
            SELECT last_key, rows_committed, status
            FROM distributed_migration_checkpoint
            WHERE job_name = %s
            """,
            (self.job_name,),
        )
        row = cur.fetchone()
        if not row:
            cur.execute(
                """
                INSERT INTO distributed_migration_checkpoint (job_name, last_key, rows_committed, status)
                VALUES (%s, 0, 0, 'running')
                """,
                (self.job_name,),
            )
            return {'last_key': 0, 'rows_committed': 0, 'status': 'running'}
        return {'last_key': int(row[0]), 'rows_committed': int(row[1]), 'status': row[2]}

    def _save_checkpoint(self, cur, *, last_key: int, rows_committed: int, status: str = 'running', error: str | None = None) -> None:
        cur.execute(
            """
            INSERT INTO distributed_migration_checkpoint (job_name, last_key, rows_committed, status, error_message, updated_at)
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

    def _table_exists(self, cur, table_name: str) -> bool:
        cur.execute(
            "SELECT to_regclass(%s)",
            (f'public.{table_name}',),
        )
        return cur.fetchone()[0] is not None

    def _is_distributed(self, cur, table_name: str) -> bool:
        cur.execute(
            """
            SELECT 1 FROM citus_tables
            WHERE table_name = %s::regclass AND citus_table_type = 'distributed'
            """,
            (table_name,),
        )
        return cur.fetchone() is not None

    def _ensure_pg_table(self, pg_conn, columns: list[dict[str, Any]]) -> list[str]:
        table = self.job['pg_table']
        key_col = self.job['pg_key_column']
        pg_cols = [c['pg_name'] for c in columns]
        with pg_conn.cursor() as cur:
            if not self._table_exists(cur, table):
                col_defs = []
                for col in columns:
                    pg_type = mssql_type_to_pg(col['data_type'], col['char_len'], col['num_precision'], col.get('num_scale'))
                    null_sql = '' if col['nullable'] else ' NOT NULL'
                    col_defs.append(f'{col["pg_name"]} {pg_type}{null_sql}')
                ddl = f'CREATE TABLE {table} ({", ".join(col_defs)})'
                cur.execute(ddl)
                cur.execute(
                    f'ALTER TABLE {table} ADD CONSTRAINT {table}_{key_col}_uniq '
                    f'UNIQUE ({key_col}, {self.job["distribution_column"]})'
                )
                pg_conn.commit()
                logger.info('Created table %s with %s columns', table, len(pg_cols))
            else:
                cur.execute(
                    """
                    SELECT 1 FROM pg_constraint c
                    JOIN pg_class t ON c.conrelid = t.oid
                    WHERE t.relname = %s AND c.contype = 'u'
                    LIMIT 1
                    """,
                    (table,),
                )
                if not cur.fetchone():
                    cur.execute(
                        f'ALTER TABLE {table} ADD CONSTRAINT {table}_{key_col}_uniq '
                        f'UNIQUE ({key_col}, {self.job["distribution_column"]})'
                    )
                    pg_conn.commit()
        return pg_cols

    def _mssql_key_index_ready(self, m_cur) -> bool:
        index_name = self.job.get('mssql_key_index')
        if not index_name:
            return True
        table = self.job['mssql_table']
        m_cur.execute(
            """
            SELECT 1
            FROM sys.indexes i
            JOIN sys.objects o ON i.object_id = o.object_id
            WHERE o.name = ? AND i.name = ? AND i.type > 0
            """,
            table,
            index_name,
        )
        return m_cur.fetchone() is not None

    def _ensure_mssql_key_index(self, m_cur) -> None:
        index_name = self.job.get('mssql_key_index')
        if not index_name:
            return
        if self._mssql_key_index_ready(m_cur):
            logger.info('MSSQL index %s ready on %s', index_name, self.job['mssql_table'])
            return

        key_col = self.job['mssql_key_column']
        table = self.job['mssql_table']
        bracketed_key = f'[{key_col}]' if ' ' in key_col else key_col
        logger.warning(
            'Creating MSSQL index %s on %s(%s) — required for batch reads (table has no key index, full scans hang)',
            index_name,
            table,
            key_col,
        )
        m_cur.execute(
            f'CREATE NONCLUSTERED INDEX [{index_name}] ON [{table}] ({bracketed_key}) WITH (MAXDOP = 4)'
        )
        logger.info('MSSQL index %s created on %s', index_name, table)

    def _wait_for_mssql_key_index(self, m_cur) -> None:
        index_name = self.job.get('mssql_key_index')
        if not index_name:
            return
        if self._mssql_key_index_ready(m_cur):
            return
        raise RuntimeError(
            f'MSSQL index {index_name} missing on {self.job["mssql_table"]} — '
            f'run scripts/ensure_mssql_migration_indexes.sh or wait for index build'
        )

    def _ensure_citus_distribution(self, pg_conn) -> None:
        table = self.job['pg_table']
        dist_col = self.job['distribution_column']
        colocate = self.job.get('colocate_with')
        with pg_conn.cursor() as cur:
            if self._is_distributed(cur, table):
                logger.info('Table %s already distributed on %s', table, dist_col)
                return
            row_count = 0
            cur.execute(f'SELECT COUNT(*) FROM {table}')
            row_count = int(cur.fetchone()[0])
            if row_count > 0:
                raise RuntimeError(f'Cannot distribute {table}: already has {row_count} rows')
            if colocate:
                cur.execute(
                    "SELECT create_distributed_table(%s, %s, colocate_with => %s)",
                    (table, dist_col, colocate),
                )
            else:
                cur.execute(
                    "SELECT create_distributed_table(%s, %s, shard_count => %s)",
                    (table, dist_col, 256),
                )
            pg_conn.commit()
            logger.info('Distributed table %s on column %s (colocate_with=%s)', table, dist_col, colocate)

    def run(self) -> None:
        job = self.job
        batch_size = int(job.get('batch_size', 50000))
        mssql_key = job['mssql_key_column']
        pg_key = job['pg_key_column']

        mssql = pyodbc.connect(
            mssql_conn_str(job['mssql_database']),
            autocommit=True,
            timeout=int(job.get('mssql_query_timeout', 600)),
        )
        pg = psycopg2.connect(**pg_conn_kwargs(PG_DATABASE))
        try:
            m_cur = mssql.cursor()
            columns = fetch_mssql_columns(m_cur, job['mssql_table'])
            if not columns:
                raise RuntimeError(f'No columns for MSSQL table {job["mssql_table"]}')

            pg_cols = self._ensure_pg_table(pg, columns)
            self._ensure_citus_distribution(pg)
            self._wait_for_mssql_key_index(m_cur)

            with pg.cursor() as p_cur:
                checkpoint = self._load_checkpoint(p_cur)
                pg.commit()

            if checkpoint['status'] == 'completed':
                logger.info('Job %s already completed at key %s (%s rows)', self.job_name, checkpoint['last_key'], checkpoint['rows_committed'])
                return

            last_key = int(checkpoint['last_key'])
            total_rows = int(checkpoint['rows_committed'])
            logger.info('Resuming %s from %s=%s (%s rows committed)', self.job_name, pg_key, last_key, total_rows)

            mssql_names = [c['mssql_name'] for c in columns]
            bracketed_key = f'[{mssql_key}]' if ' ' in mssql_key else mssql_key
            select_cols = ', '.join(f'[{c}]' if ' ' in c else c for c in mssql_names)
            insert_cols = ', '.join(pg_cols)
            conflict_sql = f' ON CONFLICT ({pg_key}, {job["distribution_column"]}) DO NOTHING'

            index_name = job.get('mssql_key_index')
            index_hint = f' WITH (INDEX([{index_name}]), NOLOCK)' if index_name else ' WITH (NOLOCK)'

            while True:
                query = f"""
                    SELECT TOP {batch_size} {select_cols}
                    FROM {job['mssql_table']}{index_hint}
                    WHERE {bracketed_key} > ?
                    ORDER BY {bracketed_key} ASC
                """
                m_cur.execute(query, last_key)
                rows = m_cur.fetchall()
                if not rows:
                    with pg.cursor() as p_cur:
                        self._save_checkpoint(p_cur, last_key=last_key, rows_committed=total_rows, status='completed')
                        pg.commit()
                    logger.info('DONE %s: %s rows committed, last_key=%s', self.job_name, total_rows, last_key)
                    return

                adapted = [tuple(adapt_value(v) for v in row) for row in rows]
                key_idx = mssql_names.index(mssql_key)
                batch_last_key = int(rows[-1][key_idx])

                with pg.cursor() as p_cur:
                    psycopg2.extras.execute_values(
                        p_cur,
                        f'INSERT INTO {job["pg_table"]} ({insert_cols}) VALUES %s{conflict_sql}',
                        adapted,
                        page_size=min(len(adapted), 1000),
                    )
                    total_rows += len(adapted)
                    self._save_checkpoint(p_cur, last_key=batch_last_key, rows_committed=total_rows, status='running')
                    pg.commit()

                last_key = batch_last_key
                logger.info('Inserted batch for %s: total=%s last_key=%s', self.job_name, total_rows, last_key)
        finally:
            mssql.close()
            pg.close()


def run_forever(job: dict[str, Any]) -> None:
    migrator = CitusMigrator(job)
    while True:
        try:
            migrator.run()
            break
        except RuntimeError as exc:
            if job.get('mssql_key_index') and 'MSSQL index' in str(exc):
                logger.warning('%s', exc)
                time.sleep(60)
                continue
            raise
        except Exception as exc:
            logger.exception('Migration %s failed: %s', job['job_name'], exc)
            try:
                pg = psycopg2.connect(**pg_conn_kwargs(PG_DATABASE))
                with pg.cursor() as cur:
                    migrator._ensure_checkpoint_table(cur)
                    state = migrator._load_checkpoint(cur)
                    migrator._save_checkpoint(
                        cur,
                        last_key=int(state['last_key']),
                        rows_committed=int(state['rows_committed']),
                        status='running',
                        error=str(exc),
                    )
                    pg.commit()
                pg.close()
            except Exception:
                pass
            time.sleep(SLEEP_ON_ERROR_SEC)
