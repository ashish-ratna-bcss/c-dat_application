<?php

/** Database settings from .env. */
function cdat_db_config(): array
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $cfg = [
        'host'     => '127.0.0.1',
        'port'     => '5432',
        'database' => 'CDATDUPL_DB',
        'user'     => 'postgres',
        'password' => '',
    ];

    $envFile = CDAT_ROOT . '/.env';
    if (is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            match ($key) {
                'CDR_DB_HOST'     => $cfg['host'] = $value,
                'CDR_DB_PORT'     => $cfg['port'] = $value,
                'CDR_DB_NAME'     => $cfg['database'] = $value,
                'CDR_DB_USER'     => $cfg['user'] = $value,
                'CDR_DB_PASSWORD' => $cfg['password'] = $value,
                default           => null,
            };
        }
    }

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
