<?php
/**
 * Main PHP entry. Route table is routes/web.php.
 *
 *   php -S localhost:8020 main.php
 */
$root = __DIR__;
$logDir = $root . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/application.log');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');

// Baseline security headers for php -S / PHP-FPM (Nginx should reinforce these).
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = '/' . ltrim(rawurldecode($uri), '/');

$blocked = (
    str_starts_with($path, '/config')
    || str_starts_with($path, '/logs')
    || str_starts_with($path, '/dataUpload')
    || preg_match('/\.(env|log)$/i', $path)
);
if ($blocked) {
    http_response_code(403);
    exit;
}

// php -S sends CSS/JS/images through this file. SCRIPT_NAME is then the
// asset URL (e.g. /public/assets/images/logo.png), not /main.php — do not
// treat that as a subdirectory prefix or the file 404s and the login
// logo/background disappear.
$static = $root . $path;
if ($path !== '/' && is_file($static) && !preg_match('/\.(php|html?|htm)$/i', $static)) {
    return false;
}

$script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/main.php'));
$web = '';
if (preg_match('#(?:^|/)main\.php$#i', $script)) {
    $web = rtrim(dirname($script), '/');
}
if ($web === '/' || $web === '\\' || $web === '.') {
    $web = '';
}

if ($web !== '' && ($path === $web || str_starts_with($path, $web . '/'))) {
    $path = substr($path, strlen($web)) ?: '/';
}
$path = '/' . ltrim($path, '/');
if ($path !== '/') {
    $path = rtrim($path, '/');
}

$blocked = (
    str_starts_with($path, '/config')
    || str_starts_with($path, '/logs')
    || str_starts_with($path, '/dataUpload')
    || preg_match('/\.(env|log)$/i', $path)
);
if ($blocked) {
    http_response_code(403);
    exit;
}

$deny = [
    'activity_logger.php',
    'bootstrap.php',
    'db_connect.php',
    'web.php',
];
$base = strtolower(basename($path));
if (in_array($base, $deny, true)) {
    http_response_code(403);
    exit;
}

$full = $root . $path;
if ($path !== '/' && is_file($full) && basename($full) !== 'main.php') {
    return false;
}
if (is_file($full) && !str_ends_with($full, '.php') && !str_ends_with($full, '.html') && !str_ends_with($full, '.htm')) {
    return false;
}

function cdat_dispatch(string $root, string $web, string $handler): void
{
    $target = $root . '/' . ltrim($handler, '/');
    if (!is_file($target)) {
        http_response_code(404);
        echo 'Not found';
        exit;
    }
    if (!defined('CDAT_BASE')) {
        define('CDAT_BASE', $web);
    }
    if (!defined('CDAT_ASSETS')) {
        define('CDAT_ASSETS', ($web === '' ? '' : $web) . '/public/assets');
    }
    if (!defined('CDAT_HANDLER')) {
        define('CDAT_HANDLER', ltrim($handler, '/'));
    }
    require $target;
}

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$routes = require $root . '/routes/web.php';
$GLOBALS['CDAT_ROUTES'] = $routes;

// Prefix: authenticated FastAPI proxy (nested job IDs under /api/data-upload/...)
if (str_starts_with($path, '/api/data-upload')) {
    cdat_dispatch($root, $web, 'modules/data-upload/upload_api_proxy.php');
    return true;
}

foreach ($routes as $route) {
    $routePath = rtrim((string) ($route['path'] ?? ''), '/') ?: '/';
    if ($routePath !== $path) {
        continue;
    }
    $allow = $route['method'] ?? 'GET';
    $allow = is_array($allow) ? array_map('strtoupper', $allow) : [strtoupper((string) $allow)];
    if (!in_array($method, $allow, true) && !in_array('*', $allow, true)) {
        continue;
    }
    cdat_dispatch($root, $web, (string) $route['handler']);
    return true;
}

if (preg_match('#^/controller/(.+)$#', $path, $m)) {
    $base = basename($m[1]);
} else {
    $base = basename($path);
}

if (preg_match('/\.(html?)$/i', $base)) {
    $base = preg_replace('/\.(html?)$/i', '.php', $base);
}

foreach ($routes as $route) {
    $handler = (string) ($route['handler'] ?? '');
    if ($handler !== '' && strcasecmp(basename($handler), $base) === 0) {
        cdat_dispatch($root, $web, $handler);
        return true;
    }
}

http_response_code(404);
echo 'Not found';
