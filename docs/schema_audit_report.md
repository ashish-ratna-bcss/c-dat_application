# Schema audit report

**Date:** 2026-08-30  
**Target:** `CDATDUPL_DB` @ `172.16.212.229:5432` (from `.env`)

## Connection status

Live connection from the dev machine **timed out** (`SQLSTATE[08006]` after 10s). Audit below is based on canonical schema files and module table references. Re-run when VPN/network to the server is available:

```bash
php scripts/schema_audit.php
```

## Expected architecture

| Database | Role |
|---|---|
| `CDATDUPL_DB` | Primary app connection via `get_cdat_pdo()` |
| `IR_DB`, `JRMS_DB`, `PDACT_DB`, `ROWDY_SHEETS_DB` | Satellite DBs mounted via `postgres_fdw` (`sql/fdw_setup.sql`) |

## Key tables referenced by modules/

| Table / view | Source schema file | Notes |
|---|---|---|
| `cdatpcsuspect`, `cdataddress`, `cdatphonearea`, `cdatcelltowerareanew` | `sql/cdr_db.sql` | Core CDR |
| `cdat_details` | `sql/cdr_db.sql` | Summary view |
| `cdat_rta` | `sql/cdr_db.sql` | Vehicle search |
| `jrms_total_2012_to_2017` | `sql/jrms_db.sql` + FDW | JRMS modules |
| `ir_particulars`, `image_table`, `habitual_offenders`, `fingerprint_matched_undetected_cases_withimage` | `sql/ir_db.sql` + FDW | IR / offenders |
| `pdact_main_table` | `sql/pdact_db.sql` + FDW | PDACT modules |
| `rowdy_sheeter_data1` | `sql/rowdy_sheets_db.sql` + FDW | Rowdy sheeter |

## Known schema reconciliation (files)

| Item | `sql/cdr_db.sql` (canonical) | `sql/mssql_to_postgres_migration.sql` (reconciled) |
|---|---|---|
| `cdatpcsuspect.ucid` | `BIGINT NOT NULL` | Updated from `SERIAL` → `BIGINT` |
| `cdatpcsuspect.phone` | `varchar(25)` | Updated from `varchar(15)` |
| `cdatpcsuspect.other` | `varchar(50)` | Updated from `varchar(15)` |

## Recommended live checks (when connected)

1. `\dt` / `\dv` in `CDATDUPL_DB` — confirm FDW foreign tables for JRMS/IR/PDACT/ROWDY
2. `\d cdatpcsuspect` — confirm `ucid` is `bigint`
3. Compare `information_schema.columns` for tables above vs `sql/*.sql`
4. Do **not** truncate `cdatpcsuspect` while `migrate_copy.py pcsuspect` is running

## FDW apply

```bash
bash sql/apply_fdw.sh
```
