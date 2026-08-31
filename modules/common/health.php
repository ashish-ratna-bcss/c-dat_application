<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$rev = 'unknown';
$headFile = dirname(__DIR__, 2) . '/.git/HEAD';
if (is_readable($headFile)) {
    $head = trim((string) file_get_contents($headFile));
    if (str_starts_with($head, 'ref:')) {
        $ref = trim(substr($head, 4));
        $refFile = dirname(__DIR__, 2) . '/.git/' . $ref;
        if (is_readable($refFile)) {
            $rev = substr(trim((string) file_get_contents($refFile)), 0, 12);
        }
    } else {
        $rev = substr($head, 0, 12);
    }
}
$status = ['status' => 'ok', 'db' => 'fail', 'version' => $rev];

try {
    get_cdat_pdo()->query('SELECT 1');
    $status['db'] = 'ok';
} catch (Throwable $e) {
    http_response_code(503);
    $status['status'] = 'degraded';
    $status['error'] = 'database unreachable';
}

echo json_encode($status, JSON_UNESCAPED_SLASHES);
