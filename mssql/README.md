# MSSQL schema dumps (SSMS, 13 Aug 2026)

UTF-16 SSMS scripts from Downloads, converted to UTF-8 and stored here.

| File | Application DB | SSMS `CREATE DATABASE` name | App `Database=>` |
| ---- | -------------- | --------------------------- | ---------------- |
| `cdatdupl.sql` | CDATDUPL | `[CDATDUPL]` | CDATDUPL |
| `JRMS.sql` | JRMS | `[JRMS]` | JRMS |
| `PDACT.sql` | PDACT | `[PDACT]` | PDACT |
| `IR.sql` | **IR** | `[FORMS]` | `FORMS` / `IRFORMS` (IR login + IR forms) |

`IR.sql` **is the IR database**. On the SQL Server instance the catalog name is `[FORMS]` — that is why PHP uses `Database=>'FORMS'` (and some pages `IRFORMS`). Same IR schema.

These scripts are **schema + DB create** (file paths on `D:\SQL SOFTWARE 2016 INSTALLATION\MSSQL13.DAU_HYD_2023\...`). They are **not** data dumps.

**Not included (app still uses these logical DBs):** TWRMDB, LOSTREPORT_HAWKEYE, MIGRANT_LABOURS_FORM, CAFs, TRAINING_DB, CIS_DATA_BASE. IRFORMS is the same IR data as `IR.sql` if it is only an alias; if IRFORMS is a separate server DB, that dump is still missing.

See `TABLES_USAGE.md` for used vs unused vs missing.
