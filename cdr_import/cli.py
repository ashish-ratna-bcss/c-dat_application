from __future__ import annotations
import argparse
import json
import logging
import sys
from pathlib import Path
from cdr_import.config import UPLOAD_DONE_DIR, UPLOAD_FAILED_DIR, UPLOAD_INBOX_DIR, UPLOAD_PROCESSING_DIR
from cdr_import.importer import CdrImportError, import_file

def setup_logging(verbose: bool) -> None:
    level = logging.DEBUG if verbose else logging.INFO
    logging.basicConfig(level=level, format='%(asctime)s [%(levelname)s] %(name)s: %(message)s')

def cmd_import(args: argparse.Namespace) -> int:
    dry_run = not args.commit
    try:
        result = import_file(args.file, dry_run=dry_run, batch_size=args.batch_size, operator=args.operator, target_phone=args.phone, resume=not args.no_resume)
    except CdrImportError as exc:
        print(str(exc), file=sys.stderr)
        return 1
    print(json.dumps(result, indent=2, default=str))
    return 0 if result.get('status') != 'failed' else 1

def cmd_validate_dir(args: argparse.Namespace) -> int:
    directory = Path(args.directory)
    files = sorted(directory.glob('*'))
    files = [f for f in files if f.is_file()]
    if not files:
        print(f'No files in {directory}')
        return 0
    exit_code = 0
    summary = []
    for fp in files:
        try:
            result = import_file(fp, dry_run=True)
            summary.append(result)
            print(f'OK  {fp.name}: operator={result['operator']} records={result['total_records']} warnings={len(result['warnings'])}')
            for w in result['warnings'][:3]:
                print(f'    warn: {w}')
        except Exception as exc:
            exit_code = 1
            print(f'ERR {fp.name}: {exc}', file=sys.stderr)
    if args.json:
        print(json.dumps(summary, indent=2, default=str))
    return exit_code

def cmd_worker(args: argparse.Namespace) -> int:
    import shutil
    import time
    from cdr_import.worker import run_once
    for d in (UPLOAD_INBOX_DIR, UPLOAD_PROCESSING_DIR, UPLOAD_DONE_DIR, UPLOAD_FAILED_DIR):
        d.mkdir(parents=True, exist_ok=True)
    logging.info('CDR import worker started (poll=%ss, commit=%s)', args.poll, args.commit)
    while True:
        processed = run_once(commit=args.commit, batch_size=args.batch_size)
        if not processed and (not args.once):
            time.sleep(args.poll)
            continue
        if args.once:
            break
    return 0

def cmd_init_schema(args: argparse.Namespace) -> int:
    from cdr_import.db import db_connection, ensure_schema
    with db_connection() as conn:
        ensure_schema(conn)
    print('CDR import schema ready (cdr_import_jobs, cdatpcsuspect_staging).')
    return 0

def build_parser() -> argparse.ArgumentParser:
    p = argparse.ArgumentParser(description='CDR document import utilities')
    p.add_argument('-v', '--verbose', action='store_true')
    sub = p.add_subparsers(dest='command', required=True)
    init = sub.add_parser('init-schema', help='Create staging/job tables in PostgreSQL')
    init.set_defaults(func=cmd_init_schema)
    imp = sub.add_parser('import', help='Import or dry-run a single file')
    imp.add_argument('file', help='Path to CDR document')
    imp.add_argument('--commit', action='store_true', help='Insert into staging table (default: dry-run)')
    imp.add_argument('--batch-size', type=int, default=500)
    imp.add_argument('--operator', choices=['airtel', 'bsnl', 'vi', 'jio'])
    imp.add_argument('--phone', help='Override target phone number')
    imp.add_argument('--no-resume', action='store_true', help='Ignore checkpoint and start from row 0 on commit')
    imp.set_defaults(func=cmd_import)
    val = sub.add_parser('validate-dir', help='Dry-run parse all files in a directory')
    val.add_argument('directory', nargs='?', default='/mnt/storage1/cdr_documents')
    val.add_argument('--json', action='store_true')
    val.set_defaults(func=cmd_validate_dir)
    worker = sub.add_parser('worker', help='Background worker for inbox directory')
    worker.add_argument('--commit', action='store_true', help='Insert into staging (default: dry-run only)')
    worker.add_argument('--poll', type=int, default=10)
    worker.add_argument('--batch-size', type=int, default=500)
    worker.add_argument('--once', action='store_true', help='Process one poll cycle and exit')
    worker.set_defaults(func=cmd_worker)
    return p

def main(argv: list[str] | None=None) -> int:
    parser = build_parser()
    args = parser.parse_args(argv)
    setup_logging(args.verbose)
    return args.func(args)
if __name__ == '__main__':
    raise SystemExit(main())
