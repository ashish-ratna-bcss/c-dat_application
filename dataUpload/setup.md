# CDAT Data Upload — setup

This FastAPI service lives in `dataUpload/`. Database settings come from the **application root** `.env` (same file PHP uses).

## Folder layout

```
c-dat_application/
  .env                 shared DB config (CDR_DB_*, satellite DB names)
  dataUpload/
    main.py            FastAPI entry (run this)
    config.py          loads root .env
    setup.md           this file
    requirements.txt
    env/               Python virtual environment (created below)
    cdr/
      preview.py       CDR CSV detect + preview
    uploads/           created at startup if missing
      cdr/             CDR files queued for staging
```

## 1. Create a virtual environment

From `dataUpload/`:

```bash
python3 -m venv env
source env/bin/activate
pip install -r requirements.txt
```

Your prompt should show `(env)` after activate. Then `python` and `uvicorn` come from this folder, not the system.

On Windows (Command Prompt):

```bat
python -m venv env
env\Scripts\activate
pip install -r requirements.txt
```

## 2. Environment values

Edit the repo-root `.env` (copy from `.env.example` if needed). These keys are used:

- `CDR_DB_HOST`, `CDR_DB_PORT`, `CDR_DB_NAME`, `CDR_DB_USER`, `CDR_DB_PASSWORD`
- `IR_DB_NAME`, `JRMS_DB_NAME`, `PDACT_DB_NAME`, `ROWDY_SHEETS_DB_NAME`

On this machine, startup:

- creates `dataUpload/uploads/` and `dataUpload/uploads/cdr/` if they are missing
- creates any missing databases from `.env` and applies `sql/*.sql` (`CREATE TABLE IF NOT EXISTS`)
- creates PostgreSQL schema `cdatpcsuspectstagingdb` inside `CDATDUPL_DB` if it is missing

`GET /health` then pings PostgreSQL.

Optional overrides (not required in `.env`):

- `DATA_UPLOAD_HOST` / `DATA_UPLOAD_PORT` — bind address (default `127.0.0.1:8090`)
- `DATA_UPLOAD_API_KEY` — optional `X-API-Key` header
- `DATA_UPLOAD_DIR` — where uploaded files are stored (default `dataUpload/uploads`)
- `DATA_UPLOAD_MAX_MB` — upload size limit (default `512`)
- `DATA_UPLOAD_URL` — public API URL used by the CDR page (default `http://127.0.0.1:8090`)

## 3. Run the API

From `dataUpload/`, with the venv **activated**:

```bash
source env/bin/activate
python main.py
```

Or:

```bash
source env/bin/activate
uvicorn main:app --host 127.0.0.1 --port 8090
```

If `uvicorn` is not found, the venv is not active.

## 4. Check that it is running

- Health: http://127.0.0.1:8090/health
- Swagger docs: http://127.0.0.1:8090/docs
- Upload: `POST http://127.0.0.1:8090/api/v1/uploads` (multipart field `file`)
- CDR preview: `POST http://127.0.0.1:8090/api/v1/cdr/preview` (multipart field `file`, `.csv` only; parsed in memory, not saved)

Example:

```bash
curl http://127.0.0.1:8090/health
curl -F "file=@/path/to/file.csv" http://127.0.0.1:8090/api/v1/uploads
curl -F "file=@/path/to/cdr.csv" http://127.0.0.1:8090/api/v1/cdr/preview
```
