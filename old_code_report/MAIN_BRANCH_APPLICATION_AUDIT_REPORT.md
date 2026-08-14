# C-DAT Main Branch — Current-State Application Report

**Audit Scope:** Current `main` branch (`dc47eca` — “Initial commit of C-DAT application.”)  
**Audit Type:** Static Codebase / Architecture / Dependency / Endpoint / Security Audit  
**Working tree at audit time:** `v2-bug-fixes` (not analysed; reports describe **git `main` only**)  
**Code Changes Made:** None (documentation files only)  
**Method:** `git ls-tree`, `git show`, `git grep` on `main`. No runtime exploitation. No live database queries.  
**Confidence:** **Medium–High** — see § Confidence at the end.

---

## 20. Executive Summary

### What is this application?

**C-DAT (Call Data Analysis Tool)** for **Hyderabad City Police**. The live UI titles pages “Untitled Document”, but chrome, email (`crimelab@hyd.tspolice.gov.in`), nginx comment, and `ADMIN_SQL_CONSOLE.PHP` header identify it as the Hyderabad City Police crime-lab CDR / interrogation-report system.

It is a **legacy PHP web app** (flat files at repo root, Spry menus, jQuery UI 1.10.4 datepickers) that queries **call detail records (CDR)**, **subscriber addresses**, **IMEI**, **interrogation reports (IR)**, **jail records (JRMS)**, **PDACT**, **rowdy sheeters**, **tower dumps**, and related police datasets.

A second layer added on the same tree: **PostgreSQL + `sqlsrv_compat.php` shim**, **activity logging**, **admin SQL console**, and a **Python FastAPI document-import service** (`cdr-import-service` on `:8088`) for CDR/SDR uploads.

### What does it currently do?

| User-facing capability | Evidence |
| ---------------------- | -------- |
| Browse CDAT report menus without login | nginx `index HOME.html`; `HOME.html` is static HTML |
| Search CDR summaries, movements, IMEI, address, vehicle, cell ID, day/night location | `HOME.html` → `*.html` forms → `*.PHP` + `sqlsrv_*` |
| IR login + IR form CRUD + image upload | `LOGIN.HTML` → `LOGIN.PHP` → `HOME_IR.HTML` |
| JRMS / PDACT / rowdy sheeter search | sidebar links on `HOME.html` |
| Tower dump reports (labelled “Under Development”) | `TOWER_HOME.HTML` |
| CDR/SDR data upload + job polling | `admin_upload.php` → curl `http://127.0.0.1:8088` |
| Admin: create user, activity log, ad-hoc SQL | `ADMIN_*.PHP` + `audit_require_admin()` |

### What is actually being used?

**Confirmed active (code + menu / form / nginx wiring):**

- Root CDAT/IR/JRMS/PDACT/IMEI/Address/Summary/Call Details pages linked from `HOME.html` / `HOME_IR.HTML` / `IR_MODULE.HTML` / `TOWER_HOME.HTML` / `HOME_IMEI.html` / `SUM_HOME.html`
- `sqlsrv_compat.php` (nginx `auto_prepend_file`)
- `activity_logger.php` + Postgres `user_sessions` / `user_activity_logs` (login + a few hardened pages)
- `admin_upload.php` + `document_processing_client.php` + `cdr-import-service` FastAPI (`/document-api/` nginx proxy)
- Local `SpryAssets/`, `jquery-ui-1.10.4.custom/`, `qrcode/php/qr_img.php`, `css_sparkle1.css`, `DROP DOWN FILTER/` (subset)
- Python `cdr_import/`, `document_processing/`, `sdr_import/` (behind FastAPI / `exec` preview)

### What appears unused?

**Standalone dumps with no root-menu `href`/`include`:** `curfewepass/`, `old ir/`, `new ir/`, `TWRDB/`, `ROUGH_TOWER/`, `SUN/`, `_notes/`.

**Vendor/demo trees mostly unused except a few includes:** bulk of `jquery-ui-1.10.4.custom/development-bundle/`, `qrcode/` sample images, `dynamicdependentbox/` (only `jquerydynamic.js` used from migrant labour page).

**Orphan / demo PHP:** `chandu.php` (symlink to `CHANDU.htm`), `untitled-1.php`, `notepad.php`, `desktop.php`, `login_page.php` (modal demo), `myindex.php` / `dump_analysis.php` (country/state demo; `get_state.php` missing at root), `jquerydynamic.php` (misnamed jQuery 2.1.1), `sample.php` → `sample.gif`.

**CLI-only (not called from PHP UI):** `image_migrate/`, `distributed_migrate/`, most `scripts/systemd/*index*` units.

### What is broken?

| Item | Evidence |
| ---- | -------- |
| `SUM_HOME_P.html` → `SUM_P.PHP` | Form action exists; **no** `sum_p.php` on `main` |
| `SUM_HOME2.html` → `SUM2.php` | **Missing** handler |
| `SUM_HOME12.html` → `SUM1.php` | **Missing** handler |
| `download_template.php` | Always **HTTP 410** |
| `db_config.php` | **Not committed** (`.gitignore`); `DB_CONFIG.PHP` symlink target missing in git |
| `myindex.php` AJAX `get_state.php` | File not at repo root |
| `demo.php.php` expects `ddtf.js` at root | Only under `DROP DOWN FILTER/` |
| MSSQL `#TEMP` / `CONVERT(VARCHAR…)` / `WITH (NOLOCK)` | Relies on incomplete `sqlsrv_compat` translation; **runtime failure risk on Postgres** |
| `client_max_body_size 750G` + 86400s timeouts | Availability / DoS risk if exposed |

### What are the biggest risks?

1. **Almost all report endpoints are unauthenticated.** Only ~15 PHP pages call `audit_require_*`. `HOME.html` is public. CDR/IR/PII searchable by URL+POST.
2. **Plaintext passwords** in `LOGINS`; `LOGIN1.PHP` concatenates username/password into SQL.
3. **Hardcoded backdoor** in `RETRIEVE1.PHP` (`USERNAME == "FORMS"` and a hardcoded `sa@…` password).
4. **Widespread SQL injection** (request values interpolated into `$sql`).
5. **XSS** on almost every result table (`echo $row[...]` without encoding).
6. **Unauthenticated IR image upload** (`IMAGE_LIST.PHP`) into SQL.
7. **Admin SQL console** (admin-gated, but weak keyword filter; linked from public `HOME.html`).
8. **Dual-engine architecture** (MSSQL dialect in PHP + Postgres shim + gitignored credentials) — deploy/ops fragility.

Business rules for each major workflow (inputs, validation, SQL conditions, writes, fallbacks) are in **§22 Business & Application Logic Audit**.

### What should be done next?

See §21 Roadmap. Immediate: isolate the app from the public internet; rotate any credentials that appeared in source; do not treat `main` as a safe production baseline without an auth gate in front of every PHP handler; snapshot DB + `db_config.php` off-repo; prefer hardening on a branch rather than rewriting SQL field names.

---

## 1. Repository Discovery

### 1.1 Scale

| Metric | Value on `main` |
| ------ | --------------- |
| Git commit | `dc47eca` (sole commit on `main` / `origin/main`) |
| Tree entries | **15,698** |
| Root unique `*.php` (case-insensitive) | **~212** |
| Root actual `*.php` files (case + symlink variants) | **~771** |
| Dominant duplicate pattern | Every page as `.php` / `.PHP` / `.html` / `.HTML` / `.htm` / `.HTM` (many **git symlinks**) |
| No `composer.json`, no root `package.json`, no `index.php` | Confirmed |

### 1.2 Top-level layout

```text
/                          Flat PHP/HTML CDAT + IR application (primary)
cdat-web.nginx.conf        Production-style nginx for :8020
.sql / sql/                Postgres schemas, indexes, FDW, upload pipeline
.sqlsrv_compat.php         MSSQL API → PDO pgsql shim (auto_prepend)
activity_logger.php        Session + audit helpers
db_config.example.php      Postgres DSN template (real db_config.php not in git)
.env.example               CDR_DB_* placeholders
admin_upload*.php          Upload UI + job JSON
cdr_import/                Python CDR parsers (Airtel/BSNL/Jio/Vi)
cdr-import-service/        FastAPI Document Processing Service
document_processing/       Job orchestrator used by FastAPI
sdr_import/                MSSQL .bak → Postgres address migration
image_migrate/             MSSQL images → Postgres (CLI only)
distributed_migrate/       Citus / distributed_db migrators (CLI/systemd)
scripts/                   systemd units, index builds, health check
SpryAssets/                Adobe Spry MenuBar (primary chrome)
jquery-ui-1.10.4.custom/   Datepickers (2014) + huge demo tree
DROP DOWN FILTER/          ddtf.js + w3.css
qrcode/                    PHP GD QR generator (used)
IMAGES/                    JPG chrome (TOPBORDER, ANALYSIS1, …)
js/sdr_resumable_upload.js Used by admin_upload.php
curfewepass/               Standalone CPMS dump (not in CDAT menu)
old ir/  new ir/  TWRDB/ ROUGH_TOWER/ SUN/  Standalone copies
dynamicdependentbox/       Country/state demo dump
```

### 1.3 Architecture map (actual)

```text
Browser
  ↓
nginx :8020  (root /mnt/storage1/cdat-web)
  ├─ /                    → HOME.html   (static, no auth)
  ├─ *.html / *.HTML      → static forms (Spry + jquery-ui)
  ├─ *.PHP rewrite        → *.php
  ├─ *.php                → php8.3-fpm
  │                         auto_prepend sqlsrv_compat.php
  │                         ↓
  │                       Page logic (sqlsrv_* calls)
  │                         ↓
  │                       PDO pgsql  ← db_config.php (not in git)
  │                         ↓
  │                       Database "postgres" (legacy MSSQL names mapped)
  ├─ /document-api/       → proxy 127.0.0.1:8088  (FastAPI)
  └─ /LOGIN.HTML          → POST LOGIN.PHP → session → HOME_IR.HTML
```

**Entry points → Pages → PHP → Database → External APIs → Output**

| Stage | What exists |
| ----- | ----------- |
| Entry | nginx `index HOME.html`; Apache `.htaccess` only maps `.PHP`→`.php` |
| Pages | Static `*.html` search forms + a few PHP-rendered result pages |
| PHP logic | Per-page scripts; almost no MVC / shared controller |
| Database | Intended MSSQL named instance `CPHYDERABAD1\DAU_HYD_2023`; **runtime on this tree is Postgres via shim** |
| External APIs | Only local FastAPI `:8088` (+ optional `exec` python preview). No WhatsApp/SMS/payment/maps APIs found in root app |
| Output | HTML tables (`bgcolor=#921215`), some CSV/XLS export (admin SQL console), QR images, base64 IR photos |

---

## 2. Application Identity

| Item | Finding | Status |
| ---- | ------- | ------ |
| Name | C-DAT / Call Data Analysis Tool; IR Forms; “Hyderabad City Police” in admin SQL console comment | Confirmed |
| Purpose | Analyse operator CDR (phone/IMEI/tower/address) and manage interrogation / jail / PDACT / rowdy records for investigations | Confirmed from menus + SQL |
| Business problem | Search millions of call records and offender IR data quickly for Hyderabad police crime lab | Confirmed (email `crimelab@hyd.tspolice.gov.in` on `TOWER_HOME.HTML`) |
| Intended users | Police analysts / crime-lab operators; roles `admin`, `poweruser`/`uploader`, `user` in `LOGINS.ROLE` | Confirmed |
| Data handled | Phone numbers, IMEI, CDR timestamps/duration/cell IDs, subscriber addresses, IR biographies/photos, jail CIN/Aadhaar, vehicle RTA, CAF, migrant labour, lost-IMEI Hawkeye reports | Confirmed |
| Formal product name / version / owner org chart | Not in source | **Needs Verification** |
| Whether `main` is what production currently runs vs a later branch | `main` is the initial dump; other branches exist (`dev`, `v2-bug-fixes`) | **Needs Verification** (ops) |

### 2.1 Main workflows (confirmed)

1. **Open HOME → pick report → fill HTML form → POST PHP → HTML table**
2. **IR login → HOME_IR → fill IR section forms → INSERT/SELECT FORMS tables**
3. **Uploader login → admin_upload → preview (`exec` python) → queue FastAPI job → poll JSON status → optional verify/approve**
4. **Admin → SQL console / create user / activity log**

### 2.2 Major modules

Summary, Call Details / Movements, CDAT contacts, IMEI, Address, Day & Night location, Offenders / Habitual / MO, IR search + IR forms, JRMS, PDACT, Rowdy Sheeter, Tower dump, Trainings, Data upload, Admin.

---

## 3. Complete Feature Inventory

Status key: **ACTIVE** = linked from a live menu or form and handler exists. **PARTIALLY ACTIVE** = menu or handler incomplete. **BROKEN** = referenced but missing/always failing. **UNUSED** = no root-menu/include path. **DEAD CODE** = junk/demo. **UNKNOWN** = cannot confirm from static analysis.

| Feature | Description | Main Files | Dependencies | Status | Evidence |
| ------- | ----------- | ---------- | ------------ | ------ | -------- |
| CDAT home menu | Static Spry menu of all CDAT reports | `HOME.html` | SpryAssets, css_sparkle1.css, IMAGES/TOPBORDER | ACTIVE | nginx `index HOME.html`; file content |
| IR login | Username/password vs `LOGINS` | `LOGIN.HTML`, `LOGIN.PHP` | `activity_logger.php`, sqlsrv, FORMS | ACTIVE | Form `action="LOGIN.PHP"`; parameterized query |
| IR login (legacy copy) | Same flow, concatenated SQL | `LOGIN1.PHP`, `LOGIN1.HTML` | same | PARTIALLY ACTIVE | Weaker copy; still redirects to `HOME_IR.HTML` |
| Logout | Destroy session, audit | `LOGOUT.PHP`, `logout.php` | activity_logger | ACTIVE | `HOME.html` / `HOME_IR.HTML` href |
| Summary Total | Phone summary from `CDAT_DETAILS` / suspect | `SUM_HOME.html` → `SUM.PHP` | CDATDUPL, enrichment SQL | ACTIVE | Form action + handler |
| Summary between dates | Date-bounded summary | `SUM_BETWEEN_DATES.html` → `SUM_BTWN_DATES.PHP` | CDATDUPL | ACTIVE | Form action |
| Summary ISD / new / in-state / out-state | Variant summaries | `SUM_ISD_CNTS.*`, `SUM_NEW_NOS.html`→`SUM_NEW_NO.PHP`, `SUM_IN_STATE.*`, `SUM_OUT_STATE.*` | CDATDUPL | ACTIVE | `HOME.html` menu |
| Summary variants SUM1/2/P | Alternate home pages | `SUM_HOME12.html`, `SUM_HOME2.html`, `SUM_HOME_P.html` | missing PHP | **BROKEN** | Form actions `SUM1.php` / `SUM2.php` / `SUM_P.PHP`; no files |
| Call details between dates | CDR rows | `CALLS_BTWN_DATES.html` → `CALLS_BTWN_DATES.PHP` | sql_safe, audit_require_session | ACTIVE | Menu + session gate |
| Call details total | Commented out in HOME | `CALLS_TOT.html` | — | UNUSED (menu commented) | `HOME.html` HTML comment |
| Calls between two numbers | Commented in HOME; form exists | `CALLS_BT_NOS.html` → `CALLS_BT_NOS.PHP` | CDATDUPL | PARTIALLY ACTIVE | Not in live menu; handler exists; **no auth** |
| Movements | Location/movement report | `MOVEMENTS.html` → `MOVEMENTS.PHP` | CDATDUPL | ACTIVE | Menu |
| Movements two numbers / comparison | Pair analysis | `MOVEMENTS_BETWEEN_TWO_NUMBERS*.html/.PHP` | CDATDUPL | ACTIVE | Menu |
| CDAT contacts / bulk / others | Contact graphs | `CDATCNTS.html`→`CDATCNTS1.php`, `BULK_CDAT_CONTACTS.*`, `OTHERSCDAT.html`→`OTHERCDAT.php` | CDATDUPL | ACTIVE | Menu + form actions |
| IMEI ↔ phone | Two directions | `IMEISEARCH.html`→`IMEI_SEARCH.PHP`, `IMEISINPHONE.html`→`IMEI_SEARCH_IN_PHONE.PHP` | sql_safe; IMEI_SEARCH has session | ACTIVE | Menu |
| IMEI Hawkeye / lost report | Request status/sum/traced/movements/maxspent | `HOME_IMEI.html`, `IMEI_REQUEST_*.PHP`, `MAXSPENTLOCATION_IMEI.PHP`, `D&N_LOC_IMEI.php` | LOSTREPORT_HAWKEYE | ACTIVE (if HOME_IMEI reached) | `HOME_IMEI.html` hrefs; **not on HOME.html** — **Needs Verification** how users open it |
| Address single / bulk | Subscriber address + QR | `ADDRESS.HTML`→`ADDRESS.PHP`, `BULKADDRESS.HTML`→`BULK_ADDRESS.php` | qrcode, w3.css/ddtf, CDATADDRESS | ACTIVE | Menu |
| Day/night location | Top locations | `DAY%26NIGHTLOC.HTML` → `D&N_LOC.PHP` / encoded `d%26n_*.php` | sql_safe on D&N_LOC | ACTIVE | Menu + triple filenames |
| Day/night between dates | Dated variant | `DAY%26NIGHTLOC_BTWN_DATES.HTML` → `D&N_BT_DTS.PHP` | `#TEMP` MSSQL | ACTIVE / PG risk | Menu |
| Habitual offenders | List | `HABITUAL.PHP` | IRFORMS/FORMS images | ACTIVE | `HOME.html` direct PHP link |
| Cell ID search | Tower lookup | `CELLID_SEARCH.html`→`CELLID_SEARCH.php` | qrcode, sql_safe, CDATCELLTOWERAREANEW | ACTIVE | Menu |
| Vehicle search | Number / chas / eng / criteria | `VEHICLE_SEARCH*.html/.PHP` | CDAT_RTA, qrcode | ACTIVE | Menu |
| Common contacts | Intersection | `COMMON_CNTS.HTML`→`COMMON_CNTS.PHP` | CDATDUPL | ACTIVE | Menu |
| IR forms home | Post-login IR menu | `HOME_IR.HTML` | static | ACTIVE | LOGIN redirect |
| IR report sections | Brief facts, family, offence, disposal, local contacts, associates, mulakath, retrieve, images | `*.HTML` → matching `*.PHP` | FORMS / IRFORMS INSERT+SELECT | ACTIVE | `HOME_IR.HTML` hrefs |
| IR search by name / head / gender / test | Search IR | `IR_SEARCH*.html/.PHP`, `IR_MODULE.HTML` | CDATDUPL / IRFORMS | ACTIVE | HOME + IR_MODULE |
| IR full page `IR.PHP` | Large GET `IRKEY` dossier | `IR.PHP` | many FORMS tables | ACTIVE | Form/link `IR.PHP?IRKEY=` |
| JRMS | Jail record search/update uniqueness | `JRMS_MAIN_PAGE1.PHP`, many `JRMS_*` | JRMS_TOTAL_2012_TO_2017 | ACTIVE | HOME link; `#TEMP` T-SQL |
| PDACT | Preventive detention search | `PDACT_MAIN_PAGE_SEARCH.PHP`, `PDACT_*` | PDACT_MAIN_TABLE | ACTIVE | HOME link |
| Rowdy sheeter by PS | PS-wise search | `ROWDYSHEETER_PS_WISE_SEARCH.PHP` → `_PHP.PHP` | ROWDY_SHEETER_DATA1 | ACTIVE | HOME link |
| Tower dump reports | Suspect / other-state / inter-tower / previous offenders | `TOWER_HOME.HTML` → `SUSPECT_SEARCH.PHP` etc. + `*_TWR.PHP` | TWRMDB | ACTIVE (labelled under development) | Menu + handlers exist |
| Trainings | Training DB + QR | `TRAINING_MODULE1.HTML/.PHP`, `TRAINING_MODULE2.*` | TRAINING_DB | ACTIVE (module1 in menu; module2 **Needs Verification**) | HOME → module1 only |
| Data upload | CDR/SDR upload UI | `admin_upload.php` + history/status/verify/sync | FastAPI :8088, exec python, audit_require_uploader | ACTIVE | HOME link + curl client |
| User activity log | Admin audit UI | `ADMIN_ACTIVITY_LOG.PHP` | Postgres user_activity_logs | ACTIVE | HOME + `audit_require_admin` |
| SQL query console | Ad-hoc SELECT + CSV/XLS | `ADMIN_SQL_CONSOLE.PHP` | audit_db PDO | ACTIVE | HOME + admin gate |
| Create user | Insert plaintext login | `ADMIN_CREATE_USER.PHP` | Postgres `logins` | ACTIVE (IR home commented + file) | `audit_require_admin` |
| Role JSON | `{is_admin, role}` | `check_role.php` | session | PARTIALLY ACTIVE | Spry JS may call; **no login required** (returns false) |
| Template download | Always 410 | `download_template.php` | uploader gate | **BROKEN** / obsolete | Explicit 410 body |
| CAF search | CAF records | `CAF_SEARCH.PHP` | CAFs DB, ftp:// internal URL | UNKNOWN / not on HOME | Handler exists |
| Migrant labours | Forms + reports | `MIGRANT_LABOURS_*.PHP` | MIGRANT_LABOUR_TABLE, dynamicdependentbox JS | UNKNOWN / not on HOME | Handlers exist |
| CIS name search | CIS_COMPLETE_DATA | `CIS_DATA_NAME_SEARCH*.PHP` | CDATDUPL | UNKNOWN / not on HOME | |
| VBR / ILD | `VBR_SEARCH.PHP` | ALL_ILD_DATA_2012 + `#TEMP` | UNKNOWN / not on HOME | |
| NBWS | `NBWS.PHP` | NBWS_VERIFY_DATA_IMPORTANT | UNKNOWN | |
| All-data search | `ALLDATA*.PHP` | — | UNKNOWN | |
| Name search | `NAMESEARCH.PHP`, `NAME_SEARCH.PHP` | CDATDUPL | UNKNOWN / not on HOME | |
| Wanted | `wanted1.html/.PHP` | linked from SUM_HOME not HOME | PARTIALLY ACTIVE | |
| FP list | `FP_LIST.PHP` | IR_MODULE menu | ACTIVE | |
| Analysis abstract | INSERT analysis notes | `ANALYSIS_ABSTRACT.PHP` | ANALYSIS_ABSTRACT table | UNKNOWN | |
| RTA Nike | `rta_nike.php` | sql_safe | UNKNOWN | |
| Nearest / nearby cell towers | Geo functions | `NEAREST_CELLIDS.PHP`, `NEAR_BY_CELLTOWERIDS.PHP` | celltower_geo.sql, session | PARTIALLY ACTIVE | Session gated; not on HOME.html |
| Curfew e-pass (CPMS) | Separate Bootstrap app | `curfewepass/` | tbladmin, Bootstrap 3.1.1 | UNUSED (from CDAT) | No root href |
| Image migrate / Citus migrate | Ops pipelines | `image_migrate/`, `distributed_migrate/` | MSSQL + postgres/distributed_db | CONFIGURED, not UI | No PHP curl |
| Dummy/demo pages | chandu, untitled-1, notepad, desktop, login_page | various | — | DEAD CODE | |

---

## 4. Complete Endpoint / Route Audit

See companion file **`MAIN_BRANCH_ENDPOINT_INVENTORY.md`** for the full table (~212 PHP handlers + nginx + FastAPI).

### 4.1 Routing mechanisms

| Mechanism | File | Behavior |
| --------- | ---- | -------- |
| nginx | `cdat-web.nginx.conf` | `:8020`, index `HOME.html`, `*.PHP`→`*.php`, FastCGI php8.3-fpm, `auto_prepend sqlsrv_compat.php`, `/document-api/` → `:8088`, `client_max_body_size 750G` |
| Apache | `.htaccess` | Only rewrite missing `.PHP` to `.php` |
| PHP built-in server | Not configured on `main` | `.htaccess` ignored; **Needs Verification** if ops uses `php -S` |
| Front controller | **None** | |

### 4.2 Auth required — summary

| Gate | Pages |
| ---- | ----- |
| `audit_require_admin()` | `ADMIN_SQL_CONSOLE.PHP`, `ADMIN_CREATE_USER.PHP`, `ADMIN_ACTIVITY_LOG.PHP` |
| `audit_require_uploader()` | `admin_upload.php`, `_history`, `_job_status`, `_sync_jobs`, `_verify`, `download_template.php` |
| `audit_require_session()` | `CALLDETAILS.PHP`, `CALLS_BTWN_DATES.PHP`, `IMEI_SEARCH.PHP`, `NEAREST_CELLIDS.PHP`, `NEAR_BY_CELLTOWERIDS.PHP` |
| **None** | All other ~190 root PHP pages + all static HTML |

### 4.3 Referenced but missing

| Endpoint | Called from | Status |
| -------- | ----------- | ------ |
| `SUM1.php` | `SUM_HOME12.html` form | Missing |
| `SUM2.php` | `SUM_HOME2.html` form | Missing |
| `SUM_P.PHP` | `SUM_HOME_P.html` form | Missing |
| `get_state.php` (root) | `myindex.php` AJAX | Missing (exists under `dynamicdependentbox/` / `TWRDB/`) |
| `ddtf.js` (root) | `demo.php.php` | Missing (exists under `DROP DOWN FILTER/`) |
| `db_config.php` | sqlsrv_compat, activity_logger, Python | Not in git |

### 4.4 Duplicate / legacy naming

- `LOGIN` vs `LOGIN1` vs `LOGIN_PAGE`
- `D&N_*` vs `D%26N_*` vs `DAY&NIGHTLOC*`
- `JRMS_*_MAHESH*`, `*_OLD`, `*_PHP.PHP`, `*1.php`
- `dbcontroller.php.php`, `demo.php.php`
- HTML form vs PHP handler vs PHP-symlink-to-HTML (menus)

---

## 5. File-by-File Audit (important files)

### 5.1 Active core

| Path | Purpose | Functions/classes | Inputs | Outputs | DB | Includes / callers | Auth | Status | Problems |
| ---- | ------- | ----------------- | ------ | ------- | -- | ------------------ | ---- | ------ | -------- |
| `LOGIN.PHP` | IR/CDAT login | (script) | POST USERNAME, PASSWORD | redirect `HOME_IR.HTML` or error HTML | FORMS.`LOGINS` parameterized | `activity_logger.php`; from `LOGIN.HTML` | Sets session | ACTIVE | Plaintext password compare; no CSRF; no rate limit; `die(print_r(sqlsrv_errors))` |
| `LOGIN1.PHP` | Login copy | (script) | same | same | string SQL | LOGIN1.HTML, RETRIEVE1 backdoor | Sets session | LEGACY | **SQLi on login** |
| `LOGOUT.PHP` | Logout | (script) | session | redirect LOGIN.HTML | user_sessions UPDATE | HOME / HOME_IR | session | ACTIVE | — |
| `activity_logger.php` | Audit + auth helpers | `audit_db`, `audit_login/logout/log`, `audit_require_session/admin/uploader`, `_detect_device` | session, PDO | inserts/updates | Postgres via `db_config.php` | required by login/admin/upload + some reports | defines gates | ACTIVE | ERRMODE_SILENT on audit PDO; no idle timeout; role only in session |
| `sqlsrv_compat.php` | sqlsrv_* → PDO pgsql | `sqlsrv_connect/query/fetch_*`, `__sqlsrv_translate`, `__sqlsrv_dbname` | connectionInfo Database name | PDO statements | all legacy DBs → `postgres` | nginx auto_prepend | n/a | ACTIVE | Incomplete T-SQL translation; timeouts 120s; requires missing `db_config.php` |
| `db_config.example.php` | DSN template | returns array | getenv CDR_DB_* | config array | — | copy target `db_config.php` | n/a | TEMPLATE | Real file gitignored |
| `HOME.html` | Main menu | none (static) | none | HTML | none | nginx index | **none** | ACTIVE | Public; links SQL console & upload |
| `HOME_IR.HTML` | IR menu | static | none | HTML | none | LOGIN redirect | **none** (page itself) | ACTIVE | Reachable without login if URL known |
| `SUM.PHP` | Summary total | script | POST PHONE_NO | HTML table | CDAT_DETAILS, #TT/#RESULT | SUM_HOME.html; activity_logger; cdr_enrichment_sql | **logs only, no require_session** | ACTIVE | SQLi `$number`; MSSQL dialect |
| `CALLS_BTWN_DATES.PHP` | Calls in range | script | POST phone + dates | HTML | CDATPCSUSPECT | form; sql_safe; audit_require_session | session | ACTIVE | Still concatenated SQL after digit filter |
| `ADDRESS.PHP` | Address + QR | script | POST PHONE_NO | HTML + qrcode img | CDATADDRESS / suspect | ADDRESS.HTML | none | ACTIVE | SQLi; XSS; `die(print_r)` |
| `IR.PHP` | IR dossier | script | GET IRKEY | huge HTML | IR_PARTICULARS + many | IR search links | none | ACTIVE | SQLi via GET IRKEY; IP `10.10.x.x` serverName in some IR files |
| `IMAGE_LIST.PHP` | Upload IR photo into SQL | script | FILES image, POST IRKEY | HTML | INSERT IMAGE_TABLE | HOME_IR | **none** | ACTIVE | Unauth upload; addslashes tmp_name; SQLi |
| `JRMS_UNIQUE_KEY_UPDATE.PHP` | Write UNIQUE_KEY/IRKEY | script | POST keys / CIN list | HTML | UPDATE JRMS_TOTAL_2012_TO_2017 | JRMS forms | none | ACTIVE | IN-list SQLi; unauthenticated write |
| `ADMIN_SQL_CONSOLE.PHP` | Ad-hoc SQL | script | POST sql_query, export | HTML/CSV/XLS | arbitrary SELECT via PDO | HOME href | admin | ACTIVE | Weak keyword filter; subquery wrap LIMIT 1000 |
| `ADMIN_CREATE_USER.PHP` | Create login | script | POST user fields | HTML | INSERT logins plaintext | HOME_IR (commented) | admin | ACTIVE | Plaintext store; CSRF |
| `admin_upload.php` | CDR/SDR UI | large script | FILES, POST | HTML + curl API | document_jobs via API | HOME; document_processing_client; exec preview | uploader | ACTIVE | `exec` python; 750G body; CDN SheetJS/PapaParse |
| `document_processing_client.php` | HTTP client | class + `request()` | file paths | JSON | — | admin_upload* | via caller | ACTIVE | curl to 127.0.0.1:8088 only |
| `cdr_upload_config.php` | API base URL | array | getenv CDR_API_URL | config | — | upload pages | — | ACTIVE | default http://127.0.0.1:8088 |
| `check_role.php` | JSON role | script | session cookie | JSON | none | Spry JS (cosmetic) | none | PARTIALLY ACTIVE | Info leak of role if session exists |
| `sql_safe.php` | Input filters + `h()` | sql_safe_digits/phone/imei/like, h() | strings | sanitized strings | — | ~9 pages | — | ACTIVE | Not parameterized queries; unused on most pages |
| `dbcontroller.php` | Dropdown AJAX helper | DBController class | POST district/PS | options HTML | CDATDUPL / CIS | GET_PS/YEAR/DIVISION/CRNO | none | ACTIVE | SQLi in GET_* pages |
| `cdat-web.nginx.conf` | HTTP server | — | — | — | — | deploy | — | ACTIVE (if used) | 750G uploads; HTML not executed as PHP |
| `cdr-import-service/app/main.py` | FastAPI | routes documents/imports | multipart, JSON | JSON | postgres | nginx proxy; PHP curl | **Needs Verification** (API auth) | ACTIVE if systemd up | Bind 127.0.0.1 assumed |

### 5.2 Potentially unused (no confirmed CDAT menu path)

Root handlers not linked from `HOME.html` / `HOME_IR.HTML` / `IR_MODULE.HTML` / `TOWER_HOME.HTML` / `SUM_HOME.html` / `HOME_IMEI.html` (static scan): `CAF_SEARCH`, `MIGRANT_LABOURS_*`, `CIS_DATA_*`, `VBR_SEARCH`, `NBWS`, `ALLDATA*`, `NAMESEARCH`, `ANALYSIS_ABSTRACT`, `rta_nike`, `MOVEMENTS_IN_PARTICULAR_PLACE`, `DUMP*`, `OFFENDER_FD`, `BULK_GANG_ID*`, `BULK_IRKEY*`, `IR_NDPS*`, `IR_SEARCH__OLD`, most `JRMS_*` extra copies, `TRAINING_MODULE2`, `wanted1` (only SUM_HOME), `NEAREST_CELLIDS`, `NEAR_BY_CELLTOWERIDS`, `PDACT_PRESS_NOTES`, `PDACT_SUBMIT`, etc.

These **may still be bookmarked or opened from inside result HTML**. Status: **Potentially unused / Needs Verification**.

### 5.3 Dead / junk

`chandu.php`, `untitled-1.php`, `notepad.php`, `desktop.php`, `login_page.php`, `jquerydynamic.php`, `css_sparkle1.php` (CSS as php?), `style.php`, `sample.php`→gif, `dbcontroller.php.php`, `demo.php.php`.

### 5.4 Dump directories

`curfewepass/`, `old ir/`, `new ir/`, `TWRDB/`, `ROUGH_TOWER/`, `SUN/` — not referenced from root menus. Treat as **UNUSED copies** unless separately hosted (**Needs Verification**).

---

## 6. Database Audit

### 6.1 Engines and connection

| Item | Finding |
| ---- | ------- |
| Legacy dialect | Microsoft SQL Server via `sqlsrv_connect($serverName, ['Database'=>…])` — typically **no UID/PWD** (Windows/trusted auth implied) |
| Dominant instance name | `CPHYDERABAD1\DAU_HYD_2023` (also `DAU_HYD`, `CPHYDERABAD1`, `10.10.x.x\DAU_HYD_2023`, `UUUU-HP`, `USER-HP`) |
| Actual runtime on this tree | **PostgreSQL** via `sqlsrv_compat.php` + `db_config.php` (`CDR_DB_HOST/PORT/NAME/USER/PASSWORD`) |
| `db_config.php` on `main` | **Absent** (gitignored). Example: host `127.0.0.1`, db `postgres`, user `postgres`, password placeholder |
| `pg_connect` | Not used |
| `mysqli` | Not used |
| Transactions | Not used in root PHP (except implicit PDO). Upload pipeline uses jobs |

### 6.2 Logical databases (legacy names → shim)

All mapped to PostgreSQL database **`postgres`** by `__sqlsrv_dbname()`:

`cdatdupl`, `cdat`, `twrmdb`, `irforms`, `forms`, `jrms`, `pdact`, `lostreport_hawkeye`, `migrant_labours_form`, `training_db`, `cpms`, `cafs`, `cis_data_base`, `cdat_import`, `testing_db`, `rough`, `distributed_db`.

**Also in dumps / Python (not all used by PHP UI):** `distributed_db` (Citus), `cdat_db`, `ai_copint_db`, MSSQL `address_db`, `cellids_db`, `DOPAMS_HYD_UNIT`.

### 6.3 Database Usage Matrix (root PHP, static)

| Table | Read | Insert | Update | Delete | Used By (examples) | Status |
| ----- | ---- | ------ | ------ | ------ | ------------------ | ------ |
| CDATPCSUSPECT | Y | Y (import) | — | — | SUM/CALLS/IMEI/D&N/MOVEMENTS; cdr_import | ACTIVE |
| CDAT_DETAILS / CDAT_DETAILS1 | Y | — | — | — | SUM.PHP, SUM_ISD_CNTS | ACTIVE |
| CDATSUSPECT | Y | — | — | — | several reports | ACTIVE |
| CDATADDRESS / ADDRESS_OTHER_STATE | Y | — | — | — | ADDRESS, enrichment; FDW lateral in shim | ACTIVE |
| CDATCELLTOWERAREANEW | Y | — | — | — | CELLID / nearest tower | ACTIVE |
| CDATPHONEAREA | Y | — | — | — | enrichment | ACTIVE |
| CDAT_RTA | Y | — | — | — | VEHICLE_* | ACTIVE |
| LOGINS / logins | Y | Y | — | — | LOGIN.PHP, ADMIN_CREATE_USER | ACTIVE (plaintext) |
| IR_PARTICULARS | Y | Y | — | — | IR.*, RETRIEVE*, IMAGE flows | ACTIVE |
| IMAGE_TABLE / MO_IMAGE_TABLE / SUSPECT_IMAGE_TABLE | Y | Y | — | — | IMAGE_LIST, IR display | ACTIVE |
| OFFENCE_DETAILS / PREVIOUS_OFFENCE_DETAILS | Y | Y | — | — | IR forms, bulk IR | ACTIVE |
| BRIEF_FACTS / FAMILY_HISTORY / DISPOSAL_OF_PROPERTY / LOCAL_CONTACTS_FACILITATORS / RELATIONSHIP_WITH_OTHER_ASSOCIATES / MULAKATH_ENTRY / ANALYSIS_ABSTRACT | Y | Y | — | — | HOME_IR section PHP | ACTIVE |
| JRMS_TOTAL_2012_TO_2017 | Y | Y | Y | — | JRMS_* including unique key update | ACTIVE |
| PDACT_MAIN_TABLE / PDACT_PRESS_NOTES_TABLE | Y | Y | — | — | PDACT_* | ACTIVE |
| ROWDY_SHEETER_DATA1 | Y | — | — | — | rowdysheeter search | ACTIVE |
| LOST_REPORT_CDR_DATA | Y | — | — | — | IMEI_REQUEST_*, D&N_LOC_IMEI | ACTIVE |
| MIGRANT_LABOUR_TABLE | Y | Y | — | — | MIGRANT_* | UNKNOWN usage from menu |
| CIS_COMPLETE_DATA | Y | — | — | — | CIS_DATA_* | UNKNOWN |
| NBWS_VERIFY_DATA_IMPORTANT | Y | — | — | — | NBWS.PHP | UNKNOWN |
| COMPLETE_MO_CLASSIFICATION | Y | — | — | — | offender MO | ACTIVE/partial |
| user_sessions / user_activity_logs | Y | Y | Y | — | activity_logger, ADMIN_ACTIVITY_LOG | ACTIVE |
| upload_activity_logs / upload_approval_queue / upload_staging_batches / document_jobs | Y | Y | Y | — | upload pipeline | ACTIVE |
| tbladmin / tblpass / tblcategory | Y | Y | Y | — | **curfewepass only** | UNUSED by CDAT UI |
| #TEMP / #TT / #RESULT (MSSQL temp) | Y | Y | Y | — | JRMS, PDACT, D&N, SUM, VBR | **PG translation required** |

**Deletes:** essentially none in root app SQL (except temp-table cleanup patterns / `#COMMON_NUMBERTABLE3`).

### 6.4 SQL risks (non-exploitative)

- **Injection:** almost every `$_POST`/`$_GET` into `$sql` string. Parameterized exceptions: `LOGIN.PHP`, parts of `activity_logger.php`, `ADMIN_ACTIVITY_LOG.PHP` filters.
- **Hardcoded DB names** in PHP (`Database=>"CDATDUPL"`) while shim ignores serverName and maps dbname.
- **Queries that may fail on Postgres:** `SELECT * INTO #TEMP`, `CONVERT(VARCHAR(20),CONVERT(DATE,…))`, `WITH (NOLOCK)`, `isnumeric()`, `+` string concat, `DATEFORMAT`, three-part `IRFORMS..IMAGE_TABLE`. Shim covers some, not all.
- **Duplicate queries:** JRMS `#TEMP` block copy-pasted across ~15 files.
- **Tables referenced but possibly missing on PG:** `ALL_ILD_DATA_2012` (VBR), `CAFs`, `TRAINING_DB` objects, Hawkeye tables — **Needs Verification** against live `postgres`.

Full matrix companion: **`MAIN_BRANCH_USAGE_MATRIX.md`**.

---

## 7. External API / Service Audit

### ACTUALLY USED (if deploy runs the service)

| Service | Endpoint | Method | Purpose | Authentication | Used By | Active? | Error Handling |
| ------- | -------- | ------ | ------- | -------------- | ------- | ------- | -------------- |
| Document Processing FastAPI | `http://127.0.0.1:8088/` (`CDR_API_URL`) | POST/GET JSON + multipart | CDR/SDR import jobs, resumable SDR | **Needs Verification** (localhost assumed) | `document_processing_client.php`, nginx `/document-api/`, `js/sdr_resumable_upload.js` | USED if systemd `cdr-import-service` up | throws RuntimeException on curl/HTTP≥400 |
| Python CDR preview | `scripts/cdr_preview.py` via `exec` | local | Preview before queue | OS user | `admin_upload.php` | USED | depends on exec/disable_functions |
| Excel→CSV | `scripts/excel_to_csv.py` via `exec` | local | Convert xls | OS user | `excel_converter.php` | USED | |

### CONFIGURED BUT NOT USED (by PHP UI)

| Service | Notes |
| ------- | ----- |
| `image_migrate` CLI | MSSQL DOPAMS images → PG; no PHP reference |
| `distributed_migrate` + systemd Citus units | MSSQL → `distributed_db`; no PHP reference |
| `scripts/cdr_import_worker.sh` | Older inbox worker vs FastAPI path |
| Docker `cdr-import-service/docker-compose.yml` | Alternate to host systemd |
| CDN Font Awesome / SheetJS / PapaParse | Only `admin_upload*.php` |

### REFERENCED BUT BROKEN / RISKY

| Item | Notes |
| ---- | ----- |
| `ftp://192.168.x.x/…` in `CAF_SEARCH.PHP` | Internal FTP URL built from DB PHONE; host hardcoded |
| Mail to `crimelab@hyd.tspolice.gov.in` | Displayed on tower home; **no mail() sending code** found in root PHP |

**Not found:** WhatsApp/Meta, SMS gateways, payment, Google Maps API, OAuth, public webhooks.

---

## 8. Dependency Audit

| Dependency | Version | Declared | Actually Used | Used Where | Risk |
| ---------- | ------- | -------- | ------------- | ---------- | ---- |
| PHP | 8.3 implied (`php8.3-fpm.sock`) | nginx conf | Yes | all PHP | Need pdo_pgsql, curl, gd; sqlsrv **not** required if shim loaded |
| PostgreSQL | not pinned | .env.example 5432 | Yes | shim + audit + import | Operational DB; huge `cdatpcsuspect` |
| `pdo_pgsql` | ext | implied | Yes | sqlsrv_compat, activity_logger | Missing ext = total outage |
| `curl` | ext | implied | Yes | document_processing_client | Upload UI fails |
| `gd` | ext | qrcode readme | Yes | qrcode/php/qr_img.php | QR pages fail |
| `exec` / python3 | OS | scripts | Yes | admin_upload preview, excel_converter | disable_functions breaks upload preview |
| FastAPI | ≥0.115.0 | cdr-import-service/requirements.txt | Yes (service) | :8088 | Supply-chain / must stay local |
| uvicorn | ≥0.32.0 | same | Yes | — | |
| python-multipart | ≥0.0.12 | same | Yes | uploads | |
| psycopg2-binary | ≥2.9.9 | requirements-cdr-import.txt + FastAPI | Yes | import | |
| pyodbc | ≥5.1.0 | requirements-cdr-import.txt | SDR/MSSQL path | sdr_import, image_migrate | Needs ODBC + MSSQL |
| Adobe Spry MenuBar | ~2006–2009 | SpryAssets | Yes | ~130 pages | Unmaintained; XSS history in old Spry |
| jQuery UI | **1.10.4 (2014-01-29)** | jquery-ui-1.10.4.custom | Yes (~50 datepickers) | known CVEs in old jQuery UI |
| jQuery | 2.1.1 in jquerydynamic.js; also in DROP DOWN FILTER | local | Yes | XSS/CVE legacy |
| w3.css | local via symlink | DROP DOWN FILTER | Yes (few pages) | Low |
| qrcode PHP lib | bundled | qrcode/ | Yes | GD; old code |
| Font Awesome | 6.4.0 CDN | admin_upload only | Yes if CDN reachable | CDN integrity not pinned (**Needs Verification**) |
| SheetJS xlsx | 0.18.5 CDN | admin_upload | Yes | CDN |
| PapaParse | 5.4.1 CDN | admin_upload | Yes | CDN |
| Bootstrap 3.1.1 + DataTables | curfewepass only | local | **No** (CDAT UI) | Unused by main app |
| Composer PHP packages | none | — | — | n/a |
| simple-datatables | **not on main** | — | No | (appears on other branches only) |

**Unused dependencies:** vast jquery-ui development-bundle demos; curfewepass vendor tree; qrcode sample assets.

**Missing:** `db_config.php`, root `get_state.php`, root `ddtf.js`, `SUM1.php`/`SUM2.php`/`SUM_P.PHP`.

---

## 9. Authentication & Authorization Audit

### 9.1 Login flow (confirmed)

```text
User → LOGIN.HTML (USERNAME, PASSWORD, no CSRF)
     → POST LOGIN.PHP
     → sqlsrv_connect FORMS
     → SELECT * FROM LOGINS WHERE USERNAME=? AND PASSWORD=?   (plaintext)
     → $_SESSION['audit_role'|'audit_fullname']
     → audit_login() → INSERT user_sessions; set audit_session_id, audit_username, audit_user_id
     → header refresh → HOME_IR.HTML
```

Alternate: `LOGIN1.PHP` (SQLi) or `RETRIEVE1.PHP` hardcoded `FORMS` / `sa@***` → `LOGIN1.php`.

### 9.2 Logout

`LOGOUT.PHP`: `audit_logout()` UPDATE `user_sessions`, `session_unset/destroy`, `Location: LOGIN.HTML`.

### 9.3 Session

- `session_start()` only in `activity_logger.php` and `check_role.php`.
- No `session_regenerate_id` after login.
- No idle timeout / absolute timeout in PHP.
- Cookie flags (`Secure`, `HttpOnly`, `SameSite`) **not set in application code** — depend on php.ini (**Needs Verification**).
- Role is **not re-read from DB** on each request.

### 9.4 Password handling

- Stored and compared **in plaintext**.
- No `password_hash` / `password_verify` / md5 / sha1 in app login path.
- `ADMIN_CREATE_USER.PHP` inserts plaintext into Postgres `logins`.

### 9.5 OTP

**Not present.**

### 9.6 Roles

| Role string (lowercased from LOGINS.ROLE) | Gate |
| ----------------------------------------- | ---- |
| `admin` | `audit_require_admin`, `audit_is_admin`, uploader allowed |
| uploader/poweruser (see `audit_require_uploader` implementation) | upload pages |
| other / empty | most of the app; also unauthenticated users |

### 9.7 Pages accessible without authorization

**All static HTML** including `HOME.html`, `HOME_IR.HTML`, every search form.

**Almost all PHP report/insert handlers**, including `SUM.PHP`, `ADDRESS.PHP`, `IR.PHP`, `IMAGE_LIST.PHP`, `JRMS_UNIQUE_KEY_UPDATE.PHP`, `GET_PS.PHP`, etc.

Admin/upload PHP **reject** unauthenticated users **if** session helpers run (redirect to `LOGIN.HTML`).

### 9.8 API authentication

FastAPI `:8088` — **Needs Verification** whether unauthenticated localhost-only. nginx exposes `/document-api/` on the same `:8020` server as the UI (**High** if that port is reachable beyond localhost).

---

## 10. Security Audit

See **`MAIN_BRANCH_ISSUES.md`** for the full numbered register. Summary:

| ID | Severity | Category | Finding |
| -- | -------- | -------- | ------- |
| SEC-01 | Critical | Authz | Most PHP/HTML unauthenticated |
| SEC-02 | Critical | Credentials | Plaintext passwords in LOGINS |
| SEC-03 | Critical | SQLi | Ubiquitous string SQL including LOGIN1 |
| SEC-04 | Critical | Backdoor | RETRIEVE1.PHP hardcoded FORMS/sa@… |
| SEC-05 | High | XSS | Unescaped `$row` echo on result pages |
| SEC-06 | High | CSRF | No tokens on any state-changing form |
| SEC-07 | High | Upload | IMAGE_LIST unauthenticated → SQL |
| SEC-08 | High | Admin | SQL console weak filter; linked from public HOME |
| SEC-09 | High | Info leak | `die(print_r(sqlsrv_errors()))` everywhere |
| SEC-10 | Medium | Session | No regenerate/timeout/cookie flags in code |
| SEC-11 | Medium | Secrets | Hostnames, instance names, internal IPs/FTP in source |
| SEC-12 | Medium | Exec | `exec` python from upload UI |
| SEC-13 | Medium | DoS | 750G body, 24h timeouts, unauthenticated heavy SQL |
| SEC-14 | Low | Deps | jQuery UI 1.10.4 / Spry / old jQuery CVEs |

**No exploitation was performed.**

---

## 11. Code Quality & Architecture Audit

| Issue | Actual impact |
| ----- | ------------- |
| PHP + HTML + SQL in one file per screen | Cannot unit-test; copy-paste bugs (JRMS `#TEMP` ×15); every UI tweak touches SQL |
| No front controller / router | 212 “endpoints” = 212 files × 6 case variants; nginx/Apache case rules required |
| Case-symlink forest (`scripts/fix_html_form_symlinks.php`) | Breaks on case-sensitive Linux if symlink target missing; `DB_CONFIG.PHP` → missing `db_config.php` |
| Dual MSSQL / Postgres story | Developers write T-SQL; production (this tree) runs PG shim; silent wrong results or errors |
| God files | `IR.PHP`, `admin_upload.php`, JRMS/PDACT pages | Hard to review; high defect density |
| Global sqlsrv connection per page | Every request reconnects; no pooling in PHP |
| Hardcoded `$serverName` | Irrelevant under shim but confuses ops; wrong host if shim **not** prepended |
| `set_time_limit(0)` on summaries | Worker exhaustion |
| Poor naming (`*_PHP.PHP`, `*_MAHESH`, `OTHERCDAT` vs `OTHERSCDAT`, `d%26n`) | Wrong form actions; broken bookmarks |
| Missing abstraction for auth | New pages forget `audit_require_session` — **the default is insecure** |
| Difficult-to-test | No PHPUnit; HTML forms not executable as PHP unless nginx snippet enabled |
| Tight coupling to table/column names | Intentional for police field compatibility; blocks ORM but preserves investigation SQL |

---

## 12. UI / Frontend Audit

### Functional UI

- **Adobe Spry** horizontal/vertical menus + `css_sparkle1.css` + `IMAGES/TOPBORDER.JPG` maroon/blue (`#921215`, `#0C5D90`, `#5195BA`).
- Search pattern: HTML form (`method=post`) → PHP echoes `<table border=1>` with colored `<th>`.
- Datepickers: **jQuery UI 1.10.4** on ~50 pages.
- Some tables: **ddtf.js** + **w3.css** (`ADDRESS.PHP`, vehicle criteria, IMEI movements).
- QR: `qrcode/php/qr_img.php?d=…`.
- Upload UI: newer HTML + **CDN** FA/SheetJS/PapaParse + `js/sdr_resumable_upload.js`.
- Print: browser print of HTML tables (no dedicated print CSS on most pages).
- Responsive: **essentially none** (fixed table widths ~1300px). IE/old-browser era (`XHTML 1.0 Transitional`).

### Legacy / unused UI

- `curfewepass` Bootstrap 3 + DataTables — not wired.
- jquery-ui **development-bundle demos**.
- `login_page`, `chandu`, `untitled-1`, Dreamweaver `_notes`.
- Commented HOME items: Call Details Total, Calls Between Two Numbers, Logout on main HOME (logout still in lower block).

### Frontend/backend coupling

Total: form field names (`PHONE_NO`, `IRKEY`, …) are the API. Changing a field name breaks PHP `$_POST` keys.

---

## 13. Error & Failure Analysis

| Pattern | Where | Effect |
| ------- | ----- | ------ |
| `die(print_r(sqlsrv_errors(), true))` | Almost every report PHP | Leaks SQL/driver errors (connection strings, schema) to browser |
| `#TEMP` / `INTO #TEMP` | JRMS, PDACT, D&N, SUM, bulk IR, VBR, migrant | Fails without shim; may fail if translation incomplete |
| Empty catch / ERRMODE_SILENT | `activity_logger` PDO | Audit failures swallowed (`error_log` only) |
| `download_template.php` 410 | upload | Dead feature |
| Missing SUM1/2/P | SUM_HOME variants | 404 |
| Missing `db_config.php` | all shim + audit | Total PHP failure |
| FastAPI down | admin_upload | Upload/status errors |
| `set_time_limit(0)` + 120s statement_timeout | summaries on huge `cdatpcsuspect` | PHP waits, PG cancels, user sees error dump |
| Commented-out menu items | HOME.html | Features exist but hidden |
| No TODO/FIXME markers of note | (grep hits were `#TEMP` false positives) | Debt is implicit, not annotated |

**Likely production failure points:** pg_hba / wrong `db_config`; shim not prepended (then `sqlsrv_*` missing or hits unreachable MSSQL); FastAPI not running; case-sensitive filename mismatch; statement timeout on full-table CDR scans.

---

## 14. Used vs Unused — Proof-Based Analysis

### Confirmed Active

- nginx site + `HOME.html` menu closure + matching PHP handlers listed in §3 ACTIVE rows.
- Login/logout/audit/admin/upload chain.
- `sqlsrv_compat.php` referenced by nginx `PHP_VALUE auto_prepend_file`.
- FastAPI client + `/document-api/` location.
- Spry, css_sparkle1, IMAGES/TOPBORDER, jquery-ui datepicker includes, qrcode img src.

### Confirmed Unused (from CDAT UI)

- `curfewepass/` (no root href/include/curl).
- `old ir/`, `new ir/`, `TWRDB/`, `ROUGH_TOWER/`, `SUN/`.
- `image_migrate/`, `distributed_migrate/` (no PHP call).
- Bootstrap/DataTables (curfewepass only).

### Potentially Unused

- Root PHP not in HOME/HOME_IR/IR_MODULE/TOWER/SUM_HOME/HOME_IMEI menus (CAF, migrant, CIS, VBR, NBWS, ALLDATA, NAMESEARCH, DUMP, many JRMS copies, TRAINING_MODULE2, …). May still be linked from result pages (`IR.PHP?IRKEY=`, “back” links). **Do not delete without runtime referer/log analysis.**

### Broken

- `SUM1.php`, `SUM2.php`, `SUM_P.PHP`
- `download_template.php` (410)
- `get_state.php` at root; `demo.php.php` + missing `ddtf.js`
- `DB_CONFIG.PHP` symlink without blob
- Any T-SQL not covered by `__sqlsrv_translate` when shim is on

### Needs Verification

- Whether production still uses native sqlsrv+MSSQL **without** prepend.
- Whether `:8020` is internal-only.
- FastAPI authentication and systemd enabled.
- HOME_IMEI / migrant / CAF reachability via undocumented bookmarks.
- Table existence on live `postgres` vs schema dumps dated 2026-07-30.
- Cookie security flags in php.ini / php-fpm pool (`scripts/php-fpm-cdat.conf`).

---

## 15. Application Dependency Graph

```text
User
 ↓
nginx :8020
 ├─ HOME.html (public menu)
 │    ├─ *.html search forms ──POST──► *.PHP ──sqlsrv_*──► sqlsrv_compat
 │    │                                              └─ PDO pgsql ──► postgres
 │    │                                                    ├─ cdatpcsuspect / cdat_details / cdataddress (FDW/Citus views)
 │    │                                                    ├─ IR / JRMS / PDACT / TWR / Hawkeye tables
 │    │                                                    └─ (optional) dist.* FDW
 │    ├─ LOGIN.HTML ──POST──► LOGIN.PHP ──► FORMS.logins + activity_logger ──► user_sessions
 │    │                         └─ HOME_IR.HTML ──► IR section PHP (INSERT/SELECT FORMS)
 │    ├─ admin_upload.php ──audit_require_uploader──► curl 127.0.0.1:8088
 │    │                                                ├─ cdr-import-service (FastAPI)
 │    │                                                │     ├─ document_processing/
 │    │                                                │     ├─ cdr_import/ (Airtel/Jio/Vi/BSNL)
 │    │                                                │     └─ sdr_import/
 │    │                                                └─ postgres document_jobs / staging
 │    └─ ADMIN_* ──audit_require_admin──► audit_db() PDO postgres
 └─ /document-api/ ──proxy──► FastAPI :8088

CLI/systemd (not UI): image_migrate, distributed_migrate, index build scripts
Unused dump: curfewepass, old ir, new ir, TWRDB, ROUGH_TOWER, SUN
```

### Critical chains

1. **Any PHP page → sqlsrv_compat → db_config.php → postgres** (single point of failure).
2. **Upload → FastAPI :8088 → postgres** (UI works without it; import does not).
3. **Login → LOGINS plaintext → session → only a few gated pages**.
4. **Address reports → CDATADDRESS FDW/Citus** (shim rewrites JOINs to LATERAL; wrong rewrite = empty/wrong addresses).

---

## 16. Current Application Flow

### Login (IR)

User → `LOGIN.HTML` → `LOGIN.PHP` → `LOGINS` → session + `user_sessions` → `HOME_IR.HTML`

### CDAT search (typical)

User → `HOME.html` → e.g. `SUM_HOME.html` → POST `PHONE_NO` → `SUM.PHP` → `CDAT_DETAILS` / temp tables → HTML table  
**No login required.**

### Calls between dates (hardened variant)

User → `CALLS_BTWN_DATES.html` → `CALLS_BTWN_DATES.PHP` → `audit_require_session()` → `sql_safe_*` → concatenated SQL → table

### IR retrieve / image

User → `RETRIEVE.HTML` → `RETRIEVE.PHP` / `IMAGE_LIST.HTML` → `IMAGE_LIST.PHP` INSERT base64 image  
**No login required on IMAGE_LIST.**

### JRMS unique key update

User → JRMS search → `JRMS_UNIQUE_KEY_UPDATE.PHP` → `UPDATE JRMS_TOTAL_2012_TO_2017`  
**Unauthenticated write.**

### Export (admin)

Admin → `ADMIN_SQL_CONSOLE.PHP` → POST SELECT → wrap LIMIT 1000 → CSV/XLS download

### CDR upload

Uploader → `admin_upload.php` → optional `exec` preview → curl FastAPI submit → poll `admin_upload_job_status.php` → `admin_upload_verify.php`

### Logout

User → `logout.php` → `audit_logout` → `LOGIN.HTML`

---

## 17. Technical Debt Report

### Critical

- Default-deny auth missing (opt-in `audit_require_*`).
- Plaintext credentials + LOGIN1 SQLi + RETRIEVE1 backdoor.
- Unauthenticated data-changing endpoints (IR inserts, JRMS update, IMAGE_LIST).
- `db_config.php` not in VCS + symlink forest.

### High

- MSSQL dialect vs Postgres shim completeness.
- SQLi/XSS on nearly all reports.
- 15k-file case-duplicate tree (ops + git + Windows/macOS vs Linux).
- 750G nginx body + unauthenticated heavy queries (availability).
- Copy-paste JRMS/PDACT/IR families.

### Medium

- No Composer/autoload; no tests; no shared UI layout on `main`.
- jQuery UI 1.10.4 / Spry.
- Commented menus vs live handlers drift.
- Audit PDO silent errors.

### Low

- Titles “Untitled Document”.
- Dead demo files (`chandu`, `untitled-1`, …).
- Unused dump directories.
- CDN vs local inconsistency on upload pages only.

---

## 18. Risk Register

| Risk | Severity | Area | Current Situation | Impact | Recommendation |
| ---- | -------- | ---- | ----------------- | ------ | -------------- |
| Unauthenticated PII/CDR access | Critical | Security | HOME + most PHP open | Mass PII/CDR leak | Front-door auth on every PHP/HTML; network ACL |
| Password dump / reuse | Critical | Security | Plaintext LOGINS + backdoor | Account takeover, possible SA password reuse | Hash passwords; remove backdoor; rotate |
| SQL injection | Critical | Security | String SQL everywhere | Data exfil / modification | Parameterize; WAF interim |
| Postgres shim mismatch | High | Data | T-SQL written, PG executed | Wrong investigative results | Query test corpus; fail closed |
| Upload/API exposure | High | Security | `/document-api/` on same vhost; 750G | Disk fill, RCE if API weak | Bind API localhost; authn; size limits |
| Credential/host leakage in git | High | Security | Instance names, IPs, FTP, sa-style secret in RETRIEVE1 | Recon / lateral movement | Secret scan; rewrite history if needed |
| Single db_config / postgres | High | Availability | All modules map to one DB | Total outage | HA + backups; separate audit DB |
| Unmaintained frontend | Medium | Security/UX | Spry + jQuery UI 2014 | XSS, broken browsers | Replace chrome incrementally |
| Case-symlink deploy | Medium | Deployment | 6× files per page | 404 on Linux | Canonical lowercase + redirects |
| Dump dirs in webroot | Medium | Security | curfewepass, old ir reachable if nginx try_files | Extra attack surface | Move outside docroot |
| No automated tests | Medium | Maintenance | None | Regressions on shim/SQL | Golden-query tests |
| Operator exec() | Medium | Security | python from PHP | RCE if upload name unsanitized | Disable exec; use API only |
| Legal/compliance (Aadhaar, CDR) | High | Data | Sensitive data in app + logs | Regulatory incident | Access logging on **all** searches (today many pages unauthenticated and unlogged) |

---

## 19. Final Application Health Score

| Area | Score / 10 | Reason |
| ---- | ---------: | ------ |
| Architecture | 3 | Flat PHP+HTML+SQL; dual MSSQL/PG; no router; 15k duplicate tree. Shim is a clever bridge but not a real architecture. |
| Security | 2 | Unauthenticated PII, plaintext passwords, SQLi/XSS/CSRF, backdoor, error leakage. A few admin gates exist but default is open. |
| Code Quality | 3 | Copy-paste families; god files; `die(print_r)`; inconsistent naming; some newer upload/audit code is clearer. |
| Maintainability | 2 | Case variants, hardcoded hosts, no tests, field-level coupling, undocumented which pages are live. |
| Dependencies | 5 | Few runtime deps (good); but ancient jQuery UI/Spry; Python stack is modern (FastAPI) and reasonably pinned. No Composer. |
| Database | 4 | Real investigative schema exists; indexes/FDW/Citus scripts show operational maturity; PHP still speaks T-SQL; one mega-table risk. |
| API Integration | 6 | Local FastAPI is coherent (client + nginx + systemd). Not public SaaS. Auth on API unverified. image_migrate unused from UI. |
| Frontend | 3 | Functional for internal IE-era users; not responsive; Spry; inconsistent upload UI vs reports. |
| Documentation | 2 | No product README on `main`. Titles “Untitled Document”. `.env.example` / `db_config.example.php` / nginx comments are the only ops docs. |
| **Overall** | **3 / 10** | The system **can** serve Hyderabad police CDAT/IR workflows and has a serious import pipeline, but `main` is an insecure, dual-runtime legacy dump. It is not a safe baseline for internet exposure or for naïve refactor. |

Scores are relative to a maintainable internal law-enforcement app, not a consumer SaaS.

---

## 21. Recommended Roadmap

**Do not implement in this audit.** No source changes beyond these report files.

### Phase 0 — Safety / Baseline

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Full backup of git + live `postgres`/`distributed_db` + off-repo `db_config.php` | Cannot recover plaintext DB or secrets from git | Restore point | P0 | Ops access | Low |
| Document actual runtime (shim vs native sqlsrv; hostname; who can reach :8020) | `main` code alone cannot prove production mode | Correct hardening plan | P0 | Ops | Low |
| Dependency snapshot (`php -m`, pip freeze, nginx/php-fpm ini) | Drift vs repo | Reproducible env | P0 | Server | Low |
| Record current behaviour (screenshot + sample queries per HOME menu item) | Prevent “fix” from changing investigation results | Baseline | P0 | Test phones/IRKEYs | Low |
| Network ACL: CDAT only on police LAN / VPN | Unauthenticated PII | Immediate risk cut | P0 | Firewall | Medium (lockout if mis-ACL) |

### Phase 1 — Critical Security & Bugs

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Force auth middleware on **every** `*.php` (auto_prepend after shim) | Default-open is SEC-01 | Stops anonymous CDR/IR access | P0 | Session + login UX for CDAT (today login is IR-oriented) | High (workflow change) |
| Remove/disable `RETRIEVE1` backdoor; rotate `sa`/FORMS-like passwords | SEC-04 | Close known secret | P0 | Identify all copies | Medium |
| Stop plaintext compare; hash new passwords; migrate `LOGINS` | SEC-02 | Credential theft resistance | P0 | Dual-write period | Medium |
| Disable or extra-gate `ADMIN_SQL_CONSOLE` + `/document-api/` | SEC-08 | Reduce blast radius | P0 | Admin workflow | Medium |
| Fix/remove `IMAGE_LIST` unauthenticated write | SEC-07 | Stop unauth photo insert | P0 | IR users | Low |
| Parameterize LOGIN1 or delete it | SEC-03 | Login SQLi | P0 | LOGIN1 users? | Low |
| Reduce nginx `client_max_body_size`; statement timeouts already 120s | SEC-13 | Availability | P1 | SDR upload sizes | Medium if huge .bak required |
| Do not “fix” SQL field names without investigator sign-off | Business rule | Avoid wrong arrests/analysis | always | — | High if ignored |

### Phase 2 — Stability

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Golden tests: HOME menu forms × shim translation | Catch PG vs MSSQL wrong results | Trustworthy reports | P1 | Sample data | Medium |
| Repair missing SUM1/2/P or remove dead HTML | Broken variants | Less 404 | P2 | Which variant is used? | Low |
| Commit `db_config.example` usage docs; never commit secrets | Deploy failures | Faster onboarding | P1 | — | Low |
| Fail closed when `db_config.php` missing (clear error, no print_r) | SEC-09 | Less leak, clearer ops | P1 | — | Low |
| Confirm FastAPI bind 127.0.0.1 + auth token | API risk | Upload integrity | P1 | systemd | Medium |

### Phase 3 — Cleanup / Dead Code

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Move `curfewepass`, `old ir`, `new ir`, `TWRDB`, `ROUGH_TOWER`, `SUN` out of docroot | Attack surface | Smaller webroot | P2 | Confirm unused in access logs | Medium if secretly used |
| Collapse case duplicates to one canonical extension | 15k → ~2k files | Git/OS sanity | P2 | nginx redirects | High if bookmarks use `.PHP` |
| Delete demo junk (`chandu`, `untitled-1`, `login_page`, `notepad`) after log check | Noise | Clarity | P3 | — | Low |

### Phase 4 — Architecture Improvement

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Shared layout + one menu (already started on other branches, not `main`) | Spry duplication | UX/maintainability | P2 | Don’t change POST field names | Medium |
| Central query helpers with bound parameters for top 20 reports | SQLi/XSS | Security + quality | P1 | sql_safe is not enough | Medium |
| Split audit DB vs CDR DB | Blast radius | Console compromise ≠ CDR | P2 | connection config | Medium |
| Replace `exec` preview with FastAPI-only preview | RCE surface | Upload path | P2 | API feature | Low |

### Phase 5 — Modernization

| Action | Why | Impact | Priority | Dependencies | Risk |
| ------ | --- | ------ | -------- | ------------ | ---- |
| Replace Spry + jQuery UI 1.10.4 | Unmaintained XSS | UI/security | P3 | UX rewrite | Medium |
| Optional API layer for searches (keep SQL column names) | Mobile/future | Incremental | P3 | Auth first | High if big-bang |
| Automated CI: php -l + golden SQL translate tests | Regressions | Confidence | P2 | Sample queries | Low |
| Consider retiring MSSQL dialect entirely **only after** parity tests | Dual runtime | Long-term | P4 | Phase 2 tests | High |

---

## 22. Business & Application Logic Audit

This section traces **how investigative work is actually implemented** on `main` (`dc47eca`). It does not restate architecture. Every workflow below was read from `git show main:<file>`.

**Shared runtime wrapper (almost every PHP page):**

```text
Request → nginx *.php → auto_prepend sqlsrv_compat.php
       → page script (sqlsrv_* + T-SQL)
       → __sqlsrv_translate() → PDO PostgreSQL (db_config.php)
       → HTML table echo
```

If prepend is **not** loaded, the same scripts expect native `sqlsrv` + MSSQL `CPHYDERABAD1\DAU_HYD_2023`. **Needs Verification** which mode production uses. Incorrect results below assume the **Postgres shim path** unless noted.

**Logic status legend:** ACTIVE · PARTIAL · BROKEN · DUPLICATE · DEAD · HARDCODED · MAY BE INCORRECT · UNUSED · MISSING DEP · NEEDS VERIFICATION

---

### 22.1 Cross-cutting business rules (hardcoded in SQL/PHP)

These rules are **not** configurable. They are the product’s investigation policy as encoded today.

| Rule | Where | Evidence | Status |
| ---- | ----- | -------- | ------ |
| Drop “telemarketing-like” others: `OTHER NOT LIKE '140%'` | `SUM.PHP` `$sql5`; `SUM_BTWN_DATES.PHP` `$sql3`; `SUM_NEW_NO.PHP` `$sql5`; `SUM_IN_STATE`/`OUT_STATE`; `IMEI_REQUEST_SUM.PHP` `$sql5` | Same filter copied | HARDCODED + DUPLICATE |
| Drop “junk duration” others: `(CALLS=DUR OR CALLS>DUR) AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I')` | `SUM.PHP` `$sql5` (and copies) | Treats call-count ≈ duration as SMS/junk unless number starts with 7–9 / G / I | HARDCODED; MAY BE INCORRECT on PG (`LEFT`, `isnumeric`) |
| `isnumeric(other)=1` before summary | `SUM.PHP` `$sql3` from `CDAT_DETAILS` | Non-numeric “other” excluded | HARDCODED; MAY BE INCORRECT if shim has no `isnumeric` |
| Phone-area key: 10-digit as-is; longer → `'00'+phone`; else VOIP/Skype/WiFi text | `SUM_BTWN_DATES.PHP` `$sql4` CASE; `cdr_enrichment_sql.php` `cdat_phone_prefix_key()` | Used to join `CDATPHONEAREA.PHONEPREFIX` | HARDCODED + DUPLICATE (SQL CASE vs PHP) |
| Current CAF/address only: `EFF_TO_DATE IS NULL` | ADDRESS, SUM variants, IMEI, CDATCNTS, COMMON_CNTS | Historical address rows ignored | HARDCODED |
| Placeholder photo: `IMAGE_TABLE WHERE IRKEY='113769'` | `PDACT_MAIN_PAGE_SEARCH.PHP` `$sql10`; `PDACT_SEARCH.PHP`; `ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP`; `BULK_CDAT_CONTACTS.PHP` `$sql71` | Missing photo → fixed IRKEY image | HARDCODED; MAY BE INCORRECT (wrong face shown) |
| Day loc: time `> 05:00` AND `< 22:00` | `D&N_LOC.PHP` `$dayPred`; `D&N_BT_DTS.PHP` `$sql1` | | HARDCODED |
| Night loc: time `> 22:00` OR `< 07:00` | `D&N_LOC.PHP` `$nightPred`; `D&N_BT_DTS.PHP` `$sql8` | **05:00–07:00 is both day and night** | HARDCODED; MAY BE INCORRECT |
| IMEI TAC match: `LEFT(imei,14)` | `IMEI_REQUEST_STATUS.PHP` `$sql3`–`$sql5` | 14-digit prefix, not full 15/16 | HARDCODED |
| IR name search: `len(replace(NAME,' ',''))>'4'` | `IR_SEARCH.PHP` `$sql9` | Names ≤4 chars (no spaces) return nothing | HARDCODED |
| JRMS “recent release” jails: `CHERLAPALLI`,`CHANCHALGUDA` only + `MAX(RELEASEDT)` | `JRMS_MAIN_PAGE1.PHP` `$sql9` | Other jails excluded on hub page | HARDCODED |
| JRMS↔IR link: numeric `IDPROOF` / Aadhaar in `IR_PARTICULARS.AADHAR_NO` | `JRMS_MAIN_PAGE1.PHP` `$sql10`; `JRMS_SEARCH.PHP` `$sql2` | Shows “IR AVAILABLE” + max IRKEY | HARDCODED |
| Uploader roles: `admin` **or** `poweruser` only | `activity_logger.php` `audit_require_uploader()` | `user` cannot upload | HARDCODED |
| Operator id map: `2→airtel`, `15→jio`, `12→vi`, `4→bsnl`; Vodafone/Idea→vi | `admin_upload.php` `mapNetworkToOperator()` | | HARDCODED |
| CELLONE operator label → `BSNL` | `CELLID_SEARCH.php` after `$operator` trim | | HARDCODED |
| Customer-care numbers list | `IMEI_REQUEST_STATUS.PHP` `$sql6` `IN('121','111','198','123','139','122','199','12345')` | | HARDCODED |
| JRMS update stamp: `APP_OR_MANUAL='APPLICATION_ENTRY'`, `ASONDATE=GETDATE()` | `JRMS_UNIQUE_KEY_UPDATE.PHP` | Distinguishes UI vs manual SQL | HARDCODED |
| IR insert stamp: `ASONDATE=GETDATE()` | `IRREPORT.PHP` INSERT | | HARDCODED |
| Login success always → `HOME_IR.HTML` (not CDAT `HOME.html`) | `LOGIN.PHP` / `LOGIN1.PHP` | CDAT menu remains unauthenticated | HARDCODED UX |

---

### 22.2 Authentication & session

#### WF-AUTH-01 — IR / CDAT login

**Trigger:** User opens `LOGIN.HTML`, POSTs form `action="LOGIN.PHP"`.

| Step | Detail |
| ---- | ------ |
| Input | `USERNAME`, `PASSWORD` (`LOGIN.HTML`) |
| Validation | `trim`; empty → HTML “USERNAME AND PASSWORD REQUIRED”; **no CSRF, no rate limit, no password policy** |
| Business rules | Exact match on `FORMS.LOGINS` (plaintext). Role → `strtolower(ROLE)` default `'user'`. Success iff `sqlsrv_has_rows` truthy (`$count == 1`) |
| Processing | `$_SESSION['audit_role']`, `audit_fullname`; `audit_login()` INSERT `user_sessions` + `audit_log('System','LOGIN')` |
| Database | **Read** `LOGINS` (parameterized `?`). **Insert** `user_sessions`, `user_activity_logs` via PDO `db_config.php` |
| API | None |
| Output | `header('refresh:0; url=HOME_IR.HTML')` or red “NO PASSWORD MATCHED” |
| Errors | Connect fail → `die(print_r(sqlsrv_errors()))`; audit PDO fail → `error_log` only (login still continues) |
| Files | `LOGIN.HTML` (UI) → `LOGIN.PHP` (handler) → `activity_logger.php` `audit_login` / `audit_db` |

```text
User → LOGIN.HTML → POST LOGIN.PHP → trim/empty check → sqlsrv FORMS.LOGINS (? , ?)
     → session + audit_login → HOME_IR.HTML
```

**Status:** ACTIVE. **Duplicate:** `LOGIN1.PHP` (same outcome, **concatenated SQL**). **Dead/partial:** `LOGIN_PAGE.*` demo. **MAY BE INCORRECT:** if Postgres `logins` vs MSSQL `LOGINS` casing/schema differs under shim.

#### WF-AUTH-02 — Logout

**Trigger:** `LOGOUT.PHP` / `logout.php` from `HOME.html` / `HOME_IR.HTML`.

| Step | Detail |
| ---- | ------ |
| Input | Session cookie |
| Validation | None (`audit_logout` no-ops if no `audit_session_id`) |
| Processing | `audit_log LOGOUT`; UPDATE `user_sessions` logout_time + duration; `session_unset/destroy` |
| Output | `Location: LOGIN.HTML` |
| Files | `LOGOUT.PHP`, `activity_logger.php` `audit_logout` |

**Status:** ACTIVE.

#### WF-AUTH-03 — Role gates

| Function | File:lines (approx) | Rule | Used by |
| -------- | ------------------- | ---- | ------- |
| `audit_require_session()` | `activity_logger.php` | empty `$_SESSION['audit_username']` → `LOGIN.HTML` | CALLDETAILS, CALLS_BTWN_DATES, IMEI_SEARCH, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS |
| `audit_require_admin()` | same | role !== `admin` → 403 HTML | ADMIN_SQL_CONSOLE, ADMIN_CREATE_USER, ADMIN_ACTIVITY_LOG |
| `audit_require_uploader()` | same | role not in `admin`,`poweruser` → 403 | admin_upload* , download_template |
| `check_role.php` | whole file | JSON `{is_admin, role}` **without requiring login** | Spry cosmetic hide |

**Status:** ACTIVE for listed pages only. **UNUSED** on ~190 other PHP pages (including SUM, ADDRESS, IR writes, JRMS update).

---

### 22.3 CDR Summary family

Shared pattern: HTML form → POST phone (± dates/state) → temp tables → junk filter → join nickname/address/phonearea → HTML table.

#### WF-SUM-01 — Summary Total (`SUM.PHP`) — **ACTIVE** (HOME → `SUM_HOME.html`)

```text
User → SUM_HOME.html (action=SUM.PHP, PHONE_NO)
     → audit_log('Summary Total')  [NO session gate]
     → trim PHONE_NO; empty → die "Phone number required"
     → #TT = CDAT_DETAILS WHERE PHONE='$number' AND isnumeric(other)=1
     → #RESULT = group PHONE,OTHER: IN/OUT counts, CALLS, DUR, FIRST/LAST call
     → #RESULT1 = drop 140% + junk duration rule
     → header from CDATPCSUSPECT + CDATSUSPECT nickname
     → PHP enrichment: cdat_fetch_suspect_nickname_map / cdataddress / other_state / phonearea
     → HTML tables + “CDRs NOT AVAILABLE” if #RESULT empty
```

| Item | Evidence |
| ---- | -------- |
| Trigger | `SUM_HOME.html` `action="SUM.PHP"` |
| Input | `PHONE_NO` |
| Validation | Non-empty trim only (**no** `sql_safe_phone`) |
| DB | **Read** `CDAT_DETAILS`, `CDATPCSUSPECT`, `CDATSUSPECT`; PHP maps from `cdr_enrichment_sql.php` |
| API | None |
| Errors | `die(print_r(sqlsrv_errors))` |
| Files | `SUM_HOME.html`, `SUM.PHP` (`$sql3`–`$sql12`, enrichment calls), `activity_logger.php`, `cdr_enrichment_sql.php` |
| MAY BE INCORRECT | `#TEMP`/`isnumeric`/`WITH (NOLOCK)` on PG; unauthenticated; SQLi |

#### WF-SUM-02 — Summary Between Dates — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `SUM_BETWEEN_DATES.html` → `SUM_BTWN_DATES.PHP` |
| Input | `PHONE_NO`, `FROM_DT`, `TO_DT` (**no empty check**) |
| Rules | Source **`CDAT_DETAILS1`** (not `CDAT_DETAILS`); date via `CONVERT(CHAR(10),STARTTIME,121) BETWEEN`; same junk filter; address CASE + `EFF_TO_DATE IS NULL` **in SQL** (not PHP maps) |
| DB | Read `CDAT_DETAILS1`, `CDATSUSPECT`, `CDATADDRESS`, `ADDRESS_OTHER_STATE`, `CDATPHONEAREA`, `CDATPCSUSPECT` |
| Output | Title `SUMMARY OF MOBILE NO: … BETWEEN … AND …` + tables |
| Files | `SUM_BETWEEN_DATES.html`, `SUM_BTWN_DATES.PHP` `$sql1`–`$sql9` |
| DUPLICATE | Junk/address CASE copied from SUM variants; **does not** use `cdr_enrichment_sql.php` |
| MAY BE INCORRECT | Date format vs PG `::date` translate; no validation on dates |

#### WF-SUM-03 — ISD contacts — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `SUM_ISD_CNTS.html` → `SUM_ISD_CNTS.PHP` |
| Input | `PHONE_NO` |
| Rules | `LEN(OTHER)>10 AND DURATION>'0'`; exclude `OTHER LIKE '1800%'`; drop rows where phone-area `ADDRESS=' JUNK-COULD BE bulk SMS or VOIP calls'`; join `'00'+other like phoneprefix` |
| Files | `SUM_ISD_CNTS.PHP` `$sql3`–`$sql7` |
| MAY BE INCORRECT | ISD defined only as “other longer than 10 digits”, not country code table |

#### WF-SUM-04 — New contacts after date — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `SUM_NEW_NOS.html` → `SUM_NEW_NO.PHP` |
| Input | `PHONE_NO`, `FROM_DT` |
| Rules | `CDAT_DETAILS1` where `STARTTIME>'$date'` AND `OTHER NOT IN` (others seen on same phone in `CDATPCSUSPECT` **before** `$date`); then junk filter |
| Files | `SUM_NEW_NO.PHP` `$sql3` |
| MAY BE INCORRECT | Mixes `CDAT_DETAILS1` vs `CDATPCSUSPECT` for “before” set |

#### WF-SUM-05 / 06 — In-state / out-state — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `SUM_IN_STATE.html` / `SUM_OUT_STATE.html` |
| Input | `PHONE_NO`, `STATE` |
| Rules | Same summary + `CDATPHONEAREA E … WHERE E.STATE='$state'` vs `E.STATE !='$state'` |
| Files | `SUM_IN_STATE.PHP` `$sql6`; `SUM_OUT_STATE.PHP` `$sql6` |
| MAY BE INCORRECT | State of **other party’s prefix**, not tower state; `!=` excludes NULL states inconsistently |

#### WF-SUM-07 — Broken variants

| Trigger | Action | Status |
| ------- | ------ | ------ |
| `SUM_HOME12.html` | `SUM1.php` | **BROKEN** — file missing |
| `SUM_HOME2.html` | `SUM2.php` | **BROKEN** |
| `SUM_HOME_P.html` | `SUM_P.PHP` | **BROKEN** |

---

### 22.4 Call details & movements

#### WF-CALL-01 — Calls between dates — **ACTIVE** (session-gated)

```text
User → CALLS_BTWN_DATES.html → CALLS_BTWN_DATES.PHP
     → audit_require_session() → sql_safe_phone/alnum dates
     → audit_log
     → #TT from CDATPCSUSPECT WHERE PHONE + date BETWEEN
     → cdr enrichment SQL (#temp_cdrs + tower)
     → table PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEI,CELLTOWER,OPERATOR,AREADESCRIPTION
     → output encoded with h()
```

| Item | Evidence |
| ---- | -------- |
| Input | `PHONE_NO`, `OPERATOR`, `STATE`, `FROM_DT`, `TO_DT` |
| Validation | `sql_safe_phone`, `sql_safe_alnum` (dates stripped to `[A-Za-z0-9_-]` max 10 — **ISO dates with `-` survive**; `/` dates would be destroyed) |
| DB | **Read** `CDATPCSUSPECT` + tower/address via `cdr_enrichment_sql.php` |
| Errors | `sqlsrv_render_query_error(...)` then continue |
| Files | `CALLS_BTWN_DATES.html`, `CALLS_BTWN_DATES.PHP` L7–L81, `sql_safe.php`, `activity_logger.php`, `cdr_enrichment_sql.php` |
| PARTIAL | `OPERATOR`/`STATE` are sanitized and logged but **not clearly applied** in `$sql1` WHERE (filter may be unused) — **Needs Verification** of `$sql2` enrichment |

#### WF-CALL-02 — Calls between two numbers — **PARTIAL** (HOME menu commented)

| Item | Detail |
| ---- | ------ |
| Trigger | `CALLS_BT_NOS.html` → `CALLS_BT_NOS.PHP` (not on live HOME) |
| Input | `PHONE_NO`, `OTHER_NO`, `OPERATOR`, `STATE` — **no sanitization, no session** |
| Rules | `CDATPCSUSPECT WHERE PHONE='$number' AND OTHER='$number1'` then same enrichment |
| Files | `CALLS_BT_NOS.PHP` `$sql1` |
| Status | Logic exists; **UNUSED from HOME**; unauthenticated SQLi |

#### WF-CALL-03 — Movements (single phone) — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `MOVEMENTS.html` → `MOVEMENTS.PHP` |
| Validation | `isset(PHONE_NO)` + `trim`; empty → “Phone Number Missing”; **parameterized** `WHERE A.PHONE = ?` |
| Rules | Chronological CDR + `INCOMING='1'→'IN'` else OUT; tower map via `cdat_fetch_tower_map`; client-side `filterTable(col)` JS |
| DB | **Read** `CDATPCSUSPECT` (bound param) + `CDATCELLTOWERAREANEW` via PHP map |
| Files | `MOVEMENTS.PHP` ~L140–L392 (`$sql` with `?`, `$count_sql`, `$towerMap`) |
| Status | ACTIVE; **better SQL style than SUM**; still no session gate; XSS on row echo (no `h()`) |

#### WF-CALL-04 — Movements between two numbers / comparison — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | HOME → `MOVEMENTS_BETWEEN_TWO_NUMBERS.html` / `_COMPARISION.html` |
| Input | `PHONE_NO`, `OTHER_NO` |
| Rules | Same as CALLS_BT_NOS + lat/long/azm (`cdr_sql_enrich_tt(..., with_lat_long)`); header from suspect MO/category |
| Files | `MOVEMENTS_BETWEEN_TWO_NUMBERS.PHP` `$sql1`, `$sql2 = cdr_sql_enrich_tt(...)` |
| Status | ACTIVE; unauthenticated; string SQL |

---

### 22.5 CDAT contacts, address, vehicle, cell, IMEI

#### WF-CDAT-01 — CDAT contacts (known suspects only) — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `CDATCNTS.html` → `CDATCNTS1.php` |
| Input | `PHONE_NO` (required non-empty) |
| Rules | From `CDAT_DETAILS1` where `other!=''` **AND** `OTHER IN (SELECT PHONE FROM CDATSUSPECT)` — only counterparties already on suspect list; nickname = `NICKNAME+'_'+ROLE` |
| Processing | PHP header enrichment like SUM (`cdat_format_sum_header_address`) |
| Files | `CDATCNTS1.php` `$sql4`, `$sql5`, `$sql10` |
| DUPLICATE | `CDATCNTS.php` / `CDATCNTS2.PHP` extra copies |

#### WF-CDAT-02 — Others CDAT — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `OTHERSCDAT.html` → `OTHERCDAT.php` |
| Rules | Others of `$number` **excluding** `CDAT_IMPORT.dbo.CALLCENTER_NOS`; then find **other CDAT phones** that also called those others (`PHONE!='$number'`); flip OTHER/PHONE for display |
| Files | `OTHERCDAT.php` `$sql4`–`$sql7` |
| MISSING DEP / MAY BE INCORRECT | `CALLCENTER_NOS` must exist on PG (`cdat_import` mapped to `postgres`) |

#### WF-CDAT-03 — Bulk CDAT contacts — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `BULK_CDAT_CONTACTS.HTML` → `BULK_CDAT_CONTACTS.PHP` |
| Input | `PHONE_NO` comma-separated list |
| Processing | `str_replace(",", "' INSERT INTO #T1 SELECT '", $number)` then `CREATE TABLE #T1` + INSERT; `IN ('$number2')` |
| Rules | Same “other ∈ CDATSUSPECT”; placeholder image IRKEY `113769` |
| Files | `BULK_CDAT_CONTACTS.PHP` `$number3`, `$sqlB1`/`$sqlB2`, `$sql71` |
| MAY BE INCORRECT | `#T1` NVARCHAR + INSERT string-split is fragile; SQLi via list |

#### WF-ADDR-01 — Single address + QR — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `ADDRESS.HTML` / HOME “Address Search” → `ADDRESS.PHP` (`isset(PHONE_NO)`) |
| Rules | Prefer `CDATADDRESS` if `EFF_TO_DATE IS NULL` else `ADDRESS_OTHER_STATE`; else phone-area description; nickname `NICKNAME+'_'+ROLE` |
| Output | HTML row + `<img src="qrcode/php/qr_img.php?d=PHONE NO:… ADDRESS:…">` (address alnum-stripped for QR) |
| API | Local QR PHP (GD), not HTTP API |
| Files | `ADDRESS.PHP` `$sql10`–`$sql11`, `qrcode/php/qr_img.php` |

#### WF-ADDR-02 — Bulk address — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `BULKADDRESS.HTML` → `BULK_ADDRESS.php` |
| Rules | Same comma→`#T1` pattern; `ADDRESS IS NULL AND LEN<>10` → `'JUNK OR VOIP CALL'`; `SUBSTRING(PHONE,1,1) IN ('7','8','9')` → `'CODE NOT AVAILABLE'` |
| Files | `BULK_ADDRESS.php` `$sql1`–`$sql8` |

#### WF-IMEI-01 — Phones used on IMEI — **ACTIVE** (session)

| Item | Detail |
| ---- | ------ |
| Trigger | `IMEISEARCH.html` → `IMEI_SEARCH.PHP` |
| Input | `IMEI_NO` → `sql_safe_imei` (digits, max 18) |
| Rules | `CDATPCSUSPECT WHERE IMEINUMBER = '$number'` exact; aggregate IN/OUT/CALLS/DUR per PHONE+IMEI; address CASE |
| Output | `h()` encoded table; blink “NO PHONES ARE AVAILABLE…” |
| Files | `IMEI_SEARCH.PHP` `$sql1`–`$sql5` |

#### WF-IMEI-02 — IMEIs used on phone — **ACTIVE** (no session)

| Item | Detail |
| ---- | ------ |
| Trigger | `IMEISINPHONE.html` → `IMEI_SEARCH_IN_PHONE.PHP` |
| Input | `PHONE_NO` → `sql_safe_phone` |
| Rules | Mirror of WF-IMEI-01 filtered by `PHONE` |
| Files | `IMEI_SEARCH_IN_PHONE.PHP` |
| Note | Same logic, **asymmetric auth** vs WF-IMEI-01 |

#### WF-IMEI-03 — Hawkeye / lost-IMEI status — **PARTIAL** (HOME_IMEI only)

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME_IMEI.html` → `IMEI_REQUEST_STATUS.html` → `IMEI_REQUEST_STATUS.PHP` |
| Input | `IMEI_NO` unsanitized |
| Rules | Match `LEFT(imei,14)`; three blocks: `COMPLAINANT_DETAILS`, `IMEI_REQUESTED_DETAILS`, `LOST_REPORT_CDR_DATA`; label 140%/1800%/care numbers |
| DB | Connect `LOSTREPORT_HAWKEYE` then still join `CDATDUPL` address tables |
| Files | `IMEI_REQUEST_STATUS.PHP` `$sql3`–`$sql6` |
| Status | Logic present; **reachability Needs Verification**; cross-DB joins **MAY BE INCORRECT** under single-`postgres` shim |

#### WF-DN-01 — Top 10 day/night locations — **ACTIVE**

```text
User → DAY%26NIGHTLOC.HTML → D&N_LOC.PHP (also d%26n_loc.php / DAY&NIGHTLOC.php twins)
     → sql_safe_phone; empty → "Invalid mobile number"
     → dayPred: time >05:00 AND <22:00
     → nightPred: time >22:00 OR <07:00
     → TOP 10 CELLTOWERID by CALLS; cdat_fetch_tower_map → lat/long/azm/area
     → htmlspecialchars tables
```

| Item | Evidence |
| ---- | -------- |
| Files | `D&N_LOC.PHP` `$dayPred`/`$nightPred`, `dn_top_towers()`, `dn_render_table()`, `cdr_escape_sql_literal()` |
| DUPLICATE filenames | `D&N_*`, `D%26N_*`, `DAY&NIGHTLOC*` |
| MAY BE INCORRECT | **05:00–07:00 counted in both day and night**; `TOP 10` is T-SQL (shim must rewrite) |

#### WF-DN-02 — Day/night between dates — **ACTIVE** (weaker)

| Item | Detail |
| ---- | ------ |
| Trigger | `DAY%26NIGHTLOC_BTWN_DATES.HTML` → `D&N_BT_DTS.PHP` |
| Input | `PHONE_NO`, `FROM_DT`, `TO_DT` — **no sql_safe** |
| Rules | Same hour windows **plus** date BETWEEN; `#TEMP` then TOP 10 |
| Files | `D&N_BT_DTS.PHP` `$sql1`, `$sql8` |
| Status | ACTIVE; more PG-fragile than WF-DN-01 |

#### WF-CELL-01 — Celltower search — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `CELLID_SEARCH.html` → `CELLID_SEARCH.php` (self-post form) |
| Input | `CELLID` (required), optional `OPERATOR`, `STATE` |
| Rules | 1) exact `CELLTOWERID` 2) else `BTS_ID` 3) else prefix `LIKE` (only if id long enough); `CELLONE`→`BSNL`; `ILIKE` filters; **limit 50** |
| Output | Tower attributes + QR |
| Files | `CELLID_SEARCH.php` `$sqlExact` / `$sqlBts` / `$sqlPrefix` |
| MAY BE INCORRECT | Uses Postgres `ILIKE` in SQL that still says `SELECT TOP` / `cdatdupl.dbo` — hybrid dialect **Needs Verification** on both runtimes |

#### WF-VEH-01 — Vehicle by registration — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `VEHICLE_SEARCH.HTML` → `VEHICLE_SEARCH.PHP` |
| Input | `VEHICLE_NO` (HTML `required`) |
| Rules | `CDAT_RTA WHERE REGN_NO LIKE '%'+'$number'` (**suffix/contains**, not exact) |
| Output | Owner/address/engine/chas + QR |
| Files | `VEHICLE_SEARCH.PHP` `$sql9` |

#### WF-VEH-02 — Vehicle by chosen column — **ACTIVE** / **MAY BE INCORRECT**

| Item | Detail |
| ---- | ------ |
| Trigger | `VEHICLE_SEARCH_CRITERIA.HTML` → `VEHICLE_SEARCH_CRITERIA.PHP` |
| Input | `VEHICLE_NO`, `VEHICLE_SOURCE` |
| Rules | `WHERE $number1 LIKE '%'+'$number'` — **`$number1` is a column name from POST** |
| Impact | Identifier injection; wrong column → error or wrong hits |
| Files | `VEHICLE_SEARCH_CRITERIA.PHP` `$sql9` |

#### WF-COMMON-01 — Common contacts across phones — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `COMMON_CNTS.HTML` → `COMMON_CNTS.PHP` |
| Input | Comma-separated `PHONE_NO` list |
| Rules | Build `#A1` phone list; CDR where `PHONE IN (list)`; others appearing with `count>1`; `FOR XML PATH` to concatenate phones; **delete** rows with `totalnumberofphones=1` |
| Files | `COMMON_CNTS.PHP` `$sql1`–`$sql9`, `$sql5 DELETE`, `$sql8 UPDATE` |
| MAY BE INCORRECT | `FOR XML PATH` is MSSQL-only; shim may fail → empty common-contacts report |

#### WF-HAB-01 — Habitual offenders list — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME.html` → `HABITUAL.PHP` (GET, no form) |
| Rules | `SELECT … FROM IRFORMS..HABITUAL…` (full list); link `IR.PHP?IRKEY=` |
| Files | `HABITUAL.PHP` `$sql9` |
| DB | Connects `CDATDUPL` then queries `IRFORMS..` three-part name |

#### WF-MO-01 — Offender search by MO — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `OFFENDER_SEARCH_BY_MO.HTML` → `OFFENDER_SEARCH_BY_MO.PHP` |
| Input | `MO` |
| Rules | `COMPLETE_MO_CLASSIFICATION` where `MO1`/`MO2`/`CRIME_HEAD LIKE '%'+REPLACE(spaces,'%')+'%'` (spaces → wildcards) |
| Output | Link `OFFENDER_FD.PHP?MO_KEY=` |
| Files | `OFFENDER_SEARCH_BY_MO.PHP` `$sql9` |
| PARTIAL | `OFFENDER_FD.PHP` not on HOME — **Needs Verification** |

#### WF-GEO-01 — Nearest cell IDs — **PARTIAL**

| Item | Detail |
| ---- | ------ |
| Trigger | Not on HOME; `NEAREST_CELLIDS.PHP` |
| Input | `LAT`, `LONG` via `sql_safe_float`; **session required** |
| Rules | `celltowerfiltered` + distance (`sql/celltower_geo.sql` functions) |
| Status | Logic present; **UNUSED from HOME**; depends on geo SQL deployed |

---

### 22.6 Interrogation Report (IR)

#### WF-IR-01 — Search by name / crime head — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `IR_SEARCH.HTML` / `IR_MODULE.HTML` → `IR_SEARCH.PHP` |
| Input | `NAME`, `CRIME_HEAD` |
| Validation | SQL-side: `ltrim(rtrim(NAME))!=''` AND `len(replace(NAME,' ',''))>'4'` |
| Rules | `IR_PARTICULARS` ⋈ `OFFENCE_DETAILS`; name `LIKE '%'+REPLACE(spaces,'%')`; crime head **or MO** like `%CRIME_HEAD%`; if IRKEY numeric and in `PDACT_MAIN_TABLE` show “PDACT IS IMPOSED” + max `PDACT_KEY` |
| Output | Table + `IR.PHP?IRKEY=` |
| Files | `IR_SEARCH.PHP` `$sql9` |
| DB | Connect `CDATDUPL` but query `FORMS..` / `PDACT..` |

#### WF-IR-02 — Full IR dossier — **ACTIVE**

```text
User → IR.PHP?IRKEY=… (from habitual/search/PDACT links)
     → $_GET['IRKEY'] unsanitized
     → many SELECTs on FORMS: IR_PARTICULARS, IMAGE_TABLE, FAMILY_HISTORY,
       OFFENCE_DETAILS, PREVIOUS_OFFENCE_DETAILS, local contacts, NBWS pending, …
     → one large HTML dossier
```

| Item | Evidence |
| ---- | -------- |
| Files | `IR.PHP` `$number=$_GET['IRKEY']`; `$sql0`+ (NBWS `$sql20` `CASE_STATUS LIKE '%PENDING%'`) |
| API | None |
| Status | ACTIVE read aggregator; unauthenticated SQLi via GET |

#### WF-IR-03 — Create IR particulars — **ACTIVE** (write)

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME_IR` → `IRREPORT.html` → `IRREPORT.PHP` |
| Input | ~50 POST fields (`NAME` … `IR_ENTRY_DONE_BY`; typo field `ELECTRICITY_CONNECTIONE`) |
| Validation | **None** |
| Rules | `INSERT INTO FORMS..IR_PARTICULARS (…) VALUES('…', GETDATE(), …)` |
| Output | Success/fail echo; `refresh:30; url=IRREPORT.html` |
| Files | `IRREPORT.PHP` `$sql` INSERT |
| Status | ACTIVE unauthenticated write |

#### WF-IR-04 — IR section inserts (same pattern) — **ACTIVE**

| Form | Handler | Table | Status |
| ---- | ------- | ----- | ------ |
| `BRIEF_FACTS.html` | `BRIEF_FACTS.PHP` | `BRIEF_FACTS` (IRKEY + 3 text fields) | ACTIVE write |
| `OFFENCE_DETAILS.html` | `OFFENCE_DETAILS.PHP` | `OFFENCE_DETAILS` | ACTIVE write |
| `FAMILY_HISTORY.html` | `FAMILY_HISTORY.PHP` | `FAMILY_HISTORY` | ACTIVE write (same insert style) |
| `PREVIOUS_OFFENCE_DETAILS.html` | matching PHP | `PREVIOUS_OFFENCE_DETAILS` | ACTIVE write |
| `DISPOSAL_OF_PROPERTY.HTML` | matching PHP | `DISPOSAL_OF_PROPERTY` | ACTIVE write |
| `LOCAL_CONTACTS.HTML` | `LOCAL_CONTACTS.PHP` | `LOCAL_CONTACTS_FACILITATORS` | ACTIVE write |
| `RELATION_WITH_…html` | matching PHP | `RELATIONSHIP_WITH_OTHER_ASSOCIATES` | ACTIVE write |
| `MULAKATH_ENTRY.html` | `MULAKATH_ENTRY.PHP` | `MULAKATH_ENTRY` | ACTIVE write |

**Shared flow:** POST all fields → string INSERT → `if(!sqlsrv_query) echo fail` → `header refresh:30` back to HTML. **No auth, no CSRF, no required IRKEY existence check.**

#### WF-IR-05 — Image upload into SQL — **ACTIVE** (dangerous)

| Item | Detail |
| ---- | ------ |
| Trigger | `IMAGE_LIST.HTML` POST `insert` + file |
| Validation | `getimagesize(tmp)==FALSE` → “please select an Image”; **no type/size/auth** |
| Processing | `addslashes(tmp_name)` then `file_get_contents` → INSERT `IMAGE_TABLE (IRKEY,CATEGORY,CCNO,IMAGE)` → SELECT back to display |
| Files | `IMAGE_LIST.PHP` L5–L34; twin `MO_IMAGE_LIST.PHP` |
| Status | ACTIVE; **MAY corrupt** if `$image` binary interpolated into SQL string |

#### WF-IR-06 — Retrieve by name + father — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `RETRIEVE.HTML` → `RETRIEVE.PHP` |
| Rules | `NAME LIKE '%'+'$NAME'+'%'` AND same for father; join `IMAGE_TABLE` on IRKEY+CATEGORY |
| Files | `RETRIEVE.PHP` `$sql`, `$sql1` |
| DUPLICATE / DEAD-DANGEROUS | `RETRIEVE1.PHP` same retrieve **plus hardcoded backdoor login** (`FORMS` / `sa@…` → `LOGIN1.php`) |

---

### 22.7 JRMS / PDACT / Rowdy / Tower / Training

#### WF-JRMS-01 — Hub: recently released (two jails) — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME.html` → `JRMS_MAIN_PAGE1.PHP` |
| Input | None for default list; jquery-ui datepickers `yy-mm-dd` on page; crime-head dropdown from distinct `HEADOFCRIME` |
| Rules | `SET DATEFORMAT DMY`; `CONVERT(DATE,RELEASEDT) = MAX(RELEASEDT)` **and** `JAILNAME IN ('CHERLAPALLI','CHANCHALGUDA')` **and** `HEADOFCRIME!=''`; name split on `/` for IDPROOF; photo `CONVERT(IMAGE,PHOTO)`; Aadhaar↔IR flag |
| Files | `JRMS_MAIN_PAGE1.PHP` `$query`, `$sql9`–`$sql10`; `dbcontroller.php` for dropdown |
| MAY BE INCORRECT | `#TEMP` + `CONVERT(IMAGE)` + `SET DATEFORMAT` on PG; only two jails |

#### WF-JRMS-02 — Search by crime head + release dates — **ACTIVE** (from JRMS UI)

| Item | Detail |
| ---- | ------ |
| Trigger | JRMS forms → `JRMS_SEARCH.PHP` |
| Input | `CRIMEHEAD`, `FROM_DT`, `TO_DT` |
| Rules | `RELEASEDT BETWEEN` dates AND `HEADOFCRIME LIKE '%'+'$CRIMEHEAD'+'%'`; count releases per `UNIQUE_KEY` |
| Files | `JRMS_SEARCH.PHP` `$sql1`, `$sql11`, `$sql2` |
| DUPLICATE | `JRMS_DATEWISE_*`, `JRMS_SEARCH_NEW`, `*_MAHESH`, `*_OLD`, `*_PHP.PHP` — same `#TEMP` block |

#### WF-JRMS-03 — Unique key / IRKEY update — **ACTIVE write**

| Item | Detail |
| ---- | ------ |
| Trigger | `JRMS_UNIQUE_KEY_UPDATE.html` → `JRMS_UNIQUE_KEY_UPDATE.PHP` |
| Input | `CIN_NO` (comma list), `UNIQUE_KEY`, `IRKEY` |
| Processing | `str_replace(",", "','", $NUMBER1)` → `UPDATE JRMS_TOTAL_2012_TO_2017 SET UNIQUE_KEY, IRKEY, ASONDATE=GETDATE(), APP_OR_MANUAL='APPLICATION_ENTRY' WHERE CIN IN ('…')` |
| Output | “Updated” / “Not Updated”; refresh 30s |
| Files | `JRMS_UNIQUE_KEY_UPDATE.PHP` entire script |
| Status | ACTIVE unauthenticated multi-row update |

#### WF-PDACT-01 — Recent PDACT hub — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME.html` → `PDACT_MAIN_PAGE_SEARCH.PHP` |
| Rules | `top 10` from `PDACT_MAIN_TABLE` order by `Date_Of_Arrest` desc; join `FORMS..IMAGE_TABLE` else IRKEY `113769`; link `PDACT_MAIN.PHP?PDACT_KEY=` |
| Files | `PDACT_MAIN_PAGE_SEARCH.PHP` `$sql9`–`$sql10`; menu to name/MO/PS searches |

#### WF-PDACT-02 — Name / MO / PS search — **ACTIVE** (from PDACT menu)

| Handler | Input | Rule |
| ------- | ----- | ---- |
| `PDACT_SEARCH.PHP` | `NAME` | `NAME LIKE '%$number%'` |
| `PDACT_MO_SEARCH.PHP` | MO text | `CRIME_HEAD`/`MINOR_HEAD`/`MODUSOPERENDI` LIKE |
| `PDACT_PS_WISE_SEARCH_PHP.PHP` | PS | `PD_ACT_PS LIKE` |

All use `#temp` + placeholder image. **DUPLICATE** of hub query shape.

#### WF-ROWDY-01 — Rowdy sheeter by PS — **ACTIVE**

| Item | Detail |
| ---- | ------ |
| Trigger | `ROWDYSHEETER_PS_WISE_SEARCH.PHP` UI → POST `ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP` |
| Input | `POLICE_STATION` |
| Rules | `ROWDY_SHEETER_DATA1 WHERE POLICE_STATION LIKE '%$number%'`; image join + `113769` fallback |
| Files | `ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP` `$sql0`, `$sql1` |

#### WF-TWR-01 — Suspect in tower dump — **ACTIVE** (labelled under development)

```text
User → TOWER_HOME.HTML → SUSPECT_SEARCH.PHP (form posts to SUSPECT_SEARCH_TWR.PHP)
     → POST PHONE_NO, Police_station, CRIME_NO, YEAR, OFF_DATE, hh1/mm1/ss1, hh2/mm2/ss2
     → TWRMDB.TWRMDB_MASTER_CDAT filtered by dump key + offence place/date/time window
     → also CDATDUPL nickname header
```

| Item | Evidence |
| ---- | -------- |
| Files | `TOWER_HOME.HTML` hrefs; `SUSPECT_SEARCH.PHP` form `action="SUSPECT_SEARCH_TWR.PHP"`; `SUSPECT_SEARCH_TWR.PHP` `$PHONE_NO`…`$SS2`, `$sql1` time between min/max `#time`, `PLACE_DESCRIPTION='PLACE_OF_OFFENCE'` |
| Siblings | `OTHER_STATE_NUMBER.PHP` / `_TWR`, `INTER_TOWER_CALLS.PHP` / `_TWR`, `PRE_OFF_SEARCH.PHP` / `_TWR` |
| Status | ACTIVE if dump loaded; **DUPLICATE** root vs `_TWR`; **MAY BE INCORRECT** if `TWRMDB` empty on PG |
| HARDCODED | Offence place filter `'PLACE_OF_OFFENCE'` |

#### WF-TRAIN-01 — Training module 1 — **ACTIVE** (menu)

| Item | Detail |
| ---- | ------ |
| Trigger | `TRAINING_MODULE1.HTML` → `TRAINING_MODULE1.PHP` |
| DB | `TRAINING_DB` + QR pattern (same address/QR style as vehicle) |
| Status | ACTIVE if training tables exist; module 2 **UNUSED** from HOME |

---

### 22.8 Admin, upload, SQL console

#### WF-ADM-01 — Create user — **ACTIVE** (admin)

| Item | Detail |
| ---- | ------ |
| Trigger | `ADMIN_CREATE_USER.PHP` POST |
| Validation | username/password/fullname required; `LOWER(username)` uniqueness |
| Rules | `INSERT INTO logins (username, password, role, fullname)` **plaintext**; default role `user`; role lowercased |
| DB | Postgres `logins` via `audit_db()` (**not** MSSQL `LOGINS` unless same table) |
| Output | HTML success/error on same page |
| Files | `ADMIN_CREATE_USER.PHP` L13–L54 |
| MAY BE INCORRECT | Dual login stores (`FORMS.LOGINS` vs Postgres `logins`) — **Needs Verification** whether LOGIN.PHP and create-user hit the **same** table under shim |

#### WF-ADM-02 — SQL console — **ACTIVE** (admin)

| Item | Detail |
| ---- | ------ |
| Trigger | `HOME.html` → `ADMIN_SQL_CONSOLE.PHP` |
| Input | `sql_query`, optional `export`/`export_type` |
| Validation | Must match `/^select\b/i`; block `;` multi-statement; block insert/update/delete/drop/… keywords; wrap `SELECT * FROM ($q) AS query_run LIMIT 1000` |
| Processing | `$db->query($wrapped_query)` on audit PDO postgres; `audit_log` execute/export |
| Output | HTML grid or CSV/XLS |
| Files | `ADMIN_SQL_CONSOLE.PHP` export block + execute block |
| MAY BE INCORRECT / bypassable | CTE/`INTO`/functions not fully blocked |

#### WF-ADM-03 — Activity log UI — **ACTIVE** (admin)

Parameterized filters over `user_activity_logs` / sessions (`ADMIN_ACTIVITY_LOG.PHP`). Read-only admin reporting.

#### WF-UPL-01 — CDR/SDR document upload — **ACTIVE** (uploader)

```text
User → HOME DATA UPLOAD → admin_upload.php [audit_require_uploader]
  A) ajax_action=preview_cdr → save csv/xls/xlsx → exec scripts/cdr_preview.py → JSON
  B) ajax_action=approve_staging → curl POST :8088 …/approve + optional X-API-Key
  C) ajax get_tables / create_table / insert_data → legacy custom-table path (FORMS sqlsrv)
  D) cdr_file upload → document_processing_client.submitDocument(module, path, …)
       → FastAPI :8088 → document_jobs → worker cdr_import / sdr_import
  E) poll admin_upload_job_status.php → getJobStatus
  F) admin_upload_verify.php → staging preview/approve
```

| Item | Evidence |
| ---- | -------- |
| Operator map | `mapNetworkToOperator()` ids 2/15/12/4 |
| Preview validation | extension ∈ csv,xls,xlsx; `move_uploaded_file` to tmp |
| API | `cdr_upload_config.php` `getenv('CDR_API_URL') ?: 'http://127.0.0.1:8088'`; `document_processing_client.php` `submitDocument` / `getJobStatus` / `waitForJob` |
| Errors | JSON `{ok:false}` / RuntimeException “upload service … port 8088”; preview `exec` exit code |
| Files | `admin_upload.php`, `admin_upload_history.php`, `_job_status.php`, `_sync_jobs.php`, `_verify.php`, `excel_converter.php`, `js/sdr_resumable_upload.js`, `cdr-import-service/` |
| BROKEN sibling | `download_template.php` always 410 |
| MISSING DEP | FastAPI down → upload fails; `exec` disabled → preview fails; `db_config.php` missing → PHP fatals |
| NEEDS VERIFICATION | API key usage; whether legacy Section 1 custom-table insert is still used |

---

### 22.9 AJAX dropdowns (IR / migrant / JRMS)

| Endpoint | Input | Rule | Status |
| -------- | ----- | ---- | ------ |
| `GET_PS.PHP` | POST district/zone | SQL options from PS master via `dbcontroller.php` | ACTIVE if parent UI used; SQLi |
| `GET_YEAR.php` / `GET_CRNO.php` / `GET_DIVISION.PHP` | cascading POST | Same pattern | PARTIAL |
| root `get_state.php` | `myindex.php` | **MISSING** | BROKEN / DEAD demo |

---

### 22.10 Logic classification summary

#### Active business logic (menu + handler + rules execute)

Login/logout; SUM total/between/ISD/new/in-state/out-state; CALLS_BTWN_DATES; MOVEMENTS (+ two-number); CDATCNTS1; OTHERCDAT; bulk CDAT/address; ADDRESS; IMEI both directions; D&N loc (± dates); CELLID; vehicle; COMMON_CNTS; HABITUAL; MO search; IR search + dossier + IR section INSERTs + IMAGE_LIST + RETRIEVE; JRMS hub/search/update; PDACT hub/name/MO/PS; rowdy by PS; tower dump suspect (TWR); training 1; admin create user / SQL console / activity; CDR upload pipeline.

#### Partially working

- CALLS_BT_NOS / CALLS_TOT (logic yes, HOME commented)
- HOME_IMEI / Hawkeye (logic yes, not on HOME)
- IMEI_SEARCH vs IMEI_SEARCH_IN_PHONE (auth mismatch)
- CALLS_BTWN_DATES OPERATOR/STATE possibly unused in WHERE
- CELLID hybrid T-SQL + `ILIKE`
- JRMS/PDACT `#TEMP` on PG
- Upload preview if `exec` disabled
- `check_role.php` (JSON only)

#### Broken

- SUM1 / SUM2 / SUM_P handlers
- `download_template.php` 410
- `myindex.php` → missing `get_state.php`
- `demo.php.php` → missing root `ddtf.js`
- `db_config.php` absent from git (all shim/audit/upload)

#### Duplicate logic

- Junk-contact filter + address CASE across ~8 SUM/IMEI files
- Phone-prefix CASE vs `cdat_phone_prefix_key()`
- JRMS `#TEMP` block × ~15 files
- PDACT `#temp` + IRKEY `113769` × several
- Tower root vs `*_TWR.PHP`
- LOGIN vs LOGIN1; RETRIEVE vs RETRIEVE1
- Day/night hour predicates in `D&N_LOC.PHP` vs `D&N_BT_DTS.PHP`
- Filename twins `D&N` / `D%26N` / `DAY&NIGHTLOC`

#### Dead / unreachable (from CDAT menus)

`LOGIN_PAGE`, `chandu`, `untitled-1`, `notepad`, `desktop`, `jquerydynamic.php`, `curfewepass/**`, `old ir/**`, `new ir/**`, `TWRDB/**`, `ROUGH_TOWER/**`, `SUN/**`, most extra JRMS `*_MAHESH`/`*_OLD` unless linked internally.

#### Hardcoded rules

See §22.1 (day/night hours, junk 140%/duration, two jails, IRKEY 113769, operator maps, care numbers, name length >4, IMEI left-14, uploader roles, `APPLICATION_ENTRY`, etc.).

#### Unused / missing dependencies

- `CALLCENTER_NOS` (`OTHERCDAT.php`)
- `LOSTREPORT_HAWKEYE` tables (`IMEI_REQUEST_*`)
- `TRAINING_DB`, `CDAT_RTA`, `TWRMDB_MASTER_CDAT`, `ROWDY_SHEETER_DATA1`, `COMPLETE_MO_CLASSIFICATION`, `celltowerfiltered` + `calculatedistance`
- FastAPI `:8088`, `scripts/cdr_preview.py`, python3, `pyodbc` (SDR)
- `sql/celltower_geo.sql` for nearest-tower

#### Logic that may produce incorrect results

1. Day **and** night both include **05:00–07:00** (`D&N_LOC.PHP` `$dayPred`/`$nightPred`).
2. Postgres shim vs `isnumeric`, `FOR XML PATH`, `CONVERT(IMAGE)`, `SET DATEFORMAT DMY`, `SELECT TOP`, `#TEMP`.
3. Placeholder photo **IRKEY 113769** shown as if it were the subject.
4. SUM total uses `CDAT_DETAILS`; dated/new/state summaries use `CDAT_DETAILS1` / `CDATPCSUSPECT` — **different universes**.
5. Vehicle `LIKE '%'+reg` matches partial plates.
6. `VEHICLE_SOURCE` interpolated as SQL identifier.
7. Hawkeye 14-digit IMEI prefix can merge distinct devices.
8. IR name search silently drops names shorter than 5 characters (spaces ignored).
9. JRMS hub hides all jails except Cherlapalli & Chanchalguda.
10. Login create-user on Postgres `logins` vs LOGIN.PHP `FORMS.LOGINS` — possible split-brain.

#### Requires manual verification

- Production prepend vs native sqlsrv (changes every `#TEMP` outcome).
- Whether `CDAT_DETAILS` and `CDAT_DETAILS1` are maintained as views/tables on live PG.
- Whether investigators rely on 05–07 overlap (bug vs intended “dawn”).
- Whether IRKEY `113769` is a known dummy record.
- HOME_IMEI / CAF / migrant / VBR still used.
- FastAPI auth + `X-API-Key`.
- Same physical table for `LOGINS` and `logins`.
- Tower dump tables populated.

---

### 22.11 End-to-end map (business, not infrastructure)

```text
Analyst (often unauthenticated)
  ├─ CDAT HOME.html
  │    ├─ Summary*     → CDAT_DETAILS / CDAT_DETAILS1 / CDATPCSUSPECT
  │    │                 + junk rules + CDATADDRESS/PHONEAREA
  │    ├─ Calls/Movements → CDATPCSUSPECT ± cdr_enrichment_sql + towers
  │    ├─ CDAT Cnts    → others ∩ CDATSUSPECT  |  Others → exclude CALLCENTER_NOS
  │    ├─ IMEI         → CDATPCSUSPECT by IMEI or PHONE
  │    ├─ Address/Veh/Cell/Common/Habitual/MO → masters + QR
  │    ├─ Day/Night    → TOP 10 towers by hour window (05–22 / 22–07)
  │    ├─ IR search    → IR_PARTICULARS ⋈ OFFENCE_DETAILS → IR.PHP dossier
  │    ├─ JRMS/PDACT/Rowdy → jail/PDACT/rowdy tables ± IR/Aadhaar/photo
  │    ├─ Tower dump   → TWRMDB_MASTER_CDAT ∩ offence time/place
  │    └─ Upload*      → [poweruser/admin] FastAPI → cdatpcsuspect load
  └─ LOGIN.HTML → LOGINS plaintext → HOME_IR.HTML
       ├─ IRREPORT / BRIEF_FACTS / OFFENCE_* / IMAGE_LIST … INSERT FORMS
       └─ RETRIEVE SELECT (+ RETRIEVE1 backdoor)

Admin* → SQL console / create user / activity log  (Postgres audit DB)
```

\* Only upload + three admin pages enforce roles. All other business logic above runs **without** a login check on `main`.

---

## Confidence

**Overall: Medium–High**

| High confidence | Medium | Low / Needs Verification |
| --------------- | ------ | ------------------------ |
| File tree, menus, login code, auth-gate call sites, sqlsrv_compat mapping, nginx, form→PHP missing SUM1/2/P, dump dirs unlinked, plaintext LOGINS, RETRIEVE1 backdoor presence, no Composer, FastAPI wiring in PHP | Whether every unlinked PHP is unused; completeness of T-SQL translation; FastAPI auth; production uses shim; table existence on live PG | Traffic volumes; which HOME_IMEI path users follow; curfewepass separate deploy; cookie flags; CDN SRI; exact sa password reuse in infra |

### Items Requiring Manual Verification

1. Production runtime: `auto_prepend sqlsrv_compat.php` vs native `sqlsrv` + MSSQL.
2. Presence and values of server `db_config.php` / `.env` (do not commit).
3. Network exposure of `:8020` and `/document-api/`.
4. systemd units actually enabled (`cdr-import-service` and index jobs).
5. Live PostgreSQL table list vs PHP table names (especially CAF, TRAINING_DB, Hawkeye, VBR, CIS).
6. Access logs for `curfewepass/`, `old ir/`, `TWRDB/`, CAF, migrant, HOME_IMEI.
7. php.ini session cookie flags and `disable_functions`.
8. Whether investigators still use `LOGIN1` / `SUM_HOME2` / `SUM_HOME12` / `SUM_HOME_P`.
9. FastAPI authentication model (`cdr-import-service/app/main.py` auth dependencies).
10. Whether `JRMS_UNIQUE_KEY_UPDATE` and `IMAGE_LIST` are used in daily IR workflow (unauthenticated writes).
11. Whether day/night overlap 05:00–07:00 (`D&N_LOC.PHP` `$dayPred`/`$nightPred`) is intended.
12. Whether placeholder photo `IRKEY='113769'` is a known dummy record.
13. Whether `CDAT_DETAILS` vs `CDAT_DETAILS1` vs `CDATPCSUSPECT` are kept in sync on live PG.
14. Whether `ADMIN_CREATE_USER` Postgres `logins` is the same table `LOGIN.PHP` reads as `FORMS.LOGINS`.

---

**Audit Scope:** Current `main` branch  
**Audit Type:** Static Codebase / Architecture / Dependency / Endpoint / Security / Business-Logic Audit  
**Code Changes Made:** None  
**Confidence:** Medium–High — static evidence is strong for identity, menus, auth gaps, security patterns, and encoded investigation rules (junk filters, day/night hours, jail list, IR name length); runtime behaviour of the MSSQL→Postgres shim and live table inventory were not executed against production.
