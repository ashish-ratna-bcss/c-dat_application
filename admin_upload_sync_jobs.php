<?php

require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/document_processing_client.php';

header('Content-Type: application/json');

try {
    audit_require_uploader();

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);
    if (!is_array($payload)) {
        $payload = [];
    }
    $jobIds = $payload['job_ids'] ?? ($_GET['job_ids'] ?? '');
    if (is_string($jobIds)) {
        $jobIds = array_filter(array_map('intval', explode(',', $jobIds)));
    }
    if (!is_array($jobIds)) {
        throw new RuntimeException('Invalid job_ids.');
    }
    $jobIds = array_values(array_unique(array_filter(array_map('intval', $jobIds))));
    if (!$jobIds) {
        echo json_encode(['ok' => true, 'jobs' => []]);
        exit;
    }

    $config = require __DIR__ . '/cdr_upload_config.php';
    $client = new DocumentProcessingClient($config['api']);
    $db = audit_db();
    $out = [];

    foreach ($jobIds as $jobId) {
        if ($jobId <= 0) {
            continue;
        }
        try {
            $job = $client->getJobStatus($jobId);
            $status = strtolower((string)($job['status'] ?? ''));
            $verifyUrl = null;
            $logId = null;

            if ($status === 'pending_verification') {
                $batchStmt = $db->prepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');
                $batchStmt->execute([':jid' => $jobId]);
                $batchId = $batchStmt->fetchColumn();
                if ($batchId) {
                    $db->prepare('
                        UPDATE upload_activity_logs
                        SET upload_status = \'Pending Verification\',
                            verification_status = \'pending\',
                            staging_batch_id = :bid,
                            total_records = :total,
                            inserted_records = :ins,
                            failed_records = GREATEST(:total - :ins, 0),
                            error_reason = NULL
                        WHERE document_job_id = :jid
                    ')->execute([
                        ':bid' => (int)$batchId,
                        ':total' => (int)($job['total_records'] ?? 0),
                        ':ins' => (int)($job['rows_committed'] ?? 0),
                        ':jid' => $jobId,
                    ]);
                }
            } elseif (in_array($status, ['completed', 'validated'], true)) {
                $db->prepare('
                    UPDATE upload_activity_logs
                    SET upload_status = \'Success\',
                        total_records = :total,
                        inserted_records = :ins,
                        failed_records = GREATEST(:total - :ins, 0),
                        error_reason = NULL
                    WHERE document_job_id = :jid AND upload_status = \'Processing\'
                ')->execute([
                    ':total' => (int)($job['total_records'] ?? 0),
                    ':ins' => (int)($job['rows_committed'] ?? 0),
                    ':jid' => $jobId,
                ]);
            } elseif ($status === 'failed') {
                $db->prepare('
                    UPDATE upload_activity_logs
                    SET upload_status = \'Failed\',
                        error_reason = :err,
                        total_records = COALESCE(NULLIF(:total, 0), total_records),
                        inserted_records = :ins,
                        failed_records = GREATEST(COALESCE(NULLIF(:total, 0), total_records) - :ins, 0)
                    WHERE document_job_id = :jid AND upload_status IN (\'Processing\', \'Failed\')
                ')->execute([
                    ':err' => $job['error_message'] ?? 'Processing failed.',
                    ':ins' => (int)($job['rows_committed'] ?? 0),
                    ':total' => (int)($job['total_records'] ?? 0),
                    ':jid' => $jobId,
                ]);
            }

            $logStmt = $db->prepare('SELECT id, upload_status, verification_status, staging_batch_id FROM upload_activity_logs WHERE document_job_id = :jid ORDER BY id DESC LIMIT 1');
            $logStmt->execute([':jid' => $jobId]);
            $logRow = $logStmt->fetch(PDO::FETCH_ASSOC);
            if ($logRow) {
                $logId = (int)$logRow['id'];
                if (in_array($status, ['pending_verification', 'running', 'queued'], true)) {
                    $verifyUrl = 'admin_upload_verify.php?log_id=' . $logId;
                }
            }

            $out[] = [
                'job_id' => $jobId,
                'status' => $job['status'] ?? null,
                'progress_percent' => $job['progress_percent'] ?? null,
                'upload_status' => $logRow['upload_status'] ?? null,
                'verification_status' => $logRow['verification_status'] ?? null,
                'log_id' => $logId,
                'verify_url' => $verifyUrl,
                'error_message' => $job['error_message'] ?? null,
            ];
        } catch (Throwable $jobEx) {
            $out[] = [
                'job_id' => $jobId,
                'error' => $jobEx->getMessage(),
            ];
        }
    }

    echo json_encode(['ok' => true, 'jobs' => $out]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
