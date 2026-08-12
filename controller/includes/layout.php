<?php
/**
 * Shared page shell: header, sidebar navigation, footer.
 *
 * Every page calls layout_begin() then prints its content then layout_end(),
 * instead of carrying its own copy of the chrome. Menus come from menu.php, so
 * changing the navigation is a one-file edit.
 *
 * Paths here are written for pages in controller/. The two constants below are
 * the only place that assumption lives, so a page elsewhere can override them
 * by defining CDAT_BASE before including this file.
 */

if (!defined('CDAT_BASE')) {
    define('CDAT_BASE', '..');          // project root, seen from controller/
}

/**
 * Is this menu entry the page currently being viewed?
 * Menu urls may use %26 for filenames with "&" (e.g. day%26nightloc.php);
 * SCRIPT_NAME is decoded — compare after urldecode.
 */
function cdat_is_active(string $url): bool
{
    $here = strtolower(rawurldecode(basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''))));
    if ($here === '') {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = parse_url($uri, PHP_URL_PATH) ?: '';
        $here = strtolower(rawurldecode(basename($path)));
    }
    $targetPath = parse_url($url, PHP_URL_PATH);
    if ($targetPath === null || $targetPath === false || $targetPath === '') {
        $targetPath = $url;
    }
    $target = strtolower(rawurldecode(basename($targetPath)));
    return $here !== '' && $target !== '' && $here === $target;
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
    echo '<ul class="nav">';
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
                echo '<li><a href="' . htmlspecialchars($c['url'], ENT_QUOTES) . '"'
                   . ($active ? ' class="is-active" aria-current="page"' : '') . '>'
                   . htmlspecialchars($c['label'], ENT_QUOTES) . '</a></li>';
            }
            echo '</ul></li>';
        } else {
            $active = cdat_is_active($item['url'] ?? '');
            echo '<li><a class="nav-item' . ($active ? ' is-active' : '') . '"'
               . ($active ? ' aria-current="page"' : '')
               . ' href="' . htmlspecialchars($item['url'], ENT_QUOTES) . '">'
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
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        return; // Skip rendering layout for single-page AJAX injections
    }

    $base  = CDAT_BASE;
    // Before $user is read: this pulls in activity_logger.php, which starts the
    // session. Reading $_SESSION first meant the signed-in name -- and the
    // quick links button with it -- was missing on every page that does not
    // include activity_logger.php itself, which is most of them.
    require_once __DIR__ . '/quick_links.php';
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
<link rel="icon" type="image/png" sizes="32x32" href="<?= $base ?>/assets/images/favicon.png">
<link rel="apple-touch-icon" href="<?= $base ?>/assets/images/apple-touch-icon.png">
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="<?= $base ?>/assets/css/app.css?v=<?= time() ?>">
<?= $head ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="shell">

  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <img src="<?= $base ?>/assets/images/logo.png" alt="" onerror="this.remove()">
      <div>
        <strong>Hyderabad City Police</strong>
        <span>Call Data Analysis Tool</span>
      </div>
    </div>
    <div class="nav-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
           stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
      <input type="search" id="navSearch" placeholder="Search menu&hellip;"
             autocomplete="off" spellcheck="false" aria-label="Search the menu"
             aria-describedby="navSearchHint">
      <button type="button" class="nav-search-clear" aria-label="Clear search" hidden>&times;</button>
    </div>
    <p id="navSearchHint" class="nav-empty" hidden>No menu item matches</p>
    <nav aria-label="Main">
      <?php cdat_render_nav($menu); ?>
    </nav>
    <div class="side-foot">
      <a class="nav-item" href="logout.php">
        <svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"/></svg>
        <span>Logout</span>
      </a>
    </div>
  </aside>

  <div class="main-wrap">
    <header class="topbar">
      <button class="burger" type="button" aria-label="Toggle navigation" aria-controls="sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="page-title">
        <h1><?= $t ?></h1>
        <?php if ($subtitle !== ''): ?>
          <p><?= htmlspecialchars($subtitle, ENT_QUOTES) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($user !== ''): ?>
        <div class="user" title="Signed in">
          <button type="button" class="ql-open" data-ql-open
                  title="Choose the pages on your dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                 ><path d="m12 3 2.6 5.6 6 .8-4.4 4.2 1.1 6.1L12 16.8 6.7 19.7l1.1-6.1L3.4 9.4l6-.8L12 3Z"/></svg>
            <span>Quick Links</span>
            <?php if ($qlCount > 0): ?>
              <span class="ql-badge"><?= (int)$qlCount ?></span>
            <?php endif; ?>
          </button>
          <span class="avatar"><?= htmlspecialchars(strtoupper(substr($user, 0, 1)), ENT_QUOTES) ?></span>
          <span class="uname"><?= htmlspecialchars($user, ENT_QUOTES) ?></span>
        </div>
      <?php endif; ?>
    </header>

    <main id="main" class="content">
      <div class="card">
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
    <footer class="foot">Hyderabad City Police &middot; Call Data Analysis Tool</footer>
  </div>
</div>
<div class="scrim" hidden></div>
<?php
require_once __DIR__ . '/quick_links.php';
cdat_ql_render_modal();
?>
<script>
// Absolute-ish so the picker works from view/ as well as controller/.
window.CDAT_CSRF  = <?= json_encode(cdat_csrf()) ?>;
window.CDAT_QLAPI = <?= json_encode($base . '/controller/quick_links_api.php') ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="<?= $base ?>/assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
    <?php
}
