# Production Handover Checklist

Use this before code audit sign-off and production launch.

## Repository hygiene

- [ ] No `.env`, `config/db_config.php`, CSV/XLSX, or subscriber data in git
- [ ] `uploads/` and `var/cdr_documents/` emptied on deploy servers (inbox dirs exist, no CSV content)
- [ ] Migration scripts removed from tree (`migrate_copy`, `drop_mssql_*`, `distributed_migrate/`, root SQL dumps)
- [ ] `qrcode/` trimmed to `php/`, `data/*.dat`, `image/*.png` only

## Automated gates

```bash
# Must exit 0
bash scripts/audit_mssql_usage.sh

# Review output — must exit 0 (fails on missing tables)
php scripts/schema_audit.php

# SQL injection regression gate
bash scripts/audit_sql_injection.sh

# No syntax errors
find modules -name '*.php' -print0 | xargs -0 -n1 php -l

# Confirm migration artifacts gone from git
git ls-files | rg -i 'migrate_copy|drop_mssql|all_mssql|distributed_migrate|image_migrate' && echo FAIL || echo OK
```

## Database (VPN required)

- [ ] `CDATDUPL_DB` reachable from app host
- [ ] FDW applied: `bash sql/apply_fdw.sh`
- [ ] Key FDW tables present: `offence_details`, `jrms_total_2012_to_2017`, `ir_particulars`, `pdact_main_table`, `habitual_offenders`
- [ ] Satellite DB `TRAINING_DB` created and FDW-mounted: `bash scripts/import_training_data.sh`
- [ ] NBWS table on CDATDUPL: `bash scripts/import_nbws_table.sh`
- [ ] `php scripts/schema_audit.php` exits 0

### Schema objects (resolved in 10/10 cleanup)

| Object | Used by | Action |
|--------|---------|--------|
| `training_strength_particulars`, `trng_att_with_empid` | `training_module1.php` | Satellite **`TRAINING_DB`** + `bash scripts/import_training_data.sh` (creates DB, loads data, applies FDW) |
| `nbws_verify_data_important` | `ir.php` | `bash scripts/import_nbws_table.sh` (+ CSV/dump if available) |

## Application smoke tests

- [ ] Login / logout
- [ ] Dashboard
- [ ] User management (`/administration/users`) — create/edit/deactivate
- [ ] SQL console (admin)
- [ ] Activity log
- [ ] One search per module: movements, sum home, cell ID, vehicle, JRMS name, IR search, PDACT search
- [ ] API dropdowns: police stations, divisions, years, crime numbers
- [ ] CDR upload (admin) — file reaches worker, job status updates

## Security

- [ ] Admin POST forms use `no-ajax` (no duplicate panels from AJAX interceptor)
- [ ] Search pages use prepared statements / `sql_safe.php` helpers
- [ ] `config/`, `logs/`, `.log` files blocked from HTTP (nginx/Apache rules)
- [ ] CSRF verified on admin user create and CDR upload
- [ ] `bash scripts/audit_sql_injection.sh` passes
- [ ] `bash scripts/smoke_routes.sh` passes (set `SMOKE_BASE_URL`, `SMOKE_USER`, `SMOKE_PASS`)

## Deploy config

- [ ] Nginx `root` path set for production (see `docs/DEPLOY.md`)
- [ ] No Windows/XAMPP paths in `.htaccess` (removed in cleanup)
- [ ] `/profile` route removed (was broken `view/profile.html` 404)
- [ ] PHP `max_execution_time` adequate for heavy searches (global guard in `sum_ui.php`)

## Out of scope (unchanged)

- [ ] `sdr_import/` — intentional MSSQL for `.bak` restore; defer retirement if not launching SDR

## Sign-off

Complete [`SECURITY_REVIEW.md`](SECURITY_REVIEW.md) before final approval.

| Role | Name | Date |
|------|------|------|
| Developer | Implementation complete — pending QA VPN run | 2026-08-31 |
| QA | | |
| Security / Audit | Internal checklist in SECURITY_REVIEW.md | |
