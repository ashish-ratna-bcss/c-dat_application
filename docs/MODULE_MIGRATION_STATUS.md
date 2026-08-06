# Module migration status (strangle pattern)

Same URLs as before. Original root files are thin forwarders into `app/`.

| Module | URLs | Implementation | Status |
|--------|------|----------------|--------|
| Auth | `/LOGIN.PHP`, `/LOGIN.HTML`, `/check_role.php` | `app/Auth/` | Migrated (forwarders) |
| Home | `/HOME.html` | `app/Home/views/home.html` | Migrated (forwarder) |
| Search | `/ADDRESS.HTML`, `/CELLID_SEARCH.html` | `app/Search/views/` | Migrated (forwarders) |
| Imei | `/IMEISEARCH.html` | `app/Imei/views/` | Migrated (forwarder) |
| Cdr | `/SUM_HOME.html`, `/CALLDETAILS.PHP` | `app/Cdr/` | Migrated (forwarders) |
| Admin | `/admin_upload.php` | `app/Admin/upload_legacy.php` | Migrated (forwarder) |
| Ir / Jrms | (remaining root pages) | placeholders | Pending next waves |

## Notes

- `sqlsrv_compat.php` still required for login/CDR SQL — not retired.
- Smoke: `./scripts/smoke_test.sh http://127.0.0.1:8020`
