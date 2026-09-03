<?php
/**
 * Shared page shell: header, sidebar navigation, footer.
 *
 * Every page calls layout_begin() then prints its content then layout_end(),
 * instead of carrying its own copy of the chrome. Menus come from menu.php, so
 * changing the navigation is a one-file edit.
 *
 * Paths here are written for pages in modules/<name>/. The two constants below are
 * the only place that assumption lives, so a page elsewhere can override them
 * by defining CDAT_BASE before including this file.
 */

if (!defined('CDAT_BASE')) {
    define('CDAT_BASE', '');
}
if (!defined('CDAT_ASSETS')) {
    define('CDAT_ASSETS', rtrim(CDAT_BASE, '/') . '/public/assets');
}
if (!defined('CDAT_BOOTSTRAP')) {
    define('CDAT_BOOTSTRAP', CDAT_ASSETS . '/vendor/bootstrap');
}

/*
 * Server-side session gate for every page that loads the shared shell.
 * Menu visibility is not a control — investigative modules must not be
 * reachable by direct URL without authentication.
 *
 * Opt out only for intentional public/JSON handlers via CDAT_SKIP_SESSION_GUARD.
 */
if (!defined('CDAT_SKIP_SESSION_GUARD') || !CDAT_SKIP_SESSION_GUARD) {
    require_once dirname(__DIR__) . '/activity_logger.php';
    audit_require_session();
}

/**
 * Is this menu entry the page currently being viewed?
 * Menu urls may use %26 for filenames with "&" (e.g. day%26nightloc.php);
 * SCRIPT_NAME is decoded — compare after urldecode.
 */
function cdat_href(string $url): string
{
    if ($url === '' || preg_match('#^(https?:)?//#i', $url)) {
        return $url;
    }
    $base = rtrim((string) CDAT_BASE, '/');
    if ($base === '.') {
        $base = '';
    }
    if (str_starts_with($url, '/')) {
        return $base === '' ? $url : $base . $url;
    }
    return ($base === '' ? '' : $base) . '/' . ltrim($url, '/');
}

/**
 * Pretty path for a legacy .php filename (from routes/web.php).
 */
function cdat_page(string $file, array $query = []): string
{
    static $map = null;
    if ($map === null) {
        $map = [];
        $root = defined('CDAT_ROOT') ? CDAT_ROOT : dirname(__DIR__, 3);
        $routes = [];
        if (isset($GLOBALS['CDAT_ROUTES']) && is_array($GLOBALS['CDAT_ROUTES'])) {
            $routes = $GLOBALS['CDAT_ROUTES'];
        } else {
            $routesFile = $root . '/routes/web.php';
            if (is_file($routesFile)) {
                $loaded = require $routesFile;
                $routes = is_array($loaded) ? $loaded : [];
                $GLOBALS['CDAT_ROUTES'] = $routes;
            }
        }
        $pending = [];
        foreach ($routes as $route) {
            $handler = strtolower(basename((string) ($route['handler'] ?? '')));
            $path = (string) ($route['path'] ?? '');
            if ($handler === '' || $path === '') {
                continue;
            }
            $methods = $route['method'] ?? 'GET';
            $methods = is_array($methods) ? $methods : [$methods];
            $methods = array_map('strtoupper', $methods);
            $isGet = in_array('GET', $methods, true) || in_array('*', $methods, true);
            if ($isGet && !isset($map[$handler])) {
                $map[$handler] = $path;
            } elseif (!$isGet && !isset($pending[$handler])) {
                $pending[$handler] = $path;
            }
        }
        foreach ($pending as $handler => $path) {
            if (!isset($map[$handler])) {
                $map[$handler] = $path;
            }
        }
        $map['sum_btwn_dates.php'] = $map['sum_btwn_dates.php'] ?? '/summary/between-dates';
        $map['sum_new_no.php'] = $map['sum_new_no.php'] ?? '/summary/new-contacts';
        $map['bulk_address.php'] = $map['bulk_address.php'] ?? '/address/bulk';
        $map['bulk_cdat_contacts1.php'] = $map['bulk_cdat_contacts.php'] ?? '/cdat/bulk-contacts';
        $map['cdatcnts2.php'] = $map['cdatcnts.php'] ?? '/cdat/contacts';
        $map['d&n_loc.php'] = $map['d&n_loc.php'] ?? '/day-night-location/top-10';
        $map['d&n_bt_dts.php'] = $map['d&n_bt_dts.php'] ?? '/day-night-location/by-date';
        $map['d%26n_loc.php'] = $map['d&n_loc.php'];
        $map['d%26n_bt_dts.php'] = $map['d&n_bt_dts.php'];
        $map['day%26nightloc.php'] = $map['day&nightloc.php'] ?? '/day-night-location/top-10';
        $map['day%26nightloc_btwn_dates.php'] = $map['day&nightloc_btwn_dates.php'] ?? '/day-night-location/by-date';
    }

    $raw = explode('?', $file, 2)[0];
    $base = strtolower(basename(rawurldecode($raw)));
    $path = $map[$base] ?? ('/' . ltrim($raw, '/'));
    $url = cdat_href($path);

    $q = $query;
    $existing = parse_url($file, PHP_URL_QUERY);
    if (is_string($existing) && $existing !== '') {
        parse_str($existing, $fromFile);
        $q = array_merge($fromFile, $q);
    }
    if ($q) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($q);
    }
    return $url;
}

/** Form action: stay on this pretty URL, or post to the mapped handler. */
function cdat_form_action(string $action): string
{
    $action = trim($action);
    if ($action === '' || $action === '#') {
        return '';
    }
    if (preg_match('#^(https?:)?//#i', $action) || str_starts_with($action, '/')) {
        return cdat_href($action);
    }
    $file = strtolower(basename(rawurldecode(explode('?', $action, 2)[0])));
    $here = defined('CDAT_HANDLER') ? strtolower(basename((string) CDAT_HANDLER)) : '';
    if ($here !== '' && $file === $here) {
        return '';
    }
    return cdat_page($action);
}

function cdat_is_active(string $url): bool
{
    $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $here = parse_url($uri, PHP_URL_PATH) ?: '';
    $here = '/' . ltrim(rawurldecode($here), '/');
    $here = rtrim(strtolower($here), '/') ?: '/';

    $base = rtrim(strtolower((string) CDAT_BASE), '/');
    if ($base !== '' && $base !== '.' && str_starts_with($here, $base)) {
        $here = substr($here, strlen($base)) ?: '/';
        $here = '/' . ltrim($here, '/');
        $here = rtrim($here, '/') ?: '/';
    }

    $targetPath = parse_url($url, PHP_URL_PATH);
    if ($targetPath === null || $targetPath === false || $targetPath === '') {
        $targetPath = $url;
    }
    $target = '/' . ltrim(rawurldecode((string) $targetPath), '/');
    $target = rtrim(strtolower($target), '/') ?: '/';
    return $here === $target;
}

/**
 * A group is open when one of its children is the current page, so the user
 * never lands on a page whose menu entry is hidden inside a collapsed group.
 */
function cdat_group_has_active(array $children): bool
{
    foreach ($children as $c) {
        if (isset($c['url']) && cdat_is_active($c['url'])) {
            return true;
        }
    }
    return false;
}

/**
 * The menu, loaded once. Quick links resolve their labels and icons against
 * it, so it has to be reachable outside layout_begin().
 */
function cdat_menu(): array
{
    static $menu = null;
    if ($menu === null) {
        $menu = require __DIR__ . '/menu.php';
    }
    return $menu;
}

/**
 * Does the signed-in user hold $need?
 *
 *   ''          any signed-in user
 *   'uploader'  admin or poweruser   (matches audit_require_uploader)
 *   'admin'     admin                (matches audit_require_admin)
 *
 * Deliberately fails closed: an unknown role name grants nothing, so a typo in
 * menu.php hides an entry rather than exposing one.
 */
function cdat_role_allows(string $need): bool
{
    if ($need === '') {
        return true;
    }
    $role = strtolower($_SESSION['audit_role'] ?? '');
    switch ($need) {
        case 'admin':    return $role === 'admin';
        case 'uploader': return $role === 'admin' || $role === 'poweruser';
        default:         return false;
    }
}

/**
 * The menu with everything this user may not open removed. A group whose
 * children are all filtered out goes with them -- an empty expander is worse
 * than no expander.
 */
function cdat_menu_visible(): array
{
    static $visible = null;
    if ($visible !== null) {
        return $visible;
    }
    $visible = [];
    foreach (cdat_menu() as $item) {
        if (!cdat_role_allows($item['role'] ?? '')) {
            continue;
        }
        if (!empty($item['children'])) {
            $kids = array_values(array_filter(
                $item['children'],
                static fn(array $c): bool => cdat_role_allows($c['role'] ?? '')
            ));
            if (!$kids) {
                continue;
            }
            $item['children'] = $kids;
        }
        $visible[] = $item;
    }
    return $visible;
}

function cdat_icon(string $name): string
{
    // Inline so the page needs no icon font or sprite request.
    $p = [
        'home'   => 'M3 10.5 12 3l9 7.5M5 9.5V21h14V9.5',
        'upload' => 'M12 16V4m0 0L8 8m4-4 4 4M4 20h16',
        'chart'  => 'M4 20V10M10 20V4M16 20v-7M22 20H2',
        'phone'  => 'M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z',
        'grid'   => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
        'device' => 'M7 2h10v20H7zM11 18h2',
        'pin'    => 'M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12Z M12 10.5h.01',
        'map'    => 'M9 3 3 6v15l6-3 6 3 6-3V3l-6 3-6-3Zm0 0v15m6-12v15',
        'file'   => 'M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Zm0 0v5h5',
        'folder' => 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z',
        'car'    => 'M5 16h14M6 16l1-5h10l1 5M7 19h2M15 19h2M4 11h16',
        'cog'    => 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8-3-2 .6M6 11.4 4 12m8 8-.6-2M12.6 6 12 4m5.7 12.7-1.4-1.4M7.7 8.7 6.3 7.3m0 9.4 1.4-1.4M16.3 8.7l1.4-1.4',
    ];
    $d = $p[$name] ?? $p['grid'];
    return '<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" '
         . 'aria-hidden="true"><path d="' . $d . '"/></svg>';
}

function cdat_render_nav(array $items): void
{
    echo '<ul class="cdat-nav">';
    foreach ($items as $i => $item) {
        $label = htmlspecialchars($item['label'], ENT_QUOTES);
        if (!empty($item['children'])) {
            $open = cdat_group_has_active($item['children']);
            $id   = 'grp' . $i;
            echo '<li class="nav-group' . ($open ? ' is-open' : '') . '">';
            // A button, not a link: expanding a parent must never navigate.
            echo '<button type="button" class="nav-parent" aria-expanded="'
               . ($open ? 'true' : 'false') . '" aria-controls="' . $id . '">'
               . cdat_icon($item['icon'] ?? 'grid')
               . '<span>' . $label . '</span>'
               . '<svg class="chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
               . 'stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>'
               . '</button>';
            echo '<ul class="nav-sub" id="' . $id . '">';
            foreach ($item['children'] as $c) {
                $active = cdat_is_active($c['url'] ?? '');
                echo '<li><a href="' . htmlspecialchars(cdat_href($c['url']), ENT_QUOTES) . '"'
                   . ($active ? ' class="is-active" aria-current="page"' : '') . '>'
                   . htmlspecialchars($c['label'], ENT_QUOTES) . '</a></li>';
            }
            echo '</ul></li>';
        } else {
            $active = cdat_is_active($item['url'] ?? '');
            echo '<li><a class="nav-item' . ($active ? ' is-active' : '') . '"'
               . ($active ? ' aria-current="page"' : '')
               . ' href="' . htmlspecialchars(cdat_href($item['url'] ?? ''), ENT_QUOTES) . '">'
               . cdat_icon($item['icon'] ?? 'grid')
               . '<span>' . $label . '</span></a></li>';
        }
    }
    echo '</ul>';
}

/**
 * @param string $head Extra markup for <head> -- a page's own <style>, CDN
 *                     tags or scripts. Capture it with ob_start()/ob_get_clean()
 *                     so the block can stay written as plain HTML in the page.
 */
function layout_begin(string $title = 'Call Data Analysis Tool', string $subtitle = '',
                      string $head = ''): void
{
    // Load before the AJAX early-return: dashboard content still calls
    // cdat_ql_render_grid() when the shell is skipped.
    require_once __DIR__ . '/quick_links.php';

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        header('X-CDAT-Title: ' . str_replace(["\r", "\n"], '', $title));
        return; // Skip rendering layout for single-page AJAX injections
    }

    $base  = CDAT_BASE;
    // ?: not ??  -- audit_fullname is set to '' when the LOGINS row has no
    // FULLNAME, and an empty name should fall through to the username.
    $user  = ($_SESSION['audit_fullname'] ?? '') ?: ($_SESSION['audit_username'] ?? '');
    $menu  = cdat_menu_visible();
    $t     = htmlspecialchars($title, ENT_QUOTES);
    $qlCount = $user !== '' ? count(cdat_ql_get()) : 0;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $t ?> &mdash; CDAT</title>
<link rel="icon" type="image/png" sizes="32x32" href="<?= CDAT_ASSETS ?>/images/favicon.png">
<link rel="apple-touch-icon" href="<?= CDAT_ASSETS ?>/images/apple-touch-icon.png">
<link rel="stylesheet" href="<?= CDAT_BOOTSTRAP ?>/css/bootstrap.min.css">
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?= CDAT_ASSETS ?>/css/cdat-bootstrap.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= CDAT_ASSETS ?>/css/app.css?v=<?= time() ?>">
<?php
require_once CDAT_COMMON . '/csrf.php';
?>
<meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
<?= $head ?>
</head>
<body>
<a class="skip visually-hidden-focusable" href="#main">Skip to content</a>
<div class="container-fluid g-0">
<div class="shell cdat-shell row g-0 flex-nowrap min-vh-100">

  <aside class="sidebar cdat-sidebar col-auto" id="sidebar">
    <div class="brand">
      <img src="<?= CDAT_ASSETS ?>/images/logo.png" alt="" onerror="this.remove()">
      <div>
        <strong>Hyderabad City Police</strong>
        <span>Call Data Analysis Tool</span>
      </div>
    </div>
    <div class="nav-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
           stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      <input type="search" id="navSearch"
             placeholder="Search menu&hellip;"
             autocomplete="off" spellcheck="false" aria-label="Search the menu"
             aria-describedby="navSearchHint">
      <button type="button" class="nav-search-clear" aria-label="Clear search" hidden>&times;</button>
    </div>
    <p id="navSearchHint" class="nav-empty" hidden>No menu item matches</p>
    <nav aria-label="Main">
      <?php cdat_render_nav($menu); ?>
    </nav>
    <div class="side-foot">
      <a class="nav-item" href="<?= htmlspecialchars(cdat_href('/logout'), ENT_QUOTES) ?>">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"/></svg>
        <span>Logout</span>
      </a>
    </div>
  </aside>

  <div class="main-wrap col min-vw-0 d-flex flex-column">
    <header class="topbar cdat-topbar navbar navbar-expand sticky-top">
      <div class="container-fluid px-3 px-lg-4">
      <button class="burger btn btn-link text-dark d-lg-none p-1 me-2" type="button"
              aria-label="Toggle navigation" aria-controls="sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" width="22" height="22" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="page-title flex-grow-1 min-w-0">
        <h1 class="h5 mb-0 fw-semibold text-truncate"><?= $t ?></h1>
        <?php if ($subtitle !== ''): ?>
          <p class="mb-0 small text-secondary text-truncate"><?= htmlspecialchars($subtitle, ENT_QUOTES) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($user !== ''): ?>
        <div class="user d-flex align-items-center gap-2 ms-auto" title="Signed in">
          <button type="button" class="ql-open btn btn-outline-secondary btn-sm rounded-pill" data-ql-open
                  title="Choose the pages on your dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="15" height="15"
                 ><path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6.1L12 16.8 6.7 19.7l1.1-6.1L3.4 9.4l6-.8L12 3Z"/></svg>
            <span>Quick Links</span>
            <?php if ($qlCount > 0): ?>
              <span class="ql-badge badge text-bg-primary"><?= (int)$qlCount ?></span>
            <?php endif; ?>
          </button>
          <span class="avatar"><?= htmlspecialchars(strtoupper(substr($user, 0, 1)), ENT_QUOTES) ?></span>
          <span class="uname d-none d-md-inline small text-secondary"><?= htmlspecialchars($user, ENT_QUOTES) ?></span>
        </div>
      <?php endif; ?>
      </div>
    </header>

    <main id="main" class="content flex-grow-1 min-vw-0">
      <div class="cdat-page container-fluid px-3 px-lg-4 py-3">
    <?php
}

function layout_end(): void
{
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        return; // Skip rendering layout for single-page AJAX injections
    }

    $base = CDAT_BASE;
    ?>
      </div>
    </main>
    <footer class="foot container-fluid px-3 px-lg-4 py-3 small text-secondary">Hyderabad City Police &middot; Call Data Analysis Tool</footer>
  </div>
</div>
</div>
<div class="scrim d-none" hidden aria-hidden="true"></div>
<?php
require_once __DIR__ . '/quick_links.php';
cdat_ql_render_modal();
?>
<script>
// Absolute-ish so the picker works from any routed module page.
window.CDAT_CSRF  = <?= json_encode(cdat_csrf()) ?>;
window.CDAT_BASE  = <?= json_encode($base) ?>;
window.CDAT_QLAPI = <?= json_encode(cdat_href('/api/quick-links')) ?>;
</script>
<script src="<?= CDAT_BOOTSTRAP ?>/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="<?= CDAT_ASSETS ?>/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
    <?php
}
