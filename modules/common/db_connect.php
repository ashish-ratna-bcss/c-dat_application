<?php

function get_cdat_pdo($dbName = 'cdatdupl'): PDO
{
    static $pdos = [];
    $dbName = strtolower($dbName);
    
    // Map legacy DB names to main DB
    $mainDb = (require CDAT_CONFIG . '/db_config.php')['database'] ?? 'CDATDUPL_DB';
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

    $cfg = require CDAT_CONFIG . '/db_config.php';
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
        error_log("Database Connection Failed: " . $e->getMessage());
        die("Database connection failed. Please check the logs.");
    }
}
