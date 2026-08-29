#!/usr/bin/env bash
# Show copy status: MSSQL source rows vs Postgres rows (5 new DBs only)
set -euo pipefail

if [[ -f /tmp/migrate_pgpass ]]; then export PGPASSWORD="$(cat /tmp/migrate_pgpass)"
else
  PGPASS=$(grep -E '^CDR_DB_PASSWORD=' /home/hyd-cat/systemd-install/cdat-web/.env 2>/dev/null | cut -d= -f2- | tr -d '"' || true)
  [[ -n "$PGPASS" ]] && export PGPASSWORD="$PGPASS"
fi
[[ -z "${PGPASSWORD:-}" ]] && { echo "No postgres password"; exit 1; }

SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W -h -1)

pg_count() {
  local db="$1" tbl="$2"
  psql -h localhost -U postgres -d "$db" -t -A -c "SELECT count(*) FROM $tbl;" 2>/dev/null || echo "ERR"
}

mssql_count() {
  local db="$1" tbl="$2"
  "${SC[@]}" -Q "SET NOCOUNT ON; USE [$db];
  IF OBJECT_ID(N'dbo.[$tbl]', N'U') IS NOT NULL
    SELECT SUM(p.rows) FROM sys.partitions p JOIN sys.objects o ON p.object_id=o.object_id
    WHERE o.name=N'$tbl' AND p.index_id IN (0,1);
  ELSE SELECT NULL;" 2>/dev/null | tr -d '\r' | grep -E '^[0-9]+$|NULL' | head -1
}

echo "========== COPY STATUS: MSSQL -> POSTGRES =========="
echo "Time: $(date)"
echo

print_job() {
  local pg_db="$1" pg_tbl="$2" mssql_db="$3" mssql_tbl="$4"
  local ms=$(mssql_count "$mssql_db" "$mssql_tbl")
  local pg=$(pg_count "$pg_db" "$pg_tbl")
  local status="PENDING"
  if [[ "$pg" == "ERR" ]]; then status="ERROR"
  elif [[ "$ms" == "NULL" || -z "$ms" ]]; then status="NO SOURCE"
  elif [[ "$pg" == "$ms" ]]; then status="DONE"
  elif [[ "$pg" != "0" && "$pg" -gt 0 ]]; then status="PARTIAL"
  elif [[ "$pg" == "0" ]]; then status="PENDING"
  fi
  printf "%-14s %-45s %-20s %-12s %-12s %s\n" "$pg_db" "$pg_tbl" "$mssql_tbl" "$ms" "$pg" "$status"
}

printf "%-14s %-45s %-20s %-12s %-12s %s\n" "POSTGRES DB" "POSTGRES TABLE" "MSSQL TABLE" "MSSQL ROWS" "PG ROWS" "STATUS"
printf "%-14s %-45s %-20s %-12s %-12s %s\n" "-------------" "--------------" "-----------" "----------" "-------" "------"

echo "--- PDACT_DB ---"
print_job PDACT_DB pdact_main_table mssql_dump_pdact PDACT_MAIN_TABLE

echo "--- JRMS_DB ---"
print_job JRMS_DB jrms_total_2012_to_2017 mssql_dump_jrms JRMS_TOTAL

echo "--- IR_DB (FORMS tables) ---"
for t in image_table local_contacts_facilitators ir_particulars offence_details disposal_of_property family_history brief_facts previous_offence_details relationship_with_other_associates; do
  mssql_t=$(echo "$t" | tr '[:lower:]' '[:upper:]')
  print_job IR_DB "$t" mssql_dump_ir "$mssql_t"
done

echo "--- ROWDY_SHEETS_DB ---"
print_job ROWDY_SHEETS_DB rowdy_sheeter_complete_data mssql_dump_ir "ROWDY_SHEETER_COMPLETE_DATA"

echo "--- CDATDUPL_DB (from CDATDUPL2 dump) ---"
print_job CDATDUPL_DB cdatsuspect mssql_dump_cdatdupl CDATSUSPECT
print_job CDATDUPL_DB cdatphonearea mssql_dump_cdatdupl CDATPHONEAREA
print_job CDATDUPL_DB cdat_civilsupply mssql_dump_cdatdupl CDAT_CIVILSUPPLY
print_job CDATDUPL_DB cdat_gas_details mssql_dump_cdatdupl CDAT_GAS_DETAILS
print_job CDATDUPL_DB cdat_passport mssql_dump_cdatdupl CDAT_PASSPORT
print_job CDATDUPL_DB complete_mo_classification mssql_dump_cdatdupl COMPLETE_MO_CLASSIFICATION
print_job CDATDUPL_DB mo_image_table mssql_dump_cdatdupl MO_IMAGE_TABLE
print_job CDATDUPL_DB mcc_mnc mssql_dump_cdatdupl MCC_MNC
print_job CDATDUPL_DB mnc_codes mssql_dump_cdatdupl MNC_CODES
print_job CDATDUPL_DB cdatcelltowerareanew mssql_dump_cdatdupl CDAT_TOWERDATA

echo
echo "--- CDATDUPL_DB (missing from dump - cannot copy yet) ---"
for t in cdatpcsuspect cdataddress address_other_state cdat_rta cdat_licence cdat_provider_master cdat_state_master; do
  printf "%-14s %-45s %-20s %-12s %-12s %s\n" "CDATDUPL_DB" "$t" "-" "-" "$(pg_count CDATDUPL_DB $t)" "WAIT FOR DUMP"
done

echo
echo "--- Running migration jobs? ---"
ps aux | grep -E 'migrate_copy|migrate_cdr|migrate_dumps' | grep -v grep || echo "none"
