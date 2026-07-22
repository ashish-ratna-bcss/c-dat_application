<?php
/**
 * cdr_upload_config.php
 * Configuration for the Common Data Upload Framework (CDR & SDR modules).
 */

return [
    'api' => [
        'base_url' => getenv('CDR_API_URL') ?: 'http://127.0.0.1:8088',
        'api_key' => getenv('CDR_API_KEY') ?: '',
        'poll_interval_seconds' => 2,
        'max_poll_seconds' => 1800,
        'cdr_batch_size' => 500,
        'sdr_batch_size' => 10000,
    ],
    'cdr' => [
        'name' => 'CDR',
        'module' => 'cdr',
        'target_table' => 'cdatpcsuspect',
        'description' => 'Upload operator CDR documents (Airtel, BSNL, Vi, Jio). Data is written to production table cdatpcsuspect.',
        'allowed_extensions' => ['csv', 'xls', 'xlsx'],
        'max_file_size' => 700 * 1024 * 1024 * 1024,
        'accept' => '.csv,.xls,.xlsx',
    ],
    'sdr' => [
        'name' => 'SDR',
        'module' => 'sdr',
        'description' => 'Upload MSSQL backup (.bak) for subscriber directory restore and migration.',
        'allowed_extensions' => ['bak'],
        'max_file_size' => 700 * 1024 * 1024 * 1024,
        'accept' => '.bak',
    ],
];
