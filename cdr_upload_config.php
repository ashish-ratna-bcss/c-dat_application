<?php
/**
 * cdr_upload_config.php
 * Configuration for the Common Data Upload Framework (CDR & SDR modules).
 */

return [
    'api' => [
        'base_url' => getenv('CDR_API_URL') ?: 'http://127.0.0.1:8088',
        'public_base_url' => getenv('CDR_API_PUBLIC_URL') ?: '/document-api',
        'api_key' => getenv('CDR_API_KEY') ?: '',
        'poll_interval_seconds' => 2,
        'max_poll_seconds' => 1800,
        'cdr_batch_size' => 5000,
        'sdr_batch_size' => 10000,
        'sdr_chunk_size' => 16 * 1024 * 1024,
    ],
    'cdr' => [
        'name' => 'CDR',
        'module' => 'cdr',
        'target_table' => 'cdatpcsuspect',
        'description' => 'Upload operator CDR documents (Airtel, BSNL, Vi, Jio). Data is staged for manual verification before production load.',
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
        'direct_upload_threshold' => 50 * 1024 * 1024,
        'accept' => '.bak',
    ],
];
