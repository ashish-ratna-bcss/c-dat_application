<?php
require_once __DIR__ . '/bootstrap.php';

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
}

function csrf_verify(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sent = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = (string) ($_SESSION['csrf_token'] ?? '');
    if ($sent === '' || $expected === '' || !hash_equals($expected, $sent)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Invalid or missing CSRF token.';
        exit;
    }
}
