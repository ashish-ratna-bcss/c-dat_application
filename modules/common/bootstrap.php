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

require_once CDAT_CONFIG . '/db_connect.php';
