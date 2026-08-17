# C-DAT Main Branch — Prioritized Issues

**Branch:** `main` @ `dc47eca`  
**Type:** Static security / bug / architecture / technical-debt register  
**Code changes:** None  
**Exploitation:** Not performed  

Severity: **Critical / High / Medium / Low / Informational**

Related narrative: `MAIN_BRANCH_APPLICATION_AUDIT_REPORT.md` §10–§18 (security/quality) and **§22** (business logic).

---

## A. Security findings

| ID | Severity | Category | File | Location | Finding | Impact | Evidence | Recommendation |
| -- | -------- | -------- | ---- | -------- | ------- | ------ | -------- | -------------- |
| SEC-01 | Critical | Authorization | `HOME.html` + ~190 root `*.PHP` | App-wide | Authentication is **opt-in**. Only ~15 pages call `audit_require_session/admin/uploader`. Static HTML homes and most report/insert PHP are reachable without a session. | Anonymous read (and often write) of CDR, addresses, IR, jail, photos | `activity_logger.php` defines gates; `git grep audit_require_` hits only admin/upload + CALLDETAILS, CALLS_BTWN_DATES, IMEI_SEARCH, NEAREST_CELLIDS, NEAR_BY_CELLTOWERIDS. nginx `index HOME.html` has no auth. | Auto_prepend a **default-deny** session check on all `*.php`; protect HTML or stop serving raw HTML forms; network ACL immediately. |
| SEC-02 | Critical | Weak passwords | `LOGIN.PHP`, `ADMIN_CREATE_USER.PHP`, `LOGINS` table | Login compare; create-user INSERT | Passwords stored and compared **in plaintext**. No `password_hash` / `password_verify`. | DB dump or SQLi = all user passwords; reuse against other systems | `LOGIN.PHP`: `SELECT * FROM LOGINS WHERE USERNAME = ? AND PASSWORD = ?` with raw POST password. Create-user inserts plaintext. | Hash existing passwords; compare with `password_verify`; never log passwords. |
| SEC-03 | Critical | SQL injection | `LOGIN1.PHP` | `$sql = "SELECT * FROM LOGINS WHERE USERNAME='$USERNAME' AND PASSWORD='$PASSWORD'"` | Login query concatenates request data. | Auth bypass / user dump | `LOGIN1.PHP` lines after `trim($_POST[…])` | Delete `LOGIN1.PHP` or use the same bound query as `LOGIN.PHP`. |
| SEC-04 | Critical | SQL injection | Widespread, e.g. `SUM.PHP`, `ADDRESS.PHP`, `CALLS_BT_NOS.PHP`, `IR.PHP`, `NAMESEARCH.PHP`, `RETRIEVE1.PHP`, `GET_PS.PHP`, `JRMS_UNIQUE_KEY_UPDATE.PHP` | `$number=$_POST[…]` then `WHERE PHONE='$number'` / `IRKEY='$number'` / `IN ('$NUMBER2')` | Almost all reports build SQL with string interpolation. `sql_safe.php` only on ~9 files and still concatenates. | Read/modify investigative PII; possible destructive SQL depending on driver | `SUM.PHP`: `WHERE PHONE='$number'`; `IR.PHP`: `$_GET['IRKEY']`; `JRMS_UNIQUE_KEY_UPDATE.PHP`: `str_replace(",", "','", $NUMBER1)` into `IN ('$NUMBER2')`; `GET_*.PHP`: `'".$_POST["POLICE_STATION"]."'` | Parameterized queries (or strict allow-lists) on every handler; treat `sql_safe_*` as insufficient. |
| SEC-05 | Critical | Hardcoded backdoor | `RETRIEVE1.PHP` | `if ( $USERNAME == "FORMS" && $PASSWORD == "sa@…" )` | Hardcoded credentials (SQL `sa`-style password pattern) set `$_SESSION` and redirect to `LOGIN1.php`. | Trivial privileged access; password also useful for MSSQL recon | File footer PHP block after retrieve query | Remove block; rotate any matching DB/OS passwords; secret-scan git history. |
| SEC-06 | High | XSS | Result pages e.g. `ADDRESS.PHP`, `CALLS_BT_NOS.PHP`, `HABITUAL.PHP`, `NAMESEARCH.PHP`, `RETRIEVE1.PHP`, `GET_CRNO.php` | `echo $row['NAME']` / `ADDRESS` / `DETAILS` without `htmlspecialchars` | Stored/reflected XSS via DB fields and interpolated “no record” strings. `h()` exists in `sql_safe.php` but almost unused. | Session theft (where cookies exist), UI phishing, malware in analyst browser | Pattern on essentially every table loop; `htmlspecialchars` rare (~admin + few hardened pages) | Encode all output; use `h()` or Twig-like layer; CSP as defense in depth. |
| SEC-07 | High | CSRF | All POST forms | No CSRF token | `activity_logger` `session_token` is an audit id, **not** a form token. Login, IR inserts, JRMS update, admin SQL, create user, uploads all CSRF-able if victim has cookie. | Forged search/update/admin actions | Grep: no `csrf` / `_token` fields on forms | SameSite cookies + per-form tokens on state-changing POST. |
| SEC-08 | High | Unauth upload | `IMAGE_LIST.PHP`, `MO_IMAGE_LIST.PHP` | `$_FILES['image']` → `getimagesize` → `file_get_contents` → SQL INSERT | No `audit_require_*`. IRKEY/CATEGORY from POST go into SQL. `addslashes(tmp_name)` is not a security control. | Unauthenticated write of images into IR DB; SQLi; possible huge payloads | `IMAGE_LIST.PHP` after `sqlsrv_connect`; linked from `HOME_IR.HTML` | Require session; validate type/size; store outside SQL or parameterized bytea; authz on IRKEY. |
| SEC-09 | High | Admin SQL console | `ADMIN_SQL_CONSOLE.PHP` | `$db->query($wrapped_query)` after `^select` + keyword block | Admin-gated arbitrary SELECT on Postgres. Filter misses CTE/`INTO`/functions; wrap `SELECT * FROM ($q) LIMIT 1000` can be bypassed in some dialects. Linked from **public** `HOME.html`. | Full CDR/PII read if admin cookie stolen or role confused; recon | File header `audit_require_admin()`; HOME href `ADMIN_SQL_CONSOLE.PHP` | Remove from HOME; IP-restrict; read-only DB role; stronger parser; never expose on shared vhost. |
| SEC-10 | High | Information leakage | Almost every report PHP | `die( print_r( sqlsrv_errors(), true));` | SQL/driver errors (and sometimes connection details) printed to the browser. | Schema/query disclosure; aids SQLi | e.g. `ADDRESS.PHP`, `SUM.PHP`, `IR.PHP`, `LOGIN.PHP` on connect failure | Log server-side; generic user error. |
| SEC-11 | High | Upload / API exposure | `cdat-web.nginx.conf` `location /document-api/`; `admin_upload.php` | Proxy to `:8088`; `client_max_body_size 750G` | Document API shares the UI vhost. API auth **Needs Verification**. 750G body + 86400s timeouts. | Disk exhaustion, long-running DoS, unauthenticated import if API open | nginx conf; `cdr_upload_config.php` default `http://127.0.0.1:8088` | Bind API to localhost only; require token; lower body size; authz on nginx location. |
| SEC-12 | Medium | Session security | `activity_logger.php` `audit_login` / `audit_require_session` | No `session_regenerate_id`; no idle timeout; cookie flags not set in code | Session fixation / long-lived cookies / missing Secure/HttpOnly/SameSite (depends on php.ini — **Needs Verification**). Role stored only in `$_SESSION['audit_role']`, not re-checked against DB. | Hijack; privilege persistence after role change | `audit_require_session` only checks `$_SESSION['audit_username']` | Regenerate on login; timeout; cookie flags; re-validate role. |
| SEC-13 | Medium | Secrets in source | `RETRIEVE1.PHP`; many `$serverName`; `CAF_SEARCH.PHP` `ftp://192.168.x.x` | Hardcoded hosts, instance names, internal FTP, backdoor password | Recon for lateral movement; FTP/MSSQL targeting | `LOGIN.PHP` `$serverName = 'CPHYDERABAD1\DAU_HYD_2023'`; IR pages also `10.10.x.x\DAU_HYD_2023`; CAF ftp URL | Remove secrets; use config; rotate; consider history rewrite if password was real. |
| SEC-14 | Medium | Command execution | `admin_upload.php`, `excel_converter.php` | `exec` python preview / excel_to_csv | OS command invocation from web. Dangerous if filenames/args unsanitized (**review before change**; do not treat as confirmed RCE without review). | RCE / file overwrite if args weak | `admin_upload.php` preview path; `excel_converter.php` | Replace `exec` with FastAPI-only preview; disable `exec` in php.ini. |
| SEC-15 | Medium | Availability / DoS | nginx + `SUM.PHP` et al. | `client_max_body_size 750G`; `set_time_limit(0)`; unauthenticated heavy SQL on `cdatpcsuspect` | Unauthenticated users can trigger expensive queries; upload can fill disk. Shim `statement_timeout=120s` mitigates some PG load. | Service outage for crime-lab | nginx conf; `SUM.PHP` `set_time_limit(0)` | Auth first; query limits; sane upload cap; rate limit. |
| SEC-16 | Medium | Auth confusion | `check_role.php` | JSON role, no login required | Returns `{is_admin:false}` when anonymous; returns real role if cookie present. Used only to **hide** menu items — not enforce. | UI spoofing; role disclosure | `check_role.php` full file | Do not rely on JS hiding; keep JSON behind session. |
| SEC-17 | Low | Vulnerable frontend libs | `jquery-ui-1.10.4.custom/`, `SpryAssets/`, `jquerydynamic.js` (2.1.1) | Datepicker + menus | Unmaintained libraries with known historical XSS/CVE classes. | XSS in datepicker/menu widgets | Version strings / file dates (jQuery UI 2014-01-29) | Replace with maintained widgets after auth hardening. |
| SEC-18 | Low | CDN without verified SRI | `admin_upload.php` | cdnjs Font Awesome 6.4.0, SheetJS 0.18.5, PapaParse 5.4.1 | CDN compromise → XSS on upload UI (uploader-authenticated). SRI **Needs Verification**. | Uploader browser compromise | script tags on admin_upload | Pin + integrity hashes or vendor locally. |
| SEC-19 | Informational | Dump trees in docroot | `curfewepass/`, `old ir/`, `new ir/`, `TWRDB/`, `ROUGH_TOWER/`, `SUN/` | nginx `root` serves any existing `$uri` | Extra apps/copies reachable by URL even if unlinked. Curfewepass has its own login/DB. | Expanded attack surface | `cdat-web.nginx.conf` `try_files $uri`; no href from HOME | Move outside webroot or `deny`. |
| SEC-20 | Informational | Debug / die | many PHP | `die()`, `exit()`, `print_r` | Abrupt failure + leak (see SEC-10). | Poor UX + recon | grep `die( print_r( sqlsrv_errors` | Central error handler. |

---

## B. Functional bugs / breakage

| ID | Severity | Category | File | Location | Finding | Impact | Evidence | Recommendation |
| -- | -------- | -------- | ---- | -------- | ------- | ------ | -------- | -------------- |
| BUG-01 | High | Missing handler | `SUM_HOME12.html` | `action="SUM1.php"` | Handler **does not exist** on `main`. | 404 for that summary UI | `git ls-tree` no `sum1.php`; form action present | Point to `SUM.PHP` or restore file. |
| BUG-02 | High | Missing handler | `SUM_HOME2.html` | `action="SUM2.php"` | Missing. | 404 | same | same |
| BUG-03 | High | Missing handler | `SUM_HOME_P.html` | `action="SUM_P.PHP"` | Missing. | 404 | same | same |
| BUG-04 | Medium | Missing config | `sqlsrv_compat.php`, `activity_logger.php` | `require …/db_config.php` | Real `db_config.php` **not in git** (gitignored). `DB_CONFIG.PHP` is a dangling symlink in the tree. | Total PHP outage on fresh clone | `.gitignore` lists `db_config.php`; `git cat-file` missing | Document deploy; fail with clear message; never commit secrets. |
| BUG-05 | Medium | Missing AJAX target | `myindex.php` | `url: "get_state.php"` | No root `get_state.php` (exists under `dynamicdependentbox/` / `TWRDB/`). | Demo page broken | ls-tree root | Ignore demo or copy file; not a HOME feature. |
| BUG-06 | Medium | Missing JS | `demo.php.php` | expects `ddtf.js` at root | Only `DROP DOWN FILTER/ddtf.js` exists. | Demo broken | ls-tree | Dead file; do not use. |
| BUG-07 | Medium | Dead feature | `download_template.php` | entire file | Always `410 Gone`. | Upload “template” links fail | file body | Remove UI button or restore templates. |
| BUG-08 | High | Dialect mismatch | JRMS/PDACT/D&N/SUM/VBR/bulk IR | `SELECT * INTO #TEMP`, `CONVERT(VARCHAR…)`, `WITH (NOLOCK)`, `isnumeric()`, `+` concat | Pages written for MSSQL. Runtime on this tree is Postgres via `__sqlsrv_translate`. Incomplete translation → errors or **wrong results**. | Failed reports or incorrect police analysis | `sqlsrv_compat.php` translate function; `#TEMP` usage across JRMS_*.PHP, `D&N_BT_DTS.PHP`, `SUM.PHP`, `VBR_SEARCH.PHP` | Golden-query tests on PG; fail closed; do not change column names without investigator sign-off. |
| BUG-09 | Medium | Case / symlink fragility | repo-wide | 6× files per page; git mode `120000` | Linux case-sensitive deploy: wrong extension → 404. nginx rewrite only maps `.PHP`→`.php` if target exists. | Intermittent 404 depending on URL casing | `git ls-tree`; `.htaccess`; nginx rewrite | Canonical lowercase + redirects. |
| BUG-10 | Medium | Login landing vs CDAT home | `LOGIN.PHP` | `header('refresh:0; url=HOME_IR.HTML')` | Successful login always goes to **IR home**, not `HOME.html` CDAT menu. CDAT HOME itself needs no login (SEC-01). | Confusing UX; analysts may think CDAT is “inside” IR login | `LOGIN.PHP` redirect | Product decision: either gate HOME or redirect to HOME after login. |
| BUG-11 | Low | Page titles | `HOME.html`, `HOME_IR.HTML`, `TOWER_HOME.HTML`, … | `<title>Untitled Document</title>` | Dreamweaver leftover. | Bookmark/tab confusion | file heads | Set real titles. |
| BUG-12 | Low | Commented menu vs live code | `HOME.html` | `CALLS_TOT`, `CALLS_BT_NOS` commented; Logout commented in one block but present lower | Drift between UI and handlers. | Users cannot find features; orphan URLs remain live | HOME.html comments | Either restore menu or retire handlers. |

---

## C. Architecture / maintainability

| ID | Severity | Category | File / area | Finding | Impact | Evidence | Recommendation |
| -- | -------- | -------- | ----------- | ------- | ------ | -------- | -------------- |
| ARC-01 | High | Dual runtime | `sqlsrv_compat.php` vs `$serverName` MSSQL | One codebase, two possible engines. Shim ignores `$serverName` and maps every `Database` to `postgres`. Without prepend, PHP expects native sqlsrv + named instance. | Ops mis-deploy → silent wrong DB or total failure | `__sqlsrv_dbname()` map; nginx `PHP_VALUE auto_prepend_file` | Make runtime explicit; health check that shim is loaded (`function_exists` guard). |
| ARC-02 | High | No front controller | repo root | 212 unique PHP “routes” = 212 files. No `index.php`. | Cannot apply global auth/logging without prepend or nginx | ls-tree; nginx try_files | Keep prepend for auth+shim; long-term router without renaming POST fields. |
| ARC-03 | High | Copy-paste families | `JRMS_*.PHP`, `PDACT_*.PHP`, `IR_SEARCH*`, `*_TWR.PHP`, `LOGIN` vs `LOGIN1` | Same `#TEMP` SQL block repeated ~15× (JRMS). Bug fixes do not propagate. | Inconsistent results; huge review cost | JRMS_MAIN_PAGE vs JRMS_SEARCH vs JRMS_DATEWISE_* | One shared query helper per report type. |
| ARC-04 | Medium | God files | `IR.PHP`, `admin_upload.php`, large JRMS/PDACT pages | Mixed HTML+SQL+auth+images. | Untestable; high defect density | file sizes / structure | Split view vs query incrementally. |
| ARC-05 | Medium | 15k-file tree | case duplicates + jquery-ui demos + curfewepass + qrcode samples | Git/IDE/deploy slowness; easy to edit the wrong case variant. | Wrong file shipped | `git ls-tree` ~15698 entries; ~5224 jquery-ui; ~4586 curfewepass | Shrink docroot; vendor ignore; one extension. |
| ARC-06 | Medium | Hardcoded chrome + SQL | every report | `$connectionInfo = array("Database"=>"CDATDUPL")` repeated; colors `#921215` inline | Change DB mapping or UI theme = hundreds of edits | grep Database=> | Config + layout include (other branches started this; **not** on `main`). |
| ARC-07 | Medium | Include graph | `activity_logger` optional | New pages forget `audit_require_*` → insecure by default (SEC-01). | Every new report is a security regression | contrast SUM.PHP (log only) vs CALLS_BTWN_DATES (require session) | Default-deny prepend. |
| ARC-08 | Low | Naming | `OTHERCDAT` vs `OTHERSCDAT`, `d%26n` vs `d&n`, `*_PHP.PHP`, `demo.php.php` | Wrong `action=` / bookmarks. | 404 / double maintenance | form actions vs filenames | Alias map then delete extras. |
| ARC-09 | Low | No tests | repo | No PHPUnit/composer test suite. Python has some `cdr_import/normalize/test_*.py`. | Shim/SQL regressions undetected | tree | Golden SQL translate + sample POST fixtures. |
| ARC-10 | Informational | Documentation | root | No product README on `main`. Titles “Untitled Document”. Ops hints only in nginx / `.env.example`. | Onboarding failure | ls-tree | Keep these audit files; add runbook off-repo for secrets. |

---

## D. Dependency / deployment

| ID | Severity | Category | Item | Finding | Impact | Recommendation |
| -- | -------- | -------- | ---- | ------- | ------ | -------------- |
| DEP-01 | High | Missing runtime secret file | `db_config.php` | Not committed; required by PHP+Python | App will not run from git alone | Example + secure deploy path |
| DEP-02 | High | PHP extensions | `pdo_pgsql`, `curl`, `gd` | Implied, not declared in composer | Partial feature failure | Document in runbook; php -m check script (`scripts/cdat-health-check.sh` exists — use it) |
| DEP-03 | Medium | FastAPI must be running | `:8088` | Upload UI depends on systemd/docker | Upload broken; search still works | Health check + clear UI error |
| DEP-04 | Medium | pyodbc / MSSQL | `sdr_import`, `image_migrate` | Required only for .bak / image migration | SDR path fails without ODBC | Document optional extra |
| DEP-05 | Medium | Ancient JS | jQuery UI 1.10.4, Spry | See SEC-17 | XSS / browser issues | Phase 5 replace |
| DEP-06 | Low | No Composer | — | Zero PHP packages (good and bad) | No lockfile / no shared libs | Add only when introducing vetted libs |
| DEP-07 | Informational | nginx `root /mnt/storage1/cdat-web` | `cdat-web.nginx.conf` | Hardcoded production path | Conf mismatch on other hosts | Parameterize |

---

## E. Data / correctness risks

| ID | Severity | Area | Finding | Impact | Recommendation |
| -- | -------- | ---- | ------- | ------ | -------------- |
| DATA-01 | High | Investigations | Unauthenticated + injectable searches on `cdatpcsuspect` / IR | Wrong or leaked case data | Auth + bind parameters + audit **all** searches (`audit_log` missing on most pages) |
| DATA-02 | High | Writes without auth | `JRMS_UNIQUE_KEY_UPDATE.PHP`, IR INSERTs, `IMAGE_LIST.PHP` | Unauthenticated mutation of jail/IR records | Gate writes; review trail |
| DATA-03 | High | Shim JOIN rewrite | `sqlsrv_compat.php` LATERAL rewrite for `cdataddress` / `cdatcelltowerareanew` | Wrong address/tower if rewrite misses a SQL shape | Snapshot queries before/after; investigator UAT |
| DATA-04 | Medium | All logical DBs → one `postgres` | `__sqlsrv_dbname` | Cross-module collision / permission blast radius | Separate DBs or schemas + roles |
| DATA-05 | Medium | Plaintext PII in `user_activity_logs.search_data` | `audit_log` JSON | Audit DB becomes a second PII store | Minimize fields; restrict access |
| DATA-06 | Informational | Schema dumps dated 2026-07-30 | `sql/schema_dumps/` | May drift from live | Refresh inventory on production |

---

## F. Technical debt ranked (same as report §17)

### Critical (before major changes)

- SEC-01 default-open auth
- SEC-02 plaintext passwords
- SEC-03/04 SQLi (especially LOGIN1 + writes)
- SEC-05 RETRIEVE1 backdoor
- BUG-08 / ARC-01 MSSQL vs Postgres truth
- DEP-01 missing `db_config.php` discipline

### High (stability / security)

- SEC-06 XSS, SEC-07 CSRF, SEC-08 unauth IMAGE_LIST, SEC-09 SQL console, SEC-11 API/750G
- BUG-01..03 missing SUM handlers
- ARC-03 copy-paste JRMS/PDACT
- DATA-02 unauthenticated writes

### Medium (maintainability)

- Session flags, exec(), dump dirs in docroot, case-symlink forest, no tests, CDN SRI
- HOME vs HOME_IR login redirect UX

### Low (cleanup)

- Untitled titles, demo files, commented menus, Spry/jQuery UI replacement, unused dump trees **after** log proof

---

## G. Suggested fix order (no implementation in this audit)

1. Network isolate `:8020`.
2. Remove/disable `RETRIEVE1` backdoor; rotate secrets.
3. Default-deny auth prepend (CDAT + IR).
4. Disable or isolate `ADMIN_SQL_CONSOLE` + `/document-api/`.
5. Gate `IMAGE_LIST` + `JRMS_UNIQUE_KEY_UPDATE`.
6. Hash passwords; delete `LOGIN1` or parameterize.
7. Replace `die(print_r(sqlsrv_errors))`.
8. Golden tests for shim SQL on top HOME reports.
9. Fix or unlink SUM1/2/P HTML.
10. Only then UI modernization (other branches) **without** renaming SQL/POST fields.

---

**Audit Scope:** Current `main` branch  
**Audit Type:** Static security / bug / architecture / debt register  
**Code Changes Made:** None  
**Confidence:** High for presence of insecure patterns and missing files; Medium for exploitability in a specific deploy (WAF, network ACL, php.ini not observed); Low for live PG table drift.

**Items requiring manual verification:** production prepend vs native sqlsrv; `:8020` exposure; FastAPI auth; php.ini cookie flags; whether RETRIEVE1 password still matches any live secret; access logs for dump directories and unlinked PHP.
