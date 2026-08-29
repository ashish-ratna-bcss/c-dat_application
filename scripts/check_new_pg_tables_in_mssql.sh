#!/usr/bin/env bash
# Check ONLY new Postgres DB schema tables vs 4 MSSQL dumps
set -euo pipefail
SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W -h -1)

lookup() {
  local pg_table="$1"
  local mssql_name="$2"
  for db in mssql_dump_pdact mssql_dump_jrms mssql_dump_ir mssql_dump_cdatdupl; do
  "${SC[@]}" -Q "SET NOCOUNT ON; USE [$db];
  IF OBJECT_ID(N'dbo.[$mssql_name]', N'U') IS NOT NULL
  BEGIN
    DECLARE @r bigint;
    SELECT @r=SUM(p.rows) FROM sys.partitions p JOIN sys.objects o ON p.object_id=o.object_id
    WHERE o.name=N'$mssql_name' AND p.index_id IN (0,1);
    SELECT '$pg_db|$pg_table|$db|$mssql_name|'+CAST(@r AS varchar(20));
  END" 2>/dev/null | grep -v "^Changed" | grep '|' && return 0
  done
  echo "$pg_db|$pg_table|MISSING|MISSING|0"
}

echo "pg_db|pg_table|mssql_db|mssql_table|rows"
echo "-----|--------|--------|-----------|----"

# PDACT_DB
pg_db=PDACT_DB
lookup pdact_main_table PDACT_MAIN_TABLE

# JRMS_DB
pg_db=JRMS_DB
lookup jrms_total_2012_to_2017 JRMS_TOTAL

# IR_DB
pg_db=IR_DB
for t in image_table local_contacts_facilitators habitual_offenders ir_particulars offence_details disposal_of_property family_history brief_facts jail previous_offence_details fingerprint_matched_undetected_cases_withimage relationship_with_other_associates; do
  lookup "$t" "$(echo "$t" | tr '[:lower:]' '[:upper:]')"
done

# ROWDY_SHEETS_DB
pg_db=ROWDY_SHEETS_DB
lookup rowdy_sheeter_complete_data ROWDY_SHEETER_COMPLETE_DATA
lookup rowdy_sheeter_complete_data ROWDY_SHEETERS_TOTAL
lookup rowdy_sheeter_complete_data "ROWDY SHEETERS TO CHECK"

# CDATDUPL_DB - dump tables only (exclude app tables)
pg_db=CDATDUPL_DB
for t in cdatpcsuspect cdatsuspect cdatcelltowerareanew cdatphonearea cdataddress address_other_state cdat_civilsupply cdat_gas_details cdat_licence cdat_passport cdat_provider_master cdat_rta cdat_state_master cdataddress_old complete_mo_classification rowdy_sheeter_data1 suspect_image_table mo_image_table mcc_mnc mnc_codes ndps_abstract_1; do
  lookup "$t" "$(echo "$t" | tr '[:lower:]' '[:upper:]')"
done
