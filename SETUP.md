# CDAT setup

PHP 8.1+ (`pdo_pgsql`, `mbstring`, `json`), PostgreSQL 14+, Python 3.10+.

## 1. Config

```bash
cp .env.example .env
```

Set at least `CDR_DB_HOST`, `CDR_DB_PORT`, `CDR_DB_NAME`, `CDR_DB_USER`, `CDR_DB_PASSWORD`.

| Variable | Use |
|---|---|
| `CDR_DB_*` | Main database (`CDATDUPL_DB`) |
| `IR_DB_NAME`, `JRMS_DB_NAME`, `PDACT_DB_NAME`, `ROWDY_SHEETS_DB_NAME`, `TRAINING_DB_NAME` | Satellite DBs (FDW) |
| `CDAT_PCSUSPECT_SCHEMA` | CDR staging schema (default `cdatpcsuspectstagingdb`) |
| `CDAT_UPLOAD_SCHEMA` | Jobs + history (default `cdatupload`) |
| `DATA_UPLOAD_URL` | Local: leave unset (browser uses `:8090`). Nginx: `/` |

Never commit `.env`.

## 2. Database

Create `CDATDUPL_DB` (and satellites if you use them). Starting `dataUpload/main.py` applies `sql/*.sql` with `CREATE TABLE IF NOT EXISTS` and creates `cdatupload` + `cdatpcsuspectstagingdb`.

Optional:

```bash
bash sql/apply_fdw.sh
php scripts/schema_audit.php
```

## 3. Local (dev)

One command for PHP + API:

```bash
./deploy/dev.sh
```

Or separately:

```bash
php -S localhost:8020 main.php
```

```bash
cd dataUpload
python3 -m venv env
source env/bin/activate
pip install -r requirements.txt
python main.py
```

| | |
|---|---|
| App | http://localhost:8020/login |
| CDR | http://localhost:8020/data-upload/cdr |
| History | http://localhost:8020/data-upload/history |
| API health | http://127.0.0.1:8090/health |
| API docs | http://127.0.0.1:8090/docs |

Default local login is `admin` / `admin123` if that account exists in `logins`.

## 4. Production

1. Copy the tree to the server. Serve `main.php` with PHP-FPM (or another PHP front).
2. `.env`: real DB credentials, `CDAT_SQL_CONSOLE=0`.
3. Start the API:

```bash
./deploy/prod.sh start
./deploy/prod.sh status
```

Leave `DATA_UPLOAD_URL` unset so the browser talks to `http://127.0.0.1:8090`, or set it to the public API URL if PHP and the API are on different hosts.

Logs:
- PHP: `logs/application.log` (errors) and `logs/php-server.log` (`./deploy/dev.sh`)
- API: `dataUpload/logs/dataupload.log`

Stop: `./deploy/prod.sh stop`.

More: [docs/DEPLOY.md](docs/DEPLOY.md), [dataUpload/setup.md](dataUpload/setup.md).

## 5. CDR flow

1. `/data-upload/cdr` — CSV preview (New / Duplicate / Already in DB / Already in staging).
2. **Staging** — background job writes only new rows into `cdatpcsuspectstagingdb`.
3. **Upload History** — View while staged; **Insert DB** copies to `public.cdatpcsuspect` and drops the staging table.

SDR rebuild notes: [docs/SDR_PIPELINE.md](docs/SDR_PIPELINE.md).
