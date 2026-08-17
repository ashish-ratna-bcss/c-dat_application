<?php
require_once __DIR__ . '/bootstrap.php';

function sql_safe_digits(string $raw, int $maxLen = 25): string
{
    return substr(preg_replace('/\D/', '', $raw), 0, $maxLen);
}

function sql_safe_float(string $raw): float
{
    return (float)filter_var($raw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
}

function sql_safe_alnum(string $raw, int $maxLen = 100): string
{
    return substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $raw), 0, $maxLen);
}

function sql_safe_like(string $raw, int $maxLen = 100): string
{
    $v = substr(preg_replace('/[^a-zA-Z0-9_-]/', '', $raw), 0, $maxLen);
    return str_replace(['%', '_'], '', $v);
}

function sql_safe_phone(string $raw): string
{
    return sql_safe_digits($raw, 15);
}

function sql_safe_imei(string $raw): string
{
    return sql_safe_digits($raw, 18);
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
