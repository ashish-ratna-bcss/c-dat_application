<?php
/**
 * public/health.php — liveness (+ optional DB) without touching business pages.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$result = [
    'ok' => true,
    'app' => 'cdat',
    'time' => gmdate('c'),
    'php' => PHP_VERSION,
    'db' => null,
];

try {
    require_once dirname(__DIR__) . '/bootstrap/db.php';
    $pdo = cdat_pdo();
    $pdo->query('SELECT 1');
    $result['db'] = 'ok';
} catch (Throwable $e) {
    // Network/pg_hba issues must not fail liveness of the web process.
    $result['db'] = 'unavailable';
    // Never leak connection/host details on a public health endpoint.
    if (getenv('CDAT_DEBUG') === '1') {
        $result['db_error'] = $e->getMessage();
    }
}

echo json_encode($result, JSON_UNESCAPED_SLASHES);
