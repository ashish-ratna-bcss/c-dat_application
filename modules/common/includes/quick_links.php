<?php
/**
 * Per-user quick links -- the tiles on the dashboard and the picker that fills
 * them.
 *
 * Only the URL is stored. Labels, groups and icons are resolved from menu.php
 * every time the tiles are drawn, so menu.php stays the single source of truth:
 * rename an entry and the tile renames itself, remove one and it drops off
 * every dashboard rather than leaving a dead link behind.
 *
 * A saved URL is only ever accepted if it appears in that menu. The picker is
 * built from the same list, so this costs nothing in normal use -- it means a
 * hand-crafted POST cannot turn a user's own dashboard into a link to anywhere
 * else on the server.
 */

require_once __DIR__ . '/../activity_logger.php';

/** More than this and the grid stops being a shortcut. */
const CDAT_QL_MAX = 12;

function cdat_ql_user(): string
{
    return (string)($_SESSION['audit_username'] ?? '');
}

/**
 * Token for the save endpoint. Saving changes stored state, so it should not be
 * possible to trigger it from another site with the user's cookie.
 */
function cdat_csrf(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['cdat_csrf'])) {
        $_SESSION['cdat_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['cdat_csrf'];
}

function cdat_csrf_ok(string $sent): bool
{
    $have = $_SESSION['cdat_csrf'] ?? '';
    return $have !== '' && hash_equals($have, $sent);
}

/**
 * Every linkable page in the menu, flattened, keyed by url.
 * Children carry their parent's label as the group and its icon.
 *
 * Built from the role-filtered menu, so this is also the whitelist a save is
 * checked against: a user cannot pin a page their role hides, and if an
 * account is demoted its tiles to pages it can no longer open stop being
 * resolved and drop off the dashboard by themselves.
 */
function cdat_ql_catalog(): array
{
    static $flat = null;
    if ($flat !== null) {
        return $flat;
    }
    $flat = [];
    foreach (cdat_menu_visible() as $item) {
        $icon = $item['icon'] ?? 'grid';
        if (!empty($item['children'])) {
            foreach ($item['children'] as $c) {
                if (empty($c['url'])) {
                    continue;
                }
                $flat[$c['url']] = [
                    'url'   => $c['url'],
                    'label' => $c['label'],
                    'group' => $item['label'],
                    'icon'  => $icon,
                ];
            }
        } elseif (!empty($item['url'])) {
            $flat[$item['url']] = [
                'url'   => $item['url'],
                'label' => $item['label'],
                'group' => '',
                'icon'  => $icon,
            ];
        }
    }
    return $flat;
}

/**
 * The table is created on demand so a fresh checkout works without a migration
 * step. It is a no-op after the first call in a request, and after the first
 * request against a database that already has it.
 */
function cdat_ql_ensure_table(PDO $db): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    $ok = $db->exec(
        'CREATE TABLE IF NOT EXISTS user_quick_links (
             id         SERIAL PRIMARY KEY,
             username   VARCHAR(100) NOT NULL,
             url        VARCHAR(255) NOT NULL,
             label      VARCHAR(150) NOT NULL DEFAULT \'\',
             position   INTEGER      NOT NULL DEFAULT 0,
             created_at TIMESTAMP    NOT NULL DEFAULT NOW(),
             CONSTRAINT user_quick_links_unique UNIQUE (username, url)
         )'
    ) !== false;
    if ($ok) {
        $db->exec('CREATE INDEX IF NOT EXISTS user_quick_links_user_pos
                   ON user_quick_links (username, position)');
    }
    return $ok;
}

/**
 * This user's links, in their chosen order, resolved against the menu.
 * Urls that are no longer in the menu are skipped.
 *
 * @return array<int, array{url:string,label:string,group:string,icon:string}>
 */
function cdat_ql_get(): array
{
    // Cached: layout_begin() needs the count for the toolbar badge and
    // layout_end() needs the set for the picker, on every page.
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $cache = cdat_ql_load();
    return $cache;
}

function cdat_ql_load(): array
{
    $user = cdat_ql_user();
    if ($user === '') {
        return [];
    }
    try {
        $db = audit_db();
        if (!cdat_ql_ensure_table($db)) {
            return [];
        }
        $stmt = $db->prepare('SELECT url FROM user_quick_links
                              WHERE username = :u ORDER BY position, id');
        if (!$stmt || !$stmt->execute([':u' => $user])) {
            return [];
        }
        $catalog = cdat_ql_catalog();
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $url) {
            if (isset($catalog[$url])) {
                $out[] = $catalog[$url];
            }
        }
        return $out;
    } catch (Throwable $e) {
        error_log('cdat_ql_get: ' . $e->getMessage());
        return [];
    }
}

/**
 * Replace this user's set with $urls, in the order given. Unknown urls are
 * dropped rather than rejected, so one stale entry cannot fail the whole save.
 *
 * @return array{ok:bool, links:array, skipped:int, error?:string}
 */
function cdat_ql_save(array $urls): array
{
    $user = cdat_ql_user();
    if ($user === '') {
        return ['ok' => false, 'links' => [], 'skipped' => 0, 'error' => 'Not signed in.'];
    }
    $catalog = cdat_ql_catalog();
    $clean = [];
    $skipped = 0;
    foreach ($urls as $u) {
        $u = is_string($u) ? trim($u) : '';
        if ($u === '' || isset($clean[$u])) {
            continue;
        }
        if (!isset($catalog[$u])) {
            $skipped++;
            continue;
        }
        $clean[$u] = true;
        if (count($clean) >= CDAT_QL_MAX) {
            break;
        }
    }
    $clean = array_keys($clean);

    try {
        $db = audit_db();
        if (!cdat_ql_ensure_table($db)) {
            return ['ok' => false, 'links' => [], 'skipped' => $skipped,
                    'error' => 'Quick links table is missing and could not be created.'];
        }
        $db->beginTransaction();
        $del = $db->prepare('DELETE FROM user_quick_links WHERE username = :u');
        $del->execute([':u' => $user]);
        if ($clean) {
            $ins = $db->prepare('INSERT INTO user_quick_links (username, url, label, position)
                                 VALUES (:u, :url, :label, :pos)');
            foreach ($clean as $i => $url) {
                $ins->execute([
                    ':u'     => $user,
                    ':url'   => $url,
                    ':label' => $catalog[$url]['label'],
                    ':pos'   => $i,
                ]);
            }
        }
        $db->commit();
    } catch (Throwable $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('cdat_ql_save: ' . $e->getMessage());
        return ['ok' => false, 'links' => [], 'skipped' => $skipped, 'error' => 'Could not save.'];
    }

    audit_log('Dashboard', 'Quick Links Saved', ['count' => count($clean)]);
    return ['ok' => true, 'links' => array_values(array_map(
        static fn($u) => $catalog[$u], $clean)), 'skipped' => $skipped];
}

/* ------------------------------------------------------------------ view */

/**
 * The dashboard grid. Replaces the decorative image that used to fill this
 * space -- tiles the user picked are more use than a picture of a logo.
 */
function cdat_ql_render_grid(): void
{
    $links = cdat_ql_get();
    ?>
    <section class="ql-panel" id="qlPanel">
      <div class="ql-head">
        <h2>Quick Links</h2>
        <button type="button" class="ql-edit btn btn-outline-secondary btn-sm" data-ql-open>
          <?= $links ? 'Edit' : 'Choose pages' ?>
        </button>
      </div>
      <?php if (!$links): ?>
        <div class="ql-empty">
          <p><strong>No quick links yet.</strong></p>
          <p>Pick the pages you open most and they will appear here, on every
             sign-in, just for you.</p>
          <button type="button" class="ql-cta btn btn-primary" data-ql-open>Choose pages</button>
        </div>
      <?php else: ?>
        <div class="ql-grid row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3" id="qlGrid">
          <?php foreach ($links as $l): ?>
            <div class="col">
            <a class="ql-tile text-decoration-none h-100 d-flex flex-column" href="<?= htmlspecialchars($l['url'], ENT_QUOTES) ?>">
              <span class="ql-ic"><?= cdat_icon($l['icon']) ?></span>
              <span class="ql-label"><?= htmlspecialchars($l['label'], ENT_QUOTES) ?></span>
              <?php if ($l['group'] !== ''): ?>
                <span class="ql-group"><?= htmlspecialchars($l['group'], ENT_QUOTES) ?></span>
              <?php endif; ?>
            </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <?php
}

/**
 * The picker. Rendered once per page by layout_end() so the toolbar button
 * works everywhere, not only on the dashboard.
 */
function cdat_ql_render_modal(): void
{
    if (cdat_ql_user() === '') {
        return;               // nothing to pin to
    }
    $chosen = [];
    foreach (cdat_ql_get() as $l) {
        $chosen[] = $l['url'];
    }
    $menu = cdat_menu_visible();   // the picker offers only what this role may open
    ?>
    <div class="ql-modal" id="qlModal" hidden>
      <div class="ql-backdrop" data-ql-close></div>
      <div class="ql-dialog" role="dialog" aria-modal="true" aria-labelledby="qlTitle">
        <header class="ql-dialog-head">
          <div>
            <h2 id="qlTitle">Quick links</h2>
            <p>Tick the pages you want on your dashboard. Up to <?= CDAT_QL_MAX ?>.</p>
          </div>
          <button type="button" class="ql-x btn btn-outline-secondary btn-sm" data-ql-close aria-label="Close">&times;</button>
        </header>

        <div class="ql-picked" id="qlPicked" aria-live="polite">
          <!-- chips, newest last; order here is the order on the dashboard -->
        </div>

        <div class="ql-searchwrap">
          <input type="search" id="qlSearch" class="form-control form-control-sm" placeholder="Search pages&hellip;"
                 autocomplete="off" spellcheck="false" aria-label="Search pages">
          <span class="ql-count badge text-bg-secondary" id="qlCount"></span>
        </div>

        <div class="ql-list" id="qlList" tabindex="0">
          <?php foreach ($menu as $item): ?>
            <?php $kids = !empty($item['children'])
                          ? $item['children']
                          : (!empty($item['url']) ? [$item] : []); ?>
            <?php if (!$kids) { continue; } ?>
            <div class="ql-sect" data-group="<?= htmlspecialchars($item['label'], ENT_QUOTES) ?>">
              <h3><?= htmlspecialchars($item['label'], ENT_QUOTES) ?></h3>
              <?php foreach ($kids as $c): ?>
                <?php if (empty($c['url'])) { continue; } ?>
                <label class="ql-opt">
                  <input type="checkbox" value="<?= htmlspecialchars($c['url'], ENT_QUOTES) ?>"
                         data-label="<?= htmlspecialchars($c['label'], ENT_QUOTES) ?>"
                         <?= in_array($c['url'], $chosen, true) ? 'checked' : '' ?>>
                  <span class="ql-opt-label"><?= htmlspecialchars($c['label'], ENT_QUOTES) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <p class="ql-nohit" id="qlNoHit" hidden>Nothing matches that.</p>
        </div>

        <footer class="ql-dialog-foot">
          <span class="ql-msg" id="qlMsg" role="status"></span>
          <button type="button" class="ql-btn-ghost btn btn-outline-secondary btn-sm" data-ql-close>Cancel</button>
          <button type="button" class="ql-btn-primary btn btn-primary btn-sm" id="qlSave">Save</button>
        </footer>
      </div>
    </div>
    <?php
}
