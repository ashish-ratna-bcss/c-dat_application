<?php
/**
 * LOGOUT.PHP — Ends the user session and records logout time.
 */
require_once __DIR__ . '/activity_logger.php';

audit_logout();

// Destroy the PHP session
session_unset();
session_destroy();

// Redirect to login
header('Location: LOGIN.HTML');
exit;
