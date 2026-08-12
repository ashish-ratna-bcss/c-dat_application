<?php
/**
 * login1.php — the login form's handler.
 *
 * Answers JSON when the caller asks for it (the AJAX form in view/auth.html)
 * and HTML otherwise, so the page still works with JavaScript turned off:
 * the <form> keeps its action and method, and a plain POST lands here exactly
 * as it did before.
 */
require_once __DIR__ . '/activity_logger.php';

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

$serverName     = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = ["Database" => "FORMS"];
$conn           = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    error_log('login1: ' . print_r(sqlsrv_errors(), true));
    // The old code printed the driver's error array to the page, which tells an
    // unauthenticated visitor the server name, database and driver version.
    login_fail($wantsJson, 'The login service is unavailable. Try again shortly.', '', 503);
}

// Placeholders, not interpolation. This query used to be built by pasting
// $USERNAME and $PASSWORD straight into the string, so a username of
//     ' OR '1'='1' --
// returned the first row in LOGINS and signed the visitor in as that user.
$st1 = sqlsrv_query(
    $conn,
    "SELECT * FROM LOGINS WHERE USERNAME = ? AND PASSWORD = ?",
    [$USERNAME, $PASSWORD]
);
$row = $st1 ? sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC) : false;

if (!$row) {
    // Deliberately does not say which of the two was wrong.
    login_fail($wantsJson, 'Username or password is incorrect.');
}

// New session id on privilege change, so a session cookie planted before login
// cannot be reused afterwards.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
}

$_SESSION['audit_role']     = strtolower($row['ROLE'] ?? 'user');
$_SESSION['audit_fullname'] = $row['FULLNAME'] ?? $USERNAME;

audit_login(
    $USERNAME,
    $_SESSION['audit_fullname'],
    (int)($row['ID'] ?? 0)
);

// Root-relative, so it is correct whoever posted the form -- view/auth.html
// sits one directory over from this handler.
$landing = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/')
         . '/home.php';

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
