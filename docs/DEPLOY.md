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
| `IR_DB_NAME`, `JRMS_DB_NAME`, `PDACT_DB_NAME`, `ROWDY_SHEETS_DB_NAME`, `TRAINING_DB_NAME` | Satellite PostgreSQL DB names (FDW sources) |
| `CDAT_SQL_CONSOLE` | Set `0` to disable admin SQL console in production |
| `CDAT_SESSION_IDLE_MINUTES` | Session idle timeout (default 30) |
| `CDAT_LOGIN_MAX_ATTEMPTS` | Failed logins before lockout (default 5) |
| `CDAT_LOGIN_LOCKOUT_MINUTES` | Lockout window (default 15) |
| `MSSQL_SA_PASSWORD`, `MSSQL_CONTAINER` | SDR `.bak` pipeline only — see [`SDR_PIPELINE.md`](SDR_PIPELINE.md) |

PHP reads these via `config/db_config.php` (generated from `.env` on first bootstrap).

**Never commit** `.env`, `config/db_config.php`, CSV uploads, or `var/` staging data.

## Database setup

1. Create `CDATDUPL_DB` and satellite DBs (`IR_DB`, `JRMS_DB`, `TRAINING_DB`, etc.) or restore from backups.
2. Apply canonical schema: `sql/cdr_db.sql` (and satellite SQL files as needed).
3. Training satellite (separate DB, not local tables on CDATDUPL):

```bash
bash scripts/import_training_data.sh   # creates TRAINING_DB, schema, optional CSV/dump, then FDW
```

4. Mount FDW foreign tables into the main DB:

```bash
bash sql/apply_fdw.sh
```

5. NBWS court data (local table on CDATDUPL until IR source exists):

```bash
bash scripts/import_nbws_table.sh
```

6. Verify schema (must exit 0):

```bash
php scripts/schema_audit.php
```

### Performance indexes

Ensure these exist on heavy search paths (create on VPN if missing):

- `cdatpcsuspect(phone)`, `cdatpcsuspect(imeinumber)`, `cdatpcsuspect(celltowerid)`
- FDW join keys: `ir_particulars(irkey)`, `offence_details(irkey)`, `jrms_total_2012_to_2017` crime-head columns
- On **TRAINING_DB**: `training_strength_particulars(employee_id, general_no, name)`, `trng_att_with_empid(employee_id)`
- `public.nbws_verify_data_important(irkey)` on CDATDUPL_DB

## Web server

### Nginx (Linux production)

Use [`cdat-web.nginx.conf`](../cdat-web.nginx.conf) as a template. Set `root` to your deploy path (e.g. `/mnt/storage1/cdat-web`).

Document API proxy is not used for the current CDR upload. PHP talks to `dataUpload/` on port **8090**.

### Apache (optional / dev)

Use [`.htaccess`](../.htaccess). All requests route through `main.php`.

### Local dev

```bash
php -S localhost:8020 main.php
```

## Python services

CDR upload API (preview / staging / insert):

```bash
cd dataUpload
source env/bin/activate
python main.py          # http://127.0.0.1:8090
```

The previous import API (`python3 main.py` on **8088**) and `worker.py` are archived. See [SDR_PIPELINE.md](SDR_PIPELINE.md).

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

## Backups & restore

Daily backup (cron example):

```bash
pg_dump -h "$CDR_DB_HOST" -U "$CDR_DB_USER" -Fc "$CDR_DB_NAME" > /var/backups/cdat_$(date +%Y%m%d).dump
```

Restore drill (staging only):

```bash
pg_restore -h "$CDR_DB_HOST" -U "$CDR_DB_USER" -d CDATDUPL_DB_STAGING --clean /var/backups/cdat_YYYYMMDD.dump
```

## Worker monitoring

CDR staging/insert workers start with `dataUpload/python main.py`. There is no separate `worker.py`.

## Health check

```bash
curl -sS http://127.0.0.1:8020/health
# {"status":"ok","db":"ok","version":"..."}
```


## SDR pipeline (optional)

SDR is not in `dataUpload/` yet. Rebuild from [SDR_PIPELINE.md](SDR_PIPELINE.md). Old restore/migrate code is in `old_removed_codeShivang/` (gitignored).
