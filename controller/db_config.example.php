<?php
/**
 * Database configuration template.
 * Copy to db_config.php and set values for your environment.
 */
return [
    'host' => getenv('CDR_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('CDR_DB_PORT') ?: '5432',
    'database' => getenv('CDR_DB_NAME') ?: 'postgres',
    'user' => getenv('CDR_DB_USER') ?: 'postgres',
    'password' => getenv('CDR_DB_PASSWORD') ?: 'your_password_here',
];
