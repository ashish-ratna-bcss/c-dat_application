<?php
/**
 * bootstrap/app.php — shared bootstrap for new app/ modules.
 */
declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';

if (!defined('CDAT_SQLSRV_READY')) {
    require_once CDAT_ROOT . '/sqlsrv_compat.php';
    define('CDAT_SQLSRV_READY', true);
}
