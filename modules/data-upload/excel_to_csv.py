#!/usr/bin/env python3
"""Convert XLS/XLSX to CSV for CDR upload pipeline."""
from __future__ import annotations
import csv
import sys
from pathlib import Path

# OLE compound document (.xls) and ZIP (.xlsx) magic bytes.
_XLS_MAGIC = b'\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1'
_XLSX_MAGIC = b'PK'


def _is_text_csv(path: Path) -> bool:
    """Many operator CDR exports are CSV saved with a .xls extension."""
    sample = path.read_bytes()[:8192]
    if not sample:
        return True
    if sample.startswith(_XLS_MAGIC) or sample.startswith(_XLSX_MAGIC):
        return False
    if b'\x00' in sample:
        return False
    try:
        sample.decode('utf-8')
        return True
    except UnicodeDecodeError:
        try:
            sample.decode('latin-1')
            return True
        except UnicodeDecodeError:
            return False


def _copy_text_csv(src: Path, dest: Path) -> None:
    raw = src.read_bytes()
    try:
        text = raw.decode('utf-8-sig')
    except UnicodeDecodeError:
        text = raw.decode('latin-1')
    dest.write_text(text, encoding='utf-8', newline='')


def convert(src: Path, dest: Path) -> None:
    suffix = src.suffix.lower()
    if suffix == '.csv' or _is_text_csv(src):
        _copy_text_csv(src, dest)
        return
    try:
        import openpyxl
    except ImportError:
        openpyxl = None
    if openpyxl and suffix in ('.xlsx', '.xlsm', '.xltx'):
        wb = openpyxl.load_workbook(src, read_only=True, data_only=True)
        ws = wb.active
        with dest.open('w', newline='', encoding='utf-8') as out:
            writer = csv.writer(out)
            for row in ws.iter_rows(values_only=True):
                writer.writerow(['' if v is None else str(v) for v in row])
        wb.close()
        return
    try:
        import xlrd
        book = xlrd.open_workbook(src)
        sheet = book.sheet_by_index(0)
        with dest.open('w', newline='', encoding='utf-8') as out:
            writer = csv.writer(out)
            for row_idx in range(sheet.nrows):
                writer.writerow([str(sheet.cell_value(row_idx, col_idx)) for col_idx in range(sheet.ncols)])
        return
    except ImportError:
        pass
    try:
        import pandas as pd
        engine = 'openpyxl' if suffix == '.xlsx' else 'xlrd'
        df = pd.read_excel(src, dtype=str, engine=engine)
        df.to_csv(dest, index=False)
        return
    except ImportError as exc:
        raise SystemExit('openpyxl, xlrd, or pandas required for binary Excel conversion') from exc


def main() -> int:
    if len(sys.argv) != 3:
        print('Usage: excel_to_csv.py <input.xlsx> <output.csv>', file=sys.stderr)
        return 2
    src = Path(sys.argv[1]).resolve()
    dest = Path(sys.argv[2]).resolve()
    if not src.is_file():
        print(f'Input not found: {src}', file=sys.stderr)
        return 1
    convert(src, dest)
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
