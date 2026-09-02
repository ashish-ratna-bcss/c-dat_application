# SDR pipeline (rebuild guide)

This is the **old** subscriber-address import. CDR now runs in `dataUpload/` (port **8090**). SDR is **not** in that service yet. The previous Python stack is archived in `old_removed_codeShivang/` (gitignored). Use this file when you rebuild SDR the same way as CDR: preview → staging → Insert DB → history.

## What SDR is

An SDR file is a **SQL Server `.bak`** of subscriber / address data (not a call CSV).

| MSSQL table | Live PostgreSQL table | Key |
|---|---|---|
| `CDATADDRESS` | `public.cdataddress` | `CDAT_SDR_KEY` |
| `ADDRESS_OTHER_STATE` | `public.address_other_state` | `OTH_SDR_KEY` |

Those live tables are defined in `sql/cdr_db.sql`. Call-search pages read `cdataddress`; keep that schema.

## Old stack (archived)

```
PHP  /data-upload/sdr  (page removed)
  → POST multipart .bak
  → FastAPI 127.0.0.1:8088   root main.py + cdr-import-service/
       document_processing/orchestrator.py  module=sdr
         sdr_import/service.py    enqueue job
         sdr_import/pipeline.py   run job
           1. mssql_restore.py    docker exec → RESTORE DATABASE
           2. migrate.py          pyodbc MSSQL → PG staging tables
           3. verification.py     approve → copy staging → live
  → worker.py  (CDR inbox poller; SDR jobs run in the 8088 thread pool)
```

Jobs lived in **`public.document_jobs`** (`sql/cdr_db.sql`), not `cdatupload.cdr_pipeline_jobs`.

### Phases (`sdr_import/pipeline.py`)

1. **`restore_mssql`** — copy `.bak` into the MSSQL data dir, `RESTORE FILELISTONLY` / `HEADERONLY`, `RESTORE DATABASE … WITH MOVE`, `REPLACE`. Needs Docker container (default name `mssql` / `cdat-mssql`) and `MSSQL_SA_PASSWORD`.
2. **`migrate_cdataddress`** — page MSSQL `CDATADDRESS` by `CDAT_SDR_KEY` into a per-job PG staging table (`document_processing.staging.create_sdr_staging_table`). Checkpoint in `phase_state` JSON.
3. **`migrate_address_other_state`** — same for `ADDRESS_OTHER_STATE` / `OTH_SDR_KEY`.
4. **`pending_verification`** — register staging batch; wait for approve.
5. **Approve** — `document_processing/verification.py` copies staging → `public.cdataddress` and `public.address_other_state`.

Resume: `last_checkpoint_key` + `phase_state` so a crash continues from the last key.

### Environment (old)

| Variable | Role |
|---|---|
| `MSSQL_SA_PASSWORD` | SA password inside the container |
| `MSSQL_CONTAINER` | Docker name (`mssql`) |
| `SDR_MSSQL_DATABASE` | Restored DB name (default `address_db`) |
| `MSSQL_DATA_HOST_DIR` | Host path for `.mdf` / `.ldf` |
| `MSSQL_DATA_CONTAINER_DIR` | Path inside container (`/var/opt/mssql/data`) |
| `SDR_IMPORT_BATCH_SIZE` | Rows per migrate page (default `10000`) |
| `SDR_PG_DATABASE` | Target PG database (often same as `CDR_DB_NAME`) |
| `CDR_DB_*` | PostgreSQL |
| `CDR_API_PORT` | **8088** (old API; do not use for new SDR) |

ODBC: `DRIVER={ODBC Driver 17 for SQL Server};SERVER=localhost;…`

## Suggested rebuild (match current CDR)

Do **not** revive port 8088, `document_jobs`, or the PHP SDR page as they were. Add SDR next to CDR in `dataUpload/`:

1. **CDR page stays CSV-only.** Add an **SDR** menu item + `modules/data-upload/admin_upload_sdr.php` if the UI should be separate.
2. **API** `dataUpload/` on **8090**: `POST /api/v1/sdr/preview`, `POST /api/v1/sdr/stage`, `GET /api/v1/sdr/jobs`, `POST /api/v1/sdr/jobs/{id}/insert`.
3. **Jobs + history** in `cdatupload` (`sql/cdatdbschema.sql`): reuse `cdr_pipeline_jobs` with `module_name = 'SDR'` or add `sdr_pipeline_jobs`. Upload History already filters by module.
4. **Staging schema** e.g. `cdatsdrstagingdb` — one table (or two: address + other-state) per Staging click: `{user}_{bakname}_{YYYYMMDD_HHMMSS}`.
5. **Preview** cannot paint a CSV grid from `.bak`. Show file name, size, restored DB name, estimated row counts per table, duplicate / already-in-DB counts against `cdataddress` / `address_other_state`.
6. **Staging worker** (same 2-thread pool as CDR): restore MSSQL → copy **new** rows only into staging (skip keys already in live or other staging tables).
7. **Insert DB**: copy staging → live, then **drop** the staging table (same as CDR).
8. Keep MSSQL credentials in `.env` only. Never in PHP.

### Duplicate key (live)

Use the same unique keys as migrate: `cdat_sdr_key` on `cdataddress`, `oth_sdr_key` on `address_other_state` (confirm actual PG column names in `sql/cdr_db.sql`).

## Archived paths

All of this is under `old_removed_codeShivang/` (not in git):

| Path | Role |
|---|---|
| `sdr_import/` | Restore + migrate |
| `document_processing/` | Jobs, locks, staging, approve |
| `cdr_import/` | Old CDR CSV import (replaced by `dataUpload/`) |
| `cdr-import-service/` | FastAPI on 8088 |
| `main.py` | `uvicorn` entry for 8088 |
| `worker.py` | Inbox poller for old CDR |

To inspect locally: `old_removed_codeShivang/sdr_import/pipeline.py`.

## Security

- No MSSQL passwords in PHP or this markdown with real values
- Isolated MSSQL / Docker network
- Delete `.bak` after a successful insert
- Rotate `MSSQL_SA_PASSWORD` after a restore window
