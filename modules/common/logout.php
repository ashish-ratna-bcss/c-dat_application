<?php
require_once __DIR__ . '/bootstrap.php';
require_once CDAT_COMMON . '/activity_logger.php';

audit_logout();

session_unset();
session_destroy();

header('Location: ' . (defined('CDAT_BASE') ? rtrim((string) CDAT_BASE, '/') : '') . '/login');
exit;
