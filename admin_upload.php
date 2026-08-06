<?php
/**
 * Admin upload entry — same URL /admin_upload.php
 */
declare(strict_types=1);
$root = __DIR__;
require_once $root . '/bootstrap/app.php';
require $root . '/app/Admin/upload_legacy.php';
