<?php
/**
 * LOGIN1.PHP  — Updated with Session + Audit Tracking
 *
 * Answers either shape of request:
 *   fetch() from auth.html -> JSON, {"ok":true,"redirect":...} or {"ok":false,"error":...}
 *   a plain form post      -> the old HTML message / refresh redirect
 *
 * auth.html still carries action="login1.php", so login keeps working with
 * JavaScript off. The AJAX path only changes how the answer is delivered: a
 * wrong password now comes back to the form instead of replacing the page with
 * a bare "NO PASSWORD MATCHED" that the user has to press Back out of.
 */
require_once __DIR__ . '/activity_logger.php';

/** Did the caller ask for JSON, or is this the plain form post? */
function login_wants_json(): bool
{
    return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

/**
 * Answer in whichever shape was asked for, and stop.
 *
 * The status stays 200 on a bad password: it is a valid answer to a valid
 * request, and 401 would make the browser raise its own credentials dialog
 * over the top of the form.
 */
function login_respond(bool $ok, string $message = '', string $redirect = ''): void
{
    if (login_wants_json()) {
        header('Content-Type: application/json');
        echo json_encode($ok
            ? ['ok' => true,  'redirect' => $redirect]
            : ['ok' => false, 'error'    => $message]);
    } elseif ($ok) {
        header('refresh:0; url=' . $redirect);
    } else {
        echo "<font size=4 face=verdana color='#921215'>"
           . htmlspecialchars($message, ENT_QUOTES) . "</font></br>";
    }
    exit;
}

$USERNAME = trim($_POST['USERNAME'] ?? '');
$PASSWORD = trim($_POST['PASSWORD'] ?? '');

if ($USERNAME === '' || $PASSWORD === '') {
    login_respond(false, 'USERNAME AND PASSWORD REQUIRED');
}

$serverName     = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = ["Database" => "FORMS"];
$conn           = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    // Was print_r(sqlsrv_errors()) straight to the page: on the AJAX path that
    // is unparseable, and either way it hands the host and driver details to
    // whoever is at the login screen. The detail belongs in the log.
    error_log('login1: connect failed: ' . print_r(sqlsrv_errors(), true));
    login_respond(false, 'DATABASE UNAVAILABLE. PLEASE TRY AGAIN.');
}

// Bound parameters, not interpolation. USERNAME went into the WHERE clause raw,
// so a quote in the box ended the string early and "' OR '1'='1" signed in as
// whichever row LOGINS returned first.
$st1 = sqlsrv_query($conn, "SELECT * FROM LOGINS WHERE USERNAME=? AND PASSWORD=?",
                    [$USERNAME, $PASSWORD]);

if ($st1 === false) {
    error_log('login1: query failed: ' . print_r(sqlsrv_errors(), true));
    login_respond(false, 'DATABASE UNAVAILABLE. PLEASE TRY AGAIN.');
}

$row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC);

if (!$row) {
    login_respond(false, 'NO PASSWORD MATCHED');
}

$_SESSION['audit_role']     = strtolower($row['ROLE'] ?? 'user');
$_SESSION['audit_fullname'] = $row['FULLNAME'] ?? $USERNAME;

audit_login(
    $USERNAME,
    $_SESSION['audit_fullname'],
    (int)($row['ID'] ?? 0)
);

// Point at the file that actually exists. HOME_IR.PHP was a byte-identical
// duplicate and was removed; it only still resolves through the extension
// fallback in .htaccess, so any environment where mod_rewrite is off or
// AllowOverride is None gets a 404 straight after a successful login.
login_respond(true, '', 'HOME_IR.PHP');
