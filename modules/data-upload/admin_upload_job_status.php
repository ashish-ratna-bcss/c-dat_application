<?php
require_once dirname(__DIR__) . '/common/bootstrap.php';
/**
 * admin_upload_job_status.php
 * JSON endpoint for polling document processing job status from the upload results page.
 */
require_once CDAT_COMMON . '/activity_logger.php';
require_once CDAT_UPLOAD . '/admin_upload_page.php';
require_once CDAT_UPLOAD . '/document_processing_client.php';

header('Content-Type: application/json');

try {
    audit_require_uploader();

    $jobId = (int)($_GET['job_id'] ?? 0);
    if ($jobId <= 0) {
        throw new RuntimeException('Invalid job ID.');
    }

    $config = require CDAT_UPLOAD . '/cdr_upload_config.php';
    $client = new DocumentProcessingClient($config['api']);
    $job = $client->getJobStatus($jobId);

    // Sync upload log when background CDR job progresses or finishes staging.
    try {
        $db = audit_db();
        $status = strtolower((string)($job['status'] ?? ''));
        if ($status === 'pending_verification') {
            $stmt = $db->prepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid');
            $stmt->execute([':jid' => $jobId]);
            $batchId = $stmt->fetchColumn();
            if ($batchId) {
                $db->prepare('
                    UPDATE upload_activity_logs
                    SET upload_status = \'Pending Verification\',
                        verification_status = \'pending\',
                        staging_batch_id = :bid,
                        total_records = :total,
                        inserted_records = 0,
                        failed_records = 0,
                        error_reason = NULL
                    WHERE document_job_id = :jid
                ')->execute([
                    ':bid' => (int)$batchId,
                    ':total' => (int)($job['total_records'] ?? 0),
                    ':jid' => $jobId,
                ]);
            }
        } elseif (in_array($status, ['running', 'queued'], true)) {
            // Progress only — do not treat unprocessed rows as failures.
            $db->prepare('
                UPDATE upload_activity_logs
                SET total_records = :total,
                    inserted_records = :ins,
                    failed_records = 0
                WHERE document_job_id = :jid AND upload_status = \'Processing\'
            ')->execute([
                ':total' => (int)($job['total_records'] ?? 0),
                ':ins' => (int)($job['rows_committed'] ?? 0),
                ':jid' => $jobId,
            ]);
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
                    inserted_records = :ins,
                    failed_records = GREATEST(:total - :ins, 0)
                WHERE document_job_id = :jid
            ')->execute([
                ':err' => $job['error_message'] ?? 'Processing failed.',
                ':ins' => (int)($job['rows_committed'] ?? 0),
                ':total' => (int)($job['total_records'] ?? 0),
                ':jid' => $jobId,
            ]);
        }
    } catch (Throwable $syncEx) {
        // non-fatal
    }

    $verifyUrl = null;
    if (in_array($job['status'] ?? '', ['pending_verification', 'running', 'queued'], true)) {
        try {
            $db = $db ?? audit_db();
            $logStmt = $db->prepare('SELECT id FROM upload_activity_logs WHERE document_job_id = :jid ORDER BY id DESC LIMIT 1');
            $logStmt->execute([':jid' => $jobId]);
            $logId = $logStmt->fetchColumn();
            if ($logId) {
                $verifyUrl = cdat_upload_verify_url((int) $logId);
            }
        } catch (Throwable $e) {
            $verifyUrl = null;
        }
    }

    echo json_encode([
        'ok' => true,
        'job_id' => $jobId,
        'status' => $job['status'] ?? null,
        'phase' => $job['phase'] ?? null,
        'operator' => $job['operator'] ?? null,
        'target_phone' => $job['target_phone'] ?? null,
        'total_records' => $job['total_records'] ?? null,
        'rows_committed' => $job['rows_committed'] ?? null,
        'progress_percent' => $job['progress_percent'] ?? null,
        'error_message' => $job['error_message'] ?? null,
        'verify_url' => $verifyUrl,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
