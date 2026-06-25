<?php
/**
 * LOGIN1.PHP  — Updated with Session + Audit Tracking
 */
require_once __DIR__ . '/activity_logger.php';

$serverName     = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = ["Database" => "FORMS"];
$conn           = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$USERNAME = trim($_POST['USERNAME'] ?? '');
$PASSWORD = trim($_POST['PASSWORD'] ?? '');

$sql  = "SELECT * FROM LOGINS WHERE USERNAME='$USERNAME' AND PASSWORD='$PASSWORD'";
$st1  = sqlsrv_query($conn, $sql);
$row  = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC);
$count = sqlsrv_has_rows($st1);

if ($count == 1) {
    $_SESSION['audit_role']     = strtolower($row['ROLE'] ?? 'user');
    $_SESSION['audit_fullname'] = $row['FULLNAME'] ?? $USERNAME;

    audit_login(
        $USERNAME,
        $_SESSION['audit_fullname'],
        (int)($row['ID'] ?? 0)
    );

    header("refresh:0; url=HOME_IR.HTML");
} else {
    echo "<font size=4 face=verdana color='#921215'>NO PASSWORD MATCHED</font></br>";
}
?>