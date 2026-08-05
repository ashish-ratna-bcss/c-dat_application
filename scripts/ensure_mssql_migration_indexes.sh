#!/usr/bin/env bash
# Create MSSQL key indexes required for Citus migration batch reads.
# Without these, ORDER BY CDAT_SDR_KEY / TOWER_KEY does a full table scan (~153M / 75M rows) and hangs.
set -euo pipefail

LOG_DIR="${DIST_MIGRATE_LOG_DIR:-/mnt/storage1/ITCell_DL_RTA_Data/distributed_migrate_logs}"
mkdir -p "$LOG_DIR"
LOG="$LOG_DIR/mssql_index_build.log"

MSSQL_PASSWORD="${MSSQL_SA_PASSWORD:?MSSQL_SA_PASSWORD must be set}"
SQLCMD=(docker exec mssql /opt/mssql-tools18/bin/sqlcmd -S localhost -U sa -P "$MSSQL_PASSWORD" -C)

run_sql() {
  local db=$1
  local sql=$2
  echo "$(date -Is) [$db] $sql" | tee -a "$LOG"
  "${SQLCMD[@]}" -d "$db" -Q "$sql" 2>&1 | tee -a "$LOG"
}

echo "=== MSSQL migration index build started $(date -Is) ===" | tee -a "$LOG"

run_sql address_db "
IF NOT EXISTS (
  SELECT 1 FROM sys.indexes
  WHERE name = 'idx_cdat_sdr_key' AND object_id = OBJECT_ID('dbo.CDATADDRESS')
)
BEGIN
  PRINT 'Building idx_cdat_sdr_key on CDATADDRESS (CDAT_SDR_KEY) ...';
  CREATE NONCLUSTERED INDEX idx_cdat_sdr_key ON dbo.CDATADDRESS (CDAT_SDR_KEY) WITH (MAXDOP = 2);
  PRINT 'idx_cdat_sdr_key done';
END
ELSE
  PRINT 'idx_cdat_sdr_key already exists';
"

run_sql cellids_db "
IF NOT EXISTS (
  SELECT 1 FROM sys.indexes
  WHERE name = 'idx_tower_key' AND object_id = OBJECT_ID('dbo.CELLIDS')
)
BEGIN
  PRINT 'Building idx_tower_key on CELLIDS (TOWER_KEY) ...';
  CREATE NONCLUSTERED INDEX idx_tower_key ON dbo.CELLIDS (TOWER_KEY) WITH (MAXDOP = 2);
  PRINT 'idx_tower_key done';
END
ELSE
  PRINT 'idx_tower_key already exists';
"

echo "=== MSSQL migration index build finished $(date -Is) ===" | tee -a "$LOG"
