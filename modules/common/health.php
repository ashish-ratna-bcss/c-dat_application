<?php
require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$status = ['status' => 'ok', 'db' => 'fail'];
try {
    get_cdat_pdo()->query('SELECT 1');
    $status['db'] = 'ok';
} catch (Throwable $e) {
    error_log('health check db failure: ' . $e->getMessage());
    $status['status'] = 'degraded';
    $status['error'] = 'database unreachable';
}
echo json_encode($status);
