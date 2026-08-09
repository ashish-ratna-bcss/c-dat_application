from __future__ import annotations
import logging
import time

import psycopg2

from .config import LOG_DIR, MAIN_PG_DATABASE, SLEEP_ON_ERROR_SEC, pg_conn_kwargs

logger = logging.getLogger(__name__)
INDEX_NAME = 'idx_cdatpcsuspect_imeinumber'
LOG_FILE = LOG_DIR / 'imei_index_build.log'


def _configure_logging() -> None:
    if not logging.getLogger().handlers:
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[logging.FileHandler(LOG_FILE), logging.StreamHandler()],
        )


def _index_exists(cur) -> bool:
    cur.execute(
        """
        SELECT 1 FROM pg_indexes
        WHERE schemaname = 'public' AND tablename = 'cdatpcsuspect' AND indexname = %s
        """,
        (INDEX_NAME,),
    )
    return cur.fetchone() is not None


def _index_ready(cur) -> bool:
    cur.execute(
        """
        SELECT indisvalid FROM pg_index i
        JOIN pg_class c ON c.oid = i.indexrelid
        WHERE c.relname = %s
        """,
        (INDEX_NAME,),
    )
    row = cur.fetchone()
    return bool(row and row[0])


def run() -> None:
    _configure_logging()
    conn = psycopg2.connect(**pg_conn_kwargs(MAIN_PG_DATABASE))
    conn.autocommit = True
    try:
        with conn.cursor() as cur:
            # Limit parallel index build RAM on a busy shared host.
            cur.execute("SET max_parallel_maintenance_workers = 2")
            cur.execute("SET maintenance_work_mem = '512MB'")
            if _index_exists(cur) and _index_ready(cur):
                logger.info('Index %s already exists and is valid', INDEX_NAME)
                return
            invalid = _index_exists(cur) and not _index_ready(cur)
            if invalid:
                logger.info('Dropping invalid index %s before rebuild', INDEX_NAME)
                cur.execute(f'DROP INDEX CONCURRENTLY IF EXISTS {INDEX_NAME}')

        ddl = (
            f'CREATE INDEX CONCURRENTLY {INDEX_NAME} '
            f'ON public.cdatpcsuspect (imeinumber) '
            f'WHERE imeinumber IS NOT NULL AND imeinumber <> 0'
        )
        logger.info('Starting: %s', ddl)
        with conn.cursor() as cur:
            cur.execute(ddl)
        logger.info('Index %s build completed', INDEX_NAME)
    finally:
        conn.close()


def run_forever() -> None:
    while True:
        try:
            run()
            return
        except Exception as exc:
            logger.exception('IMEI index build failed: %s', exc)
            time.sleep(SLEEP_ON_ERROR_SEC)
