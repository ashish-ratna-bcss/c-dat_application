# Zero-breakage architecture migration

## Goal

Modernize C-DAT structure (`public/`, `app/`, `bootstrap/`) without breaking
current URLs or features.

## Hard rules

1. No big-bang move of all root PHP/HTML files.
2. Old URLs stay valid until a replacement is proven.
3. Run `./scripts/smoke_test.sh` before and after each phase.
4. Rollback = `git revert` / restore previous commit if smoke fails.
5. `old_versionfiles/` is archive only — never the web root.
6. Keep `sqlsrv_compat.php` until a module is fully on PDO and unused.

## Current runtime

| Mode | How |
|------|-----|
| Dev (Mac) | `./scripts/run_dev_server.sh` or PHP built-in + `scripts/dev_router.php` on `:8020` |
| Prod | Nginx + PHP-FPM; see `cdat-web.nginx.conf` + `scripts/nginx-url-alias-map.conf` |
| DB | `db_config.php` loads `.env` (`CDR_DB_*`) |

## Phases

| Phase | Status | What |
|-------|--------|------|
| 0 | Done | smoke script + this doc |
| 1 | Done | `bootstrap/`, `app/`, `public/` delegate to existing root files |
| 2 | Done | `/health.php`; nginx/dev router keep live URLs |
| 3 | Done (wave 1) | Auth, Home, Search, Imei, Cdr, Admin forwarders — see MODULE_MIGRATION_STATUS.md |
| 4 | Done | nginx `root` → `public/`; `sqlsrv_compat` retained until unused |

## Smoke

```bash
./scripts/smoke_test.sh http://127.0.0.1:8020
# Optional Phase 4 front controller:
# CDAT_DOCROOT=public ./scripts/run_dev_server.sh
# ./scripts/smoke_test.sh http://127.0.0.1:8020
```

## Module migration status

See `docs/MODULE_MIGRATION_STATUS.md` (updated as modules move).
