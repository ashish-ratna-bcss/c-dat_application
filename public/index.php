<?php
/**
 * public/index.php — front controller that DELEGATES to existing root files.
 * Does not move business pages; keeps URLs stable.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

if ($uri === '/' || $uri === '/index.php') {
    $uri = '/HOME.html';
}

if (str_starts_with($uri, '/old_versionfiles')) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

// Health is served from public/
if ($uri === '/health.php' || $uri === '/public/health.php') {
    require __DIR__ . '/health.php';
    exit;
}

$path = $root . $uri;
if (is_file($path)) {
    cdat_public_serve($root, $path);
    exit;
}

// Case-insensitive fallback (same rules as scripts/dev_router.php)
$resolved = cdat_public_resolve($root, $uri);
if ($resolved !== null) {
    cdat_public_serve($root, $resolved);
    exit;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "Not found: $uri\n";
exit;

function cdat_public_serve(string $root, string $path): void
{
    $realRoot = realpath($root) ?: $root;
    $real = realpath($path);
    if ($real === false || !str_starts_with($real, $realRoot)) {
        http_response_code(403);
        echo 'Forbidden';
        return;
    }

    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    $execute = in_array($ext, ['php', 'phtml'], true);
    if (!$execute && is_readable($real)) {
        $start = ltrim((string) file_get_contents($real, false, null, 0, 32));
        if (str_starts_with($start, '<?php') || str_starts_with($start, '<?=')) {
            $execute = true;
        }
    }

    if ($execute) {
        require_once $root . '/sqlsrv_compat.php';
        chdir(dirname($real));
        require $real;
        return;
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
    ];
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($real);
}

function cdat_public_resolve(string $root, string $uri): ?string
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
