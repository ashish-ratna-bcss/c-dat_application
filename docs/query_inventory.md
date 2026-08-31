# Query inventory — modules/ native PostgreSQL rewrite

Reference catalogs (not loaded at runtime):

- `all_mssql_queries_extracted.sql` — original MSSQL with `Used in: modules/...` markers
- `all_postgress_queries_created.sql` — PG translations (update as PHP is converted)

## Status: converted to native PG SQL

### JRMS (4 files)

| File | FDW tables | Status |
|---|---|---|
| `modules/jrms/jrms_search_by_dates.php` | `jrms_total_2012_to_2017`, `ir_particulars` | Done — temp tables, `TO_CHAR`, regex numeric |
| `modules/jrms/jrms_ps_wise_search.php` | same | Done |
| `modules/jrms/jrms_name_search_php.php` | same | Done |
| `modules/jrms/jrms_search_for_uniquekey.php` | same | Done |

### PDACT (5 files)

| File | Status |
|---|---|
| `modules/pd-act/pdact_main.php` | Done |
| `modules/pd-act/pdact_mo_search.php` | Done |
| `modules/pd-act/pdact_ps_wise_search.php` | Done |
| `modules/pd-act/pdact_search.php` | Done |
| `modules/pd-act/pdact_main.php` | Done |

### Summary (6 files)

| File | Status |
|---|---|
| `modules/summary/sum_home.php` | Done — enrichment inlined |
| `modules/summary/sum_between_dates.php` | Done |
| `modules/summary/sum_in_state.php` | Done |
| `modules/summary/sum_out_state.php` | Done |
| `modules/summary/sum_new_nos.php` | Done |
| `modules/summary/sum_isd_cnts.php` | Done |

### IR (4 files)

| File | Status |
|---|---|
| `modules/interrogation-reports/ir.php` | Done |
| `modules/interrogation-reports/ir_search.php` | Done |
| `modules/interrogation-reports/ir_search_by_head.php` | Done |
| `modules/interrogation-reports/ir_search_by_head_gender.php` | Done |

### Call-details + day/night (enrichment inlined)

| File | Status |
|---|---|
| `modules/call-details/movements.php` | Done |
| `modules/call-details/calls_btwn_dates.php` | Done |
| `modules/call-details/movements_between_two_numbers.php` | Done |
| `modules/call-details/movements_between_two_numbers_comparision.php` | Done |
| `modules/call-details/movements_in_particular_place.php` | Done |
| `modules/day-night-location/day&nightloc.php` | Done |
| `modules/day-night-location/day&nightloc_btwn_dates.php` | Done |
| `modules/cdat/cdatcnts.php` | Done |

### Address / CDAT / Others

| File | Status |
|---|---|
| `modules/address/address.php` | Done |
| `modules/address/bulkaddress.php` | Done |
| `modules/cdat/bulk_cdat_contacts.php` | Done |
| `modules/cdat/otherscdat.php` | Done |
| `modules/others/common_cnts.php` | Done — `string_agg` replaces `FOR XML PATH` |
| `modules/others/vehicle_search.php` | Done — `cdat_rta` |
| `modules/others/cellid_search.php` | Done |
| `modules/offenders-list/habitual.php` | Done — FDW `habitual_offenders`, LIMIT 2000 |
| `modules/offenders-list/fp_list.php` | Done — LIMIT 2000 |
| `modules/others/training_module1.php` | Done — whitelisted column names + prepared statements |

## Removed runtime middleware

- `modules/common/sqlsrv_compat.php` — deleted
- `modules/common/cdr_enrichment_sql.php` — deleted (logic inlined in 9 callers)
- `translate_extracted_queries.php`, `migrate_php_to_pdo*.php` — deleted

## Kept connection layer

- `modules/common/bootstrap.php` → `modules/common/db_connect.php` → `get_cdat_pdo()`
- `modules/common/dbcontroller.php` — query helper (optional future removal)

## API dropdowns (PostgreSQL prepared statements)

| File | Status |
|---|---|
| `modules/common/get_ps.php` | Done — `offence_details` via FDW |
| `modules/common/get_division.php` | Done — `offence_details.sub_division` |
| `modules/common/get_year.php` | Done — prepared `crime_no` |
| `modules/common/get_crno.php` | Done — prepared `police_station` |

## Search performance guards

- `cdat_sum_ajax_need_search()` calls `cdat_sum_begin_heavy_search()` when a valid search runs (fixes PHP 30s timeout on all search pages).
- `cellid_search.php`, `vehicle_search.php`, JRMS detail searches: `LIMIT 500` + min prefix where applicable.

## Out of scope

- SDR `.bak` pipeline (`sdr_import/`) — unchanged; uses MSSQL restore tooling separately from PHP web pages.

## Grep gate

```bash
bash scripts/audit_mssql_usage.sh
# scans modules/, public/, routes/, sql/ (*.php, *.sql) — exit 0 = clean
```
