from __future__ import annotations

import argparse
import logging
import sys

from .config import ALL_IMAGE_JOBS, LOG_DIR, SUSPECT_IMAGE_JOB
from .migrator import run_forever
from .restore import ensure_source_database_forever

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(LOG_DIR / 'image_migrate_service.log'),
        logging.StreamHandler(),
    ],
)


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description='CDAT image tables MSSQL → PostgreSQL migration')
    parser.add_argument(
        'job',
        choices=['all', 'restore-only', 'suspect_image_table', 'image_table', 'mo_image_table'],
        help='Which migration job to run',
    )
    args = parser.parse_args(argv)

    if args.job in ('all', 'restore-only', 'suspect_image_table', 'image_table', 'mo_image_table'):
        ensure_source_database_forever()

    if args.job == 'restore-only':
        return 0

    jobs = ALL_IMAGE_JOBS if args.job == 'all' else [j for j in ALL_IMAGE_JOBS if j['job_name'] == args.job]
    for job in jobs:
        run_forever(job)
    return 0


if __name__ == '__main__':
    sys.exit(main())
