<?php
/**
 * admin_upload_job_status.php
 * JSON endpoint for polling document processing job status from the upload results page.
 */
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/document_processing_client.php';

header('Content-Type: application/json');

try {
    audit_require_admin();

    $jobId = (int)($_GET['job_id'] ?? 0);
    if ($jobId <= 0) {
        throw new RuntimeException('Invalid job ID.');
    }

    $config = require __DIR__ . '/cdr_upload_config.php';
    $client = new DocumentProcessingClient($config['api']);
    $job = $client->getJobStatus($jobId);

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
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ]);
}
