<?php
require_once dirname(__DIR__) . '/common/bootstrap.php';

require_once CDAT_COMMON . '/activity_logger.php';

class UploadVerificationService
{
    private PDO $db;
    private ?PDO $distDb = null;
    private array $dbConfig;

    public function __construct()
    {
        $this->dbConfig = require CDAT_CONFIG . '/db_config.php';
        $this->db = audit_db();
    }

    public function getBatchByLogId(int $logId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT b.*, l.module_name, l.file_name, l.document_job_id, l.username
            FROM upload_activity_logs l
            LEFT JOIN upload_staging_batches b ON b.batch_id = l.staging_batch_id
            WHERE l.id = :id
        ');
        $stmt->execute([':id' => $logId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        if (empty($row['batch_id']) && !empty($row['document_job_id'])) {
            $stmt2 = $this->db->prepare('SELECT * FROM upload_staging_batches WHERE document_job_id = :jid');
            $stmt2->execute([':jid' => $row['document_job_id']]);
            $batch = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($batch) {
                $row = array_merge($row, $batch);
            }
        }
        if (empty($row['batch_id']) && !empty($row['document_job_id'])) {
            
            $stmt3 = $this->db->prepare('
                SELECT b.*
                FROM document_jobs old_job
                JOIN document_jobs new_job
                  ON new_job.file_sha256 = old_job.file_sha256
                 AND new_job.job_id > old_job.job_id
                 AND new_job.status = \'pending_verification\'
                JOIN upload_staging_batches b ON b.document_job_id = new_job.job_id
                WHERE old_job.job_id = :jid
                ORDER BY new_job.job_id DESC
                LIMIT 1
            ');
            $stmt3->execute([':jid' => (int)$row['document_job_id']]);
            $batch = $stmt3->fetch(PDO::FETCH_ASSOC);
            if ($batch) {
                $row = array_merge($row, $batch);
                $this->db->prepare('
                    UPDATE upload_activity_logs
                    SET document_job_id = :jid,
                        staging_batch_id = :bid,
                        upload_status = \'Pending Verification\',
                        verification_status = \'pending\',
                        inserted_records = 0,
                        failed_records = 0
                    WHERE id = :lid
                ')->execute([
                    ':jid' => (int)$batch['document_job_id'],
                    ':bid' => (int)$batch['batch_id'],
                    ':lid' => $logId,
                ]);
                $this->db->prepare('
                    UPDATE upload_staging_batches SET upload_log_id = :lid WHERE batch_id = :bid
                ')->execute([':lid' => $logId, ':bid' => (int)$batch['batch_id']]);
            }
        }
        if (empty($row['batch_id'])) {
            return null;
        }
        $row['staging_tables'] = json_decode($row['staging_tables'] ?? '{}', true) ?: [];
        return $row;
    }

    public function fetchCdrRows(string $qualifiedTable, int $limit = 100, int $offset = 0): array
    {
        $this->assertQualifiedTable($qualifiedTable);
        $countStmt = $this->db->query("SELECT COUNT(*) FROM {$qualifiedTable}");
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT *
            FROM {$qualifiedTable}
            ORDER BY staging_row_id
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return ['total' => $total, 'rows' => $rows];
    }

    public function fetchSdrRows(string $qualifiedTable, int $limit = 50, int $offset = 0): array
    {
        return $this->fetchCdrRows($qualifiedTable, $limit, $offset);
    }

    public function updateCdrRow(string $qualifiedTable, int $stagingRowId, array $fields): void
    {
        $this->assertQualifiedTable($qualifiedTable);
        $allowed = [
            'phone', 'other', 'starttime', 'duration', 'incoming', 'imeinumber', 'imsinumber',
            'celltowerid', 'otherinfo', 'first_cellid', 'last_cellid', 'roaming_nw', 'call_type',
            'calling_no', 'called_no',
        ];
        $sets = [];
        $params = [':id' => $stagingRowId];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $fields)) {
                $sets[] = "{$col} = :{$col}";
                $params[":{$col}"] = $this->normalizeCdrEditValue($col, $fields[$col]);
            }
        }
        if (!$sets) {
            return;
        }
        $sets[] = 'user_edited = TRUE';
        $sets[] = 'is_duplicate = FALSE';
        $sets[] = 'duplicate_reason = NULL';
        $sql = 'UPDATE ' . $qualifiedTable . ' SET ' . implode(', ', $sets) . ' WHERE staging_row_id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function refreshCdrDuplicatesAfterEdit(string $qualifiedTable, int $stagingRowId): array
    {
        $this->refreshCdrDuplicates($qualifiedTable);
        return $this->fetchCdrStagingRow($qualifiedTable, $stagingRowId) ?? [];
    }

    public function fetchCdrStagingRow(string $qualifiedTable, int $stagingRowId): ?array
    {
        $this->assertQualifiedTable($qualifiedTable);
        $stmt = $this->db->prepare("SELECT * FROM {$qualifiedTable} WHERE staging_row_id = :id");
        $stmt->execute([':id' => $stagingRowId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function normalizeCdrEditValue(string $col, mixed $value): mixed
    {
        if ($col === 'incoming') {
            $v = strtolower(trim((string)$value));
            if (in_array($v, ['in', 'incoming', 'true', 'yes'], true)) {
                return 1;
            }
            if (in_array($v, ['out', 'outgoing', 'false', 'no'], true)) {
                return 0;
            }
            return is_numeric($value) ? (int)$value : 0;
        }
        if ($col === 'starttime') {
            $v = trim((string)$value);
            if ($v === '') {
                return null;
            }
            $ts = strtotime(str_replace('/', '-', $v));
            return $ts !== false ? date('Y-m-d H:i:s', $ts) : $v;
        }
        if (in_array($col, ['duration', 'imeinumber', 'imsinumber'], true)) {
            return is_numeric($value) ? (int)$value : 0;
        }
        return is_string($value) ? trim($value) : $value;
    }

    public function updateSdrRow(string $qualifiedTable, int $stagingRowId, array $fields): void
    {
        $this->assertQualifiedTable($qualifiedTable);
        $stmt = $this->db->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'upload_staging' AND table_name = :t");
        $tableOnly = preg_replace('/^upload_staging\./', '', $qualifiedTable);
        $stmt->execute([':t' => $tableOnly]);
        $allowed = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $skip = ['staging_row_id', 'is_duplicate', 'duplicate_reason', 'user_edited'];
        $sets = [];
        $params = [':id' => $stagingRowId];
        foreach ($fields as $col => $val) {
            if (!in_array($col, $allowed, true) || in_array($col, $skip, true)) {
                continue;
            }
            $sets[] = "{$col} = :{$col}";
            $params[":{$col}"] = $val;
        }
        if (!$sets) {
            return;
        }
        $sets[] = 'user_edited = TRUE';
        $sets[] = 'is_duplicate = FALSE';
        $sets[] = 'duplicate_reason = NULL';
        $sql = 'UPDATE ' . $qualifiedTable . ' SET ' . implode(', ', $sets) . ' WHERE staging_row_id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function enrichCdrStaging(string $qualifiedTable, ?int $rowId = null): void
    {
        $this->assertQualifiedTable($qualifiedTable);
        $rowFilter = $rowId !== null ? ' AND s.staging_row_id = ' . (int)$rowId : '';
        $rowFilterPlain = $rowId !== null ? ' AND staging_row_id = ' . (int)$rowId : '';

        $this->db->exec("
            UPDATE {$qualifiedTable} s
            SET duration = 0
            WHERE duration <> 0
              AND (COALESCE(s.call_type, '') ~* '(sms|mms|message|text|ussd)'
                OR COALESCE(s.otherinfo, '') ~* '(sms|mms|message|text|ussd)')
            {$rowFilter}
        ");

        try {
            $this->db->exec("
                UPDATE {$qualifiedTable} s
                SET celltowerid = sub.network_id,
                    first_cellid = COALESCE(NULLIF(s.first_cellid, ''), sub.network_id)
                FROM LATERAL (
                    SELECT COALESCE(NULLIF(p.areadescription, ''), p.phoneprefix) AS network_id
                    FROM cdatphonearea p
                    WHERE regexp_replace(COALESCE(s.phone, ''), '\\D', '', 'g') LIKE p.phoneprefix || '%'
                    ORDER BY LENGTH(p.phoneprefix) DESC
                    LIMIT 1
                ) sub
                WHERE COALESCE(s.celltowerid, '') = ''
                  AND COALESCE(s.phone, '') <> ''
                {$rowFilter}
            ");
        } catch (Throwable $e) {
            error_log('CDR area enrichment skipped: ' . $e->getMessage());
        }

        try {
            $this->db->exec("SET LOCAL statement_timeout = '15s'");
            $this->db->exec("
                UPDATE {$qualifiedTable} s
                SET tower_key = COALESCE(t.bts_key, s.tower_key),
                    state_key = COALESCE(t.state_key, s.state_key),
                    otherinfo = CASE WHEN COALESCE(s.otherinfo, '') = '' THEN t.operator ELSE s.otherinfo END
                FROM LATERAL (
                    SELECT state_key, operator,
                           NULLIF(regexp_replace(COALESCE(bts_id, ''), '\\D', '', 'g'), '')::numeric AS bts_key
                    FROM cdatcelltowerareanew
                    WHERE celltowerid = s.celltowerid
                    ORDER BY lastupdate DESC NULLS LAST
                    LIMIT 1
                ) t
                WHERE COALESCE(s.celltowerid, '') <> ''
                {$rowFilter}
            ");
        } catch (Throwable $e) {
            error_log('CDR tower enrichment skipped: ' . $e->getMessage());
        }
    }

    public function refreshCdrDuplicates(string $qualifiedTable): array
    {
        $this->assertQualifiedTable($qualifiedTable);
        // Within-file / within-staging duplicates are not flagged.
        // Only matches against production (cdatpcsuspect) count as duplicates.
        $this->db->exec("UPDATE {$qualifiedTable} SET is_duplicate = FALSE, duplicate_reason = NULL");

        $mainDup = $this->db->exec("
            UPDATE {$qualifiedTable} s
            SET is_duplicate = TRUE, duplicate_reason = 'exists_in_main'
            WHERE EXISTS (
                SELECT 1 FROM cdatpcsuspect t
                WHERE t.phone = s.phone
                  AND t.other IS NOT DISTINCT FROM s.other
                  AND t.starttime = s.starttime
                  AND t.duration IS NOT DISTINCT FROM s.duration
                  AND t.incoming IS NOT DISTINCT FROM s.incoming
            )
        ");

        $counts = $this->duplicateCounts($qualifiedTable);
        return [
            'marked_in_main' => $mainDup,
            'marked_in_batch' => 0,
            'duplicate_count' => $counts['duplicate_count'],
            'valid_count' => $counts['valid_count'],
        ];
    }

    public function refreshStagingBatchDuplicates(string $qualifiedTable): void
    {
        // Kept for SDR callers; CDR no longer marks within-batch duplicates.
        $this->assertQualifiedTable($qualifiedTable);
    }

    public function duplicateCounts(string $qualifiedTable): array
    {
        $this->assertQualifiedTable($qualifiedTable);
        $dup = (int)$this->db->query("SELECT COUNT(*) FROM {$qualifiedTable} WHERE is_duplicate = TRUE")->fetchColumn();
        $valid = (int)$this->db->query("SELECT COUNT(*) FROM {$qualifiedTable} WHERE COALESCE(is_duplicate, FALSE) = FALSE")->fetchColumn();
        return ['duplicate_count' => $dup, 'valid_count' => $valid];
    }

    public function refreshSdrDuplicates(string $qualifiedTable, string $logicalTarget, bool $includeMain = false): array
    {
        $this->assertQualifiedTable($qualifiedTable);
        $this->assertLogicalTarget($logicalTarget);
        $cols = $this->sdrDedupColumns($qualifiedTable, $logicalTarget);
        if (!$cols) {
            
            return $this->duplicateCounts($qualifiedTable);
        }

        $this->db->exec("UPDATE {$qualifiedTable} SET is_duplicate = FALSE, duplicate_reason = NULL");

        $groupExpr = implode(', ', array_map(static fn($c) => "COALESCE({$c}::text, '')", $cols));
        $this->db->exec("
            UPDATE {$qualifiedTable} s
            SET is_duplicate = TRUE,
                duplicate_reason = COALESCE(s.duplicate_reason, 'duplicate_in_batch')
            WHERE staging_row_id NOT IN (
                SELECT MIN(staging_row_id)
                FROM {$qualifiedTable}
                GROUP BY {$groupExpr}
            )
        ");

        if ($includeMain) {
            try {
                $matchExpr = implode(' AND ', array_map(static fn($c) => "t.{$c} IS NOT DISTINCT FROM s.{$c}", $cols));
                $this->db->exec("
                    UPDATE {$qualifiedTable} s
                    SET is_duplicate = TRUE,
                        duplicate_reason = 'exists_in_main'
                    WHERE EXISTS (
                        SELECT 1 FROM public.{$logicalTarget} t
                        WHERE {$matchExpr}
                    )
                ");
            } catch (Throwable $e) {
                error_log("SDR exists_in_main dedup skipped for {$logicalTarget}: " . $e->getMessage());
            }
        }

        return $this->duplicateCounts($qualifiedTable);
    }

    private function sdrDedupColumns(string $stagingTable, string $logicalTarget): array
    {
        $cols = $this->commonColumns($stagingTable, $logicalTarget, 'main');
        $excludeKeys = ['cdat_sdr_key', 'oth_sdr_key'];
        $cols = array_filter($cols, static fn($c) => !in_array(strtolower($c), $excludeKeys, true));
        $cols = array_filter($cols, static fn($c) => (bool)preg_match('/^[a-z_][a-z0-9_]*$/', $c));
        return array_values($cols);
    }

    private function assertLogicalTarget(string $target): void
    {
        if (!in_array($target, ['cdataddress', 'address_other_state'], true)) {
            throw new RuntimeException('Invalid SDR target table.');
        }
    }

    public function approveBatch(int $batchId, string $username): array
    {
        $batch = $this->getBatchById($batchId);
        if (!$batch) {
            throw new RuntimeException('Staging batch not found.');
        }
        if ($batch['verification_status'] === 'approved') {
            throw new RuntimeException('This batch is already approved.');
        }

        $module = strtolower($batch['module']);
        $queueId = $this->enqueueApproval($batchId, $username, $module);

        if (!$this->tryAcquireModulePromoteLock($module)) {
            return [
                'queued' => true,
                'queue_id' => $queueId,
                'position' => $this->getQueuePosition($queueId, $module),
                'module' => $module,
                'message' => 'Another ' . strtoupper($module) . ' promotion is in progress. Waiting in queue.',
            ];
        }

        try {
            return $this->drainApprovalQueue($module, $queueId);
        } finally {
            $this->releaseModulePromoteLock($module);
        }
    }

    public function tickApprovalQueue(int $queueId): array
    {
        $row = $this->getQueueRow($queueId);
        if (!$row) {
            throw new RuntimeException('Approval queue item not found.');
        }

        if ($row['status'] === 'completed') {
            return [
                'queued' => false,
                'queue_id' => $queueId,
                'inserted' => (int)($row['inserted_records'] ?? 0),
                'module' => $row['module'],
                'message' => 'Approved. ' . (int)($row['inserted_records'] ?? 0) . ' rows promoted to production.',
            ];
        }
        if ($row['status'] === 'failed') {
            throw new RuntimeException($row['error_message'] ?: 'Promotion failed.');
        }
        if ($row['status'] === 'cancelled') {
            throw new RuntimeException('Approval was cancelled.');
        }

        $module = strtolower($row['module']);
        if (!$this->tryAcquireModulePromoteLock($module)) {
            return [
                'queued' => true,
                'queue_id' => $queueId,
                'position' => $this->getQueuePosition($queueId, $module),
                'module' => $module,
                'message' => 'Another ' . strtoupper($module) . ' promotion is still in progress.',
            ];
        }

        try {
            return $this->drainApprovalQueue($module, $queueId);
        } finally {
            $this->releaseModulePromoteLock($module);
        }
    }

    public function getApprovalQueueStatus(int $queueId): array
    {
        $row = $this->getQueueRow($queueId);
        if (!$row) {
            throw new RuntimeException('Approval queue item not found.');
        }
        $module = strtolower($row['module']);
        $position = $row['status'] === 'queued'
            ? $this->getQueuePosition($queueId, $module)
            : 0;
        $running = $this->countModuleRunning($module);

        return [
            'queue_id' => $queueId,
            'batch_id' => (int)$row['batch_id'],
            'module' => $module,
            'status' => $row['status'],
            'position' => $position,
            'module_busy' => $running > 0,
            'inserted_records' => $row['inserted_records'] !== null ? (int)$row['inserted_records'] : null,
            'error_message' => $row['error_message'],
            'queued_at' => $row['queued_at'],
            'started_at' => $row['started_at'],
            'completed_at' => $row['completed_at'],
        ];
    }

    private function promoteBatchUnlocked(int $batchId, string $username): array
    {
        $batch = $this->getBatchById($batchId);
        if (!$batch) {
            throw new RuntimeException('Staging batch not found.');
        }
        if ($batch['verification_status'] === 'approved') {
            return ['inserted' => 0, 'module' => strtolower($batch['module'])];
        }

        $tables = json_decode($batch['staging_tables'], true) ?: [];
        $module = strtolower($batch['module']);
        $inserted = 0;

        if ($module === 'cdr') {
            $table = $tables['cdr'] ?? '';
            if (!$table) {
                throw new RuntimeException('CDR staging table missing.');
            }
            $this->enrichCdrStaging($table);
            $this->refreshCdrDuplicates($table);
        } elseif ($module === 'sdr') {
            foreach ($tables as $logical => $stagingTable) {
                if (in_array($logical, ['cdataddress', 'address_other_state'], true)) {
                    $this->refreshSdrDuplicates($stagingTable, $logical, true);
                }
            }
        }

        $this->db->beginTransaction();
        try {
            if ($module === 'cdr') {
                $inserted = $this->promoteCdr($tables['cdr']);
            } elseif ($module === 'sdr') {
                $inserted = $this->promoteSdr($tables);
            } else {
                throw new RuntimeException('Unsupported module: ' . $module);
            }

            foreach ($tables as $qualified) {
                $this->db->exec('DROP TABLE IF EXISTS ' . $qualified . ' CASCADE');
            }

            // Mark this batch approved.
            $this->db->prepare('
                UPDATE upload_staging_batches
                SET verification_status = \'approved\', verified_at = NOW(), verified_by = :user
                WHERE batch_id = :id
            ')->execute([':user' => $username, ':id' => $batchId]);

            // Shared filename staging: any other pending CDR batches pointing at the
            // same staging table are also complete once the table is promoted+dropped.
            if ($module === 'cdr' && !empty($tables['cdr'])) {
                $sibling = $this->db->prepare("
                    UPDATE upload_staging_batches
                    SET verification_status = 'approved', verified_at = NOW(), verified_by = :user
                    WHERE module = 'cdr'
                      AND verification_status = 'pending'
                      AND staging_tables->>'cdr' = :tbl
                      AND batch_id <> :id
                    RETURNING document_job_id
                ");
                $sibling->execute([
                    ':user' => $username,
                    ':tbl' => $tables['cdr'],
                    ':id' => $batchId,
                ]);
                $siblingJobIds = $sibling->fetchAll(PDO::FETCH_COLUMN) ?: [];
                foreach ($siblingJobIds as $sjid) {
                    $this->db->prepare("
                        UPDATE document_jobs
                        SET status = 'completed', phase = 'completed', completed_at = NOW(), updated_at = NOW()
                        WHERE job_id = :jid
                    ")->execute([':jid' => (int)$sjid]);
                    $this->db->prepare("
                        UPDATE upload_activity_logs
                        SET upload_status = 'Success', verification_status = 'approved'
                        WHERE document_job_id = :jid
                    ")->execute([':jid' => (int)$sjid]);
                }
            }

            $this->db->prepare('
                UPDATE upload_activity_logs
                SET upload_status = \'Success\', verification_status = \'approved\',
                    inserted_records = :ins, failed_records = GREATEST(total_records - :ins, 0)
                WHERE staging_batch_id = :id OR document_job_id = :jid
            ')->execute([
                ':ins' => $inserted,
                ':id' => $batchId,
                ':jid' => $batch['document_job_id'],
            ]);

            $this->db->prepare('
                UPDATE document_jobs
                SET status = \'completed\', phase = \'completed\', completed_at = NOW(), updated_at = NOW()
                WHERE job_id = :jid
            ')->execute([':jid' => $batch['document_job_id']]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        audit_log('Data Upload', 'Approve Staging', [
            'batch_id' => $batchId,
            'module' => $module,
            'inserted' => $inserted,
        ]);

        return ['inserted' => $inserted, 'module' => $module];
    }

    private function drainApprovalQueue(string $module, ?int $targetQueueId = null): array
    {
        $this->recoverStaleApprovalRuns($module);
        $targetResult = null;

        while (true) {
            $item = $this->claimNextQueuedApproval($module);
            if (!$item) {
                break;
            }

            $queueId = (int)$item['queue_id'];
            $this->markQueueRunning($queueId);

            try {
                $result = $this->promoteBatchUnlocked((int)$item['batch_id'], (string)$item['username']);
                $this->markQueueCompleted($queueId, (int)$result['inserted']);
                if ($targetQueueId === null || $queueId === $targetQueueId) {
                    $targetResult = $result + [
                        'queued' => false,
                        'queue_id' => $queueId,
                        'message' => 'Approved. ' . (int)$result['inserted'] . ' rows promoted to production.',
                    ];
                }
            } catch (Throwable $e) {
                $this->markQueueFailed($queueId, $e->getMessage());
                if ($targetQueueId === null || $queueId === $targetQueueId) {
                    throw $e;
                }
            }
        }

        if ($targetResult !== null) {
            return $targetResult;
        }

        if ($targetQueueId !== null) {
            $row = $this->getQueueRow($targetQueueId);
            if ($row && $row['status'] === 'completed') {
                return [
                    'queued' => false,
                    'queue_id' => $targetQueueId,
                    'inserted' => (int)($row['inserted_records'] ?? 0),
                    'module' => $module,
                    'message' => 'Approved. ' . (int)($row['inserted_records'] ?? 0) . ' rows promoted to production.',
                ];
            }
            if ($row && $row['status'] === 'failed') {
                throw new RuntimeException($row['error_message'] ?: 'Promotion failed.');
            }
        }

        throw new RuntimeException('Approval queue item could not be processed.');
    }

    private function enqueueApproval(int $batchId, string $username, string $module): int
    {
        $existing = $this->db->prepare("
            SELECT queue_id FROM upload_approval_queue
            WHERE batch_id = :bid AND status IN ('queued', 'running')
            ORDER BY queue_id DESC LIMIT 1
        ");
        $existing->execute([':bid' => $batchId]);
        $queueId = $existing->fetchColumn();
        if ($queueId) {
            return (int)$queueId;
        }

        $stmt = $this->db->prepare("
            INSERT INTO upload_approval_queue (batch_id, module, username, status)
            VALUES (:bid, :mod, :user, 'queued')
            RETURNING queue_id
        ");
        $stmt->execute([':bid' => $batchId, ':mod' => $module, ':user' => $username]);
        return (int)$stmt->fetchColumn();
    }

    private function claimNextQueuedApproval(string $module): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM upload_approval_queue
            WHERE module = :mod AND status = 'queued'
            ORDER BY queued_at ASC, queue_id ASC
            LIMIT 1
        ");
        $stmt->execute([':mod' => $module]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function getQueueRow(int $queueId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM upload_approval_queue WHERE queue_id = :id');
        $stmt->execute([':id' => $queueId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function getQueuePosition(int $queueId, string $module): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM upload_approval_queue q1
            WHERE q1.module = :mod
              AND q1.status IN ('queued', 'running')
              AND q1.queued_at <= (
                  SELECT q2.queued_at FROM upload_approval_queue q2 WHERE q2.queue_id = :qid
              )
        ");
        $stmt->execute([':mod' => $module, ':qid' => $queueId]);
        return max(1, (int)$stmt->fetchColumn());
    }

    private function countModuleRunning(string $module): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM upload_approval_queue
            WHERE module = :mod AND status = 'running'
        ");
        $stmt->execute([':mod' => $module]);
        return (int)$stmt->fetchColumn();
    }

    private function markQueueRunning(int $queueId): void
    {
        $this->db->prepare("
            UPDATE upload_approval_queue
            SET status = 'running', started_at = NOW(), error_message = NULL
            WHERE queue_id = :id
        ")->execute([':id' => $queueId]);
    }

    private function markQueueCompleted(int $queueId, int $inserted): void
    {
        $this->db->prepare("
            UPDATE upload_approval_queue
            SET status = 'completed', inserted_records = :ins, completed_at = NOW(), error_message = NULL
            WHERE queue_id = :id
        ")->execute([':ins' => $inserted, ':id' => $queueId]);
    }

    private function markQueueFailed(int $queueId, string $error): void
    {
        $this->db->prepare("
            UPDATE upload_approval_queue
            SET status = 'failed', error_message = :err, completed_at = NOW()
            WHERE queue_id = :id
        ")->execute([':err' => $error, ':id' => $queueId]);
    }

    private function recoverStaleApprovalRuns(string $module): void
    {
        $this->db->prepare("
            UPDATE upload_approval_queue
            SET status = 'failed',
                error_message = 'Promotion timed out and was reset. Please retry approval.',
                completed_at = NOW()
            WHERE module = :mod
              AND status = 'running'
              AND started_at < NOW() - INTERVAL '2 hours'
        ")->execute([':mod' => $module]);
    }

    private function modulePromoteLockKey(string $module): int
    {
        $stmt = $this->db->prepare('SELECT hashtext(:k)');
        $stmt->execute([':k' => 'cdat_module_promote:' . strtolower($module)]);
        return (int)$stmt->fetchColumn();
    }

    private function tryAcquireModulePromoteLock(string $module): bool
    {
        $key = $this->modulePromoteLockKey($module);
        $stmt = $this->db->query('SELECT pg_try_advisory_lock(' . $key . ')');
        return (bool)$stmt->fetchColumn();
    }

    private function releaseModulePromoteLock(string $module): void
    {
        $key = $this->modulePromoteLockKey($module);
        $this->db->query('SELECT pg_advisory_unlock(' . $key . ')');
    }

    public function rejectBatch(int $batchId, string $username): void
    {
        $batch = $this->getBatchById($batchId);
        if (!$batch) {
            throw new RuntimeException('Staging batch not found.');
        }

        $running = $this->db->prepare("
            SELECT queue_id FROM upload_approval_queue
            WHERE batch_id = :id AND status = 'running' LIMIT 1
        ");
        $running->execute([':id' => $batchId]);
        if ($running->fetchColumn()) {
            throw new RuntimeException('Cannot reject while promotion to production is in progress.');
        }

        $this->db->prepare("
            UPDATE upload_approval_queue
            SET status = 'cancelled', completed_at = NOW()
            WHERE batch_id = :id AND status = 'queued'
        ")->execute([':id' => $batchId]);

        $tables = json_decode($batch['staging_tables'], true) ?: [];
        $module = strtolower((string)($batch['module'] ?? ''));

        $this->db->beginTransaction();
        try {
            foreach ($tables as $key => $qualified) {
                // Shared CDR filename staging: only drop if no other pending batch still uses it.
                if ($module === 'cdr' && $key === 'cdr') {
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM upload_staging_batches
                        WHERE module = 'cdr'
                          AND verification_status = 'pending'
                          AND staging_tables->>'cdr' = :tbl
                          AND batch_id <> :id
                    ");
                    $stmt->execute([':tbl' => $qualified, ':id' => $batchId]);
                    $others = (int)$stmt->fetchColumn();
                    if ($others > 0) {
                        continue;
                    }
                }
                $this->db->exec('DROP TABLE IF EXISTS ' . $qualified . ' CASCADE');
            }
            $this->db->prepare('
                UPDATE upload_staging_batches
                SET verification_status = \'rejected\', verified_at = NOW(), verified_by = :user
                WHERE batch_id = :id
            ')->execute([':user' => $username, ':id' => $batchId]);

            $this->db->prepare('
                UPDATE upload_activity_logs
                SET upload_status = \'Rejected\', verification_status = \'rejected\'
                WHERE staging_batch_id = :id OR document_job_id = :jid
            ')->execute([':id' => $batchId, ':jid' => $batch['document_job_id']]);

            $this->db->prepare('
                UPDATE document_jobs SET status = \'rejected\', phase = \'rejected\', updated_at = NOW()
                WHERE job_id = :jid
            ')->execute([':jid' => $batch['document_job_id']]);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }

        audit_log('Data Upload', 'Reject Staging', ['batch_id' => $batchId]);
    }

    private function promoteCdr(string $qualifiedTable): int
    {
        // Re-check uniqueness at promote time against production
        // (phone, other, starttime, duration, incoming).
        $sql = "
            INSERT INTO cdatpcsuspect (
                ucid, phone, other, starttime, duration, incoming, imeinumber, imsinumber,
                celltowerid, otherinfo, tower_key, provider_key, state_key, first_cellid,
                last_cellid, roaming_nw, call_type, calling_no, called_no, asondate
            )
            SELECT
                s.ucid, s.phone, s.other, s.starttime, s.duration, s.incoming, s.imeinumber, s.imsinumber,
                s.celltowerid, s.otherinfo, s.tower_key, s.provider_key, s.state_key, s.first_cellid,
                s.last_cellid, s.roaming_nw, s.call_type, s.calling_no, s.called_no, COALESCE(s.asondate, NOW())
            FROM {$qualifiedTable} s
            WHERE COALESCE(s.is_duplicate, FALSE) = FALSE
              AND NOT EXISTS (
                    SELECT 1
                    FROM cdatpcsuspect t
                    WHERE t.phone = s.phone
                      AND t.other IS NOT DISTINCT FROM s.other
                      AND t.starttime = s.starttime
                      AND t.duration IS NOT DISTINCT FROM s.duration
                      AND t.incoming IS NOT DISTINCT FROM s.incoming
              )
        ";
        $count = $this->db->exec($sql);
        return $count === false ? 0 : (int)$count;
    }

    private function promoteSdr(array $tables): int
    {
        $dist = $this->distDb();
        $total = 0;
        foreach (['cdataddress', 'address_other_state'] as $logical) {
            $staging = $tables[$logical] ?? null;
            if (!$staging) {
                continue;
            }
            $this->assertQualifiedTable($staging);
            $cols = $this->commonColumns($staging, $logical, 'distributed');
            if (!$cols) {
                continue;
            }
            $colList = implode(', ', $cols);
            $select = $this->db->query("
                SELECT {$colList}
                FROM {$staging}
                WHERE COALESCE(is_duplicate, FALSE) = FALSE
            ");
            $batch = [];
            $batchSize = 1000;
            while ($row = $select->fetch(PDO::FETCH_NUM)) {
                $batch[] = $row;
                if (count($batch) >= $batchSize) {
                    $total += $this->bulkInsertRows($dist, $logical, $cols, $batch);
                    $batch = [];
                }
            }
            if ($batch) {
                $total += $this->bulkInsertRows($dist, $logical, $cols, $batch);
            }
        }
        return $total;
    }

    private function bulkInsertRows(PDO $pdo, string $table, array $cols, array $rows): int
    {
        if (!$rows) {
            return 0;
        }
        $colList = implode(', ', $cols);
        $rowPlaceholder = '(' . implode(', ', array_fill(0, count($cols), '?')) . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($rows), $rowPlaceholder));
        $sql = "INSERT INTO {$table} ({$colList}) VALUES {$allPlaceholders}";
        $flat = [];
        foreach ($rows as $row) {
            foreach ($row as $val) {
                $flat[] = $val;
            }
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($flat);
        return count($rows);
    }

    private function commonColumns(string $stagingTable, string $targetTable, string $connection = 'main'): array
    {
        $pdo = $connection === 'distributed' ? $this->distDb() : $this->db;
        $stagingOnly = preg_replace('/^upload_staging\./', '', $stagingTable);
        $stmt = $this->db->prepare("
            SELECT column_name FROM information_schema.columns
            WHERE table_schema = 'upload_staging' AND table_name = :t
        ");
        $stmt->execute([':t' => $stagingOnly]);
        $stagingCols = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $stmt2 = $pdo->prepare("
            SELECT column_name FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = :t
        ");
        $stmt2->execute([':t' => $targetTable]);
        $targetCols = $stmt2->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $skip = ['staging_row_id', 'is_duplicate', 'duplicate_reason', 'user_edited'];
        $usable = array_diff($stagingCols, $skip);
        return array_values(array_intersect($usable, $targetCols));
    }

    private function getBatchById(int $batchId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM upload_staging_batches WHERE batch_id = :id');
        $stmt->execute([':id' => $batchId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function distDb(): PDO
    {
        if ($this->distDb instanceof PDO) {
            return $this->distDb;
        }
        $cfg = $this->dbConfig;
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $cfg['host'],
            $cfg['port'],
            getenv('DIST_PG_DATABASE') ?: 'distribution_db'
        );
        $this->distDb = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return $this->distDb;
    }

    private function assertQualifiedTable(string $qualified): void
    {
        if (!preg_match('/^upload_staging\.[a-z][a-z0-9_]*$/', $qualified)) {
            throw new RuntimeException('Invalid staging table reference.');
        }
    }

    /**
     * Approving or rejecting a batch drops its staging table (see rejectBatch
     * and the promote path). Opening the verification screen for a finished
     * upload -- which the history list links to -- then queried a table that no
     * longer exists, and the caller got PDO's "member function on bool" back as
     * its error message. Callers check this first and say something useful.
     */
    public function stagingTableExists(string $qualified): bool
    {
        if ($qualified === '' || !preg_match('/^upload_staging\.[a-z][a-z0-9_]*$/', $qualified)) {
            return false;
        }
        $stmt = $this->db->prepare('SELECT to_regclass(:t) IS NOT NULL');
        $stmt->execute([':t' => $qualified]);
        return (bool)$stmt->fetchColumn();
    }
}
