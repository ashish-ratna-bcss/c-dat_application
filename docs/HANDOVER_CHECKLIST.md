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

# Review output — document any missing FDW tables
php scripts/schema_audit.php

# No syntax errors
find modules -name '*.php' -print0 | xargs -0 -n1 php -l

# Confirm migration artifacts gone from git
git ls-files | rg -i 'migrate_copy|drop_mssql|all_mssql|distributed_migrate|image_migrate' && echo FAIL || echo OK
```

## Database (VPN required)

- [ ] `CDATDUPL_DB` reachable from app host
- [ ] FDW applied: `bash sql/apply_fdw.sh`
- [ ] Key FDW tables present: `offence_details`, `jrms_total_2012_to_2017`, `ir_particulars`, `pdact_main_table`, `habitual_offenders`

### Known schema gaps (document for QA; not PHP/MSSQL issues)

| Object | Used by | Action |
|--------|---------|--------|
| `training_db` / training tables | `training_module1.php` | Import or FDW if module needed |
| `nbws_verify_data_important` | `ir.php` | FDW import or table create |

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
- [ ] SDR MSSQL credentials not exposed in PHP web layer

## Deploy config

- [ ] Nginx `root` path set for production (see `docs/DEPLOY.md`)
- [ ] No Windows/XAMPP paths in `.htaccess` (removed in cleanup)
- [ ] `/profile` route removed (was broken `view/profile.html` 404)
- [ ] PHP `max_execution_time` adequate for heavy searches (global guard in `sum_ui.php`)

## Out of scope (unchanged)

- [ ] `sdr_import/` — intentional MSSQL for `.bak` restore; defer retirement if not launching SDR

## Sign-off

| Role | Name | Date |
|------|------|------|
| Developer | | |
| QA | | |
| Security / Audit | | |
