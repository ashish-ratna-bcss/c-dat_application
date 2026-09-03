# Security Review Checklist

Updated after the 2026-09-03 production hardening pass. See `docs/SECURITY_AUDIT_REPORT.md` for the full audit.

## Authentication & session

- [x] Login uses prepared statements (`modules/common/login.php`)
- [x] Password hashing with bcrypt/argon2; plaintext login refused unless `CDAT_ALLOW_PLAINTEXT_MIGRATION=1`
- [x] Login rate limiting (`login_attempts` table)
- [x] Session idle timeout (`CDAT_SESSION_IDLE_MINUTES`, default 30) — `.env` loaded into `getenv`
- [x] Secure cookie flags (HttpOnly, SameSite, Secure when HTTPS)
- [x] Deactivated accounts / role changes revalidated server-side

## Authorization

- [x] `layout.php` enforces `audit_require_session()` on include (all investigative modules)
- [x] Admin / uploader guards on administrative and upload pages
- [x] Cascading `/api/*` lookup endpoints require session
- [x] Upload browser traffic goes through `/api/data-upload/*` PHP proxy

## CSRF

- [x] CSRF on admin user management and SQL console
- [x] CSRF on upload proxy mutating requests
- [x] CSRF meta + fetch wrapper for AJAX searches (client); server auth is session-based

## SQL safety

- [x] `sql_safe.php` helpers on search modules
- [x] Admin SQL console read-only intent with expanded denylist, timeouts, row caps
- [x] `CDAT_SQL_CONSOLE=0` disables console in production
- [ ] **Ops:** apply DB read-only role (`sql/security_readonly_role.sql`) if console enabled

## HTTP / infrastructure

- [x] Security headers in `main.php` and `deploy/cdat-web.nginx.conf`
- [x] `config/`, logs, `dataUpload`, `.env` blocked from HTTP
- [x] `/health` endpoint (no secrets / no git rev)
- [ ] **Ops:** enable TLS via Nginx; firewall 5432 and FastAPI ports to localhost

## Upload API

- [x] FastAPI always runs API-key dependency
- [x] `deploy/prod.sh` defaults API host to `127.0.0.1`
- [x] Set `DATA_UPLOAD_API_KEY` in production `.env`

## Database

- [x] PHP runtime PostgreSQL-only
- [ ] **Ops:** least-privilege app role; FDW privilege separation where practical
- [ ] **Ops:** rotate production DB passwords at handover

## Regression

```bash
./scripts/security_regression_check.sh
```

## Outstanding (operator)

- [ ] External penetration test on staging
- [ ] Remediate plaintext password accounts (`php scripts/list_plaintext_password_accounts.php`)
- [ ] Confirm HTTPS and firewall before investigator go-live

## Sign-off

| Reviewer | Role | Date | Notes |
|----------|------|------|-------|
| | Developer | | |
| | Security | | |
| | QA | | |
