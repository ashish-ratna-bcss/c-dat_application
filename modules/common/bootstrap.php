<?php

/**
 * Shared bootstrap for all module pages.
 */
if (!defined('CDAT_ROOT')) {
    define('CDAT_ROOT', dirname(__DIR__, 2));
}
if (!defined('CDAT_COMMON')) {
    define('CDAT_COMMON', CDAT_ROOT . '/modules/common');
}
if (!defined('CDAT_UPLOAD')) {
    define('CDAT_UPLOAD', CDAT_ROOT . '/modules/data-upload');
}
if (!defined('CDAT_CONFIG')) {
    define('CDAT_CONFIG', CDAT_ROOT . '/config');
}
if (!defined('CDAT_BASE')) {
    define('CDAT_BASE', '');
}
if (!defined('CDAT_ASSETS')) {
    define('CDAT_ASSETS', rtrim((string) CDAT_BASE, '/') . '/public/assets');
}
if (!defined('CDAT_LOG_DIR')) {
    define('CDAT_LOG_DIR', CDAT_ROOT . '/logs');
}
if (!is_dir(CDAT_LOG_DIR)) {
    @mkdir(CDAT_LOG_DIR, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', CDAT_LOG_DIR . '/application.log');

require_once CDAT_CONFIG . '/db_connect.php';
