<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * JSON endpoint for the dashboard quick links.
 *
 *   GET  ?action=list                 -> this user's links
 *   POST action=save, urls=[...]      -> replace them, in the order given
 *
 * Answers with JSON in every case, including when the caller is not signed in
 * -- audit_require_session() would redirect to the login form, and fetch()
 * would follow it and hand the picker an HTML page to parse.
 */

if (!defined('CDAT_SKIP_SESSION_GUARD')) {
    define('CDAT_SKIP_SESSION_GUARD', true);
}
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/quick_links.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

function ql_out(array $payload, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (cdat_ql_user() === '') {
    ql_out(['ok' => false, 'error' => 'Your session has expired. Sign in again.'], 401);
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

if ($action === 'list') {
    ql_out(['ok' => true, 'links' => cdat_ql_get(), 'max' => CDAT_QL_MAX]);
}

if ($action === 'save') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ql_out(['ok' => false, 'error' => 'Save must be a POST.'], 405);
    }
    if (!cdat_csrf_ok((string)($_POST['csrf'] ?? ''))) {
        ql_out(['ok' => false, 'error' => 'Security token expired. Reload the page and try again.'], 403);
    }

    $urls = $_POST['urls'] ?? [];
    if (is_string($urls)) {                    // urls sent as a JSON array
        $decoded = json_decode($urls, true);
        $urls = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($urls)) {
        $urls = [];
    }

    $res = cdat_ql_save($urls);
    ql_out($res, $res['ok'] ? 200 : 500);
}

ql_out(['ok' => false, 'error' => 'Unknown action.'], 400);
