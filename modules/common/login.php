<?php
require_once __DIR__ . '/bootstrap.php';
/**
 * login1.php — the login form's handler.
 *
 * Answers JSON when the caller asks for it (the AJAX form in view/auth.html)
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
$conn           = get_cdat_pdo();

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
