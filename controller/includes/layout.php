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
 */
function cdat_is_active(string $url): bool
{
    $here = strtolower(basename($_SERVER['SCRIPT_NAME'] ?? ''));
    return $here !== '' && strtolower(basename(parse_url($url, PHP_URL_PATH) ?: '')) === $here;
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
    $base  = CDAT_BASE;
    $user  = $_SESSION['audit_fullname'] ?? ($_SESSION['audit_username'] ?? '');
    $menu  = require __DIR__ . '/menu.php';
    $t     = htmlspecialchars($title, ENT_QUOTES);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $t ?> &mdash; CDAT</title>
<link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
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
    $base = CDAT_BASE;
    ?>
      </div>
    </main>
    <footer class="foot">Hyderabad City Police &middot; Call Data Analysis Tool</footer>
  </div>
</div>
<div class="scrim" hidden></div>
<script src="<?= $base ?>/assets/js/app.js"></script>
</body>
</html>
    <?php
}
