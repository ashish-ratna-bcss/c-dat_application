# C-DAT Production Security Audit & Hardening Report

**System:** C-DAT — Call Data Analysis Tool (Hyderabad City Police)  
**Date:** 2026-09-03  
**Scope:** Full repository audit + in-repo hardening  
**Classification:** Sensitive — law-enforcement investigative data  

---

## Executive Summary

C-DAT was **not production-ready** before this hardening pass. The highest-severity issue was **missing server-side authentication on nearly all investigative modules**, allowing unauthenticated direct-URL access to CDR, IR, JRMS, PD Act, offender lists, and related datasets.

Hardening implemented in this pass closes that gap and strengthens authentication, upload API isolation, CSRF on upload mutations, SQL-console filters, CSV formula injection defenses, env loading, and deployment defaults.

| Area | Verdict |
|------|---------|
| Overall posture (after fixes) | **Improved — conditionally suitable** for production after operational items below |
| Critical risks remaining | Shared DB credential across FDW datasets; HTTPS/firewall must be applied in ops |
| High risks remaining | SQL console still app-layer read-only (needs DB role); large-upload DoS is operational |
| Production blockers (ops) | TLS/Nginx, firewall (5432/8088), API key, plaintext account migration, DB least privilege |

**Do not deploy to an investigator network without completing the Remaining Risks / operational checklist.**

---

## Architecture Security Review

```
USER → Nginx (HTTPS, intended) → PHP (main.php / PHP-FPM)
                                      ↓
                                 PostgreSQL (CDATDUPL_DB + FDW → IR/JRMS/PDACT/ROWDY/TRAINING)
                                      ↓
                     PHP /api/data-upload/* proxy → FastAPI (127.0.0.1) → local uploads + staging
                                      ↓
                     Optional MSSQL Docker (SDR — not implemented in current dataUpload/)
```

| Component | Trust notes |
|-----------|-------------|
| PHP | Authn/authz boundary for investigators |
| Nginx | Must be sole public entry; template in `deploy/cdat-web.nginx.conf` |
| FastAPI | Internal only; API key + loopback; browser no longer calls it directly |
| PostgreSQL | One app credential reaches all FDW-mounted datasets |
| Filesystem | Upload dirs under `dataUpload/uploads`; blocked from HTTP via `main.php` |
| MSSQL/Docker | Ops scripts only; SDR pipeline not live in this tree |

---

## Authentication Audit

| Control | Status |
|---------|--------|
| Password hashing (bcrypt/argon2) | Verified on create/edit user |
| Plaintext login fallback | **Removed by default**; optional `CDAT_ALLOW_PLAINTEXT_MIGRATION=1` |
| Login lockout | `login_attempts` + env thresholds |
| Session regenerate on login | Yes |
| Idle timeout | Server-side via `CDAT_SESSION_IDLE_MINUTES` (default 30) |
| Cookie flags | HttpOnly, SameSite=Lax, Secure when HTTPS |
| Deactivated account mid-session | Revalidated ~every 60s |
| Role change mid-session | Role refreshed from DB on revalidate |
| `.env` → `getenv` | Fixed — `cdat_load_dotenv()` |

**Migration:** run `php scripts/list_plaintext_password_accounts.php` and reset or temporarily enable migration flag.

---

## Authorization Audit

| Role | Enforcement |
|------|-------------|
| user | Session required on all layout-backed investigative pages |
| poweruser | `audit_require_uploader()` on upload pages + proxy |
| admin | `audit_require_admin()` on admin pages |

Central gate: `modules/common/includes/layout.php` calls `audit_require_session()` on include (covers AJAX paths that skip `layout_begin`). Cascading `/api/police-stations|years|crime-numbers|divisions` now require session.

Menu visibility remains UI-only (documented) — pages enforce independently.

IDOR: within an authenticated investigator role, searches are intentionally org-wide (by design for LE tools). Cross-module links remain subject to the same session gate. No per-station row-level ACL exists (documented remaining risk / product decision).

---

## Data Protection Audit

Unauthenticated disclosure of CDR/SDR/IR/JRMS/PD Act/offenders/exports was **CRITICAL** and is **fixed** via the layout session gate. Exports remain client-side CSV of already-authorized page results, with formula-injection prefixing. SQL-console export is admin + CSRF + formula-safe.

---

## Upload Security Audit

| Control | Status |
|---------|--------|
| Role gate (uploader) | PHP pages + proxy |
| Browser → FastAPI direct | **Removed** — `/api/data-upload/*` proxy |
| API key | Always required (except controlled local escape hatch) |
| Username on stage | Forced from session in proxy (not client) |
| CSRF on upload mutations | Required |
| Insert replay | FastAPI rejects already-inserted jobs |
| SDR | Not applicable — component not present in live `dataUpload/` |
| 700GB DoS | Operational limits / monitoring required |

---

## Database Security Audit

| Topic | Status |
|-------|--------|
| Parameterized searches | Generally used with `sql_safe_*` helpers |
| SQL console | Admin + kill switch + stronger denylist + timeouts/row caps |
| DB-level read-only role | **Ops required** — `sql/security_readonly_role.sql` |
| FDW shared credential | **Remaining MEDIUM/HIGH** — one compromise reaches all satellites |
| Error leakage | Generic client messages; details in logs |

---

## API Security Audit

FastAPI mutating endpoints always run `verify_api_key`. Non-loopback bind without key refuses startup. `/config` no longer exposes paths/DB user. Health errors sanitized. CORS still configurable; prefer explicit origins in production.

---

## Infrastructure Security Audit

| Item | Status |
|------|--------|
| HTTPS | **Not in runtime by default** — Nginx TLS template provided; must be deployed |
| API bind | `deploy/prod.sh` defaults to `127.0.0.1` |
| Port 5432 | Must remain firewall-local (ops) |
| Security headers | Set in `main.php` + Nginx template |
| `.env` / `config` / `logs` / `dataUpload` HTTP | Blocked in `main.php` |

---

## Logging & Audit Trail

Logged: login success, logout, searches (many modules), SQL console, upload approve, admin user actions. Failed logins in `login_attempts`. Passwords/tokens not logged. Authorization failures redirect/401 (consider richer audit logging as follow-up).

---

## Findings Table

| ID | Severity | Finding | Affected Component | Root Cause | Impact | Fix Implemented | Verification | Status |
|----|----------|---------|--------------------|------------|--------|-----------------|--------------|--------|
| F-01 | CRITICAL | Investigative pages lacked session guards | Most `modules/*` routes | Auth only on a few pages; layout did not enforce | Unauthenticated access to LE datasets | Session gate on `layout.php` include | `scripts/security_regression_check.sh` | Fixed |
| F-02 | HIGH | Plaintext password login fallback | `login.php` | Legacy compatibility | Credential theft / weak storage | Default refuse; migration flag | Regression script | Fixed |
| F-03 | HIGH | FastAPI open when API key unset | `dataUpload/main.py` | Optional auth_deps | Anonymous upload/stage/insert | Always verify; refuse bad binds | Code review + script | Fixed |
| F-04 | HIGH | Browser called FastAPI directly | Upload UI | No PHP session on API | Bypass PHP role checks if API exposed | PHP proxy `/api/data-upload/*` | Script + routes | Fixed |
| F-05 | HIGH | Cascading offence APIs unauthenticated | `get_ps/year/crno/division` | No guards | Data enumeration | `audit_require_session` | Script | Fixed |
| F-06 | MEDIUM | CDAT_* env ignored by PHP getenv | `db_connect.php` | Only DB keys parsed | Lockout/console toggles ineffective | `cdat_load_dotenv()` | Code review | Fixed |
| F-07 | MEDIUM | IMEI sibling page unguarded | `imeisinphone.php` | Inconsistent guards | Auth bypass on one IMEI path | Covered by layout gate | Script | Fixed |
| F-08 | MEDIUM | SQL console blacklist weak | `admin_sql_console.php` | Keyword-only | Privilege abuse if DB role strong | Stronger denylist + comment ban; docs for DB role | Code review | Partially fixed |
| F-09 | MEDIUM | Shared PG credential via FDW | `sql/fdw_setup.sql` | Single user mapping | Full dataset compromise | Documented; ops least-privilege guide | N/A | Requires ops |
| F-10 | MEDIUM | No HTTPS in default deploy | Nginx missing from repo | HTTP on app port | Session/data interception | Template + headers; ops must enable TLS | Template present | Requires ops |
| F-11 | MEDIUM | Upload CSRF incomplete | Upload fetch | No token on API POSTs | Cross-site upload/approve | CSRF on proxy POSTs | Code review | Fixed |
| F-12 | LOW | CSV formula injection | Client/SQL exports | Untrusted imported cells | Spreadsheet code exec on open | Prefix `=+-@` cells | Code review | Fixed |
| F-13 | LOW | `/config` leaked internals | FastAPI | Verbose config | Recon | Stripped response | Code review | Fixed |
| F-14 | LOW | Health exposed git rev | `health.php` | Info disclosure | Recon | Removed | Code review | Fixed |
| F-15 | LOW | Deactivated users kept session | Session helpers | No status recheck | Continued access after deactivate | Periodic DB revalidate | Code review | Fixed |
| F-16 | INFO | Duplicate PHP/Python insert paths | Upload history + FastAPI | Two callers | Rule drift risk | Both use FastAPI enqueue; PHP adds CSRF/audit | Review | Mitigated |

---

## Known Finding Regression Matrix

| Previous Finding | Current Status | Evidence | Fix | Regression Test |
|------------------|----------------|----------|-----|-----------------|
| 1. Missing auth on investigative pages | **Fixed** | `layout.php` calls `audit_require_session()` | Central session gate | `security_regression_check.sh` |
| 2. Legacy plaintext password fallback | **Fixed** (migration exception optional) | `login.php` gated by `CDAT_ALLOW_PLAINTEXT_MIGRATION` | Refuse plaintext by default | Script + list script |
| 3. Shared PG credential across FDW | **Still present** | `fdw_setup.sql` user mappings | Ops: separate roles | Manual DBA review |
| 4. SQL console app blacklist only | **Partially fixed** | Stronger filters; DB role SQL provided | App + ops DB role | Manual |
| 5. Duplicate approval logic | **Mitigated** | PHP proxies/calls FastAPI `enqueue_insert` | Single FastAPI authority | Code review |
| 6. Inconsistent IMEI guards | **Fixed** | Both IMEI pages use layout gate | Central auth | Script |

---

## Remaining Risks (operational / cannot fully fix in-repo)

1. **Deploy HTTPS** using `deploy/cdat-web.nginx.conf` (or equivalent) with real certificates.  
2. **Firewall:** bind/expose only web entry; keep `5432` and FastAPI ports on localhost.  
3. **Set `DATA_UPLOAD_API_KEY`** and keep `DATA_UPLOAD_HOST=127.0.0.1`.  
4. **Keep `CDAT_SQL_CONSOLE=0`** unless needed; if enabled, apply `sql/security_readonly_role.sql` and use a non-superuser app role.  
5. **Migrate plaintext password accounts** (`list_plaintext_password_accounts.php`).  
6. **Rotate DB passwords** at handover; avoid superuser as `CDR_DB_USER`.  
7. **Large-upload DoS:** disk quotas, monitoring, concurrent job limits.  
8. **No per-user data compartmentalization** (by design) — any authenticated user can search org-wide investigative data.  
9. **External dependency CVE scanning** for PHP vendor assets / `requirements.txt` on a recurring schedule.  
10. **Pen-test** on a staging copy before go-live.

---

## Not applicable — component not present

OAuth/JWT/OTP, Redis/Kafka/RabbitMQ/Kubernetes, GPU/vector/RAG/LLM security, public registration, email verification, live SDR FastAPI pipeline.

---

## Acceptance criteria checklist

- [x] Sensitive investigative routes require authentication  
- [x] Role checks on admin/uploader functions  
- [x] Plaintext password path disabled by default  
- [x] Upload path cannot bypass PHP session (proxy)  
- [x] FastAPI not open by default; prod bind loopback  
- [x] SQL console safeguards strengthened (DB role still ops)  
- [x] Security regression script added and passing  
- [ ] HTTPS live in production environment (ops)  
- [ ] PostgreSQL/FastAPI firewall confirmed (ops)  
- [ ] Plaintext accounts remediated (ops)  

---

## How to re-verify

```bash
./scripts/security_regression_check.sh
# With app running (expect redirect/401 without cookie):
curl -sI http://127.0.0.1:PORT/offenders-list/habitual
curl -sI http://127.0.0.1:PORT/interrogation-reports/detail?IRKEY=1
php scripts/list_plaintext_password_accounts.php
```
