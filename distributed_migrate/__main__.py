from __future__ import annotations
import argparse
import sys

from .citus_migrator import run_forever as run_migration_forever
from .config import CDATADDRESS_JOB, CELLIDS_JOB
from .imei_index import run_forever as run_imei_forever


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(description='Citus MSSQL → distributed_db migration workers')
    parser.add_argument('job', choices=['cdataddress', 'cellids', 'imei-index'], help='Which background job to run')
    args = parser.parse_args(argv)

    if args.job == 'cdataddress':
        run_migration_forever(CDATADDRESS_JOB)
    elif args.job == 'cellids':
        run_migration_forever(CELLIDS_JOB)
    else:
        run_imei_forever()
    return 0


if __name__ == '__main__':
    sys.exit(main())
