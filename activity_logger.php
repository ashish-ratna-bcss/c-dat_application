<?php
/**
 * activity_logger.php
 * Central helper for User Activity Logging and Audit Trail.
 * Include this file in any PHP page to enable logging.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────
// Database connection (singleton)
// ─────────────────────────────────────────────
function audit_db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = require __DIR__ . '/db_config.php';
        $pdo = new PDO(
            'pgsql:host=' . $c['host'] . ';port=' . $c['port'] . ';dbname=' . $c['database'],
            $c['user'],
            $c['password'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

// ─────────────────────────────────────────────
// Session Management
// ─────────────────────────────────────────────

/**
 * Called on successful login.
 * Creates a new session record and stores session ID in PHP session.
 */
function audit_login(string $username, string $fullname = '', int $user_id = 0): int
{
    try {
        $token  = bin2hex(random_bytes(32));
        $ip     = $_SERVER['REMOTE_ADDR']  ?? 'unknown';
        $ua     = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $device = _detect_device($ua);

        $stmt = audit_db()->prepare("
            INSERT INTO user_sessions
                (user_id, username, fullname, login_time, ip_address, browser_info, device_info, session_token)
            VALUES
                (:uid, :uname, :fname, NOW(), :ip, :browser, :device, :token)
            RETURNING id
        ");
        $stmt->execute([
            ':uid'     => $user_id,
            ':uname'   => $username,
            ':fname'   => $fullname,
            ':ip'      => $ip,
            ':browser' => substr($ua, 0, 500),
            ':device'  => $device,
            ':token'   => $token,
        ]);

        $row = $stmt->fetch();
        $session_id = (int) $row['id'];

        // Store in PHP session
        $_SESSION['audit_session_id'] = $session_id;
        $_SESSION['audit_username']   = $username;
        $_SESSION['audit_fullname']   = $fullname;
        $_SESSION['audit_user_id']    = $user_id;

        // Log the LOGIN action itself
        audit_log('System', 'LOGIN', ['ip' => $ip]);

        return $session_id;
    } catch (Throwable $e) {
        error_log('audit_login error: ' . $e->getMessage());
        return 0;
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

        $sid = (int) $_SESSION['audit_session_id'];
        audit_db()->prepare("
            UPDATE user_sessions
            SET
                logout_time      = NOW(),
                session_duration = EXTRACT(EPOCH FROM (NOW() - login_time))::INTEGER
            WHERE id = :id
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

        if (empty($username) || $username === 'unknown') return;

        $json = !empty($search_data) ? json_encode($search_data, JSON_UNESCAPED_UNICODE) : null;

        audit_db()->prepare("
            INSERT INTO user_activity_logs
                (session_id, user_id, username, module_name, action_type, search_data)
            VALUES
                (:sid, :uid, :uname, :module, :action, :data::jsonb)
        ")->execute([
            ':sid'    => $sid,
            ':uid'    => $uid,
            ':uname'  => $username,
            ':module' => $module,
            ':action' => $action_type,
            ':data'   => $json,
        ]);
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
        // Was LOGIN.HTML, which resolves to login.php -- the bare POST handler with
        // no form, so users landed on "USERNAME AND PASSWORD REQUIRED" with nothing
        // to fill in. auth.html is the actual login form.
        header('Location: auth.html');
        exit;
    }
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
        die('<h2 style="color:red;font-family:verdana">ACCESS DENIED: Admin only.</h2>');
    }
}

function audit_require_uploader(): void
{
    audit_require_session();
    $role = $_SESSION['audit_role'] ?? '';
    if ($role !== 'admin' && $role !== 'poweruser') {
        http_response_code(403);
        die('<h2 style="color:red;font-family:verdana">ACCESS DENIED: Insufficient permissions.</h2>');
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
