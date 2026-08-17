# C-DAT — Final Complete Application Audit
## Current `main` Branch — Forensic Technical Report for Head / Management

| Field | Value |
| ----- | ----- |
| **Application (from source)** | Call Data Analysis Tool (C-DAT) / Hyderabad City Police crime-lab CDR + Interrogation Report system |
| **Git branch** | `main` |
| **Commit** | `dc47eca` — “Initial commit of C-DAT application.” (`origin/main` = same SHA) |
| **Audit date** | 13 August 2026 |
| **Audit type** | Static forensic audit: architecture, source, endpoints, database, APIs, auth, security, business logic, dependencies, deployment config, dead/unused code |
| **Method** | `git ls-tree` / `git show` / `git grep` on `main` only. Companion working-tree branch `v2-bug-fixes` was **not** treated as `main`. |
| **Exploitation** | Not performed |
| **Live database / production server** | **Not accessed** |
| **Code changes made** | **NONE** (this file is documentation only) |
| **Tree size** | 15,698 entries; ~212 unique root PHP basenames (case-insensitive); ~771 root `*.php` including case/symlink variants |
| **Overall confidence** | **Medium–High** for repository facts; **Low–Medium** for production runtime (shim vs native MSSQL, network exposure, live table inventory) |

**Evidence classes used throughout:**

| Class | Meaning |
| ----- | ------- |
| **CONFIRMED FROM SOURCE** | Directly proven by files on `main` |
| **INFERRED FROM SOURCE** | Strongly indicated; not 100% provable without runtime |
| **NEEDS RUNTIME VERIFICATION** | Requires server, database, logs, systemd, or user confirmation |

**Related working papers (same audit, not substitutes):** `MAIN_BRANCH_APPLICATION_AUDIT_REPORT.md`, `MAIN_BRANCH_ENDPOINT_INVENTORY.md`, `MAIN_BRANCH_USAGE_MATRIX.md`, `MAIN_BRANCH_ISSUES.md`. This document is the **standalone management + engineering baseline**.

---

# 38. MANAGEMENT SUMMARY (FRONT)

## WHAT THE APPLICATION IS

A **legacy internal police investigation web application** used by **Hyderabad City Police** (crime lab / CDAT unit). Source titles most screens “Untitled Document”, but nginx comments, `ADMIN_SQL_CONSOLE.PHP` header (“Hyderabad City Police”), tower-home email `crimelab@hyd.tspolice.gov.in`, and menu labels identify it as the **Call Data Analysis Tool (C-DAT)** plus **Interrogation Report (IR) forms**, **JRMS** (jail records), **PDACT**, **rowdy sheeter**, and **tower dump** search.

**Evidence class:** CONFIRMED FROM SOURCE (`HOME.html` menus; `TOWER_HOME.HTML` marquee; `ADMIN_SQL_CONSOLE.PHP` comment; `cdat-web.nginx.conf`).

Formal product version, owner org chart, and SLA: **Not identified in source — needs verification.**

## WHAT IT DOES

Analysts enter a **phone / IMEI / name / IRKEY / vehicle / cell ID / police station** on a static HTML form. PHP runs **T-SQL-style `sqlsrv_*` queries** against investigative tables and prints **HTML tables** (sometimes QR codes, photos, CSV/XLS from the admin SQL console).

A second layer on the same tree:

- **PostgreSQL shim** (`sqlsrv_compat.php`, nginx `auto_prepend_file`) maps every legacy MSSQL database name to one Postgres DB `postgres`.
- **Activity logging** (`activity_logger.php` → `user_sessions` / `user_activity_logs`).
- **CDR/SDR upload** (`admin_upload.php` → FastAPI `:8088` → `cdr_import` / `sdr_import`).

**Evidence class:** CONFIRMED FROM SOURCE.

## WHAT IS CURRENTLY USED

**CONFIRMED FROM SOURCE** (HOME / HOME_IR / IR_MODULE / TOWER_HOME / SUM_HOME / nginx / form `action`):

- CDAT report menus on `HOME.html` (Summary, Call Details, CDAT contacts, IMEI, Address, Day/Night, Offenders, Cell/Vehicle/Common, Admin activity, SQL console, Upload, IR search, JRMS, PDACT, Rowdy, Trainings, Tower hub).
- IR login → `HOME_IR.HTML` → IR section forms (particulars, brief facts, offence, images, retrieve, …).
- Upload UI + FastAPI client if `:8088` is running (**NEEDS RUNTIME VERIFICATION** that systemd is enabled).
- Local Spry menus, jQuery UI 1.10.4 datepickers, `qrcode/php/qr_img.php`, `css_sparkle1.css`.

## WHAT IS NOT USED / POTENTIALLY UNUSED

**CONFIRMED no CDAT-menu/`include`/`curl` path (still URL-reachable if nginx `root` serves them):**

- `curfewepass/`, `old ir/`, `new ir/`, `TWRDB/`, `ROUGH_TOWER/`, `SUN/`, `_notes/`
- Demo/junk: `chandu.php`, `untitled-1.php`, `notepad.php`, `desktop.php`, `login_page.php`, `myindex.php`, `jquerydynamic.php`
- CLI-only: `image_migrate/`, `distributed_migrate/` (no PHP caller)

**POTENTIALLY UNUSED (handlers exist; not on HOME; production bookmarks unknown):** CAF, migrant labours, CIS, VBR, NBWS, ALLDATA, NAMESEARCH, DUMP extras, HOME_IMEI/Hawkeye, commented Call Details Total / Calls Between Two Numbers, many JRMS `*_MAHESH`/`*_OLD` copies, TRAINING_MODULE2.

**NEEDS RUNTIME VERIFICATION:** HTTP access logs.

## WHAT IS BROKEN

**CONFIRMED FROM SOURCE:**

| Item | Evidence |
| ---- | -------- |
| `SUM_HOME12.html` → `SUM1.php` | Form action; no file on `main` |
| `SUM_HOME2.html` → `SUM2.php` | Missing handler |
| `SUM_HOME_P.html` → `SUM_P.PHP` | Missing handler |
| `download_template.php` | Always HTTP 410 |
| `db_config.php` | Gitignored; required by shim + logger; `DB_CONFIG.PHP` dangling symlink |
| `myindex.php` → `get_state.php` | Missing at root |
| `demo.php.php` → root `ddtf.js` | Missing (only under `DROP DOWN FILTER/`) |

**INFERRED:** Many JRMS/PDACT `#TEMP` / `CONVERT(VARCHAR)` / `FOR XML PATH` queries will fail or return wrong rows if production uses the Postgres shim.

## WHAT ARE THE TOP SECURITY RISKS

1. **CRITICAL — Unauthenticated PII/CDR.** Only ~15 PHP pages call `audit_require_*`. `HOME.html` is nginx index with **no login**. CONFIRMED FROM SOURCE.
2. **CRITICAL — Plaintext passwords** in `LOGINS`; `LOGIN.PHP` compares plaintext; `ADMIN_CREATE_USER.PHP` inserts plaintext. CONFIRMED FROM SOURCE.
3. **CRITICAL — SQL injection** including `LOGIN1.PHP` login string SQL and almost all reports (`SUM.PHP` `WHERE PHONE='$number'`). CONFIRMED FROM SOURCE.
4. **CRITICAL — Hardcoded backdoor** in `RETRIEVE1.PHP` (`USERNAME == "FORMS"` and `sa@***` password → `LOGIN1.php`). CONFIRMED FROM SOURCE (secret masked).
5. **HIGH — Unauthenticated IR photo INSERT** (`IMAGE_LIST.PHP`) and **JRMS multi-row UPDATE** (`JRMS_UNIQUE_KEY_UPDATE.PHP`). CONFIRMED FROM SOURCE.
6. **HIGH — XSS** on result tables; **CSRF** absent; **admin SQL console** linked from public HOME; **750G** nginx body + `/document-api/` on same vhost. CONFIRMED FROM SOURCE.

## WHAT ARE THE TOP CODE QUALITY RISKS

- ~15,698 tree entries, mostly **case/extension duplicates** + vendor dumps.
- PHP+HTML+SQL in one file per screen; JRMS `#TEMP` block copy-pasted ~15 times.
- Dual runtime: T-SQL written, Postgres executed via incomplete translator.
- No Composer, no PHP tests, no front controller.

## WHAT ARE THE TOP BUSINESS LOGIC RISKS

- Day **and** night both include **05:00–07:00** (`D&N_LOC.PHP`).
- Summary Total uses `CDAT_DETAILS`; dated summaries use `CDAT_DETAILS1` — different universes.
- Missing photos replaced with **IRKEY `113769`** placeholder image.
- IR search silently ignores names shorter than 5 characters (spaces stripped).
- JRMS hub only Cherlapalli + Chanchalguda + latest release date.
- Possible split-brain: create-user writes Postgres `logins`; login reads `FORMS.LOGINS`.

## WHAT NEEDS PRODUCTION VERIFICATION

See §36. Minimum: whether `auto_prepend sqlsrv_compat.php` is on; whether `:8020` is LAN-only; whether FastAPI `:8088` is up and authenticated; live `\dt` vs PHP table names; access logs for dump dirs; whether RETRIEVE1 password still matches any live secret.

## WHAT SHOULD BE DONE FIRST

1. Network-isolate `:8020` / document-api.  
2. Remove/disable `RETRIEVE1` backdoor; rotate secrets.  
3. Default-deny authentication on every `*.php`.  
4. Gate IMAGE_LIST + JRMS unique-key update + SQL console.  
5. Hash passwords; delete or parameterize `LOGIN1`.  
6. Do **not** rename SQL/POST field names without investigator sign-off.  
7. Golden-test shim SQL before any “cleanup”.

**Do not treat `main` as internet-safe production.**

---

# 1. APPLICATION OVERVIEW

| Item | Finding | Evidence class |
| ---- | ------- | -------------- |
| Name | C-DAT / Call Data Analysis Tool; IR Forms; Hyderabad City Police (in admin SQL console comment) | CONFIRMED FROM SOURCE |
| Purpose | Search operator CDR (phone, IMEI, tower, address) and manage interrogation / jail / PDACT / rowdy records | CONFIRMED FROM SOURCE (`HOME.html` menus + SQL tables) |
| Business problem | Fast investigation support for Hyderabad crime lab | CONFIRMED FROM SOURCE (email `crimelab@hyd.tspolice.gov.in` on `TOWER_HOME.HTML`) |
| Intended users | Police analysts / crime-lab operators | INFERRED FROM SOURCE (roles, menus, data types) |
| Roles in code | `admin`, `poweruser` (uploader), `user` (default) | CONFIRMED FROM SOURCE (`LOGIN.PHP` `strtolower(ROLE)`; `audit_require_uploader` allows admin\|poweruser) |
| OTP / SSO / AD login | **Not identified in source** | — |
| Formal version / vendor contract | **Not identified in source — needs verification** | — |

### Major modules (CONFIRMED FROM SOURCE — `HOME.html` / `HOME_IR.HTML` / `IR_MODULE.HTML` / `TOWER_HOME.HTML`)

| Module | Main screens | Data |
| ------ | ------------ | ---- |
| Summary | SUM_HOME, between dates, ISD, new contacts, in/out state | CDAT_DETAILS / CDAT_DETAILS1 / CDATPCSUSPECT |
| Call details / movements | CALLS_BTWN_DATES, MOVEMENTS, two-number variants | CDATPCSUSPECT + towers |
| CDAT contacts | CDATCNTS, bulk, others | CDATSUSPECT ∩ CDR |
| IMEI | IMEISEARCH, IMEISINPHONE; Hawkeye on HOME_IMEI | CDATPCSUSPECT; LOSTREPORT_HAWKEYE |
| Address | ADDRESS, BULKADDRESS + QR | CDATADDRESS, ADDRESS_OTHER_STATE, CDATPHONEAREA |
| Day/night location | DAY%26NIGHTLOC*, D&N_* handlers | CDATPCSUSPECT + cell towers |
| Offenders | HABITUAL, MO search, FP list | IRFORMS / COMPLETE_MO_CLASSIFICATION |
| IR | Login, HOME_IR forms, IR_SEARCH, IR.PHP dossier | FORMS / IRFORMS |
| JRMS | JRMS_MAIN_PAGE1 + search/update family | JRMS_TOTAL_2012_TO_2017 |
| PDACT | PDACT_MAIN_PAGE_SEARCH + name/MO/PS | PDACT_MAIN_TABLE |
| Rowdy | PS-wise search | ROWDY_SHEETER_DATA1 |
| Tower dump | TOWER_HOME + suspect/other-state/inter-tower/pre-off (+ `_TWR`) | TWRMDB_MASTER_CDAT |
| Trainings | TRAINING_MODULE1 | TRAINING_DB |
| Upload | admin_upload* | document_jobs / staging / cdatpcsuspect load |
| Admin | activity log, SQL console, create user | Postgres audit + logins |

### Input sources

- HTML POST/GET forms (phones, dates, names, IRKEY, vehicle, cell ID, PS, files).
- Uploaded operator CDR CSV/XLSX and SDR `.bak` (upload UI + FastAPI).
- **Not identified:** live operator API feeds, SMS, WhatsApp. CONFIRMED absent from root PHP.

### Output / report types

- HTML tables (primary).
- QR images (`qrcode/php/qr_img.php`).
- Base64 IR/suspect photos from SQL.
- Admin SQL console CSV/XLS export.
- Upload job JSON (`admin_upload_job_status.php`).
- PDF: **not identified** as a first-class CDAT report type on `main`.

### External systems

| System | Role | Evidence class |
| ------ | ---- | -------------- |
| PostgreSQL | Runtime DB via shim + audit + import | CONFIRMED FROM SOURCE (`sqlsrv_compat.php`, `db_config.example.php`) |
| MSSQL named instance `CPHYDERABAD1\DAU_HYD_2023` | Legacy connection target in PHP | CONFIRMED in source; **NEEDS RUNTIME VERIFICATION** if still live |
| FastAPI `:8088` | Document processing | CONFIRMED FROM SOURCE (PHP curl + nginx proxy) |
| Internal FTP `ftp://192.168.x.x` | CAF_SEARCH URL builder | CONFIRMED FROM SOURCE; usage **NEEDS RUNTIME VERIFICATION** |
| Citus / `distributed_db` | FDW/address/cellids | CONFIGURED in SQL/scripts; UI usage INFERRED via shim JOIN rewrite |

---

# 2. COMPLETE APPLICATION ARCHITECTURE

### Actual architecture (CONFIRMED FROM SOURCE)

```text
Browser
  ↓
nginx :8020   (cdat-web.nginx.conf)
  root /mnt/storage1/cdat-web
  index HOME.html
  ├─ GET /                  → HOME.html          (static, NO auth)
  ├─ GET *.html/*.HTML      → static Spry forms  (NO auth)
  ├─ *.PHP  rewrite → *.php
  ├─ *.php  → php8.3-fpm unix:/run/php/php8.3-fpm.sock
  │            PHP_VALUE auto_prepend_file=…/sqlsrv_compat.php
  │            memory_limit=512M; max_execution_time=86400
  │              ↓
  │            Page PHP (sqlsrv_* + T-SQL)
  │              ↓
  │            sqlsrv_compat::__sqlsrv_translate + PDO pgsql
  │              ↓
  │            db_config.php  (NOT IN GIT)
  │              ↓
  │            PostgreSQL dbname=postgres  (all legacy DB names mapped here)
  │              ├─ cdatpcsuspect / cdat_details / address / IR / JRMS / PDACT / …
  │              ├─ user_sessions / user_activity_logs / logins / document_jobs
  │              └─ optional FDW dist.* (wire_distributed_reference_data.sql)
  ├─ /document-api/*  proxy → 127.0.0.1:8088   (FastAPI Document Processing)
  │                            cdr-import-service + document_processing + cdr_import + sdr_import
  └─ /LOGIN.HTML → POST LOGIN.PHP → FORMS.LOGINS + activity_logger → HOME_IR.HTML
```

**Apache alternative:** `.htaccess` only rewrites missing `*.PHP` → `*.php`. No front controller. **No `index.php` on `main`.**

### Component communication

| From | To | How | Evidence |
| ---- | -- | -- | -------- |
| Browser | nginx :8020 | HTTP | `cdat-web.nginx.conf` `listen 8020` |
| nginx | php-fpm | FastCGI | `fastcgi_pass unix:/run/php/php8.3-fpm.sock` |
| Every PHP page | sqlsrv_compat | auto_prepend | nginx `PHP_VALUE auto_prepend_file=…/sqlsrv_compat.php` |
| sqlsrv_compat / activity_logger | Postgres | PDO `pgsql:` | `__sqlsrv_cfg()` / `audit_db()` `require db_config.php` |
| admin_upload | FastAPI | curl / `document_processing_client.php` | default `http://127.0.0.1:8088` |
| admin_upload preview | OS | `exec` python `scripts/cdr_preview.py` | `admin_upload.php` |
| excel_converter | OS | `exec` `scripts/excel_to_csv.py` | `excel_converter.php` |
| Large SDR | FastAPI directly | nginx `/document-api/` + `js/sdr_resumable_upload.js` | nginx location + JS |
| Login | Postgres audit | `audit_login` INSERT | `activity_logger.php` |
| Login | LOGINS | parameterized SELECT | `LOGIN.PHP` |

### Logging

| Channel | Where | Evidence class |
| ------- | ----- | -------------- |
| Login/logout + some searches | `user_sessions`, `user_activity_logs` via `audit_log()` | CONFIRMED FROM SOURCE |
| Upload jobs | `upload_activity_logs`, `document_jobs` | CONFIRMED FROM SOURCE |
| Admin SQL | `audit_log('SQL Query Console', …)` | CONFIRMED FROM SOURCE |
| PHP errors | `die(print_r(sqlsrv_errors()))` to **browser**; some `error_log` in activity_logger | CONFIRMED FROM SOURCE |
| Central app log file | **Not identified** as a dedicated rotating log in PHP | NEEDS RUNTIME VERIFICATION (php-fpm/syslog) |

### File storage

| Path | Use | Evidence |
| ---- | --- | -------- |
| `uploads/` | `.gitignore`d; admin_upload `move_uploaded_file` | CONFIRMED FROM SOURCE |
| tmp `cdr_preview_*` | preview work files | `admin_upload.php` |
| Images in SQL | `IMAGE_TABLE.IMAGE` byte/text | `IMAGE_LIST.PHP` |
| `/mnt/storage1/cdat-web` | nginx document root | `cdat-web.nginx.conf` — **production path assumption**; NEEDS RUNTIME VERIFICATION on other hosts |

---

# 3. COMPLETE FILE INVENTORY

A literal row for all **15,698** tree entries is not useful (jquery-ui alone is 5,224 files; curfewepass 4,586; qrcode 2,253). Below is the **complete operational inventory** of application-relevant files, with dump/vendor trees summarized. Classification uses **reference tracing**, not filenames.

### 3.1 Root application — ACTIVE (CONFIRMED FROM SOURCE)

| File | Type | Purpose | Called By | Calls | DB | API | Status |
| ---- | ---- | ------- | --------- | ----- | -- | --- | ------ |
| `HOME.html` | HTML | CDAT menu | nginx index | hrefs to reports | — | — | ACTIVE |
| `HOME_IR.HTML` | HTML | IR menu | LOGIN.PHP redirect; HOME | IR forms | — | — | ACTIVE |
| `IR_MODULE.HTML` | HTML | IR search hub | HOME | IR search/FP/habitual | — | — | ACTIVE |
| `TOWER_HOME.HTML` | HTML | Tower hub | HOME | SUSPECT_SEARCH etc. | — | — | ACTIVE |
| `SUM_HOME.html` | HTML | Summary hub | HOME | SUM.PHP | — | — | ACTIVE |
| `LOGIN.HTML` | HTML | Login UI | HOME, logout | LOGIN.PHP | — | — | ACTIVE |
| `LOGIN.PHP` | PHP | Auth | LOGIN.HTML POST | activity_logger, sqlsrv | LOGINS, user_sessions | — | ACTIVE |
| `LOGOUT.PHP` | PHP | Logout | HOME, HOME_IR | activity_logger | user_sessions | — | ACTIVE |
| `activity_logger.php` | PHP lib | Session/audit/gates | login/admin/upload/some reports | PDO | user_sessions, user_activity_logs | — | ACTIVE |
| `sqlsrv_compat.php` | PHP shim | sqlsrv→pgsql | nginx prepend | PDO | all mapped DBs | — | ACTIVE |
| `sql_safe.php` | PHP lib | digit/alnum/`h()` | ~9 reports | — | — | — | ACTIVE |
| `cdr_enrichment_sql.php` | PHP lib | Tower/address maps | SUM, CALLS, MOVEMENTS, D&N, CDATCNTS | sqlsrv | CDAT* | — | ACTIVE |
| `dbcontroller.php` | PHP lib | Dropdown wrapper | GET_PS/YEAR/DIVISION/CRNO | sqlsrv | CDATDUPL | — | ACTIVE |
| `db_config.example.php` | template | DSN template | copy to db_config.php | — | — | — | TEMPLATE |
| `db_config.php` | config | Secrets | shim, logger, Python | — | postgres | — | **MISSING IN GIT** |
| `SUM.PHP` + SUM_* dated/ISD/new/in/out | PHP | Summaries | SUM_HOME / HOME forms | sqlsrv, enrichment | CDAT_* | — | ACTIVE |
| `CALLS_BTWN_DATES.PHP` | PHP | CDR rows | HOME form | sql_safe, audit_require_session | CDATPCSUSPECT | — | ACTIVE |
| `MOVEMENTS.PHP` + two-number* | PHP | Movements | HOME | enrichment; MOVEMENTS uses `PHONE=?` | CDATPCSUSPECT | — | ACTIVE |
| `CDATCNTS1.php`, `BULK_CDAT_CONTACTS.PHP`, `OTHERCDAT.php` | PHP | Contacts | HOME | sqlsrv | CDAT* | — | ACTIVE |
| `IMEI_SEARCH.PHP`, `IMEI_SEARCH_IN_PHONE.PHP` | PHP | IMEI↔phone | HOME | sql_safe | CDATPCSUSPECT | — | ACTIVE |
| `ADDRESS.PHP`, `BULK_ADDRESS.php` | PHP | Address+QR | HOME | qrcode | CDATADDRESS | QR local | ACTIVE |
| `D&N_LOC.PHP`, `D&N_BT_DTS.PHP` (+ encoded twins) | PHP | Day/night | HOME | enrichment / #TEMP | CDATPCSUSPECT | — | ACTIVE |
| `HABITUAL.PHP`, `CELLID_SEARCH.php`, `VEHICLE_SEARCH*.PHP`, `COMMON_CNTS.PHP`, `OFFENDER_SEARCH_BY_MO.PHP` | PHP | Other searches | HOME / IR_MODULE | sqlsrv, qrcode | various | QR | ACTIVE |
| `IR_SEARCH.PHP`, `IR.PHP`, IR section PHP, `IMAGE_LIST.PHP`, `RETRIEVE.PHP` | PHP | IR | HOME_IR / search links | sqlsrv | FORMS | — | ACTIVE |
| `JRMS_MAIN_PAGE1.PHP`, `JRMS_SEARCH.PHP`, `JRMS_UNIQUE_KEY_UPDATE.PHP` | PHP | Jail | HOME / JRMS UI | sqlsrv | JRMS | — | ACTIVE |
| `PDACT_MAIN_PAGE_SEARCH.PHP`, `PDACT_SEARCH.PHP`, `PDACT_MO_SEARCH.PHP`, `PDACT_PS_WISE_SEARCH_PHP.PHP` | PHP | PDACT | HOME / PDACT menu | sqlsrv | PDACT | — | ACTIVE |
| `ROWDYSHEETER_PS_WISE_SEARCH.PHP` + `_PHP.PHP` | PHP | Rowdy | HOME | sqlsrv | ROWDY_SHEETER_DATA1 | — | ACTIVE |
| `SUSPECT_SEARCH.PHP` + `_TWR` and tower siblings | PHP | Tower dump | TOWER_HOME | sqlsrv | TWRMDB + CDAT | — | ACTIVE |
| `TRAINING_MODULE1.PHP` | PHP | Training | HOME | sqlsrv, qrcode | TRAINING_DB | QR | ACTIVE |
| `ADMIN_*.PHP` | PHP | Admin | HOME / HOME_IR | audit_require_admin | postgres | CSV/XLS | ACTIVE |
| `admin_upload*.php`, `document_processing_client.php`, `cdr_upload_*.php`, `excel_converter.php`, `upload_verification_service.php` | PHP | Upload | HOME | curl, exec | document_jobs | FastAPI | ACTIVE |
| `check_role.php` | PHP JSON | Cosmetic admin flag | Spry JS | session | — | — | PARTIALLY ACTIVE |
| `SpryAssets/*` | JS/CSS/GIF | Menus | ~130 pages | — | — | — | LIBRARY ACTIVE |
| `jquery-ui-1.10.4.custom/js+css` (not full development-bundle) | JS/CSS | Datepickers | ~50 pages | — | — | — | LIBRARY ACTIVE |
| `css_sparkle1.css`, `IMAGES/TOPBORDER.JPG` etc. | CSS/IMG | Chrome | HOME/IR | — | — | — | ACTIVE |
| `qrcode/php/qr_img.php` | PHP | QR | ADDRESS, VEHICLE, CELLID, TRAINING, TWR | GD | — | — | ACTIVE |
| `DROP DOWN FILTER/ddtf.js`, `w3.css` | JS/CSS | Filter tables | ADDRESS, vehicle criteria, IMEI movements | — | — | — | ACTIVE subset |
| `js/sdr_resumable_upload.js` | JS | SDR chunks | admin_upload | `/document-api/` | — | FastAPI | ACTIVE |
| `cdat-web.nginx.conf`, `.htaccess`, `.env.example` | config | Deploy | ops | — | — | — | CONFIGURATION |
| `sql/*.sql` (non-dump) | SQL | Schema/indexes/FDW/geo/upload | ops/systemd | — | postgres/distributed_db | — | CONFIGURATION |
| `cdr_import/**`, `document_processing/**`, `sdr_import/**`, `cdr-import-service/**` | Python | Import API | FastAPI / exec preview | psycopg2/pyodbc | postgres (+ MSSQL SDR) | FastAPI | ACTIVE if service up |
| `scripts/cdr_preview.py`, `excel_to_csv.py`, `run_cdr_import_service.sh`, `systemd/cdr-import-service.service` | sh/py | Upload path | PHP exec / systemd | — | — | — | ACTIVE/CONFIGURED |

### 3.2 POTENTIALLY UNUSED root PHP (no HOME/HOME_IR/IR_MODULE/TOWER/SUM_HOME href; may still be bookmarked)

CAF_SEARCH, MIGRANT_LABOURS_*, CIS_DATA_*, VBR_SEARCH, NBWS, ALLDATA*, NAMESEARCH, NAME_SEARCH, DUMP*, SUM_ALLDB, CALLS_TOT, CALLS_BT_NOS, CALLDETAILS, MOVEMENTS_IN_PARTICULAR_PLACE, IMEI_REQUEST_*, MAXSPENTLOCATION_IMEI, D&N_LOC_IMEI, HOME_IMEI, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS, extra VEHICLE_*, rta_nike, CDAT_IRFORM, IR_NDPS*, IR_SEARCH__OLD, BULK_IRKEY*, BULK_GANG*, MO_IMAGE_LIST, ANALYSIS_ABSTRACT, extra JRMS copies, extra PDACT, TRAINING_MODULE2, wanted1 (only SUM_HOME), OFFENDER_FD, GET_* dropdowns (if migrant/IR JS unused), LOGIN1, RETRIEVE1.

**Evidence class:** INFERRED FROM SOURCE (no menu reference). **NEEDS RUNTIME VERIFICATION** via access logs. Do **not** delete.

### 3.3 CONFIRMED UNUSED from CDAT UI (still possibly HTTP-reachable)

| Path | Type | Evidence | Status |
| ---- | ---- | -------- | ------ |
| `curfewepass/` | dump app | No root href/include/curl | CONFIRMED UNUSED by CDAT; UNKNOWN if separately hosted |
| `old ir/`, `new ir/` | IR copies | No root reference | CONFIRMED UNUSED by live IR (root HOME_IR) |
| `TWRDB/`, `ROUGH_TOWER/`, `SUN/` | copies | No root reference | CONFIRMED UNUSED by CDAT menus |
| `_notes/` | Dreamweaver | — | DEAD |
| `image_migrate/`, `distributed_migrate/` | Python CLI | No PHP call | CONFIGURED UNUSED by UI |
| jquery-ui `development-bundle/` demos | vendor | App uses js/css subset only | LIBRARY unused bulk |
| `qrcode/` sample images | vendor | Only `qr_img.php` referenced | LIBRARY unused bulk |
| Bootstrap 3 / DataTables in curfewepass | vendor | Not on CDAT pages | CONFIRMED UNUSED by CDAT |

### 3.4 DEAD / BROKEN root

| File | Status | Evidence |
| ---- | ------ | -------- |
| `chandu.php`, `untitled-1.php`, `notepad.php`, `desktop.php`, `login_page.php` | DEAD | No menu; stubs/demos |
| `jquerydynamic.php` | DEAD | Misnamed jQuery 2.1.1 source |
| `sample.php` | DEAD | Symlink → `sample.gif` |
| `dbcontroller.php.php`, `demo.php.php` | DEAD/BROKEN | Double extension; missing ddtf.js |
| `myindex.php` | DEAD/BROKEN | Missing `get_state.php` |
| `SUM1.php`, `SUM2.php`, `SUM_P.PHP` | BROKEN (referenced, missing) | Form actions |
| `download_template.php` | BROKEN/obsolete | Always 410 |
| `DB_CONFIG.PHP` | BROKEN symlink | Target `db_config.php` not in git |

### 3.5 Case-duplicate forest

Almost every root page exists as `.php` `.PHP` `.html` `.HTML` `.htm` `.HTM` (often git **symlinks** mode `120000`). `scripts/fix_html_form_symlinks.py` exists. **INFERRED:** one canonical blob per feature. Linux case-sensitivity **NEEDS RUNTIME VERIFICATION**.

---

# 4. APPLICATION FEATURES

For each feature: purpose, entry, input, processing, business logic, DB, API, output, files, auth, status, evidence, confidence.

### Feature catalogue (HOME-linked = ACTIVE unless noted)

| Feature | Purpose | Entry | Input | Processing / business logic | DB | API | Output | Files | Auth | Status | Confidence |
| ------- | ------- | ----- | ----- | -------------------------- | -- | --- | ------ | ----- | ---- | ------ | ---------- |
| CDAT home | Navigate reports | `/` nginx → `HOME.html` | none | Static Spry menu | — | — | HTML | HOME.html | **none** | ACTIVE | High CONFIRMED |
| IR login | Authenticate | LOGIN.HTML | USERNAME, PASSWORD | Exact plaintext match LOGINS; set session; audit_login; redirect HOME_IR | LOGINS, user_sessions | — | redirect / error HTML | LOGIN.PHP, activity_logger.php | creates session | ACTIVE | High |
| Logout | End session | logout.php | cookie | audit_logout; destroy session | user_sessions U | — | LOGIN.HTML | LOGOUT.PHP | optional | ACTIVE | High |
| Summary total | Contact summary | SUM_HOME.html | PHONE_NO | CDAT_DETAILS + isnumeric(other); junk 140%/duration; enrichment | CDAT_DETAILS, CDATPCSUSPECT, CDATSUSPECT, address | — | HTML tables | SUM.PHP, cdr_enrichment_sql.php | **none** (audit_log only) | ACTIVE | High |
| Summary between dates | Dated summary | SUM_BETWEEN_DATES.html | PHONE_NO, FROM_DT, TO_DT | CDAT_DETAILS1 + date BETWEEN + same junk + SQL address CASE | CDAT_DETAILS1 + address | — | HTML | SUM_BTWN_DATES.PHP | none | ACTIVE | High |
| ISD / new / in-state / out-state | Variant summaries | HOME | phone ± date/state | See §5 hardcoded rules | CDAT_* | — | HTML | SUM_ISD_CNTS, SUM_NEW_NO, SUM_IN_STATE, SUM_OUT_STATE | none | ACTIVE | High |
| Summary SUM1/2/P | Alternate homes | SUM_HOME12/2/_P.html | phone | **Handler missing** | — | — | 404 | — | — | **BROKEN** | High |
| Calls between dates | Raw CDR | CALLS_BTWN_DATES.html | phone, dates, operator, state | sql_safe + session; CDATPCSUSPECT + enrichment; h() output | CDATPCSUSPECT | — | HTML | CALLS_BTWN_DATES.PHP | **session** | ACTIVE | High |
| Movements | Chronological + towers | MOVEMENTS.html | PHONE_NO | Parameterized PHONE=?; IN/OUT label; tower map; JS filterTable | CDATPCSUSPECT | — | HTML | MOVEMENTS.PHP | none (logs) | ACTIVE | High |
| Movements 2 numbers / comparison | Pair CDR | HOME | two phones | String SQL + enrichment lat/long | CDATPCSUSPECT | — | HTML | MOVEMENTS_BETWEEN_TWO_NUMBERS*.PHP | none | ACTIVE | High |
| Calls btwn two nos / call details total | Same family | commented on HOME | phones | Logic exists | CDATPCSUSPECT | — | HTML | CALLS_BT_NOS, CALLS_TOT | none | PARTIALLY ACTIVE / POTENTIALLY UNUSED | Medium |
| CDAT contacts | Others who are suspects | CDATCNTS.html | PHONE_NO | other ∈ CDATSUSPECT only | CDAT_DETAILS1, CDATSUSPECT | — | HTML | CDATCNTS1.php | none | ACTIVE | High |
| Others CDAT | 2-hop minus callcenter | OTHERSCDAT.html | PHONE_NO | exclude CALLCENTER_NOS | CDATPCSUSPECT, CALLCENTER_NOS | — | HTML | OTHERCDAT.php | none | ACTIVE | Medium (table may be missing) |
| Bulk CDAT | Multi-phone contacts | BULK_CDAT_CONTACTS.HTML | comma list | #T1 split; placeholder image 113769 | same + IMAGE | — | HTML | BULK_CDAT_CONTACTS.PHP | none | ACTIVE | High |
| Address single/bulk | CAF/address + QR | ADDRESS.HTML / BULKADDRESS | phone(s) | EFF_TO_DATE IS NULL; VOIP labels | CDATADDRESS, ADDRESS_OTHER_STATE, PHONEAREA | local QR | HTML+QR | ADDRESS.PHP, BULK_ADDRESS.php | none | ACTIVE | High |
| IMEI → phones | Device users | IMEISEARCH.html | IMEI_NO | sql_safe_imei; exact IMEINUMBER | CDATPCSUSPECT | — | HTML h() | IMEI_SEARCH.PHP | **session** | ACTIVE | High |
| Phone → IMEIs | Devices on MSISDN | IMEISINPHONE.html | PHONE_NO | sql_safe_phone; same agg | CDATPCSUSPECT | — | HTML | IMEI_SEARCH_IN_PHONE.PHP | **none** | ACTIVE | High |
| Hawkeye lost IMEI | Complaint/request/CDR | HOME_IMEI.html | IMEI/phone | LEFT(imei,14); care-number labels | LOSTREPORT_HAWKEYE + CDAT | — | HTML | IMEI_REQUEST_* | none | POTENTIALLY UNUSED / UNKNOWN reachability | Medium |
| Day/night loc | Top 10 towers by hour | DAY%26NIGHTLOC.HTML | PHONE_NO | day 05–22; night 22–07; overlap 05–07 | CDATPCSUSPECT + towers | — | HTML htmlspecialchars | D&N_LOC.PHP | none | ACTIVE | High |
| Day/night dated | Same + date range | DAY%26NIGHTLOC_BTWN_DATES | phone, dates | #TEMP + TOP 10; no sql_safe | CDATPCSUSPECT | — | HTML | D&N_BT_DTS.PHP | none | ACTIVE | High |
| Cell ID search | Tower master | CELLID_SEARCH.html | CELLID, op, state | exact → BTS_ID → prefix LIKE; CELLONE→BSNL; limit 50 | CDATCELLTOWERAREANEW | QR | HTML | CELLID_SEARCH.php | none | ACTIVE | High |
| Vehicle search | RTA | VEHICLE_SEARCH.HTML | VEHICLE_NO | REGN_NO LIKE '%'+input | CDAT_RTA | QR | HTML | VEHICLE_SEARCH.PHP | none | ACTIVE | High |
| Vehicle criteria | Search by chosen column | VEHICLE_SEARCH_CRITERIA | VEHICLE_NO, VEHICLE_SOURCE | `$number1` interpolated as **column name** | CDAT_RTA | QR | HTML | VEHICLE_SEARCH_CRITERIA.PHP | none | ACTIVE / unsafe | High |
| Common contacts | Intersection | COMMON_CNTS.HTML | comma phones | FOR XML PATH concat; delete count=1 | CDATPCSUSPECT + address | — | HTML | COMMON_CNTS.PHP | none | ACTIVE | Medium (FOR XML on PG) |
| Habitual list | Offenders | HABITUAL.PHP | none | Full IRFORMS habitual table; link IR.PHP?IRKEY= | IRFORMS | — | HTML | HABITUAL.PHP | none | ACTIVE | High |
| Offender by MO | MO text search | OFFENDER_SEARCH_BY_MO.HTML | MO | LIKE with spaces→%; link OFFENDER_FD?MO_KEY= | COMPLETE_MO_CLASSIFICATION | — | HTML | OFFENDER_SEARCH_BY_MO.PHP | none | ACTIVE | High |
| IR search | Name/head | IR_SEARCH.HTML | NAME, CRIME_HEAD | name len>4; PDACT flag | FORMS + PDACT | — | HTML | IR_SEARCH.PHP | none | ACTIVE | High |
| IR dossier | Full IR | IR.PHP?IRKEY= | GET IRKEY | Many SELECTs including pending NBWS | FORMS | — | HTML | IR.PHP | none | ACTIVE | High |
| IR create/update sections | Insert IR data | HOME_IR forms | many POST fields | String INSERT; ASONDATE=GETDATE(); refresh 30s | FORMS tables | — | HTML + redirect | IRREPORT, BRIEF_FACTS, OFFENCE_DETAILS, … | **none** | ACTIVE | High |
| IR image upload | Store photo | IMAGE_LIST.HTML | file, IRKEY, CATEGORY, CCNO | getimagesize; file_get_contents → SQL INSERT | IMAGE_TABLE | — | HTML | IMAGE_LIST.PHP | **none** | ACTIVE | High |
| IR retrieve | Find IR+photo | RETRIEVE.HTML | NAME, FATHER_NAME | LIKE '%'+name | IR_PARTICULARS, IMAGE_TABLE | — | HTML | RETRIEVE.PHP | none | ACTIVE | High |
| RETRIEVE1 backdoor | Hidden login | RETRIEVE1.PHP footer | USERNAME/PASSWORD | hardcoded FORMS/sa@*** → LOGIN1 | session only | — | redirect | RETRIEVE1.PHP | bypass | LEGACY / DANGEROUS | High |
| JRMS hub | Recent releases | JRMS_MAIN_PAGE1.PHP | none (default) | MAX(RELEASEDT) + jails Cherlapalli/Chanchalguda; Aadhaar↔IR | JRMS + FORMS | — | HTML+photo | JRMS_MAIN_PAGE1.PHP | none | ACTIVE | High |
| JRMS search/update | Filter / write unique key | JRMS forms | crimehead, dates, CIN list | LIKE crimehead; UPDATE UNIQUE_KEY/IRKEY APP_OR_MANUAL=APPLICATION_ENTRY | JRMS_TOTAL_2012_TO_2017 | — | HTML | JRMS_SEARCH.PHP, JRMS_UNIQUE_KEY_UPDATE.PHP | **none** | ACTIVE | High |
| PDACT hub/search | Preventive detention | PDACT_MAIN_PAGE_SEARCH.PHP | none / NAME / MO / PS | top 10 recent; LIKE name; image 113769; link PDACT_KEY | PDACT_MAIN_TABLE | — | HTML | PDACT_* | none | ACTIVE | High |
| Rowdy by PS | Rowdy sheeter | ROWDYSHEETER_* | POLICE_STATION | LIKE PS; image 113769 | ROWDY_SHEETER_DATA1 | — | HTML | ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP | none | ACTIVE | High |
| Tower dump | Dump vs offence window | TOWER_HOME → *_TWR | phone, PS, CR, year, date, hms | TWRMDB_MASTER_CDAT ∩ PLACE_OF_OFFENCE time window | TWRMDB + CDAT | QR some | HTML | SUSPECT_SEARCH_TWR.PHP etc. | none | ACTIVE if dump loaded | Medium |
| Training 1 | Training DB + QR | TRAINING_MODULE1.HTML | fields | TRAINING_DB | TRAINING_DB | QR | HTML | TRAINING_MODULE1.PHP | none | ACTIVE if tables exist | Medium |
| Data upload | Load CDR/SDR | admin_upload.php | files, network, ajax_action | preview exec; submit FastAPI; poll JSON; approve staging; roles admin\|poweruser | document_jobs, staging | **FastAPI :8088** | HTML/JSON | admin_upload*, document_processing_client.php | **uploader** | ACTIVE | High |
| Template download | — | download_template.php | — | Always 410 | — | — | 410 | download_template.php | uploader | **BROKEN** | High |
| User activity | Audit UI | ADMIN_ACTIVITY_LOG.PHP | filters | parameterized SELECT | user_activity_logs | — | HTML | ADMIN_ACTIVITY_LOG.PHP | **admin** | ACTIVE | High |
| SQL console | Ad-hoc SELECT | ADMIN_SQL_CONSOLE.PHP | sql_query, export | ^select filter; wrap LIMIT 1000; CSV/XLS | arbitrary postgres | — | HTML/CSV/XLS | ADMIN_SQL_CONSOLE.PHP | **admin** | ACTIVE | High |
| Create user | New login | ADMIN_CREATE_USER.PHP | username, password, role, fullname | unique LOWER(username); INSERT plaintext | postgres logins | — | HTML | ADMIN_CREATE_USER.PHP | **admin** | ACTIVE | High |
| Curfew e-pass | CPMS | curfewepass/ | — | Separate Bootstrap app | tbladmin/tblpass | — | HTML | curfewepass/** | own | UNUSED by CDAT | High (unlink) |
| Demos | junk | myindex, chandu, login_page | — | — | — | — | — | various | none | DEAD | High |

---

# 5. BUSINESS LOGIC AUDIT

Full workflow traces (INPUT → VALIDATION → RULES → PROCESSING → DB/API → OUTPUT → ERRORS) were read from `git show main:<file>`. Condensed here with **exact evidence**; see also companion report §22.

### 5.1 Hardcoded / conflicting / suspicious rules

| Rule | File / section | Conflict / issue | Class |
| ---- | -------------- | ---------------- | ----- |
| Day: `CONVERT(CHAR(8),STARTTIME,108)>'05:00:00' AND <'22:00:00'` | `D&N_LOC.PHP` `$dayPred`; `D&N_BT_DTS.PHP` `$sql1` | Overlaps night | CONFIRMED |
| Night: `>'22:00:00' OR <'07:00:00'` | `$nightPred` / `$sql8` | **05:00–07:00 is BOTH day and night** | CONFIRMED; MAY BE INCORRECT |
| Junk: `OTHER NOT LIKE '140%'` + `(CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I')` | `SUM.PHP` `$sql5` and copies | Duplicated; `isnumeric`/`LEFT` PG-fragile | CONFIRMED |
| Summary Total source `CDAT_DETAILS` vs others `CDAT_DETAILS1` | SUM.PHP vs SUM_BTWN_DATES.PHP | **Conflicting universes** | CONFIRMED |
| Address current only `EFF_TO_DATE IS NULL` | ADDRESS/SUM/IMEI | Historical CAF ignored | CONFIRMED HARDCODED |
| Placeholder photo `IRKEY='113769'` | PDACT_*, ROWDY, BULK_CDAT `$sql71` | Wrong face if dummy not empty | CONFIRMED; NEEDS RUNTIME VERIFICATION dummy row |
| IR name `len(replace(NAME,' ',''))>'4'` | `IR_SEARCH.PHP` `$sql9` | Short names silently empty | CONFIRMED |
| JRMS hub jails only `CHERLAPALLI`,`CHANCHALGUDA` + `MAX(RELEASEDT)` | `JRMS_MAIN_PAGE1.PHP` `$sql9` | Other jails/dates hidden | CONFIRMED |
| IMEI Hawkeye `LEFT(imei,14)` | `IMEI_REQUEST_STATUS.PHP` | Distinct IMEIs merged | CONFIRMED |
| Uploader = `admin` OR `poweruser` only | `audit_require_uploader()` | `user` cannot upload | CONFIRMED |
| Operator map 2/15/12/4 → airtel/jio/vi/bsnl | `admin_upload.php` `mapNetworkToOperator` | HARDCODED | CONFIRMED |
| CELLONE → BSNL | `CELLID_SEARCH.php` | HARDCODED | CONFIRMED |
| Care numbers `121,111,198,123,139,122,199,12345` | `IMEI_REQUEST_STATUS.PHP` `$sql6` | HARDCODED | CONFIRMED |
| JRMS update `APP_OR_MANUAL='APPLICATION_ENTRY'` | `JRMS_UNIQUE_KEY_UPDATE.PHP` | HARDCODED stamp | CONFIRMED |
| Login always → `HOME_IR.HTML` not HOME.html | `LOGIN.PHP` | CDAT remains open | CONFIRMED |
| Phone prefix: len=10 as-is; >10 prefix `00`; else VOIP text | SUM CASE + `cdat_phone_prefix_key()` | Duplicate SQL vs PHP | CONFIRMED |

### 5.2 Major workflow traces (abbreviated; evidence in file)

**Login:** LOGIN.HTML POST → LOGIN.PHP trim/empty → `SELECT * FROM LOGINS WHERE USERNAME=? AND PASSWORD=?` plaintext → session role/fullname → `audit_login` INSERT user_sessions → refresh HOME_IR. Errors: die print_r sqlsrv_errors. Duplicate LOGIN1 concatenated SQL.

**Summary Total:** SUM_HOME POST PHONE_NO → audit_log no gate → empty die → `#TT` CDAT_DETAILS isnumeric(other)=1 → group IN/OUT/CALLS/DUR → junk filter → enrichment maps → HTML. Error die print_r.

**Calls between dates:** session required → sql_safe_phone/alnum dates → CDATPCSUSPECT date BETWEEN → enrichment → h() table. OPERATOR/STATE logged; **INFERRED possibly unused in WHERE** — NEEDS RUNTIME VERIFICATION.

**Movements:** isset PHONE_NO → parameterized `WHERE A.PHONE=?` → INCOMING 1→IN → tower map → HTML (no h()). Best SQL hygiene among reports.

**Day/night:** sql_safe_phone → TOP 10 towers by CALLS with hour predicates → htmlspecialchars. Dated variant uses unsanitized #TEMP.

**IR search:** NAME+CRIME_HEAD → LIKE with spaces as `%` → join OFFENCE_DETAILS → PDACT flag if numeric IRKEY in PDACT_MAIN_TABLE → IR.PHP?IRKEY=.

**IR insert family:** POST all fields → INSERT string SQL → echo fail/ok → refresh 30s. **No IRKEY existence check.**

**IMAGE_LIST:** POST insert + file → getimagesize → file_get_contents → INSERT IMAGE_TABLE → SELECT display. No auth.

**JRMS update:** CIN comma list → str_replace to IN list → UPDATE UNIQUE_KEY, IRKEY, GETDATE(), APPLICATION_ENTRY. No auth.

**Upload:** audit_require_uploader → preview exec python → curl FastAPI submit/approve → poll JSON. Errors: RuntimeException if :8088 down.

**SQL console:** admin → ^select + keyword block → wrap LIMIT 1000 → PDO query / CSV. Bypassable (CTE/INTO) — INFERRED.

### 5.3 Unreachable / obsolete / duplicate logic

- SUM1/2/P: unreachable (missing files).
- LOGIN_PAGE demo: dead.
- RETRIEVE1 backdoor: reachable if URL known; not on HOME_IR menu.
- JRMS `*_MAHESH`/`*_OLD`/`*_PHP.PHP`: duplicate of JRMS_SEARCH/#TEMP block.
- Tower root vs `_TWR`: duplicate feature, `_TWR` is form target from SUSPECT_SEARCH.PHP.

---

# 6. COMPLETE ENDPOINT AUDIT

Routing: nginx `:8020` + `.htaccess` `.PHP`→`.php`. No webhooks identified. FastAPI via `/document-api/` and `127.0.0.1:8088`.

### 6.1 Endpoint register (canonical unique basenames)

Auth: `none` / `session` / `admin` / `uploader` / `sets session`.

| Endpoint | Method | File | Purpose | Parameters | Auth | DB/API | Called From | Status | Evidence class |
| -------- | ------ | ---- | ------- | ---------- | ---- | ------ | ----------- | ------ | -------------- |
| `/` | GET | HOME.html | CDAT menu | — | none | — | nginx index | ACTIVE | CONFIRMED |
| `/LOGIN.HTML` | GET | LOGIN.HTML | Login UI | — | none | — | HOME, logout | ACTIVE | CONFIRMED |
| `/LOGIN.PHP` | POST | LOGIN.PHP | Authenticate | USERNAME, PASSWORD | sets session | LOGINS | LOGIN.HTML | ACTIVE | CONFIRMED |
| `/LOGIN1.PHP` | POST | LOGIN1.PHP | Login (SQLi) | USERNAME, PASSWORD | sets session | LOGINS | LOGIN1.HTML, RETRIEVE1 | LEGACY | CONFIRMED |
| `/LOGOUT.PHP` | GET | LOGOUT.PHP | Logout | cookie | optional | user_sessions | HOME, HOME_IR | ACTIVE | CONFIRMED |
| `/check_role.php` | GET | check_role.php | JSON role | cookie | none | — | Spry JS | PARTIAL | CONFIRMED |
| `/HOME_IR.HTML` | GET | HOME_IR.HTML | IR menu | — | none | — | LOGIN redirect, HOME | ACTIVE | CONFIRMED |
| `/SUM.PHP` | POST | SUM.PHP | Summary total | PHONE_NO | none | CDAT_* | SUM_HOME.html | ACTIVE | CONFIRMED |
| `/SUM_BTWN_DATES.PHP` | POST | SUM_BTWN_DATES.PHP | Dated summary | PHONE_NO, FROM_DT, TO_DT | none | CDAT_DETAILS1 | SUM_BETWEEN_DATES.html | ACTIVE | CONFIRMED |
| `/SUM_ISD_CNTS.PHP` `/SUM_NEW_NO.PHP` `/SUM_IN_STATE.PHP` `/SUM_OUT_STATE.PHP` | POST | matching | Variant summaries | phone ± date/state | none | CDAT_* | HOME html | ACTIVE | CONFIRMED |
| `/SUM1.php` `/SUM2.php` `/SUM_P.PHP` | POST | **MISSING** | Intended summary | phone | — | — | SUM_HOME12/2/_P.html | **BROKEN** | CONFIRMED |
| `/CALLS_BTWN_DATES.PHP` | POST | CALLS_BTWN_DATES.PHP | CDR rows | phone, dates, op, state | **session** | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/MOVEMENTS.PHP` | POST | MOVEMENTS.PHP | Movements | PHONE_NO | none | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/MOVEMENTS_BETWEEN_TWO_NUMBERS.PHP` + COMPARISION | POST | matching | Pair movements | two phones | none | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/CDATCNTS1.php` `/BULK_CDAT_CONTACTS.PHP` `/OTHERCDAT.php` | POST | matching | Contacts | phone(s) | none | CDAT* | HOME | ACTIVE | CONFIRMED |
| `/IMEI_SEARCH.PHP` | POST | IMEI_SEARCH.PHP | IMEI→phones | IMEI_NO | **session** | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/IMEI_SEARCH_IN_PHONE.PHP` | POST | IMEI_SEARCH_IN_PHONE.PHP | Phone→IMEIs | PHONE_NO | none | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/ADDRESS.PHP` `/BULK_ADDRESS.php` | POST | matching | Address+QR | phone(s) | none | CDATADDRESS | HOME | ACTIVE | CONFIRMED |
| `/D&N_LOC.PHP` `/d%26n_loc.php` `/DAY&NIGHTLOC.php` | POST | twins | Day/night | PHONE_NO | none | CDATPCSUSPECT | HOME encoded href | ACTIVE | CONFIRMED |
| `/D&N_BT_DTS.PHP` | POST | D&N_BT_DTS.PHP | Dated D/N | phone, dates | none | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/CELLID_SEARCH.php` | POST | CELLID_SEARCH.php | Tower search | CELLID, OPERATOR, STATE | none | CDATCELLTOWERAREANEW | HOME | ACTIVE | CONFIRMED |
| `/VEHICLE_SEARCH.PHP` `/VEHICLE_SEARCH_CRITERIA.PHP` | POST | matching | RTA | VEHICLE_NO ± SOURCE | none | CDAT_RTA | HOME | ACTIVE | CONFIRMED |
| `/COMMON_CNTS.PHP` | POST | COMMON_CNTS.PHP | Common contacts | comma phones | none | CDATPCSUSPECT | HOME | ACTIVE | CONFIRMED |
| `/HABITUAL.PHP` | GET | HABITUAL.PHP | Habitual list | — | none | IRFORMS | HOME | ACTIVE | CONFIRMED |
| `/OFFENDER_SEARCH_BY_MO.PHP` | POST | matching | MO search | MO | none | COMPLETE_MO_CLASSIFICATION | HOME | ACTIVE | CONFIRMED |
| `/IR_SEARCH.PHP` | POST | IR_SEARCH.PHP | IR by name | NAME, CRIME_HEAD | none | FORMS/PDACT | HOME, IR_MODULE | ACTIVE | CONFIRMED |
| `/IR.PHP` | GET | IR.PHP | Dossier | IRKEY | none | FORMS | search links | ACTIVE | CONFIRMED |
| `/IRREPORT.PHP` + IR section PHP | POST | matching | IR INSERT | many fields | **none** | FORMS | HOME_IR | ACTIVE | CONFIRMED |
| `/IMAGE_LIST.PHP` | POST/FILES | IMAGE_LIST.PHP | Photo INSERT | image, IRKEY, CATEGORY, CCNO | **none** | IMAGE_TABLE | HOME_IR | ACTIVE | CONFIRMED |
| `/RETRIEVE.PHP` | POST | RETRIEVE.PHP | Retrieve IR | NAME, FATHER_NAME | none | FORMS | HOME_IR | ACTIVE | CONFIRMED |
| `/JRMS_MAIN_PAGE1.PHP` | GET/POST | JRMS_MAIN_PAGE1.PHP | JRMS hub | — | none | JRMS | HOME | ACTIVE | CONFIRMED |
| `/JRMS_SEARCH.PHP` | POST | JRMS_SEARCH.PHP | JRMS filter | CRIMEHEAD, dates | none | JRMS | JRMS UI | ACTIVE | CONFIRMED |
| `/JRMS_UNIQUE_KEY_UPDATE.PHP` | POST | JRMS_UNIQUE_KEY_UPDATE.PHP | UPDATE keys | CIN_NO, UNIQUE_KEY, IRKEY | **none** | JRMS | JRMS form | ACTIVE | CONFIRMED |
| `/PDACT_MAIN_PAGE_SEARCH.PHP` | GET | PDACT_MAIN_PAGE_SEARCH.PHP | PDACT hub | — | none | PDACT | HOME | ACTIVE | CONFIRMED |
| `/PDACT_SEARCH.PHP` `/PDACT_MO_SEARCH.PHP` `/PDACT_PS_WISE_SEARCH_PHP.PHP` | POST | matching | PDACT search | NAME/MO/PS | none | PDACT | PDACT menu | ACTIVE | CONFIRMED |
| `/ROWDYSHEETER_PS_WISE_SEARCH.PHP` | GET | form | Rowdy UI | — | none | — | HOME | ACTIVE | CONFIRMED |
| `/ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP` | POST | handler | Rowdy search | POLICE_STATION | none | ROWDY_SHEETER_DATA1 | form | ACTIVE | CONFIRMED |
| `/TOWER_HOME.HTML` | GET | TOWER_HOME.HTML | Tower hub | — | none | — | HOME | ACTIVE | CONFIRMED |
| `/SUSPECT_SEARCH.PHP` | GET | form | Posts to _TWR | dump keys | none | — | TOWER_HOME | ACTIVE | CONFIRMED |
| `/SUSPECT_SEARCH_TWR.PHP` + other `_TWR` | POST | matching | Dump search | phone, PS, CR, year, date, hms | none | TWRMDB | form | ACTIVE | CONFIRMED |
| `/TRAINING_MODULE1.PHP` | POST | TRAINING_MODULE1.PHP | Training | fields | none | TRAINING_DB | HOME | ACTIVE | CONFIRMED |
| `/admin_upload.php` | GET/POST/FILES/AJAX | admin_upload.php | CDR/SDR UI | files, ajax_action, network | **uploader** | jobs + FastAPI | HOME | ACTIVE | CONFIRMED |
| `/admin_upload_history.php` `/_job_status.php` `/_sync_jobs.php` `/_verify.php` | GET/POST | matching | History/poll/verify | job id | **uploader** | jobs + FastAPI | upload UI | ACTIVE | CONFIRMED |
| `/download_template.php` | GET | download_template.php | 410 | — | uploader | — | upload UI? | **BROKEN** | CONFIRMED |
| `/ADMIN_ACTIVITY_LOG.PHP` | GET/POST | ADMIN_ACTIVITY_LOG.PHP | Activity UI | filters | **admin** | user_activity_logs | HOME | ACTIVE | CONFIRMED |
| `/ADMIN_SQL_CONSOLE.PHP` | GET/POST | ADMIN_SQL_CONSOLE.PHP | Ad-hoc SQL+export | sql_query, export | **admin** | postgres | HOME | ACTIVE | CONFIRMED |
| `/ADMIN_CREATE_USER.PHP` | GET/POST | ADMIN_CREATE_USER.PHP | Create login | user fields | **admin** | logins | HOME_IR (commented link) | ACTIVE | CONFIRMED |
| `/GET_PS.PHP` `/GET_YEAR.php` `/GET_DIVISION.PHP` `/GET_CRNO.php` | POST | matching | AJAX dropdowns | district/PS/year | none | masters | JS on migrant/IR | POTENTIALLY UNUSED | Medium |
| `/document-api/*` | * | nginx proxy :8088 | Import API | multipart/JSON | **NEEDS RUNTIME VERIFICATION** | postgres | admin_upload, JS | ACTIVE if service up | CONFIRMED wiring |
| `/qrcode/php/qr_img.php` | GET | qr_img.php | QR image | `d=` | none | — | img src | ACTIVE | CONFIRMED |

Additional ~80 unique root PHP basenames (CAF, migrant, CIS, VBR, NBWS, ALLDATA, NAMESEARCH, DUMP, extra JRMS/PDACT/IR copies, demos): see §8. Full list of 212 unique root PHP basenames is in `MAIN_BRANCH_ENDPOINT_INVENTORY.md` and usage matrix.

# 7. ACTIVE ENDPOINTS

Confirmed callers:

All rows above with Status ACTIVE and Called From = HOME / HOME_IR / IR_MODULE / TOWER / SUM_HOME / nginx / LOGIN / upload UI / search result `IR.PHP?IRKEY=` / `PDACT_MAIN.PHP?PDACT_KEY=`.

Also ACTIVE includes: `sqlsrv_compat.php` (prepend, not HTTP-useful alone), `activity_logger.php` (include).

# 8. UNUSED ENDPOINTS

**CONFIRMED UNUSED by CDAT UI (no repo reference from root app):** `curfewepass/**` HTTP app, `old ir/**`, `new ir/**`, `TWRDB/**`, `ROUGH_TOWER/**`, `SUN/**`, `image_migrate` CLI, `distributed_migrate` CLI. **Still URL-reachable** under nginx `try_files $uri` — NEEDS RUNTIME VERIFICATION of hits.

**POTENTIALLY UNUSED:** CAF_SEARCH, MIGRANT_*, CIS_*, VBR, NBWS, ALLDATA*, NAMESEARCH, DUMP*, SUM_ALLDB, CALLS_TOT, CALLS_BT_NOS, CALLDETAILS, HOME_IMEI + IMEI_REQUEST_*, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS, extra JRMS/PDACT/IR copies, TRAINING_MODULE2, LOGIN1 (unless RETRIEVE1 used), ANALYSIS_ABSTRACT, MO_IMAGE_LIST, GET_* if parent UI unused.

**NEEDS VERIFICATION:** production access logs; bookmarks; result-page deep links (`OFFENDER_FD.PHP?MO_KEY=`).

# 9. BROKEN ENDPOINTS

| Endpoint | Source | Problem | Impact | Evidence | Confidence |
| -------- | ------ | ------- | ------ | -------- | ---------- |
| `/SUM1.php` | `SUM_HOME12.html` `action="SUM1.php"` | File missing on `main` | 404 | ls-tree + form | High CONFIRMED |
| `/SUM2.php` | `SUM_HOME2.html` | Missing | 404 | same | High |
| `/SUM_P.PHP` | `SUM_HOME_P.html` | Missing | 404 | same | High |
| `/download_template.php` | upload feature | Hard-coded 410 | Feature dead | file body | High |
| `/get_state.php` | `myindex.php` AJAX | Missing at root | Demo broken | ls-tree | High |
| `/ddtf.js` (root) | `demo.php.php` | Missing | Demo broken | ls-tree | High |
| `db_config.php` | sqlsrv_compat, activity_logger, Python | Not in git | App cannot start from clone | .gitignore + cat-file | High |
| `#TEMP` / `FOR XML PATH` / `isnumeric` pages | JRMS, COMMON_CNTS, SUM, VBR, PDACT | Dialect vs shim | Wrong/empty results or SQL error | sqlsrv_compat translate vs source SQL | Medium INFERRED until runtime |

---

# 10. DATABASE AUDIT

### Engines and connection

| Item | Finding | Class |
| ---- | ------- | ----- |
| Legacy API | `sqlsrv_connect($serverName, ['Database'=>…])` typically **no UID/PWD** (Windows/trusted implied) | CONFIRMED |
| Dominant `$serverName` | `CPHYDERABAD1\DAU_HYD_2023` (also `DAU_HYD`, `CPHYDERABAD1`, `10.10.x.x\DAU_HYD_2023`, `UUUU-HP`, `USER-HP`) | CONFIRMED |
| Runtime on this tree | **PostgreSQL** via `sqlsrv_compat.php` + `db_config.php` env `CDR_DB_*` | CONFIRMED in repo; **NEEDS RUNTIME VERIFICATION** production prepend |
| Example DSN | host `127.0.0.1`, port `5432`, db `postgres`, user `postgres`, password placeholder | CONFIRMED `.env.example` / `db_config.example.php` — **not live secrets** |
| `pg_connect` / `mysqli` | Not used | CONFIRMED |
| Transactions in root PHP | Not used (except implicit PDO) | CONFIRMED |
| Shim timeouts | `statement_timeout=120s`, idle_in_tx 60s, lock 30s | CONFIRMED `sqlsrv_compat.php` |

### Legacy DB names → shim map (all → `postgres`)

`cdatdupl`, `cdat`, `twrmdb`, `irforms`, `forms`, `jrms`, `pdact`, `lostreport_hawkeye`, `migrant_labours_form`, `training_db`, `cpms`, `cafs`, `cis_data_base`, `cdat_import`, `testing_db`, `rough`, `distributed_db`.

Also in dumps/Python: `distributed_db` (Citus), `cdat_db`, `ai_copint_db`, MSSQL `address_db`, `cellids_db`, `DOPAMS_HYD_UNIT`.

### Table usage matrix (root PHP, static)

| Table | SELECT | INSERT | UPDATE | DELETE | Used By | Status |
| ----- | ------ | ------ | ------ | ------ | ------- | ------ |
| CDATPCSUSPECT | Y | Y* import | — | — | most CDR reports; Python import | ACTIVE |
| CDAT_DETAILS / CDAT_DETAILS1 | Y | — | — | — | SUM family | ACTIVE |
| CDATSUSPECT | Y | — | — | — | contacts/summaries | ACTIVE |
| CDATADDRESS / ADDRESS_OTHER_STATE | Y | — | — | — | address + enrichment; shim LATERAL | ACTIVE |
| CDATCELLTOWERAREANEW | Y | — | — | — | CELLID / D&N / movements | ACTIVE |
| CDATPHONEAREA | Y | — | — | — | prefix/state | ACTIVE |
| CDAT_RTA | Y | — | — | — | VEHICLE_* | ACTIVE |
| LOGINS / logins | Y | Y | — | — | LOGIN, ADMIN_CREATE_USER | ACTIVE plaintext |
| IR_PARTICULARS | Y | Y | — | — | IR.* | ACTIVE |
| IMAGE_TABLE / MO_IMAGE_TABLE / SUSPECT_IMAGE_TABLE | Y | Y | — | — | IMAGE_LIST, IR display | ACTIVE |
| OFFENCE_DETAILS / PREVIOUS_OFFENCE_DETAILS | Y | Y | — | — | IR + bulk | ACTIVE |
| BRIEF_FACTS / FAMILY_HISTORY / DISPOSAL_OF_PROPERTY / LOCAL_CONTACTS_FACILITATORS / RELATIONSHIP_WITH_OTHER_ASSOCIATES / MULAKATH_ENTRY / ANALYSIS_ABSTRACT | Y | Y | — | — | HOME_IR | ACTIVE / ANALYSIS potentially unused UI |
| JRMS_TOTAL_2012_TO_2017 | Y | Y | Y | — | JRMS_* | ACTIVE |
| PDACT_MAIN_TABLE / PDACT_PRESS_NOTES_TABLE | Y | Y | — | — | PDACT_* | ACTIVE / press notes potentially unused |
| ROWDY_SHEETER_DATA1 | Y | — | — | — | rowdy | ACTIVE |
| LOST_REPORT_CDR_DATA / COMPLAINANT_DETAILS / IMEI_REQUESTED_DETAILS | Y | — | — | — | Hawkeye | POTENTIALLY UNUSED UI |
| MIGRANT_LABOUR_TABLE | Y | Y | — | — | MIGRANT_* | POTENTIALLY UNUSED UI |
| CIS_COMPLETE_DATA | Y | — | — | — | CIS_* | POTENTIALLY UNUSED |
| NBWS_VERIFY_DATA_IMPORTANT | Y | — | — | — | NBWS / IR.PHP pending | PARTIAL |
| COMPLETE_MO_CLASSIFICATION | Y | — | — | — | offender MO | ACTIVE |
| user_sessions / user_activity_logs | Y | Y | Y | — | activity_logger | ACTIVE |
| upload_* / document_jobs | Y | Y | Y | — | upload pipeline | ACTIVE |
| TWRMDB_MASTER_CDAT | Y | — | — | — | tower dump | ACTIVE if loaded |
| tbladmin / tblpass / tblcategory | Y | Y | Y | — | curfewepass only | UNUSED by CDAT |
| CALLCENTER_NOS | Y | — | — | — | OTHERCDAT.php | MISSING DEP possible |
| celltowerfiltered | Y | — | — | — | NEAREST_CELLIDS | POTENTIALLY UNUSED UI |
| MSSQL `#TEMP`/`#TT`/`#RESULT` | Y | Y | Y | rare | JRMS/PDACT/SUM/D&N/VBR/COMMON | ACTIVE dialect; PG via shim |

\* Inserts into `cdatpcsuspect` from Python import, not typical search PHP. Deletes almost absent except temp-table cleanup (`COMMON_CNTS.PHP` `$sql5 delete from #common_numbertable3`).

**Missing tables/columns on live PG:** NEEDS RUNTIME VERIFICATION (`\dt`, `\d`). High-risk candidates: CAF, TRAINING_DB, Hawkeye, VBR/ALL_ILD_DATA_2012, CIS, CALLCENTER_NOS, ROWDY_SHEETER_DATA1, TWRMDB_MASTER_CDAT.

# 11. SQL / DATABASE LOGIC

### SQL logic patterns

| Pattern | Where | Impact |
| ------- | ----- | ------ |
| String interpolation SQLi | Almost all reports | CRITICAL security + wrong filters |
| Parameter binding | LOGIN.PHP; MOVEMENTS.PHP `PHONE=?`; activity_logger; ADMIN_ACTIVITY_LOG; some enrichment IN lists | Safer islands |
| `sql_safe_*` then still concatenate | ~9 pages | Not real parameterization |
| Aggregation IN/OUT/CALLS/DUR | SUM, IMEI, CDATCNTS | Core investigative metric |
| Date `CONVERT(CHAR(10),STARTTIME,121) BETWEEN` | SUM_BTWN, CALLS_BTWN, D&N_BT | Shim rewrites some to `::date` |
| `SELECT TOP n` | D&N, CELLID, PDACT hub | MSSQL; shim must rewrite LIMIT |
| `SELECT * INTO #TEMP` | JRMS, PDACT, D&N_BT, VBR, bulk IR | Shim: `CREATE TEMP TABLE … AS` |
| `FOR XML PATH` | COMMON_CNTS.PHP | **MSSQL-only** — likely broken on PG |
| `SET DATEFORMAT DMY` | JRMS | Shim strips some SET; date parse risk |
| `CONVERT(IMAGE,PHOTO)` | JRMS | Binary photo; PG bytea risk |
| `WITH (NOLOCK)` | SUM.PHP | Shim strips; no PG equivalent needed |
| `isnumeric()` | SUM.PHP, JRMS IR link | May not exist on PG |
| Dynamic identifier | VEHICLE_SEARCH_CRITERIA `$number1 LIKE` | Column injection |
| Cross-db three-part names `IRFORMS..HABITUAL` | HABITUAL.PHP | Shim strips db prefix → public table must exist |
| LATERAL rewrite for address/tower JOINs | sqlsrv_compat.php | Wrong address/tower if pattern misses — DATA risk |

**Stored procedures / views in PHP:** not called by name as SPs. Geo functions `calculatedistance` / `getbearing` in `sql/celltower_geo.sql` for nearest-tower pages. Indexes in `sql/cdatpcsuspect_*index.sql` + systemd builders — CONFIGURED; NEEDS RUNTIME VERIFICATION they exist.

---

# 12. EXTERNAL API AUDIT

| Service | Endpoint | Method | Auth | Caller | Purpose | Request/Response | Errors | Config | Status | Evidence |
| ------- | -------- | ------ | ---- | ------ | ------- | ---------------- | ------ | ------ | ------ | -------- |
| Document Processing FastAPI | `http://127.0.0.1:8088/` (`CDR_API_URL`) + nginx `/document-api/` | POST/GET multipart JSON | Optional `X-API-Key` if `cfg['api']['api_key']` set; **default empty — NEEDS RUNTIME VERIFICATION** | `document_processing_client.php`, `admin_upload.php` approve curl, `js/sdr_resumable_upload.js` | CDR/SDR jobs, resumable upload, staging approve | file + module → job JSON | RuntimeException / HTTP≥400 | `cdr_upload_config.php` | **ACTUALLY USED** if process up | CONFIRMED |
| cdr_preview.py | local exec | — | OS user | admin_upload preview | Parse preview | JSON `{ok:…}` | exec exit code | scripts/ | **USED** | CONFIRMED |
| excel_to_csv.py | local exec | — | OS user | excel_converter.php | xls→csv | file | exec | scripts/ | **USED** | CONFIRMED |
| image_migrate | CLI | — | — | none from PHP | MSSQL images→PG | — | — | image_migrate/ | **CONFIGURED UNUSED by UI** | CONFIRMED |
| distributed_migrate | CLI/systemd | — | — | none from PHP | Citus migrate | — | — | scripts/systemd | **CONFIGURED UNUSED by UI** | CONFIRMED |
| Internal FTP | `ftp://192.168.x.x/…` | URL in HTML | none | CAF_SEARCH.PHP | Link CAF files | href from PHONE | none | hardcoded | **UNKNOWN** (UI potentially unused) | CONFIRMED string |
| CDN FA/SheetJS/PapaParse | cdnjs | GET | none | admin_upload.php | Upload UI JS | scripts | CDN down → UI degrade | script tags | **USED** on upload pages | CONFIRMED |
| WhatsApp/SMS/SMTP/Maps/OAuth/Payments | — | — | — | — | — | — | — | — | **NOT IDENTIFIED** in root PHP | CONFIRMED absent |
| Mail `crimelab@hyd.tspolice.gov.in` | display only | — | — | TOWER_HOME.HTML | Contact analysts | marquee text | — | hardcoded | INFORMATIONAL; no `mail()` sender found | CONFIRMED |

---

# 13. AUTHENTICATION AUDIT

### Auth flow (CONFIRMED)

```text
LOGIN.HTML (USERNAME, PASSWORD, no CSRF)
  → POST LOGIN.PHP
  → sqlsrv FORMS.LOGINS  USERNAME=? AND PASSWORD=?   (plaintext)
  → $_SESSION['audit_role'|'audit_fullname']
  → audit_login() INSERT user_sessions; set audit_session_id, audit_username, audit_user_id
  → header refresh HOME_IR.HTML
LOGOUT.PHP → audit_logout UPDATE user_sessions → session_destroy → LOGIN.HTML
```

| Topic | Finding | Class |
| ----- | ------- | ----- |
| password_hash / verify / md5 / sha1 login | **None** | CONFIRMED |
| OTP | **Not present** | CONFIRMED |
| session_regenerate_id | **Not present** | CONFIRMED |
| Idle timeout in PHP | **Not present** | CONFIRMED |
| Cookie Secure/HttpOnly/SameSite | **Not set in application code** | CONFIRMED; flags **NEEDS RUNTIME VERIFICATION** php.ini |
| Role re-check vs DB each request | **No** — session only | CONFIRMED |
| API auth FastAPI | Optional X-API-Key; default URL localhost | NEEDS RUNTIME VERIFICATION |
| LOGIN1 | Concatenated SQL login | CONFIRMED SQLi |
| RETRIEVE1 | Hardcoded FORMS/sa@*** backdoor → LOGIN1.php | CONFIRMED |

# 14. AUTHORIZATION AUDIT

### Pages that do **not** enforce authentication (CONFIRMED)

- All static `*.html` including `HOME.html`, `HOME_IR.HTML`, every search form.
- Almost all report PHP including `SUM.PHP`, `ADDRESS.PHP`, `IR.PHP`, `IRREPORT.PHP`, `IMAGE_LIST.PHP`, `JRMS_UNIQUE_KEY_UPDATE.PHP`, `HABITUAL.PHP`, tower, PDACT, rowdy, vehicle, cell, day/night, CDAT contacts.
- `check_role.php` (returns is_admin false if anonymous).

### Pages that **do** enforce

| Gate | Pages |
| ---- | ----- |
| `audit_require_session` | CALLDETAILS, CALLS_BTWN_DATES, IMEI_SEARCH, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS |
| `audit_require_admin` | ADMIN_SQL_CONSOLE, ADMIN_CREATE_USER, ADMIN_ACTIVITY_LOG |
| `audit_require_uploader` | admin_upload*, download_template |

### Privilege escalation (INFERRED / CONFIRMED)

- Direct URL to `ADMIN_SQL_CONSOLE.PHP` without admin session → 403 (gate works **if** logger loads). CONFIRMED code.
- Direct URL to `SUM.PHP` / `IR.PHP` / IMAGE_LIST → **no gate**. CONFIRMED.
- `check_role.php` only hides menu — not enforcement. CONFIRMED.
- Create-user can set `role=admin` if caller is already admin (no extra approval). CONFIRMED.
- Uploader (`poweruser`) cannot hit SQL console (admin only) — CONFIRMED. But poweruser can import CDR into `cdatpcsuspect` — high data impact.

---

# 15. HARD-CODED VALUES AUDIT

**Secrets are masked. Existence is reported.**

| File | Location | Type | Classification | Risk | Evidence (masked) |
| ---- | -------- | ---- | -------------- | ---- | ----------------- |
| `RETRIEVE1.PHP` | footer `if ($USERNAME == "FORMS" && $PASSWORD == "sa@***")` | Default user + password | **SECRET / CREDENTIAL** | CRITICAL backdoor + possible SA reuse | CONFIRMED FROM SOURCE |
| `db_config.example.php` / `.env.example` | placeholders | DB password template | CONFIGURATION | Low (placeholder) | CONFIRMED |
| `db_config.php` | not in git | Real DB creds | SECRET | High if leaked off-repo | NEEDS RUNTIME VERIFICATION |
| `LOGIN.PHP` + ~100 reports | `$serverName = 'CPHYDERABAD1\DAU_HYD_2023'` | Hostname/instance | INFRASTRUCTURE | Recon | CONFIRMED |
| Some IR/JRMS | `10.10.x.x\DAU_HYD_2023` | Internal IP + instance | INFRASTRUCTURE | Recon | CONFIRMED (octet masked in this report) |
| `CAF_SEARCH.PHP` | `ftp://192.168.x.x/` | FTP URL | INFRASTRUCTURE | Internal share exposure | CONFIRMED |
| `CAF_SEARCH.PHP` | `UUUU-HP` / `USER-HP` | Hostnames | INFRASTRUCTURE | Recon | CONFIRMED |
| `cdat-web.nginx.conf` | `listen 8020`; `root /mnt/storage1/cdat-web`; proxy `127.0.0.1:8088` | Ports/paths | INFRASTRUCTURE | Env coupling | CONFIRMED |
| `cdr_upload_config.php` | default `http://127.0.0.1:8088` | URL | CONFIGURATION | OK if localhost-only | CONFIRMED |
| `TOWER_HOME.HTML` | `crimelab@hyd.tspolice.gov.in` | Email | INFORMATIONAL | Spam/targeting | CONFIRMED |
| `admin_upload.php` `mapNetworkToOperator` | 2/15/12/4 | Business constant | BUSINESS RULE | Low | CONFIRMED |
| `D&N_LOC.PHP` | 05:00 / 22:00 / 07:00 | Business constant | BUSINESS RULE | Incorrect loc reports | CONFIRMED |
| `JRMS_MAIN_PAGE1.PHP` | Cherlapalli, Chanchalguda | Business constant | BUSINESS RULE | Missing jails | CONFIRMED |
| PDACT/ROWDY/BULK_CDAT | IRKEY `113769` | Business constant | BUSINESS RULE | Wrong photo | CONFIRMED |
| `IMEI_REQUEST_STATUS.PHP` | care number list | Business constant | BUSINESS RULE | Mislabel | CONFIRMED |
| `IR_SEARCH.PHP` | name length >4 | Business constant | BUSINESS RULE | Missed hits | CONFIRMED |
| `sqlsrv_compat.php` | statement_timeout 120s | Config constant | CONFIGURATION | Query abort | CONFIRMED |
| nginx | `client_max_body_size 750G`; timeouts 86400s | Config | CONFIGURATION | DoS | CONFIRMED |
| `scripts/systemd/*` env examples | `MSSQL_SA_PASSWORD`, `DIST_PG_PASSWORD` placeholders | CREDENTIAL templates | CONFIGURATION | If real values committed elsewhere — scan | example files only on main |

No encryption keys / JWT secrets identified in root PHP. FastAPI `api_key` is config-driven (`getenv`), not hardcoded in the PHP client default beyond empty.

---

# 16. SECURITY AUDIT

Do **not** exploit. Severity CRITICAL/HIGH/MEDIUM/LOW/INFO.

| ID | Severity | Category | File | Function/section | Evidence | Attack surface | Impact | Recommendation | Confidence |
| -- | -------- | -------- | ---- | ---------------- | -------- | -------------- | ------ | -------------- | ---------- |
| SEC-01 | CRITICAL | Authz bypass | HOME.html + ~190 PHP | missing `audit_require_*` | grep audit_require_ ~15 hits only; nginx index HOME.html | Anyone who can reach :8020 | Full CDR/IR/PII read; many writes | Default-deny prepend; network ACL | High CONFIRMED |
| SEC-02 | CRITICAL | Weak crypto | LOGIN.PHP; ADMIN_CREATE_USER.PHP | plaintext compare/INSERT | `PASSWORD = ?` raw; INSERT password unbound hash | DB dump, SQLi, backups | All user passwords | password_hash/verify | High CONFIRMED |
| SEC-03 | CRITICAL | SQLi | LOGIN1.PHP | `$sql = "… USERNAME='$USERNAME' AND PASSWORD='$PASSWORD'"` | exact string | Login endpoint | Auth bypass / dump LOGINS | Delete or bind like LOGIN.PHP | High CONFIRMED |
| SEC-04 | CRITICAL | SQLi | SUM.PHP, ADDRESS.PHP, IR.PHP, JRMS_UNIQUE_KEY_UPDATE.PHP, GET_PS.PHP, NAMESEARCH, RETRIEVE*, … | interpolated `$number` / IRKEY / IN lists | e.g. SUM.PHP `WHERE PHONE='$number'`; JRMS `IN ('$NUMBER2')` | Almost every report/write | Data exfil/modify | Bind parameters | High CONFIRMED |
| SEC-05 | CRITICAL | Backdoor | RETRIEVE1.PHP | hardcoded FORMS/sa@*** | footer if-block | Known URL | Privileged session + SA recon | Remove; rotate secrets; history scan | High CONFIRMED |
| SEC-06 | HIGH | XSS | ADDRESS.PHP, HABITUAL.PHP, CALLS_BT_NOS, RETRIEVE*, GET_CRNO, most tables | `echo $row['NAME']` no htmlspecialchars | pattern ubiquitous; `h()` almost unused | Result pages | Session theft / analyst malware | Encode output | High CONFIRMED |
| SEC-07 | HIGH | CSRF | all POST forms | no csrf token; session_token is audit id only | grep no csrf fields | Logged-in admin/uploader | Forged update/SQL/upload | Tokens + SameSite | High CONFIRMED |
| SEC-08 | HIGH | Unauth upload | IMAGE_LIST.PHP, MO_IMAGE_LIST.PHP | FILES→SQL INSERT | no audit_require; getimagesize only | HOME_IR reachable without login | Unauth photo insert + SQLi | Auth + type/size + parameterized bytea | High CONFIRMED |
| SEC-09 | HIGH | Admin SQL | ADMIN_SQL_CONSOLE.PHP | `$db->query($wrapped_query)` | weak ^select filter; HOME link public | Admin cookie / filter bypass | Full postgres read | Isolate; RO role; stronger parser | High CONFIRMED |
| SEC-10 | HIGH | Info leak | almost every report | `die(print_r(sqlsrv_errors(), true))` | e.g. ADDRESS.PHP L24, SUM.PHP, LOGIN.PHP | All users | Schema/query disclosure | Generic errors; server log | High CONFIRMED |
| SEC-11 | HIGH | API/DoS | cdat-web.nginx.conf; admin_upload | `/document-api/` + 750G + 86400s | nginx conf | Same vhost as UI | Disk fill; unauth import if API open | Localhost bind; authn; size cap | High CONFIRMED wiring; API auth NEEDS RUNTIME |
| SEC-12 | MEDIUM | Session | activity_logger.php | no regenerate/timeout/cookie flags | audit_require_session only checks audit_username | Session cookie | Fixation / hijack | Regenerate; timeout; cookie flags | High code; php.ini NEEDS RUNTIME |
| SEC-13 | MEDIUM | Secrets | RETRIEVE1; $serverName; CAF ftp | see §15 | source strings | Git clone | Recon / credential reuse | Remove; rotate | High CONFIRMED |
| SEC-14 | MEDIUM | Command exec | admin_upload.php; excel_converter.php | `exec(...)` python | preview/convert | Uploader | RCE if args weak — **not confirmed RCE** | API-only preview; disable exec | Medium INFERRED |
| SEC-15 | MEDIUM | DoS | SUM.PHP `set_time_limit(0)`; unauth heavy SQL | + nginx 750G | source | Unauth user | Lab outage | Auth; limits; pagination | High CONFIRMED |
| SEC-16 | MEDIUM | Auth confusion | check_role.php | JSON no login required | full file | Anyone | Role disclosure if cookie | Require session | High CONFIRMED |
| SEC-17 | LOW | Outdated libs | jquery-ui 1.10.4 (2014); Spry; jQuery 2.1.1 | version strings | datepicker/menu | Historical XSS CVEs | Replace after auth | High CONFIRMED versions |
| SEC-18 | LOW | CDN | admin_upload.php | FA 6.4 / SheetJS 0.18.5 / PapaParse 5.4.1 | script tags; SRI **not verified** | Uploader browser | Supply chain XSS | SRI or vendor local | Medium |
| SEC-19 | INFO | Dump trees in docroot | curfewepass, old ir, … | nginx try_files $uri | extra apps reachable | Extra surface | Move outside root | High CONFIRMED |
| SEC-20 | INFO | Verbose die/exit | many PHP | die/print_r | grep | Users | UX + recon | Central handler | High CONFIRMED |
| SEC-21 | HIGH | Unauth write | JRMS_UNIQUE_KEY_UPDATE.PHP; IRREPORT.PHP etc. | UPDATE/INSERT no gate | full scripts | Anyone with URL | Corrupt jail/IR records | Authz + audit | High CONFIRMED |
| SEC-22 | MEDIUM | Identifier injection | VEHICLE_SEARCH_CRITERIA.PHP `$sql9` | `WHERE $number1 LIKE` | POST column name | Vehicle search | SQLi via identifier | Allow-list columns | High CONFIRMED |
| SEC-23 | LOW | Open data in QR | ADDRESS.PHP, VEHICLE_SEARCH.PHP | qr_img.php?d= phone+address | img src | Anyone viewing page | PII in QR URL/logs | Auth first | High CONFIRMED |

Path traversal / LFI / SSRF classic patterns: not identified as primary bugs beyond FTP URL and local curl to :8088 (localhost SSRF low). Directory listing: depends on nginx (try_files; no autoindex in conf snippet) — NEEDS RUNTIME VERIFICATION.

---

# 17. DATA PRIVACY / SENSITIVE DATA

| Data | Collected | Stored | Displayed | Logged | Exported | Who can access (code) | Protection | Risk |
| ---- | --------- | ------ | --------- | ------ | -------- | --------------------- | ---------- | ---- |
| Phone numbers / CDR | HTML POST, uploads | CDATPCSUSPECT, CDAT_DETAILS* | Almost all CDAT reports | audit_log search_data on **some** pages only (e.g. SUM, CALLS_BTWN, MOVEMENTS, IMEI_SEARCH) | SQL console CSV | **Unauthenticated** by default | None beyond network | CRITICAL |
| IMEI | POST | CDATPCSUSPECT, Hawkeye | IMEI reports | some audit_log | SQL console | mostly unauth (IMEI_SEARCH has session) | weak | CRITICAL |
| Subscriber address / CAF | DB joins | CDATADDRESS, ADDRESS_OTHER_STATE | ADDRESS, summaries, QR | rare | SQL console | unauth | none | CRITICAL |
| Aadhaar / IDPROOF | IR insert; JRMS compare | IR_PARTICULARS.AADHAR_NO; JRMS IDPROOF | JRMS “IR AVAILABLE” | not systematically | SQL console | unauth JRMS | none | CRITICAL (Aadhaar) |
| Photographs | IMAGE_LIST upload; JRMS PHOTO; suspect images | IMAGE_TABLE SQL; JRMS PHOTO | IR/PDACT/rowdy/JRMS | no | SQL console possible | unauth IMAGE_LIST write+read | none | CRITICAL |
| Offender IR / MO / crime | IR forms + search | FORMS / IRFORMS / PDACT / ROWDY | IR.PHP dossier | rare | SQL console | unauth | none | CRITICAL |
| Jail CIN / unique key | JRMS | JRMS_TOTAL_2012_TO_2017 | JRMS pages | no | SQL console | unauth **write** | none | CRITICAL |
| Credentials | login / create user | LOGINS/logins **plaintext** | not displayed | audit_log does not log password (CONFIRMED create-user log has username/role/fullname only) | DB dump | admin create; anyone SQLi | none | CRITICAL |
| Vehicle RTA | search | CDAT_RTA | VEHICLE_* + QR | no | SQL console | unauth | none | HIGH |

Legal/compliance (Aadhaar, CDR retention): **Not identified in source** — organizational **NEEDS VERIFICATION**.

---

# 18. INPUT VALIDATION

| Source | Validation | Sanitization | DB handling | Output | Gap |
| ------ | ---------- | ------------ | ----------- | ------ | --- |
| LOGIN USERNAME/PASSWORD | trim; non-empty | none | bound `?` (LOGIN.PHP) / concatenated (LOGIN1) | HTML error | No rate limit/CSRF |
| SUM PHONE_NO | trim non-empty only | none | string SQL | echo rows | No digit check |
| CALLS_BTWN / IMEI_SEARCH / D&N_LOC / NEAREST | sql_safe_phone/imei/float/alnum | strip non-digits/alnum | still concatenated | h() on some | Dates with `/` destroyed by alnum filter |
| MOVEMENTS PHONE_NO | trim isset | none | **bound `?`** | raw echo | XSS |
| IR NAME | SQL len>4 after strip spaces | REPLACE spaces to `%` | string LIKE | raw | SQLi/XSS |
| IR INSERT fields | **none** | none | string INSERT | refresh | Mass assignment |
| IMAGE_LIST file | getimagesize only | addslashes tmp_name (useless) | binary in SQL string | img | No MIME/size/auth |
| VEHICLE_SOURCE | HTML required on number | none | **identifier interpolation** | raw | SQLi |
| Upload preview | ext ∈ csv,xls,xlsx; move_uploaded_file sanitized name | preg_replace filename | FastAPI | JSON | exec path |
| Upload create_table | `preg_replace('/[^a-zA-Z0-9_]/','')` | yes | dynamic SQL table name | JSON | Still powerful if uploader malicious |
| Admin SQL | ^select + keyword block | wrap LIMIT 1000 | PDO query | HTML/CSV | Bypassable |
| CELLID | trim non-empty; quote escape `'` | str_replace quotes; LIKE escape | string SQL + ILIKE | htmlspecialchars some | Hybrid dialect |

---

# 19. OUTPUT / REPORTING

| Type | Input | Processing | Output | Auth | Data exposure | Status |
| ---- | ----- | ---------- | ------ | ---- | ------------- | ------ |
| HTML tables | search POST | SQL → echo `<td>` | Browser | mostly none | Full PII/CDR on screen | ACTIVE |
| QR | phone/address/vehicle | qr_img.php?d= | PNG | none | PII in query string | ACTIVE |
| Photos | IRKEY | base64/SQL image | `<img>` | none | Faces | ACTIVE |
| CSV/XLS | admin SQL | PDO fetch | download | admin | Whatever SELECT returns | ACTIVE |
| JSON job status | job id | FastAPI/DB | JSON | uploader | job metadata | ACTIVE |
| Print | browser print of HTML | none dedicated print CSS on most main pages | paper | none | same as HTML | ACTIVE (implicit) |
| PDF | — | **not identified** as CDAT report | — | — | — | NOT IDENTIFIED |

---

# 20. FILE UPLOAD / DOWNLOAD

| Item | admin_upload.php (CDR/SDR) | IMAGE_LIST.PHP (IR photo) |
| ---- | -------------------------- | ------------------------- |
| Auth | `audit_require_uploader` (admin\|poweruser) | **none** |
| Extensions | preview: csv/xls/xlsx; CDR/SDR via API (operator files / .bak) | getimagesize only (not extension allow-list) |
| MIME | not strictly validated in PHP | getimagesize |
| Filename | preg_replace non-alnum; time prefix | tmp_name addslashes |
| Size | nginx 750G; PHP memory 512M | unbounded in PHP |
| Storage | `uploads/` + tmp preview; FastAPI staging | **inside SQL IMAGE column** |
| Execution risk | exec python preview; uploaded files not executed as PHP if uploads outside docroot — **NEEDS RUNTIME VERIFICATION** uploads path | SQL interpolation of binary |
| Scanning | not identified (no AV hook in PHP) | none |
| Download auth | job/status uploader; download_template 410 | images displayed inline unauth |
| Cleanup | preview unlink in finally-ish blocks (isset workPath unlink) | none |
| Resumable SDR | js/sdr_resumable_upload.js → /document-api/ | n/a |

---

# 21. LOGGING / AUDIT TRAIL

| What | Where | Who can access (code) | Sensitive? | Gap |
| ---- | ----- | --------------------- | ---------- | --- |
| Login/logout | user_sessions + audit_log System LOGIN/LOGOUT | admin activity UI | IP, UA, username | No password logged (good) |
| Some searches | user_activity_logs.search_data JSON (phone, dates, imei) | admin | **PII in audit DB** | Most reports **do not** call audit_log |
| SQL console | audit_log Execute/Export | admin | query text | |
| Create user | audit_log username/role/fullname | admin | no password in log | |
| Upload | upload_activity_logs + document_jobs | uploader/admin | filenames, operator | |
| PHP SQL errors | **browser** via print_r | anyone | schema | No central error log in app |
| FastAPI logs | **Not in PHP**; NEEDS RUNTIME VERIFICATION journald/uvicorn | ops | possible PII in request logs | |

**Auditability of investigations:** **Weak.** Unauthenticated searches leave no user identity. Only a minority of pages call `audit_log`.

---

# 22. ERROR HANDLING

| Pattern | Where | Impact |
| ------- | ----- | ------ |
| `die(print_r(sqlsrv_errors(), true))` | Almost every report + LOGIN | Leak + abort |
| Empty-ish catch / PDO ERRMODE_SILENT | activity_logger `audit_db` | Audit fail silent; login continues |
| `header refresh:30` after IR/JRMS write | IRREPORT, JRMS_UNIQUE_KEY_UPDATE | User waits; easy to miss failure |
| FastAPI down | admin_upload RuntimeException text | Upload fails; search still works |
| Missing db_config.php | shim/logger require | Fatal on all PHP |
| `sqlsrv_render_query_error` | CALLS_BTWN, IMEI_SEARCH | Named error then continue |
| No retry/transaction | IR multi-section inserts | Partial IR records |
| `download_template` 410 | intentional | Dead feature |

---

# 23. CODE QUALITY

| Issue | Example | Impact |
| ----- | ------- | ------ |
| Mixed PHP/HTML/SQL | SUM.PHP, IR.PHP (~800+ lines dossier), JRMS_MAIN_PAGE1.PHP | Untestable |
| Copy-paste | JRMS `#TEMP` × ~15; SUM junk filter × ~8; PDACT `#temp`+113769 | Bugs don’t propagate |
| God files | `IR.PHP`, `admin_upload.php` | High defect density |
| Repeated sqlsrv_connect | every page | No pool |
| Magic numbers | 113769, 05:00, 140%, Cherlapalli | Undocumented policy |
| Naming | OTHERCDAT vs OTHERSCDAT; `*_PHP.PHP`; `demo.php.php`; `d%26n` | Broken actions |
| Globals | `$GLOBALS['__sqlsrv_connections']` in shim | Process-local OK; confusing |
| No abstraction | New page = copy old page | Insecure by default (forget audit_require) |
| Commented menus | HOME.html CALLS_TOT / CALLS_BT_NOS | Drift |
| Titles Untitled Document | HOME, HOME_IR, TOWER | Dreamweaver leftover |
| No PHPUnit | repo | Shim regressions |
| Python tests exist | `cdr_import/normalize/test_*.py` | Import side only |

---

# 24. DEAD CODE AUDIT

### Dead / unreachable (CONFIRMED FROM SOURCE)

chandu, untitled-1, notepad, desktop, login_page, jquerydynamic.php, sample.php→gif, demo.php.php, myindex (broken AJAX), css_sparkle1.php/style.php duplicates, Dreamweaver `_notes`.

Commented-out HOME items still have live handlers (CALLS_TOT, CALLS_BT_NOS) — **not dead**, just hidden.

RETRIEVE1 backdoor is **reachable**, not dead.

# 25. UNUSED CODE AUDIT

### Unused (see §3.3 / §8)

Dump directories, image_migrate/distributed_migrate UI-unused, jquery-ui development-bundle, qrcode samples, curfewepass vendor, Bootstrap/DataTables.

# 26. DUPLICATE / LEGACY CODE

### Duplicate / legacy current vs old

| Family | Current (linked) | Legacy copies | Why current |
| ------ | ---------------- | ------------- | ----------- |
| Login | LOGIN.PHP (bound SQL) | LOGIN1 (concat), LOGIN_PAGE demo | HOME → LOGIN.HTML → LOGIN.PHP |
| Summary | SUM.PHP / SUM_BTWN_DATES.PHP | SUM_HOME2/12/_P → missing PHP | HOME → SUM_HOME.html → SUM.PHP |
| Day/night | D&N_LOC.PHP (sql_safe + htmlspecialchars) | D&N_BT_DTS unsanitized; filename twins D%26N / DAY&NIGHTLOC | HOME encoded href + sql_safe version |
| IMEI | IMEI_SEARCH.PHP (session+h()) | IMEI_SEARCH_IN_PHONE no session; Hawkeye HOME_IMEI | HOME links first two |
| IR live | root HOME_IR + IR.PHP | `old ir/`, `new ir/` | no href to dump dirs |
| Tower live | root TOWER_HOME + *_TWR form target | `TWRDB/`, `ROUGH_TOWER/` | HOME → TOWER_HOME.HTML |
| JRMS live | JRMS_MAIN_PAGE1.PHP | *_MAHESH, *_OLD, *_PHP.PHP, JRMS.php | HOME → JRMS_MAIN_PAGE1.PHP |
| Case variants | lowercase `.php` after nginx rewrite | `.PHP` `.HTML` `.htm` symlinks | nginx rewrite + symlink script |

---

# 27. DEPENDENCY AUDIT

| Dependency | Declared | Used | Where | Version | Status | Risk |
| ---------- | -------- | ---- | ----- | ------- | ------ | ---- |
| PHP 8.3-fpm | nginx conf | YES | all php | 8.3 implied | ACTIVE | Need pdo_pgsql, curl, gd |
| pdo_pgsql | implied | YES | shim, logger | ext | ACTIVE | Critical missing=outage |
| curl | implied | YES | document client | ext | ACTIVE | Upload fails |
| gd | qrcode comments | YES | qr_img.php | ext | ACTIVE | QR fails |
| sqlsrv native | legacy calls | NO if prepend | — | — | NOT REQUIRED if shim | Required without prepend |
| exec/python3 | scripts | YES | preview, excel | OS | ACTIVE | disable_functions breaks preview |
| FastAPI | requirements.txt ≥0.115 | YES | :8088 | pinned min | ACTIVE | Must stay local |
| uvicorn | ≥0.32 | YES | service | | ACTIVE | |
| python-multipart | ≥0.0.12 | YES | uploads | | ACTIVE | |
| psycopg2-binary | ≥2.9.9 | YES | import+API | | ACTIVE | |
| pyodbc | ≥5.1.0 | SDR/MSSQL paths | sdr_import, image_migrate | | CONFIGURED | Needs ODBC |
| Spry MenuBar | SpryAssets | YES | ~130 pages | ~2006–09 | ACTIVE unmaintained | XSS history |
| jQuery UI | local tree | YES | datepickers | **1.10.4 (2014-01-29)** | ACTIVE | CVEs |
| jQuery | jquerydynamic / DROP DOWN FILTER | YES subset | 2.1.1 | ACTIVE | CVEs |
| w3.css | symlink | YES few pages | local | ACTIVE | Low |
| qrcode PHP | bundled | YES | GD | old | ACTIVE | Low |
| Font Awesome CDN | admin_upload | YES | 6.4.0 | ACTIVE | CDN |
| SheetJS CDN | admin_upload | YES | 0.18.5 | ACTIVE | CDN |
| PapaParse CDN | admin_upload | YES | 5.4.1 | ACTIVE | CDN |
| Bootstrap 3.1.1 | curfewepass | NO CDAT | 3.1.1 | UNUSED by CDAT | — |
| DataTables | curfewepass | NO CDAT | — | UNUSED | — |
| Composer PHP pkgs | **none** | n/a | — | — | No lockfile |
| npm root | **none** | n/a | — | — | — |
| simple-datatables | **not on main** | NO | — | — | (other branches only) |

---

# 28. CONFIGURATION AUDIT

### Repository configuration (CONFIRMED)

| File | Role |
| ---- | ---- |
| `cdat-web.nginx.conf` | :8020, root `/mnt/storage1/cdat-web`, php8.3-fpm, prepend shim, /document-api→:8088, 750G, 86400s |
| `.htaccess` | Apache `.PHP`→`.php` |
| `.env.example` | CDR_DB_* placeholders; **not auto-loaded by PHP** (comment in file) |
| `db_config.example.php` | getenv fallback 127.0.0.1:5432 postgres |
| `.gitignore` | db_config.php, .env, uploads/, vendor/, logs |
| `scripts/php-fpm-cdat.conf` | pool snippet |
| `scripts/systemd/*.service` | cdr-import-service + index/Citus oneshots |
| `cdr-import-service/docker-compose.yml` + Dockerfile | alternate API container |
| `scripts/cdat-health-check.sh`, `install-cdat-stability.sh` | ops |

# 29. DEPLOYMENT / INFRASTRUCTURE

### Production vs repo (MUST NOT conflate)

| Topic | In repo | Production |
| ----- | ------- | ---------- |
| Whether prepend is enabled | nginx snippet says yes | **NEEDS RUNTIME VERIFICATION** |
| Real db_config.php | absent | **NEEDS RUNTIME VERIFICATION** |
| Whether :8020 is LAN-only | not in conf (listen 8020 all interfaces implied) | **NEEDS RUNTIME VERIFICATION** firewall |
| systemd enabled | unit files present | **NEEDS RUNTIME VERIFICATION** `systemctl is-enabled` |
| Actual hostname/path | `/mnt/storage1/cdat-web` | may differ (this Mac workspace uses php -S + controller/ — **not main layout**) |

**Insecure defaults:** public HOME index; 750G body; plaintext passwords; no CSRF; FastAPI may be unauthenticated; php.ini cookie flags unknown.

---

# 30. PERFORMANCE (source-only; no benchmarks)

| Concern | Evidence | Impact |
| ------- | -------- | ------ |
| Full-table CDR scans | SUM/CALLS/IMEI on `cdatpcsuspect` without pagination | Slow; 120s statement_timeout abort |
| `set_time_limit(0)` | SUM.PHP | Worker hold |
| Repeated sqlsrv_connect per page | every handler | Connect overhead |
| N+1 style | some IR.PHP many sequential queries per IRKEY | Latency |
| `#TEMP` materialization | JRMS/PDACT/SUM | Extra write+read |
| `FOR XML PATH` / leading `%LIKE%` | COMMON_CNTS, IR_SEARCH, VEHICLE LIKE '%'+ | Index unfriendly |
| CELLID fallback contains-search | comment “hard-capped” / prefix only if long | Good intent; still can scan |
| 750G upload / 86400s timeouts | nginx | Starvation |
| Synchronous FastAPI waitForJob | document_processing_client | PHP blocked |
| No result LIMIT on most reports | SUM/ADDRESS/HABITUAL | Huge HTML |

Index SQL exists (`sql/cdatpcsuspect_*index.sql`) — **NEEDS RUNTIME VERIFICATION** applied.

---

# 31. RELIABILITY

| Scenario | Behavior | Gap |
| -------- | -------- | --- |
| Postgres down | die print_r / PDO exception | No friendly fallback |
| db_config missing | fatal require | Total outage |
| FastAPI down | upload errors; search continues | Partial degradation (good) |
| Shim translate miss | wrong/empty report or SQL error | Silent wrong police analysis |
| IR section insert fail | echo fail; other sections already committed | No transaction across IR forms |
| Preview exec fail | JSON error | Upload can still proceed via API |
| Duplicate upload | fingerprint SQL exists (`upload_content_fingerprint.sql`) | NEEDS RUNTIME VERIFICATION used |
| Retry | not in PHP search path; FastAPI jobs may retry — NEEDS RUNTIME VERIFICATION | |
| Multi-DB logical names → one postgres | collision / permission blast | Architecture risk |

---

# 32. APPLICATION FLOW (major modules)

### CDAT search (typical)

```text
User → HOME.html → e.g. SUM_HOME.html
  → POST PHONE_NO → SUM.PHP
  → (optional audit_log, usually NO session)
  → sqlsrv_connect CDATDUPL → T-SQL #TEMP/junk filter
  → sqlsrv_compat translate → Postgres
  → HTML table → User
```

### IR login + write

```text
User → LOGIN.HTML → LOGIN.PHP → LOGINS plaintext → session → HOME_IR.HTML
  → IRREPORT.html → POST → IRREPORT.PHP INSERT IR_PARTICULARS → refresh 30s
  → IMAGE_LIST.html → POST file → IMAGE_TABLE INSERT (no auth if URL known)
```

### Calls between dates (hardened)

```text
User → CALLS_BTWN_DATES.html → CALLS_BTWN_DATES.PHP
  → audit_require_session → sql_safe_* → CDATPCSUSPECT + enrichment → h() HTML
```

### JRMS unique key

```text
User → JRMS UI → JRMS_UNIQUE_KEY_UPDATE.html → POST CIN list + keys
  → UPDATE JRMS_TOTAL_2012_TO_2017 … APPLICATION_ENTRY → “Updated”
```

### Upload

```text
Uploader → admin_upload.php (role admin|poweruser)
  → preview: exec cdr_preview.py
  → submit: curl FastAPI :8088 → document_jobs
  → poll: admin_upload_job_status.php
  → verify/approve: admin_upload_verify.php / approve_staging
  → worker writes cdatpcsuspect / address tables
```

### Admin SQL

```text
Admin → ADMIN_SQL_CONSOLE.PHP → POST SELECT → wrap LIMIT 1000 → HTML or CSV/XLS
```

---

# 33. ACTIVE / UNUSED MASTER MATRIX

| Component | Type | Status | Evidence | Confidence |
| --------- | ---- | ------ | -------- | ---------- |
| HOME.html CDAT menu | page | ACTIVE | nginx index + hrefs | High |
| HOME_IR.HTML | page | ACTIVE | LOGIN redirect | High |
| LOGIN.PHP / LOGOUT.PHP | endpoint | ACTIVE | forms/hrefs | High |
| SUM.PHP + dated/ISD/new/in/out | feature | ACTIVE | HOME/SUM_HOME forms | High |
| SUM1/2/P | endpoint | BROKEN | form action, missing file | High |
| CALLS_BTWN_DATES.PHP | feature | ACTIVE | HOME + session gate | High |
| MOVEMENTS* | feature | ACTIVE | HOME | High |
| CALLS_BT_NOS / CALLS_TOT | feature | POTENTIALLY UNUSED | commented HOME; handlers exist | Medium |
| CDATCNTS1 / BULK / OTHERCDAT | feature | ACTIVE | HOME | High |
| IMEI_SEARCH / IMEI_SEARCH_IN_PHONE | feature | ACTIVE | HOME | High |
| HOME_IMEI / IMEI_REQUEST_* | feature | POTENTIALLY UNUSED | not on HOME | Medium |
| ADDRESS / BULK_ADDRESS | feature | ACTIVE | HOME | High |
| D&N_LOC / D&N_BT_DTS | feature | ACTIVE | HOME | High |
| CELLID / VEHICLE / COMMON / HABITUAL / MO | feature | ACTIVE | HOME | High |
| IR search + IR.PHP + IR inserts + IMAGE_LIST + RETRIEVE | feature | ACTIVE | HOME_IR / IR_MODULE | High |
| RETRIEVE1 backdoor | endpoint | LEGACY DANGEROUS | code present | High |
| JRMS_MAIN_PAGE1 / SEARCH / UNIQUE_KEY_UPDATE | feature | ACTIVE | HOME | High |
| Extra JRMS copies | files | POTENTIALLY UNUSED | not HOME entry | Medium |
| PDACT hub + name/MO/PS | feature | ACTIVE | HOME | High |
| Rowdy PS search | feature | ACTIVE | HOME | High |
| Tower hub + *_TWR | feature | ACTIVE | TOWER_HOME | High |
| TRAINING_MODULE1 | feature | ACTIVE | HOME | Medium (tables) |
| TRAINING_MODULE2 | feature | POTENTIALLY UNUSED | not HOME | Medium |
| admin_upload + FastAPI | feature/API | ACTIVE | HOME + curl | High if service up |
| download_template | endpoint | BROKEN | 410 | High |
| ADMIN SQL / activity / create user | feature | ACTIVE | HOME + gates | High |
| check_role.php | endpoint | PARTIAL | Spry cosmetic | High |
| sqlsrv_compat.php | middleware | ACTIVE | nginx prepend | High repo; runtime NEEDS VERIFICATION |
| db_config.php | config | MISSING IN GIT | gitignore | High |
| activity_logger.php | lib | ACTIVE | includes | High |
| cdr_enrichment_sql.php / sql_safe.php / dbcontroller.php | lib | ACTIVE | includes | High |
| Spry / jquery-ui datepicker / qrcode / css_sparkle1 / IMAGES chrome | lib/assets | ACTIVE | href/src | High |
| jquery-ui development-bundle | lib | POTENTIALLY UNUSED bulk | only js/css needed | High |
| curfewepass / old ir / new ir / TWRDB / ROUGH_TOWER / SUN | dump | CONFIRMED UNUSED by CDAT UI | no href | High; logs NEEDS VERIFICATION |
| image_migrate / distributed_migrate | service | CONFIGURED UNUSED by UI | no PHP call | High |
| cdr_import / document_processing / sdr_import / FastAPI | service | ACTIVE | PHP+nginx | High if systemd up |
| CDATPCSUSPECT / CDAT_DETAILS* / address / IR / JRMS / PDACT / rowdy / towers | tables | ACTIVE | SQL | High |
| Hawkeye / CAF / CIS / VBR / migrant / TRAINING_DB / CALLCENTER_NOS | tables | POTENTIALLY UNUSED or MISSING | PHP refs, no HOME | Medium |
| tbladmin (CPMS) | tables | UNUSED by CDAT | curfewepass only | High |
| Composer/npm | deps | ABSENT | ls-tree | High |
| Bootstrap/DataTables | deps | UNUSED by CDAT | curfewepass only | High |
| CDN FA/SheetJS/PapaParse | deps | ACTIVE upload UI | script tags | High |
| nginx :8020 / php-fpm / prepend | infra | CONFIGURED | conf file | High repo; prod NEEDS VERIFICATION |
| Demo junk PHP | files | DEAD | no menu | High |

---

# 34. COMPLETE ISSUE REGISTER

| ID | Category | Severity | File | Location | Finding | Impact | Recommendation | Confidence |
| -- | -------- | -------- | ---- | -------- | ------- | ------ | -------------- | ---------- |
| SEC-01 | Security | CRITICAL | HOME.html + most PHP | missing gates | Unauthenticated PII/CDR | Mass leak | Default-deny auth | High |
| SEC-02 | Security | CRITICAL | LOGIN.PHP, ADMIN_CREATE_USER | plaintext | Passwords stored/compared plaintext | Credential dump | Hash | High |
| SEC-03 | Security | CRITICAL | LOGIN1.PHP | string SQL login | SQLi on login | Auth bypass | Delete/bind | High |
| SEC-04 | Security | CRITICAL | SUM.PHP, IR.PHP, JRMS_UNIQUE_KEY_UPDATE, … | interpolated SQL | Widespread SQLi | Exfil/modify | Bind all | High |
| SEC-05 | Security | CRITICAL | RETRIEVE1.PHP | backdoor | Hardcoded FORMS/sa@*** | Takeover | Remove; rotate | High |
| SEC-06 | Security | HIGH | result pages | echo $row | XSS | Browser attack | htmlspecialchars | High |
| SEC-07 | Security | HIGH | all POST | no CSRF | Forged actions | CSRF | Tokens | High |
| SEC-08 | Security | HIGH | IMAGE_LIST.PHP | unauth INSERT | Unauth photo write | Data integrity | Auth+validate | High |
| SEC-09 | Security | HIGH | ADMIN_SQL_CONSOLE.PHP | weak filter + HOME link | Arbitrary SELECT | PII dump | Isolate | High |
| SEC-10 | Security | HIGH | many PHP | print_r sqlsrv_errors | Error leak | Recon | Generic errors | High |
| SEC-11 | Security | HIGH | nginx / admin_upload | 750G + document-api | DoS / API exposure | Outage/import | Cap+authn | High |
| SEC-21 | Security | HIGH | JRMS_UNIQUE_KEY_UPDATE, IRREPORT | unauth writes | Corrupt IR/jail | Authz | High |
| SEC-22 | Security | MEDIUM | VEHICLE_SEARCH_CRITERIA.PHP | column interpolation | SQLi | Allow-list | High |
| SEC-12–20 | Security | MED–INFO | (see §16) | session, secrets, exec, CDN, dumps | various | see §16 | High/Med |
| BUG-01–03 | Bug | HIGH | SUM_HOME12/2/_P | missing SUM1/2/P | 404 | Fix/unlink | High |
| BUG-04 | Bug | MEDIUM | db_config.php | missing | App won't start from git | Document deploy | High |
| BUG-07 | Bug | MEDIUM | download_template.php | 410 | Dead button | Remove UI | High |
| BUG-08 | Logic/DB | HIGH | JRMS/PDACT/SUM/COMMON | T-SQL vs PG shim | Wrong police results | Golden tests | Medium |
| BUG-10 | Logic | MEDIUM | LOGIN.PHP | redirect HOME_IR not HOME | UX confusion | Product decision | High |
| LOGIC-01 | Logic | HIGH | D&N_LOC.PHP | 05–07 both day+night | Wrong loc reports | Align windows | High |
| LOGIC-02 | Logic | HIGH | SUM vs SUM_BTWN | CDAT_DETAILS vs DETAILS1 | Inconsistent summaries | Confirm views | Medium |
| LOGIC-03 | Logic | MEDIUM | PDACT/ROWDY/BULK | IRKEY 113769 photo | Wrong face | Dummy check | Medium |
| LOGIC-04 | Logic | MEDIUM | IR_SEARCH.PHP | name len>4 | Missed persons | Confirm policy | High |
| LOGIC-05 | Logic | MEDIUM | JRMS_MAIN_PAGE1 | two jails only | Incomplete hub | Confirm policy | High |
| LOGIC-06 | Logic | MEDIUM | ADMIN_CREATE_USER vs LOGIN.PHP | logins vs LOGINS | Split-brain auth | Verify same table | Medium |
| ARC-01 | Architecture | HIGH | sqlsrv_compat vs $serverName | dual runtime | Silent wrong DB | Explicit runtime check | High |
| ARC-02 | Architecture | HIGH | no front controller | 212 files | Can't global-auth easily | Prepend gate | High |
| ARC-03 | Code Quality | HIGH | JRMS_* copies | copy-paste SQL | Inconsistent fixes | Shared helper | High |
| ARC-05 | Code Quality | MEDIUM | 15k file tree | case dups + dumps | Wrong file edited | Shrink docroot | High |
| PERF-01 | Performance | MEDIUM | unauth full scans + set_time_limit(0) | outage | Auth+limit+indexes | High |
| DEP-01 | Configuration | HIGH | db_config.php gitignored | missing on clone | Outage | Secure deploy path | High |
| DEP-03 | Deployment | MEDIUM | FastAPI optional | upload down | Health check | Medium |
| PRIV-01 | Privacy | CRITICAL | unauth CDR/Aadhaar/photos | compliance/leak | Auth+minimize logs | High |
| DOC-01 | Documentation | LOW | no README; Untitled Document | onboarding | Keep this audit | High |
| DEAD-01 | Dead Code | LOW | chandu/untitled-1/login_page | noise | Delete after logs | High |
| UNUSED-01 | Unused Code | MEDIUM | curfewepass/old ir/TWRDB in docroot | extra surface | Move out after logs | High |

---

# 35. RISK REGISTER

| Risk | Cause | Evidence | Impact | Likelihood | Severity | Recommendation | Dependency |
| ---- | ----- | -------- | ------ | ---------- | -------- | -------------- | ---------- |
| Mass CDR/PII leak | Unauthenticated HOME + SQLi | SEC-01/04 | Legal, operational, reputational | High if :8020 reachable beyond LAN | **CRITICAL** | Isolate network; default-deny auth | Firewall + prepend |
| Password/SA compromise | Plaintext LOGINS + RETRIEVE1 sa@*** | SEC-02/05 | Domain/DB takeover | Medium–High if git cloned widely | **CRITICAL** | Remove backdoor; hash; rotate | Secret scan |
| Wrong arrest/analysis | Shim vs T-SQL; day/night overlap; DETAILS vs DETAILS1; photo 113769 | BUG-08, LOGIC-01..03 | Investigative error | Medium on PG runtime | **HIGH** | Golden tests; investigator UAT | Runtime mode known |
| Unauth IR/jail mutation | IMAGE_LIST, IR INSERT, JRMS UPDATE | SEC-08/21 | Case file integrity | High if URL known | **HIGH** | Authz on writes | Session design for CDAT |
| Upload/API abuse | /document-api + 750G + weak API auth | SEC-11 | Disk fill, bad data load | Medium | **HIGH** | Bind localhost; token; size cap | systemd + nginx |
| Audit trail failure | Most searches unlogged + unauth | §21 | Cannot attribute queries | High today | **HIGH** | Log all searches after auth | activity_logger everywhere |
| Deploy/outage | missing db_config; dual engine; case symlinks | BUG-04, ARC-01, BUG-09 | Total down / 404 | Medium on new host | **HIGH** | Runbook + health check | ops |
| Dump-dir attack surface | extra apps in nginx root | SEC-19 | Extra vulns | Medium if exposed | **MEDIUM** | Move out of docroot | access logs |
| Outdated JS XSS | Spry + jQuery UI 2014 | SEC-17 | Analyst browser | Medium | **MEDIUM** | Replace after auth | UI rewrite |
| Aadhaar/CDR compliance | PII unauth + in audit JSON | §17 | Regulatory | Unknown (policy NEEDS VERIFICATION) | **HIGH** (if applicable) | Legal review + access control | org policy |

---

# 36. WHAT WE KNOW VS WHAT WE DON'T KNOW

### Confirmed from source

- `main` = single commit `dc47eca`; 15,698 files; flat PHP/HTML CDAT+IR app.
- nginx/Apache routing; no index.php; HOME.html is index.
- Login plaintext + LOGIN1 SQLi + RETRIEVE1 backdoor.
- Auth gates only on ~15 PHP pages; HOME public.
- sqlsrv_compat maps all DBs to postgres; db_config.php not in git.
- Form→PHP wiring for HOME/HOME_IR/TOWER/SUM_HOME; SUM1/2/P missing.
- Hardcoded investigation rules (junk filter, day/night hours, two jails, IRKEY 113769, operator map, name length>4, IMEI left-14).
- FastAPI client + nginx /document-api/; exec preview.
- Dump dirs unlinked from CDAT menus.
- No Composer; jQuery UI 1.10.4; Spry; no WhatsApp/SMS/payment APIs in root PHP.
- Unauth IMAGE_LIST insert and JRMS unique-key update.
- die(print_r(sqlsrv_errors)) widespread.

### Strongly inferred

- Production intended to use php-fpm + prepend shim (nginx file exists).
- `#TEMP`/`FOR XML`/`isnumeric` will misbehave on Postgres.
- Dump directories and unlinked PHP are unused in daily CDAT work.
- FastAPI is the live import path vs older cdr_import_worker.sh.
- `logins` vs `LOGINS` may be the same PG table after shim lowercase — or split-brain.

### Cannot be determined from repository

- Whether `:8020` is on the internet or police LAN.
- Whether prepend is actually enabled on the live server.
- Live table/row counts; whether CAF/Hawkeye/TWR tables exist.
- Whether RETRIEVE1 password still matches any live SA/app password.
- php.ini cookie flags and disable_functions.
- FastAPI authentication in production.
- Access-log usage of potentially unused pages.
- Legal retention/Aadhaar policy.
- Whether investigators consider 05–07 overlap a bug.

### Requires production verification (access needed)

| Unknown | Access required |
| ------- | --------------- |
| Shim vs native sqlsrv | Server: php-fpm pool `auto_prepend_file`; `phpinfo` |
| db_config values | Server file `db_config.php` / `.env` (do not copy into git) |
| Network exposure | Firewall / nginx listen / NAT |
| FastAPI up + auth | `systemctl status cdr-import-service`; curl :8088; API key env |
| Live schema | `psql` `\dt` `\d cdatpcsuspect` etc. |
| Dump-dir usage | nginx/php access logs |
| Cookie flags | php.ini / php-fpm-cdat.conf runtime |
| Index presence | `\di` vs sql/cdatpcsuspect_*index.sql |
| Citus FDW | `\dew` / query `dist.cdataddress` |
| User confirmation | Which HOME_IMEI / LOGIN1 / SUM_HOME2 paths still used |

---

# 37. FINAL ASSESSMENT

Scores are **explained**, not arbitrary. Scale 1–10 relative to a maintainable internal law-enforcement system.

| Area | Score /10 | Explanation |
| ---- | --------: | ----------- |
| **Application status** | 4 | Can serve CDAT/IR/JRMS/PDACT/tower search and import, but `main` is an insecure dual-runtime dump with broken SUM variants and missing db_config. |
| **Architecture** | 3 | Flat PHP+HTML+SQL; no router; 15k case-duplicate tree; clever but incomplete sqlsrv shim; FastAPI import is the only modern island. |
| **Security** | 2 | Unauthenticated PII, plaintext passwords, SQLi, XSS, CSRF, backdoor, unauth writes, error leaks. A few admin gates exist; **default is open**. |
| **Code quality** | 3 | Copy-paste families; god files; mixed concerns; a few newer pages (CALLS_BTWN, D&N_LOC, MOVEMENTS bound param, upload) are better. |
| **Database** | 4 | Real investigative schema + index/FDW scripts show ops maturity; PHP still speaks T-SQL; one mega-table; possible missing masters. |
| **API** | 6 | Local FastAPI is coherent (client+nginx+systemd). Not public SaaS. Auth unverified. image_migrate unused from UI. |
| **Dependencies** | 5 | Few runtime deps (good); ancient Spry/jQuery UI; Python stack reasonably pinned; no Composer. |
| **Maintainability** | 2 | Case variants, hardcoded hosts/rules, no tests, field-level coupling, undocumented live vs dump files. |
| **Documentation** | 3 | No product README on `main`; this audit set is the first baseline. Titles still “Untitled Document”. |
| **Overall risk** | **CRITICAL if network-exposed; HIGH even on LAN** | Unauthenticated investigative PII + writable IR/jail + SQL console + plaintext creds. |

**Do not give `main` a passing security grade.**

---

# 38. MANAGEMENT SUMMARY (END)

### WHAT THE APPLICATION IS
Hyderabad City Police **C-DAT** (Call Data Analysis Tool) plus **IR / JRMS / PDACT / rowdy / tower dump** — a legacy PHP crime-lab system.

### WHAT IT DOES
Search CDR/IMEI/address/vehicle/cell/offender/jail data via HTML forms; enter IR data and photos; upload operator CDR/SDR; admin SQL and user admin.

### WHAT IS CURRENTLY USED
HOME-linked CDAT reports, IR login+forms, JRMS/PDACT/rowdy/tower (as labelled), trainings module 1, upload+FastAPI (if running), admin tools.

### WHAT IS NOT USED / POTENTIALLY UNUSED
Standalone dumps (`curfewepass`, `old ir`, `new ir`, `TWRDB`, …), demos, CLI migrators, many extra JRMS copies, Hawkeye/CAF/migrant unless bookmarked.

### WHAT IS BROKEN
SUM1/SUM2/SUM_P handlers; template download 410; db_config not in git; demo AJAX; likely T-SQL-on-Postgres failures.

### TOP SECURITY RISKS
Unauthenticated PII; plaintext passwords; SQLi; RETRIEVE1 backdoor; unauth IR photo + JRMS update; SQL console; 750G API.

### TOP CODE QUALITY RISKS
15k duplicate tree; copy-paste SQL; dual MSSQL/Postgres runtime; no tests.

### TOP BUSINESS LOGIC RISKS
Day/night 05–07 overlap; CDAT_DETAILS vs DETAILS1; dummy photo 113769; IR name length; two-jail JRMS hub; possible logins split-brain.

### WHAT NEEDS PRODUCTION VERIFICATION
§36 list (prepend, exposure, FastAPI, `\dt`, access logs, secrets rotation).

### WHAT SHOULD BE DONE FIRST
Isolate network → kill backdoor/rotate → default-deny auth → gate writes/SQL console/API → hash passwords → golden-test SQL **without renaming investigative field names**.

---

# 39. RECOMMENDATION ROADMAP

**Do not implement in this audit.**

### PHASE 0 — Verification / Safety

| Action | Why | Affected | Risk if skipped | Priority | Dependencies | Benefit |
| ------ | --- | -------- | --------------- | -------- | ------------ | ------- |
| Backup git + live DBs + off-repo db_config | Recovery | all | Data loss | P0 | ops | Restore point |
| Confirm prepend vs native sqlsrv; :8020 ACL; FastAPI status | Know runtime | nginx/php-fpm/systemd | Wrong fixes | P0 | server access | Correct plan |
| Snapshot HOME menu behaviour (sample phones/IRKEYs) | Don’t break investigations | reports | Wrong “fixes” | P0 | test data | Baseline |
| Network isolate :8020 and /document-api | Stop unauth PII | nginx/firewall | Ongoing leak | P0 | network | Immediate risk cut |

### PHASE 1 — Critical Security

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| Remove RETRIEVE1 backdoor; rotate sa/FORMS-like secrets | SEC-05 | RETRIEVE1, infra creds | Medium lockout | P0 | secret inventory | Close known hole |
| Default-deny `audit_require_session` via prepend on all *.php | SEC-01 | every PHP | High UX change (CDAT currently open) | P0 | login UX for CDAT not only IR | Stop anonymous access |
| Gate IMAGE_LIST + JRMS_UNIQUE_KEY_UPDATE + IR INSERTs | SEC-08/21 | IR/JRMS writes | Low–Med workflow | P0 | session | Integrity |
| Isolate SQL console + document-api auth | SEC-09/11 | admin + upload | Med | P0 | admin workflow | Blast radius |
| Hash passwords; delete/parameterize LOGIN1 | SEC-02/03 | LOGINS, LOGIN1 | Med migration | P0 | dual-write | Credential safety |

### PHASE 2 — Critical Bugs / Broken Features

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| Fix or unlink SUM1/2/P HTML | 404 | SUM_HOME2/12/_P | Low | P1 | which variant used? | Fewer broken UIs |
| Replace die(print_r(sqlsrv_errors)) | SEC-10 | all reports | Low | P1 | logging | Less leak |
| Confirm IRKEY 113769 dummy; day/night windows; DETAILS vs DETAILS1 with investigators | LOGIC-01..03 | D&N, SUM, PDACT | High if silent change | P1 | UAT | Correct analysis |
| download_template UI | 410 | upload | Low | P2 | — | Less confusion |

### PHASE 3 — Stability

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| Golden tests: HOME reports × shim translate | BUG-08 | sqlsrv_compat + SUM/JRMS/CALLS | Med | P1 | sample data | Trustworthy PG |
| Health check db_config + FastAPI + prepend | DEP | ops scripts already exist | Low | P1 | systemd | Faster incident response |
| Reduce nginx body size; keep 120s statement_timeout | SEC-15 | nginx/php | Med if huge .bak required | P1 | SDR sizes | Availability |
| Bind FastAPI 127.0.0.1 + API key | SEC-11 | cdr-import-service | Med | P1 | upload UI | API safety |

### PHASE 4 — Dead / Unused Code Cleanup

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| After access-log proof: move dump dirs out of docroot | SEC-19 | curfewepass, old ir, TWRDB, … | Med if secretly used | P2 | logs | Smaller attack surface |
| Delete demo junk after log check | noise | chandu, untitled-1, login_page, … | Low | P3 | logs | Clarity |
| Collapse case duplicates to one extension | 15k→~2k | root tree | High if bookmarks use .PHP | P2 | nginx redirects | Maintainability |

### PHASE 5 — Architecture Improvement

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| Shared layout + one menu (**without** renaming POST/SQL fields) | Spry duplication | UI | Med | P2 | Phase 1 auth | UX |
| Central bound-query helpers for top 20 reports | SQLi/XSS | SUM/CALLS/IMEI/ADDRESS/IR | Med | P1 | sql_safe insufficient | Security+quality |
| Split audit DB vs CDR DB | blast radius | connections | Med | P2 | config | Console ≠ CDR |
| Replace exec preview with FastAPI-only | SEC-14 | upload | Low | P2 | API feature | Less RCE surface |

### PHASE 6 — Modernization

| Action | Why | Affected | Risk | Priority | Deps | Benefit |
| ------ | --- | -------- | ---- | -------- | ---- | ------- |
| Replace Spry + jQuery UI 1.10.4 | unmaintained XSS | frontend | Med | P3 | Phase 1+5 | Security/UX |
| CI: php -l + golden SQL tests | regressions | CI | Low | P2 | sample queries | Confidence |
| Retire MSSQL dialect only after parity | dual runtime | all SQL | High | P4 | Phase 3 tests | Long-term simplicity |

**Constraint for all phases:** do not rename investigative SQL/POST field names without investigator sign-off.

---

# AUDIT FOOTER

| Field | Value |
| ----- | ----- |
| **Audit Scope** | Entire `main` branch application: PHP/HTML/JS/CSS/SQL/Python/nginx/systemd/Docker/scripts/config/assets/dumps |
| **Branch** | `main` |
| **Commit** | `dc47ecaab9978cb6628d2909d196e23209d4a7271` (“Initial commit of C-DAT application.”) |
| **Audit Type** | Static forensic codebase / architecture / endpoint / database / API / authentication / authorization / security / business-logic / dependency / deployment-config / dead-code audit |
| **Files Analyzed** | Full tree listing (15,698); detailed read/grep of all operational root PHP (~212 unique basenames), nginx, sqlsrv_compat, activity_logger, upload/FastAPI stack, sql/*.sql, systemd, requirements; dump/vendor trees inventoried by reference tracing not byte-by-byte demo contents |
| **Endpoints Analyzed** | All unique root PHP handlers + nginx `/` + `/document-api/*` + QR + AJAX GET_* + FastAPI client methods + broken SUM1/2/P |
| **Features Analyzed** | All HOME/HOME_IR/IR_MODULE/TOWER/SUM_HOME/HOME_IMEI menu features + upload/admin + dump apps + demos |
| **Security Areas Analyzed** | SQLi, XSS, CSRF, authn/authz, session, uploads, exec, secrets, error leak, DoS, outdated libs, API exposure, privacy |
| **Database Areas Analyzed** | sqlsrv vs PDO pgsql shim, table CRUD matrix, T-SQL dialect risks, schema SQL files, FDW/Citus scripts, index scripts |
| **Confidence** | **Medium–High** for repository facts and insecure patterns; **Medium** for shim behavioural correctness; **Low–Medium** for production runtime topology |
| **Code Changes Made** | **NONE** |
| **Production Verification Required** | Yes — see §36 (server, php-fpm prepend, db_config, network ACL, systemd FastAPI, psql inventory, access logs, php.ini, secret rotation confirmation) |

*This report is suitable for Head / Management submission and as the engineering baseline for any future hardening or modernization. It must not be interpreted as authorization to change production without a separate approved change window.*
