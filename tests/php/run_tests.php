<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/modules/common/sql_safe.php';

function assert_eq(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, 'FAIL: ' . $label . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
    echo 'OK: ' . $label . PHP_EOL;
}

assert_eq('7569422355', sql_safe_phone('7569422355'), 'sql_safe_phone digits');
assert_eq('', sql_safe_phone('abc'), 'sql_safe_phone rejects alpha');
assert_eq('2024-01-15', sql_safe_date('2024-01-15'), 'sql_safe_date valid');
assert_eq('', sql_safe_date('15-01-2024'), 'sql_safe_date invalid');
assert_eq('%test%', sql_like_pattern('test'), 'sql_like_pattern');
assert_eq('INSPECTOR', sql_safe_enum('INSPECTOR', ['INSPECTOR', 'SI']), 'sql_safe_enum');
assert_eq('', sql_safe_enum('HACK', ['INSPECTOR', 'SI']), 'sql_safe_enum reject');
assert_eq('12345', sql_safe_irkey('IR-12345-X'), 'sql_safe_irkey digits');

function test_sql_console_validate(string $sql): string
{
    $sql = trim($sql);
    if ($sql === '') {
        throw new Exception('Query is empty.');
    }
    if (strlen($sql) > 8192) {
        throw new Exception('Query exceeds maximum length (8192 characters).');
    }
    if (!preg_match('/^(select|with)\b/is', $sql)) {
        throw new Exception('Only read-only SELECT queries are allowed.');
    }
    if (preg_match('/\b(insert|update|delete|drop|truncate|alter|create|grant|revoke)\b/i', $sql)) {
        throw new Exception('Only read-only SELECT queries are allowed.');
    }
    return rtrim($sql, ';');
}

assert_eq('SELECT 1', test_sql_console_validate('SELECT 1'), 'select ok');
try {
    test_sql_console_validate('DELETE FROM logins');
    fwrite(STDERR, "FAIL: delete should throw\n");
    exit(1);
} catch (Exception) {
    echo "OK: delete blocked\n";
}

$routes = require dirname(__DIR__, 2) . '/routes/web.php';
$root = dirname(__DIR__, 2);
foreach ($routes as $route) {
    $handler = (string) ($route['handler'] ?? '');
    if ($handler === '' || !is_file($root . '/' . $handler)) {
        fwrite(STDERR, 'FAIL: missing handler ' . $handler . PHP_EOL);
        exit(1);
    }
}
echo 'OK: all route handlers exist (' . count($routes) . " routes)\n";

echo "All PHP tests passed.\n";
