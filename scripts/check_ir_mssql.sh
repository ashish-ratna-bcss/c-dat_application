#!/usr/bin/env bash
set -euo pipefail
SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W)

echo "=== IR MSSQL all target tables ==="
"${SC[@]}" -Q "SET NOCOUNT ON; USE mssql_dump_ir;
SELECT t.name, SUM(p.rows) cnt
FROM sys.tables t
JOIN sys.partitions p ON t.object_id=p.object_id
WHERE p.index_id IN (0,1)
  AND t.name IN (
    'IMAGE_TABLE','LOCAL_CONTACTS_FACILITATORS','HABITUAL_OFFENDERS','IR_PARTICULARS',
    'OFFENCE_DETAILS','DISPOSAL_OF_PROPERTY','FAMILY_HISTORY','BRIEF_FACTS','JAIL',
    'PREVIOUS_OFFENCE_DETAILS','FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE',
    'RELATIONSHIP_WITH_OTHER_ASSOCIATES','ROWDY SHEETERS TO CHECK','ROWDY_SHEETERS_TOTAL'
  )
GROUP BY t.name ORDER BY cnt DESC;"

echo
echo "=== USB dump files ==="
ls -lh "/media/hyd-cat/Extreme SSD"/*.BAK 2>/dev/null || ls -lh /media/hyd-cat/*/ 2>/dev/null | head -20
