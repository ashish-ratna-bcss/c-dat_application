<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * login1.php — the login form's handler.
 *
 * Answers JSON when the caller asks for it (the AJAX form in modules/common/auth.php)
 * and HTML otherwise, so the page still works with JavaScript turned off:
 * the <form> keeps its action and method, and a plain POST lands here exactly
 * as it did before.
 */
require_once CDAT_COMMON . '/activity_logger.php';

$wantsJson = ($_POST['ajax'] ?? '') === '1'
    || strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0;

/**
 * @param string $field name of the input to point at, '' for a form-level error
 */
function login_fail(bool $json, string $message, string $field = '', int $code = 200): void
{
    if ($json) {
        http_response_code($code);
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        echo json_encode(['ok' => false, 'error' => $message, 'field' => $field]);
    } else {
        http_response_code($code === 200 ? 401 : $code);
        echo "<font size=4 face=verdana color='#921215'>"
           . htmlspecialchars($message, ENT_QUOTES) . "</font></br>";
    }
    exit;
}

$USERNAME = trim($_POST['USERNAME'] ?? '');
$PASSWORD = trim($_POST['PASSWORD'] ?? '');

// Checked here as well as in the browser -- client-side validation is a
// convenience, not a control.
if ($USERNAME === '') {
    login_fail($wantsJson, 'Enter your username.', 'USERNAME');
}
if ($PASSWORD === '') {
    login_fail($wantsJson, 'Enter your password.', 'PASSWORD');
}

function login_ensure_attempts_table(PDO $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $conn->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id SERIAL PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        attempted_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
        success BOOLEAN NOT NULL DEFAULT FALSE
    )");
    $conn->exec('CREATE INDEX IF NOT EXISTS idx_login_attempts_user_time ON login_attempts (username, attempted_at DESC)');
}

function login_is_locked(PDO $conn, string $username): bool
{
    login_ensure_attempts_table($conn);
    $maxAttempts = max(3, (int) (getenv('CDAT_LOGIN_MAX_ATTEMPTS') ?: 5));
    $windowMin = max(1, (int) (getenv('CDAT_LOGIN_LOCKOUT_MINUTES') ?: 15));
    $st = $conn->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE username = ? AND success = FALSE
           AND attempted_at > NOW() - (? || ' minutes')::interval"
    );
    $st->execute([$username, (string) $windowMin]);
    return ((int) $st->fetchColumn()) >= $maxAttempts;
}

function login_record_attempt(PDO $conn, string $username, bool $success): void
{
    login_ensure_attempts_table($conn);
    $st = $conn->prepare('INSERT INTO login_attempts (username, success) VALUES (?, ?::boolean)');
    $st->execute([$username, $success ? 'true' : 'false']);
    if ($success) {
        $clear = $conn->prepare('DELETE FROM login_attempts WHERE username = ?');
        $clear->execute([$username]);
    }
}

$conn = get_cdat_pdo();

if (login_is_locked($conn, $USERNAME)) {
    login_fail($wantsJson, 'Too many failed login attempts. Try again later.', '', 429);
}

// Placeholders, not interpolation. This query used to be built by pasting
// $USERNAME and $PASSWORD straight into the string, so a username of
//     ' OR '1'='1' --
// returned the first row in LOGINS and signed the visitor in as that user.
$st1 = $conn->prepare("SELECT * FROM LOGINS WHERE USERNAME = ?");
    $st1->execute([$USERNAME]);
$row = $st1 ? $st1->fetch(PDO::FETCH_ASSOC) : false;

$stored = (string)($row['PASSWORD'] ?? $row['password'] ?? '');
$valid = false;
if ($row && $stored !== '') {
    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2')) {
        $valid = password_verify($PASSWORD, $stored);
    } else {
        $valid = hash_equals($stored, $PASSWORD);
        if ($valid) {
            $hashed = password_hash($PASSWORD, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE logins SET PASSWORD = ? WHERE USERNAME = ?");
            $stmt->execute([$hashed, $USERNAME]);
        }
    }
}

if (!$valid) {
    login_record_attempt($conn, $USERNAME, false);
    login_fail($wantsJson, 'Username or password is incorrect.');
}

$accountStatus = strtolower((string)($row['STATUS'] ?? $row['status'] ?? 'active'));
if ($accountStatus !== 'active') {
    login_fail($wantsJson, 'This account is deactivated. Contact an administrator.');
}

// New session id on privilege change, so a session cookie planted before login
// cannot be reused afterwards.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

$_SESSION['audit_role']     = strtolower($row['ROLE'] ?? $row['role'] ?? 'user');
$_SESSION['audit_fullname'] = $row['FULLNAME'] ?? $row['fullname'] ?? $USERNAME;

audit_login(
    $USERNAME,
    $_SESSION['audit_fullname'],
    (int) ($row['id'] ?? $row['ID'] ?? 0)
);
login_record_attempt($conn, $USERNAME, true);
$_SESSION['audit_last_active'] = time();

$landing = (defined('CDAT_BASE') ? rtrim((string) CDAT_BASE, '/') : '') . '/dashboard';

if ($wantsJson) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode([
        'ok'       => true,
        'redirect' => $landing,
        'name'     => $_SESSION['audit_fullname'] ?: $USERNAME,
    ]);
    exit;
}

header('Location: ' . $landing);
exit;
