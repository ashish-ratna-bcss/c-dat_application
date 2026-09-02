# CDAT

Crime Data Analysis Tool: PHP search modules plus a Python upload API for Call Data Records (CDR).

## What runs

| Piece | How | URL |
|---|---|---|
| Web app | `php -S` or Nginx + PHP-FPM (`main.php`) | http://localhost:8020 |
| CDR upload API | `dataUpload/main.py` | http://127.0.0.1:8090 |

Login: `/login`. CDR upload: `/data-upload/cdr`. History: `/data-upload/history`.

## Layout

```
main.php                 PHP front controller
modules/                 Search, login, CDR + history pages
public/assets/           CSS / JS
routes/web.php           Pretty URLs
sql/                     Live tables (`cdr_db.sql`) + upload schemas (`cdatdbschema.sql`)
dataUpload/              FastAPI: preview → staging → Insert DB
deploy/                  `dev.sh` (local) and `prod.sh` (API daemon)
docs/                    Deploy, SDR rebuild notes, user guides
```

PostgreSQL database `CDATDUPL_DB` (and satellite DBs via FDW). Jobs and upload history live in schema `cdatupload`. Per-file CDR staging tables live in `cdatpcsuspectstagingdb` and are dropped after a successful Insert DB.

SDR (subscriber `.bak`) is not in `dataUpload/` yet. See [docs/SDR_PIPELINE.md](docs/SDR_PIPELINE.md).

## Quick start

See **[SETUP.md](SETUP.md)**. Short version:

```bash
cp .env.example .env    # set CDR_DB_*
./deploy/dev.sh         # PHP 8020 + API 8090
```

Production: Nginx from `cdat-web.nginx.conf`, `DATA_UPLOAD_URL=/` in `.env`, and `./deploy/prod.sh start`. Details in [docs/DEPLOY.md](docs/DEPLOY.md).

## Do not commit

`.env`, `config/db_config.php`, CDR CSV files, `dataUpload/uploads/`, `var/`.
