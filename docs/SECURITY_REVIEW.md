# Security Review Checklist (10/10 Sign-off)

Internal security review template. Complete before production handover.

## Authentication & session

- [x] Login uses prepared statements (`modules/common/login.php`)
- [x] Password hashing with bcrypt upgrade path
- [x] Login rate limiting (`login_attempts` table)
- [x] Session idle timeout (`CDAT_SESSION_IDLE_MINUTES`, default 30)
- [x] Secure cookie flags (HttpOnly, SameSite, Secure when HTTPS)

## CSRF

- [x] CSRF tokens on admin user management forms
- [x] CSRF on data-upload POST/AJAX (`csrf.php` + meta tag + fetch wrapper)
- [x] CSRF on SQL console execute/export

## SQL safety

- [x] `sql_safe.php` helpers on all search modules
- [x] `scripts/audit_sql_injection.sh` in CI
- [x] Admin SQL console read-only with length/timeout/row caps
- [x] `CDAT_SQL_CONSOLE=0` disables console in production

## HTTP / infrastructure

- [x] Security headers in `cdat-web.nginx.conf`
- [x] `config/`, logs blocked from HTTP
- [x] `/health` endpoint for monitoring (no secrets)

## Database

- [x] PHP runtime PostgreSQL-only (`audit_mssql_usage.sh`)
- [x] Schema audit gate (`schema_audit.php` exits 1 on gaps)
- [x] Training + NBWS tables via import scripts

## Outstanding (operator)

- [ ] External penetration test (optional for strict audit)
- [ ] Rotate production DB passwords at handover
- [ ] Confirm `CDAT_SQL_CONSOLE=0` on production if console not needed

## Sign-off

| Reviewer | Role | Date | Notes |
|----------|------|------|-------|
| | Developer | | |
| | Security | | |
| | QA | | |
