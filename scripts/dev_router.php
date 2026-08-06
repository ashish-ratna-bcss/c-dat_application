<?php
/**
 * Router for PHP built-in server (local Mac/dev).
 * Usage: php -S 127.0.0.1:8020 scripts/dev_router.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($uri === '/') {
    $uri = '/HOME.html';
}

// Block archive folder
if (str_starts_with($uri, '/old_versionfiles')) {
    http_response_code(404);
    echo 'Not found';
    return true;
}

$path = $root . $uri;

// Exact file hit
if ($uri !== '/' && is_file($path) && !is_dir($path)) {
    return serve($root, $path, $uri);
}

// Case-insensitive / extension fallback using tracked canonical names on disk
$resolved = resolve_local($root, $uri);
if ($resolved !== null) {
    return serve($root, $resolved, $uri);
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "Not found: $uri\n";
return true;

function serve(string $root, string $path, string $uri): bool
{
    $real = realpath($path);
    if ($real === false || !str_starts_with($real, realpath($root) ?: $root)) {
        http_response_code(403);
        echo 'Forbidden';
        return true;
    }

    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    if (in_array($ext, ['php', 'phtml'], true) || str_ends_with(strtolower($real), '.php')) {
        require_once $root . '/sqlsrv_compat.php';
        chdir(dirname($real));
        require $real;
        return true;
    }

    // Let the built-in server serve static assets when path matches request
    if ($path === $root . $uri && is_file($path)) {
        return false;
    }

    $types = [
        'html' => 'text/html; charset=utf-8',
        'htm' => 'text/html; charset=utf-8',
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'txt' => 'text/plain; charset=utf-8',
    ];
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($real);
    return true;
}

function resolve_local(string $root, string $uri): ?string
{
    $rel = ltrim($uri, '/');
    if ($rel === '') {
        return null;
    }
    $dir = dirname($rel);
    $base = basename($rel);
    $stem = pathinfo($base, PATHINFO_FILENAME);
    $searchDir = $dir === '.' ? $root : $root . '/' . $dir;
    if (!is_dir($searchDir)) {
        return null;
    }

    $candidates = [];
    foreach (scandir($searchDir) ?: [] as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        if (strcasecmp($name, $base) === 0) {
            return $searchDir . '/' . $name;
        }
        if (strcasecmp(pathinfo($name, PATHINFO_FILENAME), $stem) === 0) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ['html', 'htm', 'php'], true)) {
                $candidates[$ext] = $searchDir . '/' . $name;
            }
        }
    }
    foreach (['php', 'html', 'htm'] as $pref) {
        if (isset($candidates[$pref])) {
            return $candidates[$pref];
        }
    }
    return null;
}
