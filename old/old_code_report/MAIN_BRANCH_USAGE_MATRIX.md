# C-DAT Main Branch — Usage Matrix

**Branch:** `main` @ `dc47eca`  
**Method:** Static reference tracing (`git grep` / menu `href` / `form action` / `include`/`require` / nginx / systemd). No production access logs.  
**Code changes:** None  

Classification rules:

- **ACTIVE** — evidence of execution path (menu, form, nginx, include from an active file, systemd unit referenced by PHP, or auto_prepend).
- **UNUSED** — no root-app href/include/curl/exec path found.
- **POTENTIALLY UNUSED** — no confirmed path; could still be bookmarked or linked from result HTML.
- **BROKEN** — referenced but missing or hard-fails.
- **DEAD** — demo/junk/misnamed.
- **CONFIGURED** — ops/CLI present, not called from PHP UI.
- **NEEDS VERIFICATION** — static analysis insufficient.

---

## 1. Feature usage

See also `MAIN_BRANCH_APPLICATION_AUDIT_REPORT.md` §3.

| Feature | Status | Evidence |
| ------- | ------ | -------- |
| CDAT HOME menu + Summary / Call Details / CDAT / IMEI / Address / Day-Night / Offenders / Others | ACTIVE | `HOME.html` hrefs + matching PHP |
| IR login / IR home / IR section forms | ACTIVE | `LOGIN.PHP` → `HOME_IR.HTML` hrefs |
| JRMS / PDACT / Rowdy sheeter | ACTIVE | `HOME.html` confirm_selection links |
| Tower dump hub | ACTIVE | `HOME.html` → `TOWER_HOME.HTML` → four PHP links |
| Trainings module 1 | ACTIVE | `HOME.html` → `TRAINING_MODULE1.HTML` |
| Data upload + job poll/verify | ACTIVE | `HOME.html` → `admin_upload.php` → curl `:8088` |
| Admin activity + SQL console | ACTIVE | `HOME.html` → `ADMIN_*.PHP` + `audit_require_admin` |
| Create user | ACTIVE (gated) | file + admin gate; HOME_IR link commented |
| IMEI Hawkeye (HOME_IMEI) | POTENTIALLY UNUSED | Menu file exists; **not** linked from `HOME.html` |
| CAF / migrant / CIS / VBR / NBWS / ALLDATA / NAMESEARCH / DUMP extra | POTENTIALLY UNUSED | Handlers exist; no HOME href |
| Call Details Total / Calls Btwn Two Nos | POTENTIALLY UNUSED | **Commented** on HOME; handlers exist |
| Summary SUM1/SUM2/SUM_P variants | BROKEN | HTML forms → missing PHP |
| Template download | BROKEN | `download_template.php` always 410 |
| Curfew e-pass CPMS | UNUSED (from CDAT) | `curfewepass/` no root reference |
| image_migrate / Citus migrate | CONFIGURED | CLI/systemd only |
| Demo pages (chandu, myindex, login_page, untitled-1) | DEAD | no menu; some broken AJAX |

---

## 2. File usage — confirmed active (root application)

| File / glob | Why active |
| ----------- | ---------- |
| `HOME.html`, `HOME_IR.HTML`, `IR_MODULE.HTML`, `TOWER_HOME.HTML`, `SUM_HOME.html`, `LOGIN.HTML` | Menus / nginx index / login |
| `LOGIN.PHP`, `LOGOUT.PHP`, `activity_logger.php` | Auth chain |
| `sqlsrv_compat.php` | nginx `auto_prepend_file` |
| `db_config.example.php` | Template for required runtime file |
| `SUM.PHP`, `SUM_BTWN_DATES.PHP`, `SUM_ISD_CNTS.PHP`, `SUM_NEW_NO.PHP`, `SUM_IN_STATE.PHP`, `SUM_OUT_STATE.PHP` | HOME summary menu + form actions |
| `CALLS_BTWN_DATES.PHP`, `MOVEMENTS.PHP`, `MOVEMENTS_BETWEEN_TWO_NUMBERS.PHP`, `MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.PHP` | HOME call-details menu |
| `CDATCNTS1.php`, `BULK_CDAT_CONTACTS.PHP`, `OTHERCDAT.php` | HOME CDAT menu |
| `IMEI_SEARCH.PHP`, `IMEI_SEARCH_IN_PHONE.PHP` | HOME IMEI menu |
| `ADDRESS.PHP`, `BULK_ADDRESS.php` | HOME address menu |
| `D&N_LOC.PHP`, `D&N_BT_DTS.PHP` (+ `d%26n_*` twins) | HOME day/night menu |
| `HABITUAL.PHP`, `CELLID_SEARCH.php`, `VEHICLE_SEARCH.PHP`, `VEHICLE_SEARCH_CRITERIA.PHP`, `COMMON_CNTS.PHP` | HOME others |
| `ADMIN_ACTIVITY_LOG.PHP`, `ADMIN_SQL_CONSOLE.PHP`, `admin_upload.php` (+ history/status/sync/verify) | HOME others + upload |
| `IR_SEARCH.PHP`, `OFFENDER_SEARCH_BY_MO.PHP`, `JRMS_MAIN_PAGE1.PHP`, `PDACT_MAIN_PAGE_SEARCH.PHP`, `ROWDYSHEETER_PS_WISE_SEARCH.PHP` + `_PHP.PHP` | HOME lower links |
| `TRAINING_MODULE1.PHP` | HOME trainings |
| `SUSPECT_SEARCH.PHP`, `OTHER_STATE_NUMBER.PHP`, `INTER_TOWER_CALLS.PHP`, `PRE_OFF_SEARCH.PHP` | TOWER_HOME |
| `*_TWR.PHP` (suspect/other_state/inter_tower/pre_off) | Form-action scan includes `SUSPECT_SEARCH_TWR.PHP` |
| IR section PHP: `IRREPORT`, `BRIEF_FACTS`, `FAMILY_HISTORY`, `OFFENCE_DETAILS`, `PREVIOUS_OFFENCE_DETAILS`, `DISPOSAL_OF_PROPERTY`, `LOCAL_CONTACTS`, `RELATION_WITH_OTHER_ASSOCIATES_AND_GANGS`, `MULAKATH_ENTRY`, `IMAGE_LIST`, `RETRIEVE`, `IR.PHP` | HOME_IR hrefs / IRKEY links |
| `FP_LIST.PHP`, `IR_SEARCH_BY_HEAD_GENDER.PHP`, `IR_SEARCH_TEST.PHP` | IR_MODULE hrefs |
| `document_processing_client.php`, `cdr_upload_config.php`, `cdr_upload_parser.php`, `cdr_enrichment_sql.php`, `excel_converter.php`, `upload_verification_service.php` | Required by upload / SUM enrichment |
| `sql_safe.php` | Required by CALLDETAILS, CALLS_BTWN_DATES, CELLID_SEARCH, D&N_LOC, IMEI_SEARCH*, NEAREST/NEAR_BY, rta_nike |
| `dbcontroller.php` | GET_PS / GET_YEAR / GET_DIVISION / GET_CRNO |
| `check_role.php` | Spry optional JSON |
| `SpryAssets/*` (MenuBar js/css/gif) | ~130 pages |
| `css_sparkle1.css` | HOME / IR chrome |
| `IMAGES/TOPBORDER.JPG`, `IMAGES/BORDER.jpg`, `IMAGES/TOWER2.jpeg`, `IMAGES/ANALYSIS1.jpg` (and similar chrome) | img/background |
| `jquery-ui-1.10.4.custom/` (datepicker css/js, not full development-bundle) | ~50 pages |
| `qrcode/php/qr_img.php` | ADDRESS, CELLID, VEHICLE, TRAINING_MODULE1, SUSPECT_SEARCH_TWR |
| `DROP DOWN FILTER/ddtf.js`, `w3.css` | ADDRESS, VEHICLE_SEARCH_CRITERIA, IMEI_REQUEST_MOVEMENTS |
| `js/sdr_resumable_upload.js` | admin_upload.php |
| `cdr_import/**`, `document_processing/**`, `sdr_import/**`, `cdr-import-service/**` | FastAPI + preview `exec` |
| `scripts/cdr_preview.py`, `scripts/excel_to_csv.py`, `scripts/run_cdr_import_service.sh`, `scripts/systemd/cdr-import-service.service` | Upload path |
| `cdat-web.nginx.conf`, `.htaccess`, `.env.example` | Deploy |
| `sql/*.sql` (cdr_import, document_processing, upload_*, indexes, celltower_geo, wire_distributed, postgres_cdat_stability) | Schema/ops for live PG path |

---

## 3. File usage — potentially unused (root PHP)

No HOME / HOME_IR / IR_MODULE / TOWER_HOME / SUM_HOME / HOME_IMEI href found. **May still be linked from inside result pages.** Do not delete without access-log proof.

| Basename | Notes |
| -------- | ----- |
| `CAF_SEARCH` | ftp:// internal URL |
| `MIGRANT_LABOURS_*` | uses dynamicdependentbox JS |
| `CIS_DATA_NAME_SEARCH*` | |
| `VBR_SEARCH` | ALL_ILD_DATA_2012 + `#TEMP` |
| `NBWS` | |
| `ALLDATA`, `ALLDATA_SEARCH` | |
| `NAMESEARCH`, `NAME_SEARCH` | |
| `DUMP`, `DUMP_SEARCH`, `DUMP_ANALYSIS` | dump_analysis also demo-like |
| `SUM_ALLDB` | |
| `CALLS_TOT`, `CALLS_BT_NOS`, `CALLDETAILS`, `CALLS_BTWN_DATES1` | some commented on HOME |
| `MOVEMENTS_IN_PARTICULAR_PLACE` | |
| `CDATCNTS`, `CDATCNTS2`, `BULK_CDAT_CONTACTS1` | extra copies |
| `COMMON_CNTS1` | |
| `IMEI_REQUEST_*`, `MAXSPENTLOCATION_IMEI`, `D&N_LOC_IMEI`, `HOME_IMEI` | only on HOME_IMEI hub |
| `NEAREST_CELLIDS`, `NEAR_BY_CELLTOWERIDS` | session-gated; not on HOME |
| `VEHICLE_SEARCH1`, `VEHICLE_CHAS_SEARCH`, `VEHICLE_ENG_SEARCH` | extra vehicle |
| `rta_nike` | |
| `CDAT_IRFORM`, `IR_NDPS`, `IR_NDPS1`, `IR_SEARCH__OLD`, `IR_SEARCH_BY_HEAD` (without gender) | |
| `BULK_IRKEY*`, `BULK_IRSEARCH_IRKEY*`, `BULK_GANG_ID*` | |
| `MO_IMAGE_LIST` | |
| `ANALYSIS_ABSTRACT` | |
| `OFFENCE_DETAILS1`, `PREVIOUS_OFFENCE_DETAILS1` | |
| `RETRIEVE1` | dangerous legacy |
| `HOME_IR_MODIFIED`, `HOME_MODIFIED`, `HOME_JRMS`, `TOWERDUMP_HOME` | extra homes |
| Most extra `JRMS_*` copies (`_MAHESH`, `_OLD`, `_PHP`, datewise1, name_search vs php, …) | JRMS_MAIN_PAGE1 is the HOME entry |
| Extra `PDACT_*` beyond MAIN_PAGE_SEARCH | |
| `TRAINING_MODULE2` | |
| `wanted1` | only SUM_HOME |
| `OFFENDER_FD` | |
| `GET_*` dropdowns | only if migrant/IR JS used |
| `LOGIN1`, `LOGIN_PAGE` | legacy/demo |

---

## 4. File usage — confirmed unused / dump trees

| Path | Evidence |
| ---- | -------- |
| `curfewepass/` | No root `href`/`include`/`curl`/`exec` |
| `old ir/` | No root reference |
| `new ir/` | No root reference (live IR is root `HOME_IR.HTML`) |
| `TWRDB/` | No `TWRDB/` href from root; tower UI is root `TOWER_HOME.HTML` |
| `ROUGH_TOWER/` | No reference |
| `SUN/` | No reference (`ir_search_by_head1` copy) |
| `_notes/` | Dreamweaver notes |
| `jquery-ui-1.10.4.custom/development-bundle/` (demos, docs, AUTHORS, …) | App only needs `js/` + `css/` datepicker subset |
| `qrcode/` sample images / extra readmes | Only `qrcode/php/qr_img.php` referenced |
| `dynamicdependentbox/` except `jquerydynamic.js` | Migrant page includes JS; rest is country/state demo dump |
| `image_migrate/` | No PHP call |
| `distributed_migrate/` | No PHP call (systemd/CLI only) |
| `scripts/systemd/cdat-*-index*.service`, `cdataddress-citus-migration.service`, `cellids-citus-migration.service`, `cdatpcsuspect-imei-index.service`, `cdat-mssql-migration-index.service` | CONFIGURED ops; not UI |
| `curfewepass` Bootstrap 3.1.1 + DataTables | Unused by CDAT UI |

---

## 5. Dead / junk files (root)

| File | Evidence |
| ---- | -------- |
| `chandu.php` | Symlink to `CHANDU.htm` stub |
| `untitled-1.php` | Untitled Dreamweaver page |
| `notepad.php`, `desktop.php` | Non-app |
| `login_page.php` | Modal demo, not LOGIN.HTML |
| `jquerydynamic.php` | Misnamed jQuery 2.1.1 |
| `sample.php` | Symlink to `sample.gif` |
| `dbcontroller.php.php` | Double-extension copy |
| `demo.php.php` | DDTF demo; missing root `ddtf.js` |
| `myindex.php` | Country/state demo; missing `get_state.php` |
| `css_sparkle1.php`, `style.php` | CSS served as php / duplicates |

---

## 6. Broken references

| Reference | From | Status |
| --------- | ---- | ------ |
| `SUM1.php` | `SUM_HOME12.html` form | Missing handler |
| `SUM2.php` | `SUM_HOME2.html` form | Missing handler |
| `SUM_P.PHP` | `SUM_HOME_P.html` form | Missing handler |
| `get_state.php` (root) | `myindex.php` AJAX | Missing |
| `ddtf.js` (root) | `demo.php.php` | Missing (exists under `DROP DOWN FILTER/`) |
| `db_config.php` | `sqlsrv_compat.php`, `activity_logger.php`, Python config | Not in git; `DB_CONFIG.PHP` symlink dangling in repo |
| `download_template.php` | upload feature | Always HTTP 410 |

---

## 7. API / service usage

| Service | Status | Evidence |
| ------- | ------ | -------- |
| FastAPI document service `:8088` | ACTIVE (if process up) | `document_processing_client.php`, nginx `/document-api/`, `cdr_upload_config.php` default URL, `js/sdr_resumable_upload.js` |
| `exec` `scripts/cdr_preview.py` | ACTIVE | `admin_upload.php` |
| `exec` `scripts/excel_to_csv.py` | ACTIVE | `excel_converter.php` |
| systemd `cdr-import-service.service` | CONFIGURED + intended USED | `scripts/run_cdr_import_service.sh` |
| Older `scripts/cdr_import_worker.sh` | CONFIGURED / possibly superseded | No PHP reference |
| `image_migrate` CLI | CONFIGURED UNUSED by UI | No PHP reference |
| `distributed_migrate` + Citus units | CONFIGURED UNUSED by UI | No PHP reference |
| Docker compose `cdr-import-service` | CONFIGURED | Alternate to host systemd |
| WhatsApp / SMS / SMTP / Maps / Payments / OAuth | UNUSED / absent | No root matches |
| `ftp://192.168.x.x` in `CAF_SEARCH.PHP` | POTENTIALLY UNUSED feature; BROKEN if host gone | Hardcoded FTP URL builder |
| CDN FA 6.4.0 / SheetJS 0.18.5 / PapaParse 5.4.1 | ACTIVE on upload pages only | `admin_upload.php` script tags |

---

## 8. Dependency usage

| Dependency | Declared | Used? | Where | Risk |
| ---------- | -------- | ----- | ----- | ---- |
| PHP 8.3-fpm | nginx conf | YES | all `*.php` | High if missing pdo_pgsql |
| `pdo_pgsql` | implied | YES | shim, logger, upload verify | Critical |
| `curl` | implied | YES | document client | Upload fails without |
| `gd` | qrcode comments | YES | `qrcode/php/qr_img.php` | QR pages |
| `sqlsrv` native | legacy code | NO if prepend on | — | Required only without shim |
| Python 3.10+ | pyproject | YES | FastAPI + preview | Upload |
| fastapi / uvicorn / python-multipart | requirements.txt | YES | cdr-import-service | |
| psycopg2-binary | both req files | YES | import + API | |
| pyodbc | requirements-cdr-import.txt | SDR/MSSQL paths | sdr_import, image_migrate | CONFIGURED |
| Spry MenuBar | SpryAssets | YES | menus | Unmaintained |
| jQuery UI 1.10.4 | local tree | YES | datepickers | CVE-era |
| jQuery 2.x | jquerydynamic / DROP DOWN FILTER | YES | subset | CVE-era |
| w3.css | symlink | YES | few tables | Low |
| qrcode PHP | local | YES | several reports | Old GD |
| Bootstrap 3.1.1 | curfewepass | NO (CDAT) | — | Unused |
| DataTables | curfewepass | NO (CDAT) | — | Unused |
| Composer packages | none | n/a | — | — |
| simple-datatables | not on main | NO | — | — |
| Font Awesome / SheetJS / PapaParse CDN | admin_upload | YES | upload UI | CDN availability / no SRI verified |

---

## 9. Database object usage

### 9.1 Engines

| Object | Status | Evidence |
| ------ | ------ | -------- |
| MSSQL instance names in PHP | LEGACY DECLARED | `$serverName = 'CPHYDERABAD1\DAU_HYD_2023'` etc. Ignored by shim |
| PostgreSQL `postgres` | ACTIVE (this tree) | `__sqlsrv_dbname` maps all legacy names here; `db_config.example.php` |
| PostgreSQL `distributed_db` | CONFIGURED | schema dumps + FDW `wire_distributed_reference_data.sql` + migrators |
| `cdat_db` / `ai_copint_db` dumps | UNKNOWN | schema dumps only; not in shim map as separate live targets |

### 9.2 Tables (root PHP)

| Table | R | I | U | D | Used by (examples) | Status |
| ----- | - | - | - | - | ------------------ | ------ |
| CDATPCSUSPECT | Y | Y* | — | — | most CDR reports; *import pipeline insert | ACTIVE |
| CDAT_DETAILS / CDAT_DETAILS1 | Y | — | — | — | SUM.PHP, SUM_ISD_CNTS | ACTIVE |
| CDATSUSPECT | Y | — | — | — | several reports | ACTIVE |
| CDATADDRESS | Y | — | — | — | ADDRESS + shim LATERAL | ACTIVE |
| ADDRESS_OTHER_STATE | Y | — | — | — | enrichment / other-state | ACTIVE |
| CDATCELLTOWERAREANEW | Y | — | — | — | CELLID / nearest | ACTIVE |
| CDATPHONEAREA | Y | — | — | — | enrichment | ACTIVE |
| CDAT_RTA | Y | — | — | — | VEHICLE_* | ACTIVE |
| LOGINS / logins | Y | Y | — | — | LOGIN, ADMIN_CREATE_USER | ACTIVE |
| IR_PARTICULARS | Y | Y | — | — | IR.* | ACTIVE |
| IMAGE_TABLE | Y | Y | — | — | IMAGE_LIST, IR display | ACTIVE |
| MO_IMAGE_TABLE | Y | Y | — | — | MO_IMAGE_LIST | POTENTIALLY UNUSED UI |
| SUSPECT_IMAGE_TABLE | Y | — | — | — | some offender/tower | PARTIAL |
| OFFENCE_DETAILS | Y | Y | — | — | IR + bulk IR | ACTIVE |
| PREVIOUS_OFFENCE_DETAILS | Y | Y | — | — | HOME_IR | ACTIVE |
| BRIEF_FACTS | Y | Y | — | — | HOME_IR | ACTIVE |
| FAMILY_HISTORY | Y | Y | — | — | HOME_IR | ACTIVE |
| DISPOSAL_OF_PROPERTY | Y | Y | — | — | HOME_IR | ACTIVE |
| LOCAL_CONTACTS_FACILITATORS | Y | Y | — | — | HOME_IR | ACTIVE |
| RELATIONSHIP_WITH_OTHER_ASSOCIATES | Y | Y | — | — | HOME_IR | ACTIVE |
| MULAKATH_ENTRY | Y | Y | — | — | HOME_IR | ACTIVE |
| ANALYSIS_ABSTRACT | Y | Y | — | — | ANALYSIS_ABSTRACT.PHP | POTENTIALLY UNUSED UI |
| JRMS_TOTAL_2012_TO_2017 | Y | Y | Y | — | JRMS_* + UNIQUE_KEY_UPDATE | ACTIVE |
| PDACT_MAIN_TABLE | Y | Y | — | — | PDACT_* | ACTIVE |
| PDACT_PRESS_NOTES_TABLE | — | Y | — | — | PDACT_PRESS_NOTES | POTENTIALLY UNUSED UI |
| ROWDY_SHEETER_DATA1 | Y | — | — | — | rowdysheeter | ACTIVE |
| LOST_REPORT_CDR_DATA | Y | — | — | — | IMEI_REQUEST_* / D&N_LOC_IMEI | POTENTIALLY UNUSED hub |
| MIGRANT_LABOUR_TABLE | Y | Y | — | — | MIGRANT_* | POTENTIALLY UNUSED UI |
| CIS_COMPLETE_DATA | Y | — | — | — | CIS_DATA_* | POTENTIALLY UNUSED UI |
| NBWS_VERIFY_DATA_IMPORTANT | Y | — | — | — | NBWS | POTENTIALLY UNUSED UI |
| COMPLETE_MO_CLASSIFICATION | Y | — | — | — | offender MO | ACTIVE/PARTIAL |
| user_sessions | Y | Y | Y | — | activity_logger | ACTIVE |
| user_activity_logs | Y | Y | — | — | logger + ADMIN_ACTIVITY_LOG | ACTIVE |
| upload_activity_logs | Y | Y | Y | — | upload pipeline | ACTIVE |
| upload_approval_queue | Y | Y | Y | — | verify/approve | ACTIVE |
| upload_staging_batches | Y | — | Y | — | staging | ACTIVE |
| document_jobs | Y | — | Y | — | FastAPI + PHP poll | ACTIVE |
| TWRMDB_MASTER_CDAT | Y | — | — | — | tower dump | ACTIVE |
| PS_NAMES / STATES / COUNTRIES / CITIES / JAIL | Y | — | — | — | dropdowns / JRMS | PARTIAL |
| tbladmin / tblpass / tblcategory | Y | Y | Y | — | **curfewepass only** | UNUSED by CDAT |
| MSSQL `#TEMP` / `#TT` / `#RESULT` | Y | Y | Y | — | JRMS/PDACT/SUM/D&N/VBR | ACTIVE dialect; PG via shim |

\* Inserts into `cdatpcsuspect` come from Python import, not typical search PHP.

### 9.3 Tables referenced but possibly missing on live PG

**Needs Verification** against production `\dt`:

- CAF / `CAFs` objects (`CAF_SEARCH.PHP`)
- `TRAINING_DB` objects
- `ALL_ILD_DATA_2012` / VBR temps
- Hawkeye `LOST_REPORT_CDR_DATA`
- `CIS_COMPLETE_DATA`
- `NBWS_VERIFY_DATA_IMPORTANT`
- `ROWDY_SHEETER_DATA1`
- CPMS tables if someone hits `curfewepass` under same DB

### 9.4 SQL files vs runtime

| SQL file | Status |
| -------- | ------ |
| `sql/cdr_import_schema.sql` | USED by import design |
| `sql/document_processing_schema.sql` | USED |
| `sql/upload_*.sql` | USED by verify pipeline |
| `sql/cdatpcsuspect_*index.sql` | CONFIGURED (systemd index jobs) |
| `sql/celltower_geo.sql` | USED by nearest-tower pages if deployed |
| `sql/wire_distributed_reference_data.sql` | CONFIGURED / likely USED if address FDW live |
| `sql/cpms_postgres.sql` | UNUSED by CDAT UI |
| `sql/schema_dumps/*` | SNAPSHOT only (2026-07-30) |
| `postgress_schema.sql` (root typo filename) | SNAPSHOT / UNKNOWN vs live |

---

## 10. Include / caller matrix (critical libs)

| Library | Included by | Callers of those pages |
| ------- | ----------- | ---------------------- |
| `activity_logger.php` | LOGIN, LOGIN1, LOGOUT, ADMIN_*, admin_upload*, CALLDETAILS, CALLS_BTWN_DATES, IMEI_SEARCH, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS, SUM.PHP (log only), others that `require` it | Menus above |
| `sqlsrv_compat.php` | **all** `.php` via nginx prepend (if conf deployed) | Entire app |
| `sql_safe.php` | 9 report files listed in §2 | Some HOME, some not |
| `dbcontroller.php` | GET_PS, GET_YEAR, GET_DIVISION, GET_CRNO | JS on migrant/IR forms |
| `document_processing_client.php` | admin_upload* | HOME DATA UPLOAD |
| `cdr_enrichment_sql.php` | SUM.PHP (and possibly others) | SUM_HOME |

---

## 11. Proof notes

- **Do not treat “not in HOME.html” as unused.** Example: `IR.PHP?IRKEY=` is opened from habitual/IR search result HTML; `JRMS_UNIQUE_KEY_UPDATE.PHP` from JRMS forms.
- **Do not treat dump directories as delete-safe** without HTTP access logs — they remain inside nginx `root` and are URL-reachable (`try_files $uri`).
- **Case variants** of an ACTIVE file are ACTIVE aliases (symlinks), not extra features.
- Runtime **Needs Verification:** whether production enables `auto_prepend`; without it, `sqlsrv_*` either fatals (no extension) or hits real MSSQL.

---

**Audit Scope:** Current `main` branch  
**Audit Type:** Static usage / dead-code / dependency / DB-object matrix  
**Code Changes Made:** None  
**Confidence:** High for HOME-linked features and dump-dir isolation; Medium for unlinked PHP; Low for live PG table existence.
