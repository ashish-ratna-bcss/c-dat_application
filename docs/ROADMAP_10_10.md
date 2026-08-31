# C-DAT Roadmap: 7/10 → 10/10

Goal: production handover with **zero open technical debt**, **proof every route works**, and **automated gates** so regressions cannot merge.

Current baseline (Aug 2026): repo clean (~523 tracked files), PHP runtime PostgreSQL-only, ~21 search pages SQL-hardened, handover docs exist.

---

## Score targets by phase

| Phase | Focus | Score after |
|-------|--------|-------------|
| 0 | Quick cleanup | 7.2 |
| 1 | Database / schema | 8.0 |
| 2 | Security hardening | 8.5 |
| 3 | QA + smoke tests | 9.0 |
| 4 | CI + automated tests | 9.5 |
| 5 | Ops + monitoring | 9.7 |
| 6 | Audit sign-off | 10.0 |

**Estimated calendar time:** 4–6 weeks (1 developer) or 2–3 weeks (2 developers, VPN access).

---

## Phase 0 — Quick cleanup (1 day)

### Tasks

| # | Task | Files / action | Done when |
|---|------|----------------|-----------|
| 0.1 | Delete unused dead code | `modules/common/check_role.php`, `public/assets/css/style.css`, `public/assets/css/w3.css`, `public/assets/js/jquerydynamic.js`, `cdat_sum_tower_cascade_script()` in `modules/common/includes/sum_ui.php` | `grep -r check_role modules/` empty; assets not linked |
| 0.2 | Confirm local secrets not in git | `run_cmds.txt`, `.env`, `config/db_config.php` | `git status` clean; `git ls-files` has none |
| 0.3 | Update stale comments | `login.php`, `activity_logger.php`, `bulk_cdat_contacts.php`, `layout.php` — remove `view/` references | Comments match `modules/` architecture |
| 0.4 | Trim audit script excludes | `scripts/audit_mssql_usage.sh` — remove excludes for already-deleted migration files | Script only excludes `sdr_import/` |

### Acceptance

- No dead PHP/CSS/JS tracked
- `bash scripts/audit_mssql_usage.sh` passes
- `find modules -name '*.php' \| xargs php -l` passes

---

## Phase 1 — Database & schema (3–5 days, VPN required)

### Known gaps

| Object | Used by | Fix |
|--------|---------|-----|
| `TRAINING_DB.TRAINING_STRENGTH_PARTICULARS`, `TRNG_ATT_WITH_EMPID` | `modules/others/training_module1.php` | FDW import from training DB **or** local table import |
| `nbws_verify_data_important` | `modules/interrogation-reports/ir.php` | FDW import from IR DB **or** local table |

### Tasks

| # | Task | Action | Done when |
|---|------|--------|-----------|
| 1.1 | Run live schema audit on VPN | `php scripts/schema_audit.php > docs/schema_audit_latest.txt` | Report saved; missing list is complete |
| 1.2 | Add IR FDW table for NBWS | Extend `sql/fdw_setup.sql` + `sql/ir_db.sql` if table lives in IR DB; re-run `bash sql/apply_fdw.sh` | `nbws_verify_data_important` appears in audit |
| 1.3 | Add training DB FDW | New `TRAINING_DB_NAME` in `.env.example`; new server in `sql/fdw_setup.sql`; import `training_strength_particulars`, `trng_att_with_empid` | `/others/trainings` search returns rows |
| 1.4 | Make schema audit fail CI | `scripts/schema_audit.php`: exit 1 if any module-referenced object missing (except documented allowlist) | `./scripts/schema_audit.php; echo $?` → 1 when gap exists |
| 1.5 | Index review for heavy searches | Document indexes on `cdat_details`, `cdat_details1`, FDW key columns (`irkey`, `phone`, `imei`, `cell_id`) | Query plans checked; slow searches < 30s on VPN |
| 1.6 | Update handover docs | `docs/HANDOVER_CHECKLIST.md`, `docs/DEPLOY.md` with new env vars and FDW steps | Docs match live setup |

### Decision (pick one per gap)

- **Option A (preferred):** FDW from satellite PostgreSQL DBs — matches existing IR/JRMS/PDACT pattern.
- **Option B:** One-time pg_dump import into `CDATDUPL_DB` local tables — simpler ops, stale data risk.
- **Option C:** Remove route from menu if module not needed in production.

### Acceptance

- `php scripts/schema_audit.php` exits 0 on production DB
- IR detail page (`/interrogation-reports/detail`) loads NBWS section without error
- Training search works end-to-end

---

## Phase 2 — Security hardening (5–7 days)

### 2A — SQL injection sweep (remaining modules)

**Already hardened (use `sql_safe.php`):** summary/*, JRMS/*, PDACT/*, day-night/*, IMEI/*, IR search-by-head*, calls_btwn_dates, movements_between_two_numbers, movements_in_particular_place.

**Still need audit + fix:**

| File | Risk | Fix |
|------|------|-----|
| `modules/others/training_module1.php` | Column name whitelisted but `$searchNo` unbounded | `sql_safe_like_value()`, enum for `$criteria`/`$rank` |
| `modules/others/vehicle_search.php` | LIKE param | `sql_like_pattern()` |
| `modules/others/cellid_search.php` | Verify min prefix + bind | Already partially fixed — re-verify |
| `modules/address/address.php`, `bulkaddress.php` | Phone input | `sql_safe_phone()` |
| `modules/cdat/cdatcnts.php`, `otherscdat.php`, `bulk_cdat_contacts.php` | Phone/bulk | `sql_safe_phone()`, temp-table pattern |
| `modules/others/common_cnts.php`, `offender_fd.php`, `offender_search_by_mo.php` | Search params | `sql_safe_*` helpers |
| `modules/call-details/movements.php`, `movements_between_two_numbers_comparision.php` | Dates/phones | `sql_safe_phone()`, `sql_safe_date()` |
| `modules/jrms/jrms_search_for_uniquekey.php` | Key input | `sql_safe_digits()` or whitelist |
| `modules/interrogation-reports/ir_search.php` | Name/IR params | prepared + `sql_like_pattern()` |
| `modules/interrogation-reports/ir.php` | IRKEY | `sql_safe_enum` or strict alphanumeric pattern |
| `modules/administration/admin_create_user.php` | User CRUD | Audit all statements (mostly done) |
| `modules/data-upload/*.php` | File paths, job IDs | Validate IDs as integers/UUIDs |

**Deliverable:** new script `scripts/audit_sql_injection.sh` — fails on `WHERE col='$var'` or string-concat SQL with `$_POST`/`$_GET`.

### 2B — CSRF protection

| # | Task | Action |
|---|------|--------|
| 2.1 | Add CSRF helper | `modules/common/csrf.php`: `csrf_token()`, `csrf_verify()` |
| 2.2 | Issue token in layout | `modules/common/includes/layout.php` — hidden field helper |
| 2.3 | Protect admin POSTs | `admin_create_user.php`, all `data-upload/*` POST handlers |
| 2.4 | Protect login | Optional (lower priority); rate-limit is higher value |

### 2C — Session & auth

| # | Task | Action |
|---|------|--------|
| 2.1 | Secure cookie flags | `session_set_cookie_params(['httponly'=>true,'secure'=>true,'samesite'=>'Lax'])` in bootstrap (prod only) |
| 2.2 | Idle timeout | Expire session after N minutes in `audit_require_session()` |
| 2.3 | Login rate limit | Track failed attempts in DB or Redis; lockout after 5 failures / 15 min |
| 2.4 | Remove plaintext password path | After all users migrated to bcrypt, drop `hash_equals` fallback in `login.php` |

### 2D — Admin SQL console

Already read-only SELECT. Additional hardening:

- Max query length (e.g. 8 KB)
- Max rows returned (e.g. 10 000) — already may have limit; verify
- Statement timeout cap (e.g. 60s, not unlimited in prod)
- Optional: disable entirely in production via env `CDAT_SQL_CONSOLE=0`

### 2E — HTTP / nginx

| # | Task | Action |
|---|------|--------|
| 2.1 | Security headers | CSP, HSTS, X-Frame-Options, X-Content-Type-Options in `cdat-web.nginx.conf` |
| 2.2 | Block sensitive paths | Verify `config/`, `logs/`, `.env` denied (already in `.htaccess`) |
| 2.3 | SDR credentials | Confirm MSSQL vars only in Python `sdr_import/`, never in PHP |

### Acceptance

- `scripts/audit_sql_injection.sh` passes
- CSRF token required on admin user create/delete
- Login lockout tested manually
- Security checklist in `HANDOVER_CHECKLIST.md` all checked

---

## Phase 3 — QA & smoke tests (3–5 days, VPN)

### 3A — Manual checklist (sign-off sheet)

Execute every item in `docs/HANDOVER_CHECKLIST.md` on **production-like VPN host**. Record pass/fail + screenshot/log per row.

**Critical paths:**

| Route | Test input |
|-------|------------|
| `/login`, `/logout` | Valid + invalid creds, deactivated user |
| `/` | Dashboard loads |
| `/administration/create-user` | Create, edit role, deactivate |
| `/administration/user-activity` | Log entries appear |
| `/administration/sql-console` | SELECT only; write blocked |
| `/summary/total` | Known phone number |
| `/call-details/movements` | Known phone |
| `/others/cell-id` | 5+ char prefix |
| `/others/vehicle` | Sample reg no |
| `/jrms/name` | Sample name |
| `/interrogation-reports/name` | Sample IR name |
| `/interrogation-reports/detail` | Known IRKEY (incl. NBWS) |
| `/pd-act/name` | Sample name |
| `/imei-search/phones-by-imei` | Sample IMEI |
| `/day-night-location/top-10` | Known phone |
| `/cdat/bulk-contacts` | 2–3 phones |
| `/data-upload/cdr` | Test CSV → worker → job status |
| `/api/police-stations`, `/api/divisions`, `/api/years`, `/api/crime-numbers` | JSON 200 |

### 3B — Automated smoke script

Create `scripts/smoke_routes.sh`:

```bash
# Curl each GET route with session cookie; expect HTTP 200 (not 500)
# Requires SMOKE_BASE_URL, SMOKE_USER, SMOKE_PASS env vars
```

Create `scripts/smoke_search_post.sh` for one POST per module with canned test data (run on VPN only).

### Acceptance

- Signed `HANDOVER_CHECKLIST.md` (all boxes checked)
- Smoke scripts pass on staging/VPN
- Zero HTTP 500 on listed routes

---

## Phase 4 — CI & automated tests (5–7 days)

### 4A — GitHub Actions (or GitLab CI)

Create `.github/workflows/ci.yml`:

```yaml
jobs:
  php-lint:
    - find modules -name '*.php' | xargs php -l
  mssql-audit:
    - bash scripts/audit_mssql_usage.sh
  sql-injection-audit:
    - bash scripts/audit_sql_injection.sh   # Phase 2 deliverable
  python-tests:
    - pytest cdr_import/normalize/ document_processing/
  schema-audit:
    - php scripts/schema_audit.php          # VPN/staging DB secret in CI
```

### 4B — PHP unit tests

Add `tests/php/` with PHPUnit:

| Test file | Covers |
|-----------|--------|
| `SqlSafeTest.php` | `sql_safe_phone`, `sql_safe_date`, `sql_like_pattern`, `sql_safe_enum` |
| `LoginTest.php` | Prepared statement path (mock PDO) |
| `SqlConsoleTest.php` | `cdat_sql_console_validate()` rejects INSERT/UPDATE |
| `RoutesTest.php` | Every route in `routes/web.php` resolves to existing handler file |

Bootstrap: `tests/php/bootstrap.php` loads `modules/common/sql_safe.php` without DB.

### 4C — Integration tests (VPN/staging only)

| Test | DB required |
|------|-------------|
| Login → session cookie | Yes |
| `/api/police-stations` returns JSON array | Yes |
| Summary search returns ≤501 rows | Yes |

Run nightly on staging, not every PR (VPN latency).

### Acceptance

- CI green on `main`
- PRs blocked if audit scripts fail
- ≥20 PHPUnit tests passing

---

## Phase 5 — Ops & monitoring (3–5 days)

| # | Task | Deliverable |
|---|------|-------------|
| 5.1 | Error logging | PHP `error_log` → centralized file; rotate daily |
| 5.2 | App health endpoint | `GET /health` → JSON `{db: ok, version: ...}` |
| 5.3 | PostgreSQL backups | Daily `pg_dump CDATDUPL_DB`; restore drill documented |
| 5.4 | CDR worker monitoring | Systemd unit or cron; alert if `worker.py` not running |
| 5.5 | Deploy runbook | Extend `docs/DEPLOY.md`: staging → prod, rollback, env rotation |
| 5.6 | SDR runbook | Separate `docs/SDR_PIPELINE.md` for `sdr_import/` MSSQL ops |

### Acceptance

- Backup restore tested once
- Health check returns 200 on prod
- On-call runbook exists

---

## Phase 6 — Audit sign-off (1–2 weeks)

| # | Task | Owner |
|---|------|-------|
| 6.1 | Internal security review | Dev team walkthrough of Phase 2 changes |
| 6.2 | External pen test (optional but needed for strict 10/10) | Third party |
| 6.3 | Management demo | All modules on VPN |
| 6.4 | Sign-off table | `HANDOVER_CHECKLIST.md` — Developer, QA, Security dates filled |

### 10/10 definition

All of the following true simultaneously:

1. `schema_audit.php` exits 0 on production DB
2. All HANDOVER_CHECKLIST items checked with evidence
3. CI pipeline green; no audit script failures
4. No open HIGH/MEDIUM security findings
5. Ops runbook + backup restore verified
6. Zero known broken routes

---

## Dependency order

```
Phase 0 ──► Phase 1 (VPN) ──► Phase 3 (QA)
                │
                ▼
           Phase 2 (Security)
                │
                ▼
           Phase 4 (CI/tests) ──► Phase 5 (Ops) ──► Phase 6 (Sign-off)
```

Phase 2 and Phase 3 can overlap after Phase 1 schema gaps are closed.

---

## If time is limited (minimum viable 8.5/10)

Do only:

1. Phase 1 — fix both schema gaps
2. Phase 3 — full manual smoke test + sign-off
3. Phase 4A — CI with lint + mssql audit + php -l
4. Phase 2B + 2D — CSRF on admin + SQL console prod lockdown

Skip pen test and full PHPUnit suite until post-launch.

---

## File creation summary

| New file | Phase |
|----------|-------|
| `scripts/audit_sql_injection.sh` | 2 |
| `modules/common/csrf.php` | 2 |
| `scripts/smoke_routes.sh` | 3 |
| `scripts/smoke_search_post.sh` | 3 |
| `.github/workflows/ci.yml` | 4 |
| `tests/php/*` | 4 |
| `docs/SDR_PIPELINE.md` | 5 |
| `docs/schema_audit_latest.txt` | 1 |

---

## Tracking

Copy checklist into your issue tracker. Suggested labels: `phase-0` … `phase-6`, `vpn-required`, `security`, `blocked-on-db`.

Update this doc when each phase completes.
