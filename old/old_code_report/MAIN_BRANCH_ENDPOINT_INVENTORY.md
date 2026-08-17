# C-DAT Main Branch — Endpoint / Route Inventory

**Branch:** `main` @ `dc47eca`  
**Type:** Static inventory (`git ls-tree` / `git show` / `git grep`)  
**Code changes:** None  
**Note:** Almost every handler exists as six case/extension variants (`.php` `.PHP` `.html` `.HTML` `.htm` `.HTM`), often **git symlinks** to one blob. This inventory lists **canonical unique basenames** (case-insensitive) unless a variant is a distinct handler.

**Routing:**

| Layer | File | Behavior |
| ----- | ---- | -------- |
| nginx | `cdat-web.nginx.conf` | listen **8020**; `root /mnt/storage1/cdat-web`; `index HOME.html HOME.HTML index.html index.php`; rewrite `*.PHP` → `*.php`; FastCGI php8.3-fpm; `auto_prepend_file=…/sqlsrv_compat.php`; `/document-api/` → `http://127.0.0.1:8088/`; `client_max_body_size 750G` |
| Apache | `.htaccess` | If `*.PHP` missing, map to `*.php` |
| Front controller | — | **None.** No `index.php` on `main`. |

**Auth column values:** `admin` = `audit_require_admin()`; `uploader` = `audit_require_uploader()`; `session` = `audit_require_session()`; `none` = no gate found; `sets session` = login.

**Status:** ACTIVE (menu/form/nginx evidence) · PARTIAL · UNUSED · BROKEN · DEAD · UNKNOWN

---

## A. Infrastructure / proxy

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/` | GET | nginx index → `HOME.html` | CDAT main menu | — | none | Browser | ACTIVE |
| `*.PHP` | * | rewrite → `*.php` | Case fix | — | n/a | nginx/Apache | ACTIVE |
| `/document-api/*` | * | proxy `127.0.0.1:8088/` | CDR/SDR document API | multipart/JSON | **Needs Verification** (localhost assumed) | `admin_upload.php`, `js/sdr_resumable_upload.js`, `document_processing_client.php` | ACTIVE if service up |
| `/. *` | * | nginx `deny all` | Hide dotfiles | — | deny | — | ACTIVE |

---

## B. Authentication

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/LOGIN.HTML` | GET | `LOGIN.HTML` | Login form | — | none | `HOME.html` “IR FORMS”, logout redirect | ACTIVE |
| `/LOGIN.PHP` | POST | `LOGIN.PHP` | Validate `LOGINS`, start audit session | `USERNAME`, `PASSWORD` | none (creates session) | `LOGIN.HTML` `action="LOGIN.PHP"` | ACTIVE |
| `/LOGIN1.HTML` | GET | `LOGIN1.HTML` | Alternate login UI | — | none | RETRIEVE1 redirect path | PARTIAL / LEGACY |
| `/LOGIN1.PHP` | POST | `LOGIN1.PHP` | Same as login; **string SQL** | `USERNAME`, `PASSWORD` | none | `LOGIN1.HTML`; `RETRIEVE1.PHP` | LEGACY |
| `/LOGIN_PAGE.*` | GET | `LOGIN_PAGE.php` | Unrelated modal/JS demo | — | none | **no CDAT menu** | DEAD |
| `/LOGOUT.PHP` `/logout.php` | GET | `LOGOUT.PHP` | Destroy session, audit logout | cookie | session optional | `HOME.html`, `HOME_IR.HTML` | ACTIVE |
| `/check_role.php` | GET | `check_role.php` | JSON `{is_admin, role}` | cookie | none (returns false if empty) | Spry JS (cosmetic) | PARTIAL |

---

## C. Homes / menus (static HTML, often also `*.php` symlink)

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/HOME.html` | GET | `HOME.html` | CDAT Spry menu | — | none | nginx `/`, self | ACTIVE |
| `/HOME_IR.HTML` | GET | `HOME_IR.HTML` | IR forms menu | — | none | `LOGIN.PHP` redirect; `HOME.html` | ACTIVE |
| `/HOME_IR_MODIFIED.*` | GET | `HOME_IR_MODIFIED.html` | Alternate IR home | — | none | **not on HOME** | UNKNOWN |
| `/HOME_MODIFIED.*` | GET | `HOME_MODIFIED.html` | Alternate CDAT home | — | none | **not on HOME** | UNKNOWN |
| `/HOME_IMEI.html` | GET | `HOME_IMEI.html` | Lost-IMEI / Hawkeye menu | — | none | **not on HOME.html** | UNKNOWN / PARTIAL |
| `/HOME_JRMS.html` | GET | `HOME_JRMS.html` | JRMS hub (empty href scan) | — | none | **not on HOME** (HOME uses `JRMS_MAIN_PAGE1.PHP`) | UNKNOWN |
| `/SUM_HOME.html` | GET | `SUM_HOME.html` | Summary hub → `SUM.PHP` | — | none | `HOME.html` | ACTIVE |
| `/SUM_HOME2.html` | GET | `SUM_HOME2.html` | Summary hub → **`SUM2.php`** | — | none | not primary HOME | **BROKEN** (handler missing) |
| `/SUM_HOME12.html` | GET | `SUM_HOME12.html` | → **`SUM1.php`** | — | none | not primary HOME | **BROKEN** |
| `/SUM_HOME_P.html` | GET | `SUM_HOME_P.html` | → **`SUM_P.PHP`** | — | none | not primary HOME | **BROKEN** |
| `/IR_MODULE.HTML` | GET | `IR_MODULE.HTML` | IR search hub | — | none | `HOME.html` | ACTIVE |
| `/TOWER_HOME.HTML` | GET | `TOWER_HOME.HTML` | Tower dump hub | — | none | `HOME.html` (“Under Development”) | ACTIVE |
| `/TOWERDUMP_HOME.*` | GET | `TOWERDUMP_HOME.html` | Alternate tower hub | — | none | **no hrefs in file** | UNKNOWN |
| `/TOWER1.*` | GET | `tower1.php` / jpg | Image/stub | — | none | — | DEAD / asset |
| `/PDACT_MAIN.*` `/PDACT_MAIN_PAGE.*` | GET | hub pages | PDACT entry | — | none | HOME uses `PDACT_MAIN_PAGE_SEARCH.PHP` | PARTIAL |

---

## D. Admin / upload / JSON

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/ADMIN_ACTIVITY_LOG.PHP` | GET/POST | `ADMIN_ACTIVITY_LOG.PHP` | User activity UI | filters | **admin** | `HOME.html` | ACTIVE |
| `/ADMIN_SQL_CONSOLE.PHP` | GET/POST | `ADMIN_SQL_CONSOLE.PHP` | Ad-hoc SELECT + CSV/XLS | `sql_query`, `export`, `export_type` | **admin** | `HOME.html` | ACTIVE |
| `/ADMIN_CREATE_USER.PHP` | GET/POST | `ADMIN_CREATE_USER.PHP` | Create plaintext login | user fields | **admin** | `HOME_IR.HTML` (commented link still in source) | ACTIVE |
| `/admin_upload.php` | GET/POST/FILES | `admin_upload.php` | CDR/SDR upload UI, preview `exec`, curl API | `preview_file`, `cdr_file`, module fields | **uploader** | `HOME.html` “DATA UPLOAD” | ACTIVE |
| `/admin_upload_history.php` | GET | `admin_upload_history.php` | Job history | — | **uploader** | upload UI | ACTIVE |
| `/admin_upload_job_status.php` | GET | `admin_upload_job_status.php` | JSON job status | job id | **uploader** | upload UI poll | ACTIVE |
| `/admin_upload_sync_jobs.php` | GET | `admin_upload_sync_jobs.php` | JSON sync | — | **uploader** | upload UI | ACTIVE |
| `/admin_upload_verify.php` | GET/POST | `admin_upload_verify.php` | Staging preview/approve | job ids | **uploader** | upload UI | ACTIVE |
| `/download_template.php` | GET | `download_template.php` | Always **410** | — | **uploader** | upload UI? | **BROKEN** / obsolete |

---

## E. Summary reports

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/SUM_HOME.html` form | POST | `SUM.PHP` | Summary total | `PHONE_NO` | none (audit_log only) | `SUM_HOME.html` | ACTIVE |
| `/SUM_BETWEEN_DATES.html` | POST | `SUM_BTWN_DATES.PHP` | Summary in date range | phone + dates | none | `HOME.html` | ACTIVE |
| `/SUM_BETWEEN_DATES.html` (page) | GET | `SUM_BETWEEN_DATES.html` | Form UI | — | none | `HOME.html` | ACTIVE |
| `/SUM_ISD_CNTS.html` | POST | `SUM_ISD_CNTS.PHP` | ISD contacts summary | phone | none | `HOME.html` | ACTIVE |
| `/SUM_NEW_NOS.html` | POST | `SUM_NEW_NO.PHP` | New contacts | phone | none | `HOME.html` | ACTIVE |
| `/SUM_IN_STATE.html` | POST | `SUM_IN_STATE.PHP` | Within state | phone + state | none | `HOME.html` | ACTIVE |
| `/SUM_OUT_STATE.html` | POST | `SUM_OUT_STATE.PHP` | Outside state | phone + state | none | `HOME.html` | ACTIVE |
| `/SUM_ALLDB.PHP` | POST | `SUM_ALLDB.PHP` | Multi-DB summary + `#DTEMP` | phone | none | **not on HOME** | UNKNOWN |
| `/SUM1.php` | POST | **MISSING** | Intended summary variant | phone | — | `SUM_HOME12.html` | **BROKEN** |
| `/SUM2.php` | POST | **MISSING** | Intended summary variant | phone | — | `SUM_HOME2.html` | **BROKEN** |
| `/SUM_P.PHP` | POST | **MISSING** | Intended summary variant | phone | — | `SUM_HOME_P.html` | **BROKEN** |

---

## F. Call details / movements / day-night

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/CALLS_BTWN_DATES.html` | POST | `CALLS_BTWN_DATES.PHP` | CDR rows in range | phone, dates | **session** | `HOME.html` | ACTIVE |
| `/CALLS_BTWN_DATES1.PHP` | POST | `CALLS_BTWN_DATES1.PHP` | Copy | phone, dates | none | form variants | UNKNOWN |
| `/CALLDETAILS.PHP` | POST | `CALLDETAILS.PHP` | Call details | phone/dates | **session** | forms | PARTIAL (not on HOME; CALLS_TOT commented) |
| `/CALLS_TOT.html` | GET/POST | `CALLS_TOT.html` / `.PHP` | Call details total | — | none | **commented** on HOME | UNUSED (menu) |
| `/CALLS_BT_NOS.html` | POST | `CALLS_BT_NOS.PHP` | Calls between two numbers | `PHONE`/`OTHER` | none | **commented** on HOME | PARTIAL |
| `/MOVEMENTS.html` | POST | `MOVEMENTS.PHP` | Movements | phone + dates | none | `HOME.html` | ACTIVE |
| `/MOVEMENTS_BETWEEN_TWO_NUMBERS.html` | POST | `MOVEMENTS_BETWEEN_TWO_NUMBERS.PHP` | Pair movements | two phones | none | `HOME.html` | ACTIVE |
| `/MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.html` | POST | `MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.PHP` | Comparison | two phones | none | `HOME.html` | ACTIVE |
| `/MOVEMENTS_IN_PARTICULAR_PLACE.*` | POST | `MOVEMENTS_IN_PARTICULAR_PLACE.PHP` | Place filter | phone/place | none | **not on HOME** | UNKNOWN |
| `/DAY%26NIGHTLOC.HTML` | GET | HTML form | Top day/night loc UI | — | none | `HOME.html` | ACTIVE |
| `/D&N_LOC.PHP` `/d%26n_loc.php` `/DAY&NIGHTLOC.php` | POST | `D&N_LOC.PHP` (+ encoded twins) | Top 10 day/night | phone | none (`sql_safe` on D&N_LOC) | form | ACTIVE |
| `/DAY%26NIGHTLOC_BTWN_DATES.HTML` | GET | HTML | Dated UI | — | none | `HOME.html` | ACTIVE |
| `/D&N_BT_DTS.PHP` `/d%26n_bt_dts.php` | POST | `D&N_BT_DTS.PHP` | Dated day/night + `#TEMP` | phone, dates | none | form | ACTIVE |
| `/COMMON_CNTS.HTML` | POST | `COMMON_CNTS.PHP` | Common contacts | phones | none | `HOME.html` | ACTIVE |
| `/COMMON_CNTS1.PHP` | POST | copy | — | none | — | UNKNOWN |

---

## G. CDAT contacts / address / vehicle / cell / IMEI

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/CDATCNTS.html` | POST | `CDATCNTS1.php` (typical) | CDAT contacts | phone | none | `HOME.html` | ACTIVE |
| `/CDATCNTS.php` `/CDATCNTS2.PHP` | POST | variants | contacts | phone | none | copies | PARTIAL |
| `/BULK_CDAT_CONTACTS.HTML` | POST | `BULK_CDAT_CONTACTS.PHP` | Bulk contacts | list | none | `HOME.html` | ACTIVE |
| `/BULK_CDAT_CONTACTS1.PHP` | POST | copy | — | none | — | UNKNOWN |
| `/OTHERSCDAT.html` | POST | `OTHERCDAT.php` | Others CDAT | phone | none | `HOME.html` | ACTIVE |
| `/ADDRESS.HTML` | POST | `ADDRESS.PHP` | Single address + QR | `PHONE_NO` | none | `HOME.html` | ACTIVE |
| `/BULKADDRESS.HTML` | POST | `BULK_ADDRESS.php` | Bulk addresses | list | none | `HOME.html` | ACTIVE |
| `/bulkaddress.php` | — | symlink/alias | — | — | — | PARTIAL |
| `/IMEISEARCH.html` | POST | `IMEI_SEARCH.PHP` | Phones used on IMEI | IMEI | **session** | `HOME.html` | ACTIVE |
| `/IMEISINPHONE.html` | POST | `IMEI_SEARCH_IN_PHONE.PHP` | IMEIs on phone | phone | none (`sql_safe`) | `HOME.html` | ACTIVE |
| `/imeisearch.php` `/imeisinphone.php` | — | often HTML symlinks | UI only | — | — | ACTIVE as UI |
| `/HOME_IMEI.html` children | POST | `IMEI_REQUEST_STATUS.PHP`, `_SUM`, `_TRACED_DETAILS`, `_MOVEMENTS`, `MAXSPENTLOCATION_IMEI.PHP`, `D&N_LOC_IMEI.php` | Hawkeye / lost IMEI | IMEI/request ids | none | `HOME_IMEI.html` only | UNKNOWN reachability |
| `/CELLID_SEARCH.html` | POST | `CELLID_SEARCH.php` | Cell/tower search + QR | cell id | none (`sql_safe`) | `HOME.html` | ACTIVE |
| `/NEAREST_CELLIDS.PHP` | POST | `NEAREST_CELLIDS.PHP` | Geo nearest | lat/lon | **session** | **not on HOME** | UNKNOWN |
| `/NEAR_BY_CELLTOWERIDS.PHP` | POST | `NEAR_BY_CELLTOWERIDS.PHP` | Nearby towers | cell | **session** | **not on HOME** | UNKNOWN |
| `/VEHICLE_SEARCH.HTML` | POST | `VEHICLE_SEARCH.PHP` | Vehicle by number | reg | none | `HOME.html` | ACTIVE |
| `/VEHICLE_SEARCH_CRITERIA.HTML` | POST | `VEHICLE_SEARCH_CRITERIA.PHP` | Criteria + ddtf | fields | none | `HOME.html` | ACTIVE |
| `/VEHICLE_SEARCH1.PHP` `/VEHICLE_CHAS_SEARCH.PHP` `/VEHICLE_ENG_SEARCH.PHP` | POST | handlers | chas/engine | ids | none | forms / not all on HOME | PARTIAL |
| `/rta_nike.php` | POST | `rta_nike.php` | RTA variant | — | none (`sql_safe`) | **not on HOME** | UNKNOWN |

---

## H. Offenders / IR search (CDAT side)

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/HABITUAL.PHP` | GET/POST | `HABITUAL.PHP` | Habitual offenders | — | none | `HOME.html` | ACTIVE |
| `/FP_LIST.PHP` | GET/POST | `FP_LIST.PHP` | FP list | — | none | `IR_MODULE.HTML` | ACTIVE |
| `/OFFENDER_SEARCH_BY_MO.HTML` | POST | `OFFENDER_SEARCH_BY_MO.PHP` | Search by modus | MO text | none | `HOME.html` | ACTIVE |
| `/OFFENDER_FD.PHP` | — | `offender_fd.php` | FD variant | — | none | **not on HOME** | UNKNOWN |
| `/IR_SEARCH.HTML` | POST | `IR_SEARCH.PHP` | IR by name | name fields | none | `HOME.html`, `IR_MODULE` | ACTIVE |
| `/IR_SEARCH_BY_HEAD.PHP` `/_GENDER` `/_TEST` `/__OLD` | POST | handlers | IR by head/gender/test/old | various | none | IR_MODULE (head gender, test); old unused | PARTIAL |
| `/IR.PHP` | GET | `IR.PHP` | Full IR dossier | `IRKEY` | none | search result links | ACTIVE |
| `/CDAT_IRFORM.PHP` | GET/POST | `CDAT_IRFORM.PHP` | IR by mobile | phone | none | — | UNKNOWN |
| `/IR_NDPS.PHP` `/IR_NDPS1.PHP` | GET/POST | NDPS IR | — | none | — | UNKNOWN |
| `/BULK_IRKEY.PHP` `/BULK_IRKEY_NDPS.PHP` `/BULK_IRSEARCH_IRKEY*.PHP` `/BULK_GANG_ID*.PHP` | POST | bulk IR/gang | IRKEY lists | none | forms | UNKNOWN (not on HOME) |
| `/wanted1.html` | GET/POST | `wanted1.php` | Wanted list | — | none | `SUM_HOME.html` only | PARTIAL |

---

## I. IR forms (post-login menu — pages themselves unauthenticated)

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/IRREPORT.html` | POST | `IRREPORT.PHP` | IR particulars | IR fields | none | `HOME_IR.HTML` | ACTIVE |
| `/BRIEF_FACTS.html` | POST | `BRIEF_FACTS.PHP` | Insert/select brief facts | fields | none | `HOME_IR` | ACTIVE |
| `/FAMILY_HISTORY.html` | POST | `FAMILY_HISTORY.PHP` | Family history | fields | none | `HOME_IR` | ACTIVE |
| `/OFFENCE_DETAILS.html` | POST | `OFFENCE_DETAILS.PHP` | Offence details | fields | none | `HOME_IR` | ACTIVE |
| `/OFFENCE_DETAILS1.PHP` | POST | copy | — | none | — | UNKNOWN |
| `/PREVIOUS_OFFENCE_DETAILS.html` | POST | `PREVIOUS_OFFENCE_DETAILS.PHP` | Previous offences | fields | none | `HOME_IR` | ACTIVE |
| `/PREVIOUS_OFFENCE_DETAILS1.PHP` | POST | copy | — | none | — | UNKNOWN |
| `/DISPOSAL_OF_PROPERTY.HTML` | POST | `DISPOSAL_OF_PROPERTY.PHP` | Property disposal | fields | none | `HOME_IR` | ACTIVE |
| `/LOCAL_CONTACTS.HTML` | POST | `LOCAL_CONTACTS.PHP` | Facilitators | fields | none | `HOME_IR` | ACTIVE |
| `/RELATION_WITH_OTHER_ASSOCIATES_AND_GANGS.html` | POST | matching `.PHP` | Associates | fields | none | `HOME_IR` | ACTIVE |
| `/MULAKATH_ENTRY.html` | POST | `MULAKATH_ENTRY.PHP` | Mulakath | fields | none | `HOME_IR` | ACTIVE |
| `/IMAGE_LIST.HTML` | POST/FILES | `IMAGE_LIST.PHP` | Photo → IMAGE_TABLE | `image`, IRKEY, category | **none** | `HOME_IR` | ACTIVE |
| `/MO_IMAGE_LIST.*` | POST/FILES | `MO_IMAGE_LIST.PHP` | MO images | files | **none** | forms | UNKNOWN |
| `/RETRIEVE.HTML` | POST | `RETRIEVE.PHP` | Retrieve IR | name fields | none | `HOME_IR` | ACTIVE |
| `/RETRIEVE1.HTML` `/RETRIEVE1.PHP` | POST | `RETRIEVE1.PHP` | Retrieve + **hardcoded backdoor login** | NAME/FATHER_NAME; USERNAME/PASSWORD | none | not on HOME_IR menu | **LEGACY / DANGEROUS** |
| `/ANALYSIS_ABSTRACT.PHP` | POST | `ANALYSIS_ABSTRACT.PHP` | Insert analysis notes | text fields | none | **not on HOME_IR** | UNKNOWN |

---

## J. JRMS / PDACT / Rowdy / Trainings

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/JRMS_MAIN_PAGE1.PHP` | GET/POST | `JRMS_MAIN_PAGE1.PHP` | JRMS hub/search | search fields | none | `HOME.html` | ACTIVE |
| `/JRMS.php` `/JRMS_MAIN_PAGE.PHP` `/JRMS_SEARCH*.PHP` `/JRMS_NAME_SEARCH*.PHP` `/JRMS_PS_WISE*.PHP` `/JRMS_DATEWISE*.PHP` `/JRMS_CIN*.PHP` `/JRMS_SEARCH_BY_PRISONERNO*.PHP` `/JRMS_SEARCH_FOR_UNIQUEKEY.PHP` `/JRMS_NEW_RECORDS_ENTRY*.PHP` `/JRMS_UNIQUENESS_UPDATE*.PHP` | GET/POST | family of copies including `_MAHESH`, `_OLD`, `_PHP.PHP` | Jail search / uniqueness | CIN, name, dates, PS, prisoner no | none | internal JRMS links / forms | PARTIAL (many duplicates) |
| `/JRMS_UNIQUE_KEY_UPDATE.PHP` | POST | `JRMS_UNIQUE_KEY_UPDATE.PHP` | **UPDATE** unique key / IRKEY | keys, CIN list | **none** | JRMS forms | ACTIVE (write) |
| `/PDACT_MAIN_PAGE_SEARCH.PHP` | GET/POST | `PDACT_MAIN_PAGE_SEARCH.PHP` | PDACT search hub | filters | none | `HOME.html` | ACTIVE |
| `/PDACT_MAIN.PHP` `/PDACT_SEARCH.PHP` `/PDACT_MO_SEARCH.PHP` `/PDACT_PS_WISE_SEARCH*.PHP` `/PDACT_SUBMIT.PHP` `/PDACT_PRESS_NOTES.PHP` | POST | family | PDACT CRUD/search | name/MO/PS | none | PDACT forms | PARTIAL |
| `/ROWDYSHEETER_PS_WISE_SEARCH.PHP` | GET | form/hub | Rowdy by PS UI | — | none | `HOME.html` | ACTIVE |
| `/ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP` | POST | handler | Rowdy search | PS | none | form action | ACTIVE |
| `/TRAINING_MODULE1.HTML` | POST | `TRAINING_MODULE1.PHP` | Training DB + QR | fields | none | `HOME.html` | ACTIVE |
| `/TRAINING_MODULE2.*` | POST | `TRAINING_MODULE2.PHP` | Module 2 | — | none | **not on HOME** | UNKNOWN |

---

## K. Tower dump

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/TOWER_HOME.HTML` | GET | static | Hub + marquee email | — | none | `HOME.html` | ACTIVE |
| `/SUSPECT_SEARCH.PHP` | GET/POST | `SUSPECT_SEARCH.PHP` | Suspects in dump | — | none | `TOWER_HOME.HTML` | ACTIVE |
| `/OTHER_STATE_NUMBER.PHP` | GET/POST | `OTHER_STATE_NUMBER.PHP` | Other-state numbers | — | none | `TOWER_HOME` | ACTIVE |
| `/INTER_TOWER_CALLS.PHP` | GET/POST | `INTER_TOWER_CALLS.PHP` | Inter-tower calls | — | none | `TOWER_HOME` | ACTIVE |
| `/PRE_OFF_SEARCH.PHP` | GET/POST | `PRE_OFF_SEARCH.PHP` | Previous offenders in dump | — | none | `TOWER_HOME` | ACTIVE |
| `/SUSPECT_SEARCH_TWR.PHP` `/OTHER_STATE_NUMBER_TWR.PHP` `/INTER_TOWER_CALLS_TWR.PHP` `/PRE_OFF_SEARCH_TWR.PHP` | POST | TWRMDB variants | Same features on TWRMDB | dump filters | none | forms (e.g. `SUSPECT_SEARCH_TWR.PHP` in form-action scan) | ACTIVE/PARTIAL |
| `/DUMP.PHP` `/DUMP_SEARCH.PHP` `/DUMP_ANALYSIS.PHP` | mixed | dump tools | — | none | **not on TOWER_HOME** | UNKNOWN |

---

## L. AJAX dropdowns

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/GET_PS.PHP` | POST | `GET_PS.PHP` | PS options | `DISTRICT` / similar | none | migrant / IR forms JS | ACTIVE (if those UIs used) |
| `/GET_YEAR.php` | POST | `GET_YEAR.php` | Year options | PS/crime | none | JS | PARTIAL |
| `/GET_DIVISION.PHP` | POST | `GET_DIVISION.PHP` | Division | ZONE | none | migrant | PARTIAL |
| `/GET_CRNO.php` | POST | `GET_CRNO.php` | Crime numbers | PS, year | none | JS | PARTIAL |
| `/get_state.php` (root) | POST | **MISSING** | States | country id | — | `myindex.php` | **BROKEN** |

---

## M. Other root PHP (not on primary menus)

| Endpoint | Method | File/Handler | Purpose | Parameters | Auth Required | Called From | Status |
| -------- | ------ | ------------ | ------- | ---------- | ------------- | ----------- | ------ |
| `/CAF_SEARCH.PHP` | POST | `CAF_SEARCH.PHP` | CAF search + ftp URL | phone | none | **no HOME href** | UNKNOWN |
| `/MIGRANT_LABOURS_1.PHP` `_2` `_REPORT` `_DATE_REPORT` | POST | migrant family | Labour camp forms/reports | many | none | **no HOME href**; uses `dynamicdependentbox/jquerydynamic.js` | UNKNOWN |
| `/CIS_DATA_NAME_SEARCH*.PHP` | POST | CIS search | name | none | **no HOME href** | UNKNOWN |
| `/VBR_SEARCH.PHP` | POST | VBR/ILD + `#TEMP` updates | phones | none | **no HOME href** | UNKNOWN |
| `/NBWS.PHP` | POST | NBWS verify data | — | none | **no HOME href** | UNKNOWN |
| `/ALLDATA.PHP` `/ALLDATA_SEARCH.php` | POST | all-data search | — | none | **no HOME href** | UNKNOWN |
| `/NAMESEARCH.PHP` `/NAME_SEARCH.PHP` | POST | name search | `NAME`, `ADDRESS` | none | **no HOME href** | UNKNOWN |
| `/myindex.php` | GET/AJAX | country/state demo | — | none | **no HOME href**; broken AJAX | DEAD/DEMO |
| `/dump_analysis.php` | GET | same demo family | — | none | — | DEAD/DEMO |
| `/demo.php.php` | GET | DDTF demo | expects root `ddtf.js` | none | — | **BROKEN** |
| `/chandu.php` | GET | symlink → `CHANDU.htm` | hello/stub | none | — | DEAD |
| `/untitled-1.php` `/notepad.php` `/desktop.php` | GET | junk | — | none | — | DEAD |
| `/jquerydynamic.php` | GET | **jQuery 2.1.1 source** misnamed | — | none | — | DEAD |
| `/css_sparkle1.php` `/style.php` `/w3.css` | GET | CSS (w3 symlink to DROP DOWN FILTER) | — | none | linked as `.css` usually | PARTIAL |
| `/sample.php` | GET | symlink → `sample.gif` | — | none | — | DEAD |
| `/dbcontroller.php` `/dbcontroller.php.php` | include | sqlsrv wrapper | — | n/a | GET_* pages | ACTIVE include |
| `/sql_safe.php` | include | sanitizers + `h()` | — | n/a | ~9 reports | ACTIVE include |
| `/sqlsrv_compat.php` | prepend | sqlsrv shim | — | n/a | nginx PHP_VALUE | ACTIVE |
| `/activity_logger.php` | include | audit/auth | — | n/a | login/admin/upload/some reports | ACTIVE |
| `/db_config.example.php` | include template | DSN | env | n/a | copy to `db_config.php` | TEMPLATE |
| `/db_config.php` | include | **not in git** | secrets | n/a | shim + logger | **MISSING IN REPO** |
| `/cdr_upload_config.php` `/cdr_upload_parser.php` `/cdr_enrichment_sql.php` `/excel_converter.php` `/upload_verification_service.php` `/document_processing_client.php` | include/lib | upload pipeline | — | via upload pages | ACTIVE includes |

---

## N. FastAPI (`cdr-import-service`) — via `/document-api/` or `:8088`

Exact OpenAPI paths: **Needs Verification** from `cdr-import-service/app/main.py` at deploy time. PHP client methods confirmed:

| Client method | HTTP (typical) | Purpose | Called From | Status |
| ------------- | -------------- | ------- | ----------- | ------ |
| `submitDocument($module, $filePath, …)` | POST multipart | Enqueue CDR/SDR job | `admin_upload.php` | ACTIVE |
| `getJobStatus($jobId)` | GET | Poll job | status/history pages | ACTIVE |
| `waitForJob($jobId)` | GET loop | Block until done | upload flow | ACTIVE |
| resumable SDR | POST chunks | Large `.bak` | `js/sdr_resumable_upload.js` → `/document-api/` | ACTIVE if JS included |

---

## O. Duplicate / legacy / unreachable summary

### Referenced but missing

- `SUM1.php`, `SUM2.php`, `SUM_P.PHP`
- root `get_state.php`
- root `ddtf.js` (for `demo.php.php`)
- `db_config.php` (runtime config)

### Duplicate endpoint families (same feature, multiple URLs)

- Login: `LOGIN.PHP` vs `LOGIN1.PHP`
- Day/night: `D&N_*` vs `D%26N_*` vs `DAY&NIGHTLOC*`
- JRMS: `*1`, `*_PHP.PHP`, `*_MAHESH*`, `*_OLD`
- Tower: root vs `*_TWR.PHP`
- Summary homes: `SUM_HOME` / `2` / `12` / `_P`

### Unreachable / no known CDAT caller

- `curfewepass/**` (own `index.php` if that tree is separately routed — **not** in `cdat-web.nginx.conf`)
- `old ir/**`, `new ir/**`, `TWRDB/**`, `ROUGH_TOWER/**`, `SUN/**`
- Demo/junk PHP listed in §M DEAD

### Obsolete

- `download_template.php` (hard-coded 410)
- `LOGIN_PAGE.*`
- Commented HOME links (`CALLS_TOT`, `CALLS_BT_NOS`) — handlers may still be hit directly

---

## P. Form-action coverage (root HTML → PHP)

Scanned `<form action="*.php">` on root HTML (excluding dump dirs). **All listed actions exist except SUM1 / SUM2 / SUM_P.**

Confirmed existing actions include: `LOGIN.PHP`, `SUM.PHP`, `SUM_BTWN_DATES.PHP`, `SUM_ISD_CNTS.PHP`, `SUM_NEW_NO.PHP`, `SUM_IN_STATE.PHP`, `SUM_OUT_STATE.PHP`, `CALLS_BTWN_DATES.PHP`, `CALLS_BT_NOS.PHP`, `CALLDETAILS.PHP`, `MOVEMENTS*.PHP`, `CDATCNTS1.php`, `BULK_CDAT_CONTACTS.PHP`, `OTHERCDAT.php`, `ADDRESS.PHP`, `BULK_ADDRESS.php`, `IMEI_SEARCH.PHP`, `IMEI_SEARCH_IN_PHONE.PHP`, `IMEI_REQUEST_*.PHP`, `MAXSPENTLOCATION_IMEI.PHP`, `D&N_LOC.PHP`, `D&N_BT_DTS.PHP`, `D&N_LOC_IMEI.php`, `CELLID_SEARCH.php`, `NEAREST_CELLIDS.PHP`, `NEAR_BY_CELLTOWERIDS.PHP`, `VEHICLE_*.PHP`, `COMMON_CNTS.PHP`, `IR.PHP`, `IR_SEARCH.PHP`, `IR_SEARCH_BY_HEAD*.PHP`, `IRREPORT.PHP`, `BRIEF_FACTS.PHP`, `FAMILY_HISTORY.PHP`, `OFFENCE_DETAILS.PHP`, `PREVIOUS_OFFENCE_DETAILS*.PHP`, `DISPOSAL_OF_PROPERTY.PHP`, `LOCAL_CONTACTS.PHP`, `RELATION_WITH_OTHER_ASSOCIATES_AND_GANGS.PHP`, `MULAKATH_ENTRY.PHP`, `IMAGE_LIST.PHP`, `MO_IMAGE_LIST.PHP`, `RETRIEVE.PHP`, `RETRIEVE1.PHP`, `JRMS_*.PHP`, `JRMS_UNIQUE_KEY_UPDATE.PHP`, `PDACT_SEARCH.PHP`, `PDACT_MO_SEARCH.PHP`, `ROWDYSHEETER_PS_WISE_SEARCH_PHP.PHP`, `TRAINING_MODULE1.PHP`, `SUSPECT_SEARCH_TWR.PHP`, `CAF_SEARCH.PHP`, `NAMESEARCH.PHP`, `NAME_SEARCH.PHP`, `ALLDATA_SEARCH.php`, `ANALYSIS_ABSTRACT.PHP`, `VBR_SEARCH.PHP`, `MIGRANT_LABOURS_DATE_REPORT.PHP`, `rta_nike.php`, `OFFENDER_SEARCH_BY_MO.PHP`, `BULK_IRSEARCH_IRKEY*.PHP`, `BULK_GANG_ID_SEARCH.PHP`.

---

**Audit Scope:** Current `main` branch  
**Audit Type:** Static endpoint inventory  
**Code Changes Made:** None  
**Confidence:** High for nginx + HOME/HOME_IR/TOWER/SUM_HOME form wiring; Medium for unlinked PHP reachability; Low for FastAPI exact routes without executing OpenAPI.
