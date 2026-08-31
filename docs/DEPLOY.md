# CDAT Web — Production Deploy

## Prerequisites

- PHP 8.1+ with `pdo_pgsql`, `mbstring`, `json`
- PostgreSQL 14+ with `postgres_fdw` extension
- Python 3.10+ (CDR import worker + document API)
- Nginx or Apache with PHP-FPM

## Environment

Copy `.env.example` to `.env` and set:

| Variable | Purpose |
|----------|---------|
| `CDR_DB_HOST`, `CDR_DB_PORT` | PostgreSQL host |
| `CDR_DB_NAME` | Main app DB (default `CDATDUPL_DB`) |
| `CDR_DB_USER`, `CDR_DB_PASSWORD` | DB credentials |
| `IR_DB_NAME`, `JRMS_DB_NAME`, `PDACT_DB_NAME`, `ROWDY_SHEETS_DB_NAME` | FDW source DB names |
| `MSSQL_SA_PASSWORD`, `MSSQL_CONTAINER` | SDR `.bak` pipeline only |

PHP reads these via `config/db_config.php` (generated from `.env` on first bootstrap).

**Never commit** `.env`, `config/db_config.php`, CSV uploads, or `var/` staging data.

## Database setup

1. Create `CDATDUPL_DB` and satellite DBs (`IR_DB`, `JRMS_DB`, etc.) or restore from backups.
2. Apply canonical schema: `sql/cdr_db.sql` (and satellite SQL files as needed).
3. Mount FDW foreign tables into the main DB:

```bash
bash sql/apply_fdw.sh
```

## Web server

### Nginx (Linux production)

Use [`cdat-web.nginx.conf`](../cdat-web.nginx.conf) as a template. Set `root` to your deploy path (e.g. `/mnt/storage1/cdat-web`).

Document API proxy (`/document-api/`) forwards to Python service on port 8088.

### Apache (optional / dev)

Use [`.htaccess`](../.htaccess). All requests route through `main.php`.

### Local dev

```bash
php -S localhost:8020 main.php
```

## Python services

```bash
# Document processing API (CDR/SDR uploads)
python3 main.py          # http://127.0.0.1:8088

# Background CDR import worker
python3 worker.py
```

## Runtime directories

Create empty inbox folders (gitignored; required for uploads):

```
var/cdr_documents/inbox/cdr/
var/cdr_documents/inbox/sdr/
```

Do not populate `uploads/` or `var/` with real subscriber data in the repository.

## Pre-deploy checks

```bash
bash scripts/audit_mssql_usage.sh    # PHP web app must be PostgreSQL-only
php scripts/schema_audit.php         # Tables/FDW vs modules/
find modules -name '*.php' -print0 | xargs -0 -n1 php -l
```

See [`HANDOVER_CHECKLIST.md`](HANDOVER_CHECKLIST.md) for full sign-off steps.

## SDR pipeline (optional)

SDR subscriber `.bak` uploads use `sdr_import/` (MSSQL restore → PostgreSQL migrate). This is the **only** runtime MSSQL dependency. Requires Docker MSSQL or equivalent. Out of scope for standard CDR-only deployments.
