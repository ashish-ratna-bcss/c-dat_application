from __future__ import annotations
import os
import re
import shutil
import subprocess
from dataclasses import dataclass
from pathlib import Path
from .config import MSSQL_CONTAINER, MSSQL_DATA_CONTAINER_DIR, MSSQL_DATA_HOST_DIR, MSSQL_SA_PASSWORD

class SdrRestoreError(Exception):
    pass

@dataclass
class BackupFileRow:
    logical_name: str
    file_type: str
    physical_name: str

def sql_literal(value: str) -> str:
    return value.replace("'", "''")

def bracket_db(name: str) -> str:
    return '[' + name.replace(']', ']]') + ']'

def _docker_exec_base() -> list[str]:
    cmd = ['docker', 'exec']
    if MSSQL_SA_PASSWORD:
        cmd.extend(['-e', f'MSSQL_SA_PASSWORD={MSSQL_SA_PASSWORD}'])
    cmd.append(MSSQL_CONTAINER)
    return cmd

def run_sqlcmd_query(tsql: str, *, timeout: int | None=120) -> str:
    inner = '/opt/mssql-tools18/bin/sqlcmd -C -b -S localhost -U SA -P "$MSSQL_SA_PASSWORD" -s"|" -W -w 65535 -Q ' + repr(tsql)
    cmd = _docker_exec_base() + ['bash', '-c', inner]
    proc = subprocess.run(cmd, capture_output=True, text=True, timeout=timeout)
    out = (proc.stdout or '') + ('\n' + proc.stderr if proc.stderr else '')
    if proc.returncode != 0:
        raise SdrRestoreError(f'sqlcmd failed ({proc.returncode}):\n{out}')
    return out

def run_sqlcmd_stdin(tsql: str, *, timeout: int | None=None) -> tuple[int, str]:
    inner = '/opt/mssql-tools18/bin/sqlcmd -C -b -S localhost -U SA -P "$MSSQL_SA_PASSWORD" -w 65535 -i /dev/stdin'
    cmd = _docker_exec_base() + ['bash', '-c', inner]
    proc = subprocess.run(cmd, input=tsql, text=True, capture_output=True, timeout=timeout)
    out = (proc.stdout or '') + ('\n' + proc.stderr if proc.stderr else '')
    return (proc.returncode, out)

def is_sqlcmd_separator_row(line: str) -> bool:
    parts = [p.strip() for p in line.split('|')]
    if not parts:
        return True
    return all((not p or set(p) <= {'-', '.'} for p in parts))

def parse_filelistonly(output: str) -> list[BackupFileRow]:
    lines = [ln.rstrip() for ln in output.splitlines() if ln.strip()]
    header_idx = None
    for i, line in enumerate(lines):
        if line.startswith('LogicalName|'):
            header_idx = i
            break
    if header_idx is None:
        raise SdrRestoreError(f'Could not parse FILELISTONLY:\n{output[:2000]}')
    rows: list[BackupFileRow] = []
    for line in lines[header_idx + 1:]:
        if 'rows affected' in line:
            break
        if is_sqlcmd_separator_row(line):
            continue
        parts = line.split('|')
        if len(parts) < 3:
            continue
        logical, physical, ftype = (parts[0].strip(), parts[1].strip(), parts[2].strip())
        if logical and (not set(logical) <= {'-', '.'}):
            rows.append(BackupFileRow(logical, ftype, physical))
    if not rows:
        raise SdrRestoreError('No logical files found in backup.')
    return rows

def parse_headeronly_database_name(output: str) -> str:
    lines = [ln.rstrip() for ln in output.splitlines() if ln.strip()]
    for i, line in enumerate(lines):
        if '|' not in line or 'DatabaseName' not in line:
            continue
        headers = [h.strip() for h in line.split('|')]
        if 'DatabaseName' not in headers:
            continue
        idx = headers.index('DatabaseName')
        for cand in lines[i + 1:]:
            if is_sqlcmd_separator_row(cand):
                continue
            vals = cand.split('|')
            if idx < len(vals):
                name = vals[idx].strip()
                if name and (not set(name) <= {'-', '.'}):
                    return name
    raise SdrRestoreError(f'Could not parse database name from HEADERONLY:\n{output[:2000]}')

def sanitize_filename_part(value: str) -> str:
    value = re.sub('[^\\w\\-.]+', '_', value)
    return value[:120]

def dest_path_for_file_row(database_name: str, row: BackupFileRow) -> Path:
    ft = row.file_type.upper()
    phys = Path(row.physical_name.replace('\\', '/'))
    suf = phys.suffix.lower()
    if ft == 'D':
        ext = suf if suf in ('.mdf', '.ndf') else '.mdf'
    elif ft == 'L':
        ext = '.ldf'
    else:
        ext = suf if suf else f'_{row.file_type}.dat'
    base = sanitize_filename_part(f'{database_name}_{row.logical_name}')
    return MSSQL_DATA_CONTAINER_DIR / f'{base}{ext}'

def build_restore_sql(database_name: str, bak_path: str, file_rows: list[BackupFileRow], *, replace: bool) -> str:
    move_clauses = []
    for row in file_rows:
        dest = dest_path_for_file_row(database_name, row)
        move_clauses.append(f"MOVE N'{sql_literal(row.logical_name)}' TO N'{sql_literal(str(dest))}'")
    replace_clause = ', REPLACE' if replace else ''
    buf = os.environ.get('RESTORE_BUFFER_COUNT', '64').strip() or '64'
    moves = ',\n    '.join(move_clauses)
    return f"\nSET NOCOUNT ON;\nRESTORE DATABASE {bracket_db(database_name)}\nFROM DISK = N'{sql_literal(bak_path)}'\nWITH\n    {moves},\n    RECOVERY,\n    STATS = 5,\n    BUFFERCOUNT = {int(buf)},\n    MAXTRANSFERSIZE = 4194304{replace_clause};\n".strip()

def build_restore_restart_sql(database_name: str, bak_path: str) -> str:
    return f"\nSET NOCOUNT ON;\nRESTORE DATABASE {bracket_db(database_name)}\nFROM DISK = N'{sql_literal(bak_path)}'\nWITH RESTART;\n".strip()

def get_database_state(database_name: str) -> str | None:
    q = f"SELECT CONCAT(CAST(COUNT(*) AS varchar(2)), ':', ISNULL(MAX(state_desc), '')) FROM sys.databases WHERE name = N'{sql_literal(database_name)}';"
    out = run_sqlcmd_query(q, timeout=60)
    for line in out.splitlines():
        match = re.match('^(\\d+):(.*)$', line.strip())
        if not match:
            continue
        count, desc = (match.group(1), match.group(2).strip())
        if count == '0':
            return None
        return desc or None
    raise SdrRestoreError(f'Could not parse database state:\n{out}')

def stage_bak_on_host(source_bak: Path) -> tuple[Path, str]:
    if not source_bak.is_file():
        raise SdrRestoreError(f'Backup file not found: {source_bak}')
    if source_bak.suffix.lower() != '.bak':
        raise SdrRestoreError('SDR uploads must be a .bak file.')
    MSSQL_DATA_HOST_DIR.mkdir(parents=True, exist_ok=True)
    dest = MSSQL_DATA_HOST_DIR / source_bak.name
    if source_bak.resolve() != dest.resolve():
        try:
            os.link(source_bak, dest)
        except OSError:
            with source_bak.open('rb') as src, dest.open('wb') as out:
                shutil.copyfileobj(src, out, length=64 * 1024 * 1024)
    container_path = str(MSSQL_DATA_CONTAINER_DIR / dest.name)
    return (dest, container_path)

def restore_database_from_bak(source_bak: Path, *, replace: bool=True) -> dict:
    _, container_bak = stage_bak_on_host(source_bak)
    bak_sql = sql_literal(container_bak)
    _, header_out = _run_sqlcmd_query_rc(f"RESTORE HEADERONLY FROM DISK = N'{bak_sql}';", timeout=600)
    db_name = parse_headeronly_database_name(header_out)
    state = get_database_state(db_name)
    if state == 'RESTORING':
        sql = build_restore_restart_sql(db_name, container_bak)
        rc, out = run_sqlcmd_stdin(sql, timeout=None)
        if rc != 0:
            raise SdrRestoreError(f'RESTORE WITH RESTART failed:\n{out}')
    elif state == 'ONLINE' and (not replace):
        return {'database': db_name, 'status': 'already_online', 'message': 'Database already online.'}
    else:
        _, filelist_out = _run_sqlcmd_query_rc(f"RESTORE FILELISTONLY FROM DISK = N'{bak_sql}';", timeout=600)
        files = parse_filelistonly(filelist_out)
        need_replace = bool(state == 'ONLINE' and replace)
        sql = build_restore_sql(db_name, container_bak, files, replace=need_replace or state is not None)
        rc, out = run_sqlcmd_stdin(sql, timeout=None)
        if rc != 0:
            raise SdrRestoreError('RESTORE failed. Re-run to resume with RESTORE ... WITH RESTART if DB is RESTORING.\n' + out)
    final_state = get_database_state(db_name)
    if final_state != 'ONLINE':
        raise SdrRestoreError(f'Expected ONLINE after restore, got {final_state!r}')
    return {'database': db_name, 'status': 'restored', 'bak_path': container_bak}

def _run_sqlcmd_query_rc(tsql: str, *, timeout: int | None=120) -> tuple[int, str]:
    inner = '/opt/mssql-tools18/bin/sqlcmd -C -b -S localhost -U SA -P "$MSSQL_SA_PASSWORD" -s"|" -W -w 65535 -Q ' + repr(tsql)
    cmd = _docker_exec_base() + ['bash', '-c', inner]
    proc = subprocess.run(cmd, capture_output=True, text=True, timeout=timeout)
    out = (proc.stdout or '') + ('\n' + proc.stderr if proc.stderr else '')
    return (proc.returncode, out)
