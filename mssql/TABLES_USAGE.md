# MSSQL dump vs CDAT application table usage

Compared: dumps in `mssql/` vs PHP `Database=>` + SQL table names on `main`.

Status:

- **USED** — CDAT PHP queries this table
- **IN DUMP, NOT USED BY CDAT UI** — exists in SSMS script, no CDAT PHP reference found
- **USED BY APP, MISSING FROM DUMP** — PHP queries it; not in the matching dump file
- **NAME MISMATCH** — similar name in dump, not exact

Runtime today still maps these MSSQL DB names to Postgres via `sqlsrv_compat.php`. This comparison is against **legacy MSSQL schema**, not live Postgres.

---

## 1. CDATDUPL (`cdatdupl.sql`) — 46 tables + 9 views

### USED by CDAT (present in dump)

| Table / view | Kind | Used by |
| ------------ | ---- | ------- |
| CDATPCSUSPECT | table | SUM, CALLS, IMEI, D&N, MOVEMENTS, CELLID, import |
| CDAT_DETAILS | view | SUM.PHP Total |
| CDAT_DETAILS1 | view | dated summaries, CDATCNTS, IMEI_REQUEST_SUM |
| CDATSUSPECT | table | nicknames / contacts joins |
| CDATADDRESS | table | ADDRESS + enrichment |
| ADDRESS_OTHER_STATE | table | other-state address |
| CDATCELLTOWERAREANEW | table | CELLID, D&N, movements |
| CELLTOWERFILTERED | table | NEAREST_CELLIDS |
| CDATPHONEAREA | table | prefix / area |
| CDAT_RTA | table | VEHICLE_SEARCH |
| CDAT_CIVILSUPPLY | table | ALLDATA_SEARCH / NAMESEARCH |
| CDAT_LICENCE | table | ALLDATA_SEARCH / NAMESEARCH |
| CDAT_PASSPORT | table | ALLDATA_SEARCH |
| COMPLETE_MO_CLASSIFICATION | table | OFFENDER_FD |
| ROWDY_SHEETER_DATA1 | table | rowdy sheeter search |
| MO_IMAGE_TABLE | table | MO_IMAGE_LIST / OFFENDER_FD (PHP often uses FORMS; table lives in CDATDUPL dump) |
| SUSPECT_IMAGE_TABLE | table | IR suspect photos / migrate |
| CDAT_PROVIDER_MASTER | table | CDR import normalizers |
| CDAT_STATE_MASTER | table | CDR import normalizers |
| MNC_CODES | table | cdr_import VI normalizer |
| CDATCELLTOWERAREANEW_MAX / _MIN / _BTSMAX / CELLTOWER_DETAILS | views | tower helper views (enrichment / reports) |

### USED by CDAT but **MISSING from CDATDUPL dump**

| Table | Evidence | Impact |
| ----- | -------- | ------ |
| **CDATSUSPECT2** | ALLDATA_SEARCH, NAMESEARCH | name/suspect extra search may fail on this MSSQL DB |
| **CALLCENTER_NOS** | OTHERCDAT.php | call-center number lookup missing |
| **IMAGES_BASE64_FORMAT** | BULK_GANG_ID_SEARCH | gang image search missing |
| **ISD_DATA_TOT_2012** | VBR_SEARCH.PHP | VBR ISD search missing |
| **ISD_DATA_TOT_2013** | VBR_SEARCH.PHP | VBR ISD search missing |
| **isd_data_tot** | VBR_SEARCH.PHP | VBR ISD search missing |

### IN DUMP, not used by CDAT UI (junk / old / test)

ABCD_CAT, Access_Log, accesslogs, CDAT_GAS_DETAILS, CDAT_ZONE_PS, CDATADDRESS_OLD, CDATCELLTOWER, CDATCELLTOWER1, CDATCELLTOWERAREANEW2, cdatdupl1CDATSUSPECT, CDATDUPLCDATSUSPECT, CDATSUSPECT_DAU, MCC_MNC, ndps_abstract_1, NDPS_HNEW_CALLDETAILS, OLD_CELLTOWERIDS, pdact_main, PP, RTA_ACTIVA, RTA_DATABASE, sai_suspect_25_11_2025, sai_test_cdatdata, sai_TEST_susepct, sai_test1, SMS_SERVICE_AREA_CODE, SMS_SERVICE_PROVIDER_AREA_CODE, SMS_SERVICE_PROVIDER_CODE, WASTENOS, ALL_ADDRESSES, ALL_ADDRESS_VIEW, ANALYSIS_WING1, CDATCELLTOWERAREANEW_MAX1

---

## 2. IR (`IR.sql`) — 114 tables

This **is the IR database**. SSMS script header is `CREATE DATABASE [FORMS]` because that is the SQL Server catalog name. CDAT IR pages connect with `Database=>'FORMS'` or `IRFORMS`.

### USED by CDAT (present in dump)

| Table | Used by |
| ----- | ------- |
| LOGINS | LOGIN.PHP, ADMIN_CREATE_USER |
| IR_PARTICULARS | IR forms, retrieve, CDAT_IRFORM |
| IMAGE_TABLE | IMAGE_LIST, IR display |
| OFFENCE_DETAILS | IR offence, DUMP_SEARCH |
| PREVIOUS_OFFENCE_DETAILS | IR previous offence |
| PREVIOUS_OFFENCE_DETAILS1 | PREVIOUS_OFFENCE_DETAILS1.PHP |
| BRIEF_FACTS | HOME_IR |
| FAMILY_HISTORY | HOME_IR |
| DISPOSAL_OF_PROPERTY | HOME_IR |
| LOCAL_CONTACTS_FACILITATORS | HOME_IR |
| RELATIONSHIP_WITH_OTHER_ASSOCIATES | HOME_IR |
| NBWS_VERIFY_DATA | close name to IR pending-NBWS (see mismatch) |

### USED by CDAT but **MISSING from IR dump**

| Table | Evidence | Note |
| ----- | -------- | ---- |
| **ANALYSIS_ABSTRACT** | ANALYSIS_ABSTRACT.PHP | not in IR dump |
| **FORMS240719** | SUSPECT_SEARCH.PHP | not in dump |
| **MO_IMAGE_TABLE** | MO_IMAGE_LIST | **exists in CDATDUPL dump, not FORMS** |
| **SUSPECT_IMAGE_TABLE** | IR photos | **exists in CDATDUPL dump, not FORMS** |
| **MULAKATH_ENTRY** | MULAKATH_ENTRY.PHP uses `Database=> JRMS` | **exists in JRMS dump, not FORMS** — OK if PHP stays on JRMS |
| **HABITUAL** | HABITUAL.PHP / `IRFORMS..HABITUAL` | dump has **HABITUAL_OFFENDERS**, not `HABITUAL` |

### NAME MISMATCH (IR / FORMS catalog)

| PHP uses | Dump has |
| -------- | -------- |
| NBWS_VERIFY_DATA_IMPORTANT | NBWS_VERIFY_DATA |
| HABITUAL (IRFORMS..) | HABITUAL_OFFENDERS |

### IN DUMP, not used by CDAT UI

Most other FORMS tables are one-off lists / backups / Excel imports, including: ABSTRACT_*, ARREST_*, AUTO_*, CATTLE_THEFT, CHILLI_POWDER_*, DUPLICATE_OFFENCE_DETAILS, FAMILY, fingerprint_*, GANG_*, HYD_OFFENDER_DATA, IR_FORMS_MUTIPLE, IR_MULTIPLE_CRIMES, IR_PARTICULARS1, IRPHOTOS_1, JAN_TO_JUNE_IR_DATA, JEWELRY_SHOP, mahesh*, MO_LIST*, MOBILE_SNATCH*, NBWS_*, NICK*, NUMBER_PROFORMA, OFFENCE_DETAILS_OLD / _3 / _4 / _06_12_2025, PDACT*, PROPERTY_OFFENDERS_*, RECEIVERS_*, REPEATED_OFFENDERS_*, ROWDY*, SAMPLE*, Sheet1$, SNATCHING_*, SUS1/SUS2, Suspect_Sheets, TEMP_THEFT, TSCOP_MO_LIST1, UI, WARRANT_PURPOSE, `07032017 FAMILY_HISTORY`, ab/ab1/ab2/ab3, am, BA, HANUMAN, ma, nk, twoandabove, SERIES, SIX_MNTHS_DATA, FOUND_MISSING_CCNO, GANGS_ARREST_DATE, HABITUAL_OFFENDERS_TOTAL1, IR, IR_PARTICULARS_CRIME, ir_previous_2017, JRMS / jrms_chanchalguda (copies inside FORMS), MULAKATH_DETAILS, PDACT_MATCHING_IR*, pdcell_data, ROWDY SHEETERS TO CHECK, Suspect_sheet matched with IR, etc.

---

## 3. JRMS (`JRMS.sql`) — 89 tables

### USED by CDAT (present in dump)

| Table | Used by |
| ----- | ------- |
| JRMS_TOTAL_2012_TO_2017 | all JRMS search + unique-key UPDATE |
| JAIL | JRMS hub filters (Cherlapalli / Chanchalguda) |
| MULAKATH_ENTRY | MULAKATH_ENTRY.PHP (`Database=> JRMS`) |

### USED by CDAT — not missing from JRMS dump

No extra JRMS table names from PHP beyond the three above (plus `#TEMP` session tables, which are not permanent dump objects). Dump also contains `#temp_Data` (SSMS temp leftover).

### IN DUMP, not used by CDAT UI

Almost everything else is backup / test / month slices: `1`, `11`, `a`, `aa`, `AB`, `ABC`, `abcd`, `abcde`, `AC`, `AR`, `b`, `b_jrms`, ARRESTED_*, CHERLAPALLI*, CHANCHALGUDA_*, CDAT_JRMS_*, JRMS_BACKUP_*, jrms_total / jrms_total_old / jrms1 / JRMS1119, MAHESH*, photos_data*, TEMP_AIRTEL_NEW, TEMP_JIO1, Sheet1$, SNATCHING, SUS1, nikesh.JRMS_FILTERED_FINAL, etc.

---

## 4. PDACT (`PDACT.sql`) — 39 tables

### USED by CDAT (present in dump)

| Table | Used by |
| ----- | ------- |
| PDACT_MAIN_TABLE | PDACT_MAIN*, PS-wise, MO search, submit |
| PDACT_PRESS_NOTES_TABLE | PDACT_PRESS_NOTES.PHP |

### USED by CDAT but missing from PDACT dump

None identified beyond the two above.

### IN DUMP, not used by CDAT UI

`111`, `222`, `a`, `AB`, `D`, `JAHA`, jahangir_pdact, NBWS_*, not_found22, NOT_IN_PDACT_20, notin_data24, PDACELL, pdact_check_with_pdcell, PDACT_FOR_APP, PDACT_FROM_SERVER, PDACT_JAHAGIR, PDACT_MAIN_TABLE_WITH_CALL_KEYS, pdact_main_table1, pdact_ndps_*, PDACT_NOT IN IRS, PDACT_PDCELL_RAW_DATA, pdact_practice, pdact_previous_match, pdact_rough_table, PDACT_TOTAL_*, pdact_with_photos_keys, pdcell_pdact_data1, Query, `rowdy_pdact _20`, WITH_AGE, B_PDACT_FROM_IR_ADDRESS

---

## 5. Whole databases the app uses but **you did not provide dumps for**

| Logical DB | App uses | Tables PHP expects (examples) |
| ---------- | -------- | ----------------------------- |
| **IRFORMS** | CDAT_IRFORM, IR_NDPS*, HABITUAL (`IRFORMS..`) | Same IR schema as `IR.sql` if IRFORMS = FORMS alias. Separate dump only if IRFORMS is another server DB. |
| **TWRMDB** | INTER_TOWER_CALLS_TWR, PRE_OFF_SEARCH_TWR, DUMP_SEARCH | **TWRMDB_MASTER_CDAT** |
| **LOSTREPORT_HAWKEYE** | IMEI_REQUEST_* | LOST_REPORT_CDR_DATA, COMPLAINANT_DETAILS, IMEI_REQUESTED_DETAILS |
| **MIGRANT_LABOURS_FORM** | MIGRANT_* | MIGRANT_LABOUR_TABLE, PS_NAMES |
| **CAFs** | CAF_SEARCH | IO_DETAILS |
| **TRAINING_DB** | TRAINING_MODULE1 | TRAINING_STRENGTH_PARTICULARS, TRNG_ATT_WITH_EMPID |
| **CIS_DATA_BASE** | CIS_DATA_NAME_SEARCH | CIS_COMPLETE_DATA |

Postgres-only (not MSSQL): `user_sessions`, `user_activity_logs`, `document_jobs`, `upload_*`, `cdatpcsuspect_staging`.

---

## 6. Missing summary (what to get next)

### High — app will break without these objects

1. Provide **IRFORMS** dump (or confirm IRFORMS = FORMS on server).
2. Provide **TWRMDB** dump (`TWRMDB_MASTER_CDAT`).
3. In CDATDUPL: **CALLCENTER_NOS**, **CDATSUSPECT2**, **IMAGES_BASE64_FORMAT**, **ISD_DATA_TOT***.
4. Confirm **HABITUAL** vs **HABITUAL_OFFENDERS**.
5. Confirm **NBWS_VERIFY_DATA_IMPORTANT** vs **NBWS_VERIFY_DATA**.
6. **ANALYSIS_ABSTRACT** (FORMS) — missing from dump.
7. Hawkeye / Migrant / CAF / Training / CIS dumps if those menus stay in production.

### Low — dump has extra junk

JRMS/PDACT/FORMS/CDATDUPL contain many `sai_*`, `mahesh*`, `Sheet1$`, numbered test tables, and dated backups. CDAT UI does **not** use them.

---

## Counts

| Dump DB | Objects in script | Used by CDAT UI | Missing vs PHP | Unused extras |
| ------- | ----------------- | --------------- | -------------- | ------------- |
| CDATDUPL | 56 (46 table + 9 view + extras) | ~20 | 6 tables | ~30+ |
| IR (`IR.sql`, catalog FORMS) | 114 tables | ~12 | 4+ | ~100 |
| JRMS | 89 tables | 3 | 0 for core JRMS | ~85 |
| PDACT | 39 tables | 2 | 0 | ~37 |
