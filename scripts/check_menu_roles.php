<?php
/**
 * Does every 'role' in menu.php match the guard the page really enforces?
 *
 * Hiding a menu entry is tidiness. The guard inside the page is the control.
 * If the two drift apart you get one of two bugs, and the quiet one is worse:
 *
 *   menu says admin, page has no guard  -> the link is hidden but the page is
 *                                          open to anyone who types the URL
 *   menu says nothing, page requires it -> the link is offered to people who
 *                                          will only get "ACCESS DENIED"
 *
 * Run:  php scripts/check_menu_roles.php
 * Exits non-zero if anything is out of step, so it can go in a pre-commit hook.
 */

$root = dirname(__DIR__);
$menu = require $root . '/modules/common/includes/menu.php';

/**
 * Where a menu url actually lands: pretty routes from routes/web.php,
 * then a matching file under modules/ or view/.
 */
function resolve_page(string $root, string $url): ?string
{
    $rel = rawurldecode(parse_url($url, PHP_URL_PATH) ?? $url);
    $relPath = '/' . ltrim($rel, '/');
    $relPath = rtrim($relPath, '/') ?: '/';

    $routesFile = $root . '/routes/web.php';
    if (is_file($routesFile)) {
        $routes = require $routesFile;
        foreach ($routes as $route) {
            $routePath = rtrim((string) ($route['path'] ?? ''), '/') ?: '/';
            if ($routePath === $relPath && !empty($route['handler'])) {
                $handler = $root . '/' . ltrim((string) $route['handler'], '/');
                if (is_file($handler)) {
                    return $handler;
                }
            }
        }
    }

    $rel = ltrim($rel, '/');
    $direct = $root . '/' . $rel;
    if (is_file($direct)) {
        return $direct;
    }
    $base = preg_replace('/\.(html?|php)$/i', '', basename($rel));
    foreach ([$root . '/modules', $root . '/view'] as $dir) {
        $hits = glob($dir . '/*/' . $base . '.{php,html,htm}', GLOB_BRACE) ?: [];
        if ($hits) {
            return $hits[0];
        }
        foreach (['.php', '.html', '.htm'] as $ext) {
            $p = $dir . '/' . $base . $ext;
            if (is_file($p)) {
                return $p;
            }
        }
    }
    return null;
}

/** guard actually present in a page file */
function guard_of(?string $file): string
{
    if ($file === null || !is_file($file)) {
        return 'missing';
    }
    $src = file_get_contents($file);
    if (preg_match('/audit_require_admin\s*\(/', $src))    { return 'admin'; }
    if (preg_match('/audit_require_uploader\s*\(/', $src)) { return 'uploader'; }
    if (preg_match('/audit_require_session\s*\(/', $src))  { return 'session'; }
    return 'none';
}

$rows = [];
foreach ($menu as $item) {
    $kids = !empty($item['children']) ? $item['children'] : [$item];
    foreach ($kids as $c) {
        if (empty($c['url'])) {
            continue;
        }
        $rows[] = [
            'label'    => $c['label'],
            'url'      => $c['url'],
            'declared' => $c['role'] ?? '',
            'actual'   => guard_of(resolve_page($root, $c['url'])),
        ];
    }
}

$bad = $open = 0;
$unguarded = [];

foreach ($rows as $r) {
    $declared = $r['declared'];
    $actual   = $r['actual'];

    if ($actual === 'missing') {
        printf("  MISSING FILE   %-28s %s\n", $r['label'], $r['url']);
        $bad++;
        continue;
    }

    // A page may guard MORE tightly than the menu claims only if the menu
    // would then offer it to someone who cannot open it.
    if ($declared === '' && in_array($actual, ['admin', 'uploader'], true)) {
        printf("  OFFERED TO ALL %-28s page requires %s\n", $r['label'], $actual);
        $bad++;
    } elseif ($declared !== '' && $declared !== $actual) {
        printf("  MISMATCH       %-28s menu says %s, page enforces %s\n",
               $r['label'], $declared, $actual);
        $bad++;
    }

    if ($actual === 'none') {
        $unguarded[] = $r['label'] . '  (' . $r['url'] . ')';
        $open++;
    }
}

printf("\n%d menu links checked, %d inconsistent with the page's own guard\n",
       count($rows), $bad);

if ($unguarded) {
    printf("\n%d of them enforce nothing at all -- open to anyone who knows the\n"
         . "URL, signed in or not. Hiding a link cannot fix that; the page needs\n"
         . "audit_require_session().\n\n", $open);
    foreach ($unguarded as $u) {
        echo "    $u\n";
    }
}

exit($bad === 0 ? 0 : 1);
