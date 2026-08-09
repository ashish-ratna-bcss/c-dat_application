# Windows equivalent of run_cdr_import_service.sh.
#
# The DB env vars are NOT optional. cdr_import/config.py::load_db_config() only
# falls back to db_config.php when an env value is empty, and its built-in
# defaults (port 5432, database "postgres") are non-empty -- so without these
# the service silently connects to the wrong PostgreSQL instance.

$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$Python = if ($env:CDR_PYTHON) { $env:CDR_PYTHON }
          else { "C:\Users\sunakshi\AppData\Local\Python\pythoncore-3.14-64\python.exe" }

if (-not (Test-Path $Python)) {
    throw "Python not found at '$Python'. Set CDR_PYTHON to your python.exe."
}

$env:PYTHONPATH      = $Root
$env:CDR_DB_HOST     = if ($env:CDR_DB_HOST)     { $env:CDR_DB_HOST }     else { "127.0.0.1" }
$env:CDR_DB_PORT     = if ($env:CDR_DB_PORT)     { $env:CDR_DB_PORT }     else { "5433" }
$env:CDR_DB_NAME     = if ($env:CDR_DB_NAME)     { $env:CDR_DB_NAME }     else { "postgress_db" }
$env:CDR_DB_USER     = if ($env:CDR_DB_USER)     { $env:CDR_DB_USER }     else { "postgres" }
$env:CDR_DB_PASSWORD = if ($env:CDR_DB_PASSWORD) { $env:CDR_DB_PASSWORD } else { "admin123" }

$ApiHost = if ($env:CDR_API_HOST) { $env:CDR_API_HOST } else { "127.0.0.1" }
$ApiPort = if ($env:CDR_API_PORT) { $env:CDR_API_PORT } else { "8088" }

# Note: run_cdr_import_service.sh sets CDR_STAGING_TABLE=cdatpcsuspect, which makes
# staging and target the same table and bypasses the approval step. We keep the
# config.py default (cdatpcsuspect_staging) so uploads stage for review first.

Set-Location (Join-Path $Root "cdr-import-service")
& $Python -m uvicorn app.main:app --host $ApiHost --port $ApiPort
