<?php
require_once dirname(__DIR__) . '/common/bootstrap.php';
/**
 * Upload page mode helpers for CDR / SDR / Custom Table upload screens.
 */

function cdat_upload_self_url(string $page = 'cdr'): string
{
    $path = match ($page) {
        'sdr' => '/data-upload/sdr',
        'custom' => '/data-upload/custom',
        default => '/data-upload/cdr',
    };
    return function_exists('cdat_href') ? cdat_href($path) : $path;
}

function cdat_upload_url_for_module(?string $moduleName): string
{
    $m = strtolower((string) $moduleName);
    if (str_contains($m, 'custom')) {
        return cdat_upload_self_url('custom');
    }
    if (str_contains($m, 'sdr')) {
        return cdat_upload_self_url('sdr');
    }
    return cdat_upload_self_url('cdr');
}

function cdat_upload_verify_url(?int $logId = null): string
{
    $path = function_exists('cdat_href') ? cdat_href('/data-upload/verify') : '/data-upload/verify';
    if ($logId && $logId > 0) {
        return $path . '?log_id=' . $logId;
    }
    $history = function_exists('cdat_href') ? cdat_href('/data-upload/history') : '/data-upload/history';
    return $history . '?type=standard';
}

/** Normalize PDO row keys (CASE_UPPER) to lowercase for templates. */
function cdat_upload_row(array $row): array
{
    $out = [];
    foreach ($row as $key => $value) {
        $out[strtolower((string) $key)] = $value;
    }
    return $out;
}

/** @param mixed $default */
function cdat_upload_row_val(array $row, string $key, mixed $default = ''): mixed
{
    $upper = strtoupper($key);
    return $row[$key] ?? $row[$upper] ?? $default;
}

/**
 * Move one staged document job into the live table.
 * @return array{ok:bool, inserted:?int, status:?string, message:?string, error?:string}
 */
function cdat_insert_staging_job(int $jobId): array
{
    if ($jobId <= 0) {
        return ['ok' => false, 'error' => 'Missing job id.', 'inserted' => null, 'status' => null, 'message' => null];
    }

    set_time_limit(0);

    try {
        require_once CDAT_UPLOAD . '/upload_verification_service.php';
        $db = audit_db();
        $stmt = $db->prepare('SELECT batch_id FROM upload_staging_batches WHERE document_job_id = :jid LIMIT 1');
        $stmt->execute([':jid' => $jobId]);
        $batchId = (int) $stmt->fetchColumn();
        if ($batchId <= 0) {
            return [
                'ok' => false,
                'error' => 'No staging batch found for this upload. Process the file first, then open Staging to review.',
                'inserted' => null,
                'status' => null,
                'message' => null,
            ];
        }

        $user = (string) ($_SESSION['audit_username'] ?? 'admin');
        $service = new UploadVerificationService();
        $result = $service->approveBatchNow($batchId, $user);

        $inserted = (int) ($result['inserted'] ?? 0);
        return [
            'ok' => true,
            'inserted' => $inserted,
            'status' => 'completed',
            'message' => $result['message'] ?? ('Approved. ' . $inserted . ' rows promoted to production.'),
        ];
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'error' => $e->getMessage(),
            'inserted' => null,
            'status' => null,
            'message' => null,
        ];
    }
}
