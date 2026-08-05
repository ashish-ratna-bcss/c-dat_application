<?php
require_once __DIR__ . '/activity_logger.php';

$serverName = 'CPHYDERABAD1\\DAU_HYD_2023';
$connectionInfo = ['Database' => 'FORMS'];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$USERNAME = trim($_POST['USERNAME'] ?? '');
$PASSWORD = trim($_POST['PASSWORD'] ?? '');

if ($USERNAME === '' || $PASSWORD === '') {
    echo "<font size=4 face=verdana color='#921215'>USERNAME AND PASSWORD REQUIRED</font></br>";
    exit;
}

$st1 = sqlsrv_query(
    $conn,
    "SELECT * FROM LOGINS WHERE USERNAME = ? AND PASSWORD = ?",
    [$USERNAME, $PASSWORD]
);
$row = $st1 ? sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC) : false;
$count = $st1 && sqlsrv_has_rows($st1);

if ($count == 1) {
    $_SESSION['audit_role'] = strtolower($row['ROLE'] ?? 'user');
    $_SESSION['audit_fullname'] = $row['FULLNAME'] ?? $USERNAME;

    audit_login(
        $USERNAME,
        $_SESSION['audit_fullname'],
        (int)($row['ID'] ?? 0)
    );

    header('refresh:0; url=HOME_IR.HTML');
} else {
    echo "<font size=4 face=verdana color='#921215'>NO PASSWORD MATCHED</font></br>";
}
