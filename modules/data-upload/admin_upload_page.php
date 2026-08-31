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
    $cfg = require CDAT_UPLOAD . '/cdr_upload_config.php';
    $base = rtrim($cfg['api']['base_url'] ?? 'http://127.0.0.1:8088', '/');
    $user = $_SESSION['audit_username'] ?? 'user';
    $url = $base . '/api/v1/documents/' . $jobId . '/staging/approve?username=' . rawurlencode($user);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => '',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 300,
    ]);
    if (!empty($cfg['api']['api_key'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-Key: ' . $cfg['api']['api_key']]);
    }
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    if ($resp === false) {
        return [
            'ok' => false,
            'error' => 'Could not reach the upload service (is it running on port 8088?): ' . $curlErr,
            'inserted' => null,
            'status' => null,
            'message' => null,
        ];
    }
    $json = json_decode($resp, true);
    if ($code >= 400 || !is_array($json)) {
        $msg = is_array($json) ? ($json['detail'] ?? $json['message'] ?? 'Insert failed.') : ('Service error (HTTP ' . $code . ').');
        return [
            'ok' => false,
            'error' => is_array($msg) ? json_encode($msg) : (string) $msg,
            'inserted' => null,
            'status' => null,
            'message' => null,
        ];
    }
    $inserted = (int) ($json['inserted'] ?? 0);
    try {
        $db = audit_db();
        $db->prepare('
            UPDATE upload_activity_logs
            SET upload_status = \'Success\',
                verification_status = \'approved\',
                inserted_records = :ins,
                failed_records = GREATEST(COALESCE(total_records, 0) - :ins, 0)
            WHERE document_job_id = :jid
        ')->execute([
            ':ins' => $inserted,
            ':jid' => $jobId,
        ]);
    } catch (Throwable $logEx) {
        // Insert succeeded; history update is best-effort.
    }
    return [
        'ok' => true,
        'inserted' => $json['inserted'] ?? $inserted,
        'status' => $json['status'] ?? 'completed',
        'message' => $json['message'] ?? 'Data inserted into the live table.',
    ];
}
