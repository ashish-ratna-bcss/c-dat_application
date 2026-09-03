<?php

/**
 * Load KEY=VALUE pairs from the application root .env into putenv/$_ENV
 * so getenv('CDAT_*') and related toggles work for PHP-FPM / php -S.
 * Does not overwrite variables already set in the process environment.
 */
function cdat_load_dotenv(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    if (!defined('CDAT_ROOT')) {
        return;
    }
    $envFile = CDAT_ROOT . '/.env';
    if (!is_readable($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim((string) $line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            continue;
        }
        $value = trim($value, "\"'");
        if (getenv($key) !== false) {
            continue;
        }
        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

/** Database settings from .env. */
function cdat_db_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    cdat_load_dotenv();

    $cfg = [
        'host'     => getenv('CDR_DB_HOST') ?: '127.0.0.1',
        'port'     => getenv('CDR_DB_PORT') ?: '5432',
        'database' => getenv('CDR_DB_NAME') ?: 'CDATDUPL_DB',
        'user'     => getenv('CDR_DB_USER') ?: 'postgres',
        'password' => getenv('CDR_DB_PASSWORD') !== false ? (string) getenv('CDR_DB_PASSWORD') : '',
    ];

    return $cfg;
}

function get_cdat_pdo($dbName = 'cdatdupl'): PDO
{
    static $pdos = [];
    $dbName = strtolower($dbName);

    $cfg = cdat_db_config();
    $mainDb = $cfg['database'];
    $map = [
        'cdatdupl' => $mainDb,
        'cdat' => $mainDb,
        'postgres' => $mainDb,
        'twrmdb' => $mainDb,
        'irforms' => $mainDb,
        'forms' => $mainDb,
        'jrms' => $mainDb,
        'pdact' => $mainDb,
        'rowdy_sheets_database' => $mainDb,
    ];
    $targetDb = $map[$dbName] ?? $mainDb;

    if (isset($pdos[$targetDb])) {
        return $pdos[$targetDb];
    }

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $cfg['host'], $cfg['port'], $targetDb);

    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_CASE => PDO::CASE_UPPER,
            PDO::ATTR_TIMEOUT => 10,
        ]);
        $pdo->exec("SET statement_timeout = '120s'");
        $pdo->exec("SET idle_in_transaction_session_timeout = '60s'");
        $pdo->exec("SET lock_timeout = '30s'");
        $pdos[$targetDb] = $pdo;
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database Connection Failed: ' . $e->getMessage());
        die('Database connection failed. Please check the logs.');
    }
}
