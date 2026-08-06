<?php
/**
 * bootstrap/env.php — load project .env once.
 */
declare(strict_types=1);

if (defined('CDAT_ENV_LOADED')) {
    return;
}
define('CDAT_ENV_LOADED', true);

if (!defined('CDAT_ROOT')) {
    define('CDAT_ROOT', dirname(__DIR__));
}

$envFile = CDAT_ROOT . '/.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
