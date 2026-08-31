<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * activity_logger.php
 * Central helper for User Activity Logging and Audit Trail.
 * Include this file in any PHP page to enable logging.
 */

if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ─────────────────────────────────────────────
// Database connection (singleton)
// ─────────────────────────────────────────────
function audit_db(): PDO
{
    return get_cdat_pdo();
}

// ─────────────────────────────────────────────
// Session Management
// ─────────────────────────────────────────────

/**
 * Called on successful login.
 * Creates a new session record and stores the session token in PHP session.
 */
function audit_login(string $username, string $fullname = '', int $user_id = 0): string
{
    try {
        $token = bin2hex(random_bytes(32));
        $ip    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $role  = (string) ($_SESSION['audit_role'] ?? 'user');

        $stmt = audit_db()->prepare("
            INSERT INTO user_sessions
                (session_id, user_id, username, role, ip_address, expires_at, last_active_at)
            VALUES
                (:sid, :uid, :uname, :role, :ip, NOW() + INTERVAL '12 hours', NOW())
        ");
        $ok = $stmt->execute([
            ':sid'     => $token,
            ':uid'     => $user_id,
            ':uname'   => $username,
            ':role'    => $role,
            ':ip'      => $ip,
        ]);
        if (!$ok) {
            return '';
        }

        // Store in PHP session
        $_SESSION['audit_session_id'] = $token;
        $_SESSION['audit_username']   = $username;
        $_SESSION['audit_fullname']   = $fullname;
        $_SESSION['audit_user_id']    = $user_id;

        // Log the LOGIN action itself
        audit_log('System', 'LOGIN', ['ip' => $ip]);

        return $token;
    } catch (Throwable $e) {
        error_log('audit_login error: ' . $e->getMessage());
        return '';
    }
}

/**
 * Called on logout.
 * Updates the session record with logout time and duration.
 */
function audit_logout(): void
{
    try {
        if (empty($_SESSION['audit_session_id'])) return;

        audit_log('System', 'LOGOUT', []);

        $sid = (string) $_SESSION['audit_session_id'];
        audit_db()->prepare("
            UPDATE user_sessions
            SET
                last_active_at = NOW(),
                expires_at = NOW()
            WHERE session_id = :id
        ")->execute([':id' => $sid]);

    } catch (Throwable $e) {
        error_log('audit_logout error: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// Activity Logging
// ─────────────────────────────────────────────

/**
 * Log any user action.
 *
 * @param string $module      e.g. 'Phone History', 'IMEI Search'
 * @param string $action_type e.g. 'Search', 'Page View', 'Excel Export'
 * @param array  $search_data Key-value pairs of search parameters (NO results)
 */
function audit_log(string $module, string $action_type, array $search_data = []): void
{
    try {
        $sid      = $_SESSION['audit_session_id'] ?? null;
        $uid      = $_SESSION['audit_user_id']    ?? 0;
        $username = $_SESSION['audit_username']   ?? 'unknown';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (empty($username) || $username === 'unknown') return;

        $json = !empty($search_data) ? json_encode($search_data, JSON_UNESCAPED_UNICODE) : null;

        audit_db()->prepare("
            INSERT INTO user_activity_logs
                (username, module, action, detail, ip_address)
            VALUES
                (:uname, :module, :action, :data, :ip)
        ")->execute([
            ':uname'  => $username,
            ':module' => $module,
            ':action' => $action_type,
            ':data'   => $json,
            ':ip'     => $ip,
        ]);

        if (!empty($sid)) {
            audit_db()->prepare("
                UPDATE user_sessions
                SET last_active_at = NOW()
                WHERE session_id = :sid
            ")->execute([':sid' => (string) $sid]);
        }
    } catch (Throwable $e) {
        error_log('audit_log error: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// Auth / Role Helpers
// ─────────────────────────────────────────────

function audit_require_session(): void
{
    if (empty($_SESSION['audit_username'])) {
        // Was LOGIN.HTML, which resolved to the bare POST handler with no form, so
        // users landed on "USERNAME AND PASSWORD REQUIRED" with nothing to fill in.
        // modules/common/auth.php is the login form.
        header('Location: ' . (defined('CDAT_BASE') ? rtrim((string) CDAT_BASE, '/') : '') . '/login');
        exit;
    }
    $idleLimit = (int) (getenv('CDAT_SESSION_IDLE_MINUTES') ?: 30);
    $now = time();
    $last = (int) ($_SESSION['audit_last_active'] ?? $now);
    if ($idleLimit > 0 && ($now - $last) > ($idleLimit * 60)) {
        audit_logout();
        header('Location: ' . (defined('CDAT_BASE') ? rtrim((string) CDAT_BASE, '/') : '') . '/login?expired=1');
        exit;
    }
    $_SESSION['audit_last_active'] = $now;
}

function audit_is_admin(): bool
{
    return ($_SESSION['audit_role'] ?? '') === 'admin';
}

function audit_require_admin(): void
{
    audit_require_session();
    if (!audit_is_admin()) {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/vendor/bootstrap/css/bootstrap.min.css">'
           . '</head><body class="bg-light"><main class="container py-5"><div class="alert alert-danger" role="alert">'
           . '<h2 class="h5 mb-2">Access denied</h2><p class="mb-0">Admin only.</p></div></main></body></html>');
    }
}

function audit_require_uploader(): void
{
    audit_require_session();
    $role = $_SESSION['audit_role'] ?? '';
    if ($role !== 'admin' && $role !== 'poweruser') {
        http_response_code(403);
        die('<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
           . '<link rel="stylesheet" href="' . htmlspecialchars(CDAT_ASSETS, ENT_QUOTES) . '/vendor/bootstrap/css/bootstrap.min.css">'
           . '</head><body class="bg-light"><main class="container py-5"><div class="alert alert-danger" role="alert">'
           . '<h2 class="h5 mb-2">Access denied</h2><p class="mb-0">Insufficient permissions.</p></div></main></body></html>');
    }
}

// ─────────────────────────────────────────────
// Internal helpers
// ─────────────────────────────────────────────

function _detect_device(string $ua): string
{
    if (preg_match('/mobile/i', $ua))  return 'Mobile';
    if (preg_match('/tablet/i', $ua))  return 'Tablet';
    return 'Desktop';
}
