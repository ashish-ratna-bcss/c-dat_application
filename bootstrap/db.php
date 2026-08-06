<?php
/**
 * bootstrap/db.php — PDO helper wrapping db_config.php (does not replace sqlsrv_compat).
 */
declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * @return array{host:string,port:string,database:string,user:string,password:string}
 */
function cdat_db_config(): array
{
    /** @var array{host:string,port:string,database:string,user:string,password:string} $cfg */
    $cfg = require CDAT_ROOT . '/db_config.php';
    return $cfg;
}

function cdat_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = cdat_db_config();
    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $c['host'], $c['port'], $c['database']);
    $pdo = new PDO($dsn, $c['user'], $c['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    return $pdo;
}
