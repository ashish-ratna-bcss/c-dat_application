<?php
require_once __DIR__ . '/activity_logger.php';

audit_logout();

session_unset();
session_destroy();

header('Location: auth.html');
exit;
