#!/usr/bin/env bash
set -euo pipefail
SA="$(docker exec mssql printenv MSSQL_SA_PASSWORD)"
SC=(docker exec -e "MSSQL_SA_PASSWORD=$SA" mssql /opt/mssql-tools18/bin/sqlcmd -C -S localhost -U SA -P "$SA" -W)

TABLES="JAIL ADDRESS_OTHER_STATE CDAT_LICENCE CDAT_RTA CDATADDRESS CDATADDRESS_OLD CDATPCSUSPECT ROWDY_SHEETER_DATA1 CDAT_PROVIDER_MASTER CDAT_STATE_MASTER SUSPECT_IMAGE_TABLE NDPS_ABSTRACT_1 ROWDY_SHEETER_COMPLETE_DATA CDATCELLTOWERAREANEW"

echo "=== Exact table search in 4 MSSQL dumps ==="
for db in mssql_dump_pdact mssql_dump_jrms mssql_dump_ir mssql_dump_cdatdupl; do
  echo "--- $db ---"
  for t in $TABLES; do
    "${SC[@]}" -Q "SET NOCOUNT ON; USE [$db]; IF OBJECT_ID(N'dbo.[$t]', N'U') IS NOT NULL BEGIN DECLARE @r bigint; SELECT @r=SUM(p.rows) FROM sys.partitions p JOIN sys.objects o ON p.object_id=o.object_id WHERE o.name=N'$t' AND p.index_id IN (0,1); SELECT '$t' tbl, @r rows; END" 2>/dev/null | grep -v "^Changed" | grep -v "^$" || true
  done
done

echo
echo "=== Similar names (LIKE search) for missing ones ==="
for pat in '%JAIL%' '%ADDRESS%' '%LICENCE%' '%RTA%' '%PCSUSPECT%' '%ROWDY_SHEET%' '%PROVIDER%' '%STATE_MASTER%' '%SUSPECT_IMAGE%' '%NDPS%'; do
  echo "pattern: $pat"
  for db in mssql_dump_pdact mssql_dump_jrms mssql_dump_ir mssql_dump_cdatdupl; do
    "${SC[@]}" -Q "SET NOCOUNT ON; USE [$db]; SELECT '$db' db, t.name, SUM(p.rows) cnt FROM sys.tables t JOIN sys.partitions p ON t.object_id=p.object_id WHERE p.index_id IN (0,1) AND t.name LIKE '$pat' GROUP BY t.name;" 2>/dev/null | grep -v "^Changed" | grep -v "^$" || true
  done
  echo
done
