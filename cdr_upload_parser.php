<?php
/**
 * cdr_upload_parser.php
 * Backend service for CDR/SDR uploads via the Document Processing API.
 */

require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/document_processing_client.php';
require_once __DIR__ . '/excel_converter.php';

class CdrUploadParser
{
    private $db;
    private $config;
    private $modules;

    public function __construct()
    {
        $this->db = audit_db();
        $this->config = require __DIR__ . '/cdr_upload_config.php';
        $this->modules = $this->config;
        unset($this->modules['api']);
    }

    public function processUpload(
        string $moduleKey,
        string $filePath,
        string $fileName,
        int $fileSize,
        string $ext,
        array $columnMapping,
        string $ipAddress,
        ?string $operator = null
    ): array {
        if (!isset($this->modules[$moduleKey])) {
            return $this->failedResult('Invalid module specified.', 0);
        }

        $moduleConfig = $this->modules[$moduleKey];
        $apiModule = $moduleConfig['module'];
        $apiConfig = $this->config['api'];
        $batchSize = $apiModule === 'sdr'
            ? (int)$apiConfig['sdr_batch_size']
            : (int)$apiConfig['cdr_batch_size'];

        $userId = $_SESSION['audit_user_id'] ?? 0;
        $username = $_SESSION['audit_username'] ?? 'system';
        $moduleName = $moduleConfig['name'];

        try {
            $client = new DocumentProcessingClient($apiConfig);
            $submit = $client->submitDocument($apiModule, $filePath, $fileName, $batchSize, $operator);
            $jobId = (int)$submit['job_id'];
            $preview = $submit['preview'] ?? [];
            $totalRecords = (int)($preview['total_records'] ?? 0);

            // CDR jobs run asynchronously on the API; check once for already-finished jobs.
            $job = [];
            $timedOut = false;
            if ($apiModule === 'cdr') {
                $job = $client->getJobStatus($jobId);
                $jobStatus = strtolower((string)($job['status'] ?? ''));
                $rowsCommitted = (int)($job['rows_committed'] ?? 0);
                $totalRecords = (int)($job['total_records'] ?? $totalRecords);

                if ($jobStatus === 'pending_verification') {
                    $uploadStatus = 'Pending Verification';
                    $errorReason = null;
                    $inserted = $rowsCommitted;
                    $failed = 0;
                } elseif ($jobStatus === 'completed' || $jobStatus === 'validated') {
                    $uploadStatus = 'Success';
                    $errorReason = null;
                    $inserted = $rowsCommitted > 0 ? $rowsCommitted : $totalRecords;
                    $failed = max(0, $totalRecords - $inserted);
                } elseif ($jobStatus === 'failed') {
                    $uploadStatus = 'Failed';
                    $errorReason = $job['error_message'] ?? 'Document processing failed.';
                    $inserted = $rowsCommitted;
                    $failed = max(0, $totalRecords - $inserted);
                } else {
                    $uploadStatus = 'Processing';
                    $errorReason = 'Document job #' . $jobId . ' is processing. Status updates automatically below.';
                    $inserted = $rowsCommitted;
                    // Do not treat remaining rows as failed while the job is still running.
                    $failed = 0;
                    $timedOut = true;
                }
            } else {
                $job = $client->waitForJob($jobId);
                $timedOut = !empty($job['timed_out']);
                $jobStatus = strtolower((string)($job['status'] ?? ''));
                $rowsCommitted = (int)($job['rows_committed'] ?? 0);
                $totalRecords = (int)($job['total_records'] ?? $totalRecords);

                if ($timedOut) {
                    $uploadStatus = 'Processing';
                    $errorReason = 'Document job #' . $jobId . ' is still running. Refresh upload history later for final status.';
                    $inserted = $rowsCommitted;
                    $failed = 0;
                } elseif ($jobStatus === 'pending_verification') {
                    $uploadStatus = 'Pending Verification';
                    $errorReason = null;
                    $inserted = $rowsCommitted;
                    $failed = 0;
                } elseif ($jobStatus === 'completed' || $jobStatus === 'validated') {
                    $uploadStatus = 'Success';
                    $errorReason = null;
                    $inserted = $rowsCommitted > 0 ? $rowsCommitted : $totalRecords;
                    $failed = max(0, $totalRecords - $inserted);
                } elseif ($jobStatus === 'failed') {
                    $uploadStatus = 'Failed';
                    $errorReason = $job['error_message'] ?? 'Document processing failed.';
                    $inserted = $rowsCommitted;
                    $failed = max(0, $totalRecords - $inserted);
                } else {
                    $uploadStatus = 'Processing';
                    $errorReason = 'Document job #' . $jobId . ' ended with status: ' . $jobStatus;
                    $inserted = $rowsCommitted;
                    $failed = max(0, $totalRecords - $inserted);
                }
            }

            $logId = $this->insertUploadLog(
                $userId,
                $username,
                $moduleName,
                $fileName,
                $fileSize,
                $totalRecords,
                $inserted,
                $failed,
                $uploadStatus,
                $errorReason,
                $ipAddress,
                $jobId
            );

            $stagingBatchId = $this->linkStagingBatch($jobId, $logId, $uploadStatus);

            audit_log('Data Upload', 'Upload File', [
                'module' => $moduleName,
                'file_name' => $fileName,
                'status' => $uploadStatus,
                'job_id' => $jobId,
                'total' => $totalRecords,
                'inserted' => $inserted,
                'failed' => $failed,
            ]);

            return [
                'status' => $uploadStatus,
                'reason' => $errorReason,
                'total' => $totalRecords,
                'inserted' => $inserted,
                'failed' => $failed,
                'skipped' => 0,
                'errors' => $errorReason ? [$errorReason] : [],
                'job_id' => $jobId,
                'module' => $apiModule,
                'target_table' => $moduleConfig['target_table'] ?? null,
                'operator' => $job['operator'] ?? ($preview['operator'] ?? null),
                'target_phone' => $job['target_phone'] ?? ($preview['target_phone'] ?? null),
                'mssql_database' => $job['mssql_database'] ?? ($preview['mssql_database'] ?? null),
                'phase' => $job['phase'] ?? ($submit['phase'] ?? null),
                'progress_percent' => $job['progress_percent'] ?? null,
                'warnings' => $preview['warnings'] ?? [],
                'log_id' => $logId,
                'staging_batch_id' => $stagingBatchId,
                'verify_url' => $stagingBatchId ? ('admin_upload_verify.php?log_id=' . $logId) : null,
                'pending' => $timedOut || $uploadStatus === 'Processing',
                'staging_rows' => $this->fetchStagingPreview($jobId, $uploadStatus),
            ];
        } catch (Throwable $e) {
            $this->insertUploadLog(
                $userId,
                $username,
                $moduleName,
                $fileName,
                $fileSize,
                0,
                0,
                0,
                'Failed',
                $e->getMessage(),
                $ipAddress,
                null
            );

            audit_log('Data Upload', 'Upload File', [
                'module' => $moduleName,
                'file_name' => $fileName,
                'status' => 'Failed',
                'error' => $e->getMessage(),
            ]);

            return $this->failedResult($e->getMessage(), 0);
        }
    }

    private function insertUploadLog(
        int $userId,
        string $username,
        string $moduleName,
        string $fileName,
        int $fileSize,
        int $totalRecords,
        int $inserted,
        int $failed,
        string $uploadStatus,
        ?string $errorReason,
        string $ipAddress,
        ?int $jobId
    ): ?int {
        $stmt = $this->db->prepare("
            INSERT INTO upload_activity_logs (
                user_id, username, module_name, file_name, file_size,
                total_records, inserted_records, failed_records,
                upload_status, error_reason, ip_address, document_job_id
            ) VALUES (
                :uid, :uname, :mod, :fname, :fsize,
                :total, :inserted, :failed,
                :status, :reason, :ip, :job_id
            )
            RETURNING id
        ");

        $stmt->execute([
            ':uid' => $userId,
            ':uname' => $username,
            ':mod' => $moduleName,
            ':fname' => $fileName,
            ':fsize' => $fileSize,
            ':total' => $totalRecords,
            ':inserted' => $inserted,
            ':failed' => $failed,
            ':status' => $uploadStatus,
            ':reason' => $errorReason,
            ':ip' => $ipAddress,
            ':job_id' => $jobId,
        ]);

        return (int)$stmt->fetchColumn();
    }

    private function failedResult(string $reason, int $total): array
    {
        return [
            'status' => 'Failed',
            'reason' => $reason,
            'total' => $total,
            'inserted' => 0,
            'failed' => $total,
            'skipped' => 0,
            'errors' => [$reason],
        ];
    }

    private function linkStagingBatch(int $jobId, int $logId, string $uploadStatus): ?int
    {
        if ($uploadStatus !== 'Pending Verification') {
            return null;
        }
        try {
            $stmt = $this->db->prepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');
            $stmt->execute([':jid' => $jobId]);
            $batchId = $stmt->fetchColumn();
            if (!$batchId) {
                return null;
            }
            $batchId = (int)$batchId;
            $upd = $this->db->prepare('
                UPDATE upload_staging_batches SET upload_log_id = :lid WHERE batch_id = :bid
            ');
            $upd->execute([':lid' => $logId, ':bid' => $batchId]);
            $upd2 = $this->db->prepare('
                UPDATE upload_activity_logs
                SET staging_batch_id = :bid, verification_status = \'pending\'
                WHERE id = :lid
            ');
            $upd2->execute([':bid' => $batchId, ':lid' => $logId]);
            return $batchId;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function fetchStagingPreview(int $jobId, string $uploadStatus): array
    {
        if ($uploadStatus !== 'Pending Verification') {
            return [];
        }
        try {
            $stmt = $this->db->prepare('SELECT staging_tables FROM upload_staging_batches WHERE document_job_id = :jid');
            $stmt->execute([':jid' => $jobId]);
            $json = $stmt->fetchColumn();
            if (!$json) {
                return [];
            }
            $tables = json_decode($json, true) ?: [];
            $cdrTable = $tables['cdr'] ?? null;
            if (!$cdrTable || !preg_match('/^upload_staging\.[a-z][a-z0-9_]*$/', $cdrTable)) {
                return [];
            }
            $q = $this->db->query("
                SELECT staging_row_id, phone, other, starttime, duration, incoming, operator,
                       is_duplicate, duplicate_reason
                FROM {$cdrTable}
                ORDER BY staging_row_id
                LIMIT 10
            ");
            return $q->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function fetchProductionPreview(?string $tableName, ?string $targetPhone, string $uploadStatus): array
    {
        if ($tableName !== 'cdatpcsuspect' || empty($targetPhone)) {
            return [];
        }
        if (!in_array($uploadStatus, ['Success', 'Processing', 'Partial'], true)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("
                SELECT ucid AS staging_id, phone, other, starttime, duration, incoming,
                       NULL::varchar AS operator, asondate AS imported_at
                FROM cdatpcsuspect
                WHERE phone = :phone
                ORDER BY asondate DESC NULLS LAST, ucid DESC
                LIMIT 10
            ");
            $stmt->execute([':phone' => $targetPhone]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}
