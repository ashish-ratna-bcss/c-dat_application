# Windows equivalent of run_cdr_import_service.sh.
#
# No database settings here. load_db_config() reads controller/db_config.php --
# the same file the web app uses -- so the connection is configured in exactly
# one place. Set CDR_DB_HOST / _PORT / _NAME / _USER / _PASSWORD only to point
# this service somewhere other than the site's own database.
#
# This script used to carry them, including the password as a literal default.
# It is inside the web root, so that password was being served over HTTP to
# anyone who requested /scripts/run_cdr_import_service.ps1.

$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
$Python = if ($env:CDR_PYTHON) { $env:CDR_PYTHON }
          else { "C:\Users\sunakshi\AppData\Local\Python\pythoncore-3.14-64\python.exe" }

if (-not (Test-Path $Python)) {
    throw "Python not found at '$Python'. Set CDR_PYTHON to your python.exe."
}

$env:PYTHONPATH = $Root

$ApiHost = if ($env:CDR_API_HOST) { $env:CDR_API_HOST } else { "127.0.0.1" }
$ApiPort = if ($env:CDR_API_PORT) { $env:CDR_API_PORT } else { "8088" }

# Note: run_cdr_import_service.sh sets CDR_STAGING_TABLE=cdatpcsuspect, which makes
# staging and target the same table and bypasses the approval step. We keep the
# config.py default (cdatpcsuspect_staging) so uploads stage for review first.

Set-Location (Join-Path $Root "cdr-import-service")

# uvicorn writes its startup banner and every request line to stderr, which is
# normal. Windows PowerShell wraps a native command's stderr in ErrorRecords, so
# with ErrorActionPreference still "Stop" the first log line -- "Started server
# process" -- terminates the script and takes the server with it. That only
# shows up when stderr is redirected (piping to a log, running from a task
# runner); in a plain console window it goes unnoticed until someone does.
$ErrorActionPreference = "Continue"
& $Python -m uvicorn app.main:app --host $ApiHost --port $ApiPort
