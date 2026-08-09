<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="rta_nike.html">Back</a></li>
<?php
require_once __DIR__ . '/sql_safe.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$number = strtoupper(trim((string)($_POST['REGN_NO'] ?? '')));
$number = preg_replace('/[^A-Z0-9]/', '', $number);
if ($number === '') {
    die('<font color=#F9FBFC>Invalid vehicle number.</font>');
}
$numberSql = str_replace("'", "''", $number);

// Exact match first (fast on distributed RTA). Prefix fallback for partial plates.
$sql1 = "SELECT TOP 20 REGN_NO, FULLNAME, FATHERNAME, FULLADDRESS, PHONE, CITY, MKR_CLAS, COLOUR, VEH_CLASS, ENG_NO, CHAS_NO
FROM CDATDUPL..CDAT_RTA
WHERE REGN_NO = '{$numberSql}'";
$st1 = sqlsrv_query($conn, $sql1);
$rows = [];
if ($st1 !== false) {
    while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}
if ($rows === []) {
    $sql1 = "SELECT TOP 20 REGN_NO, FULLNAME, FATHERNAME, FULLADDRESS, PHONE, CITY, MKR_CLAS, COLOUR, VEH_CLASS, ENG_NO, CHAS_NO
FROM CDATDUPL..CDAT_RTA
WHERE REGN_NO LIKE '{$numberSql}%'";
    $st1 = sqlsrv_query($conn, $sql1);
    if ($st1 !== false) {
        while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
    }
}

echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>RTA DETAIL OF VEHICLE. "
    . htmlspecialchars($number, ENT_QUOTES)
    . "<center></td></font></br>";

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th width=250px ><font size=3 face=verdana color='#F9FBFC'>VEHICLE DETAILS</font></th>
</tr>
</table>";

if ($rows === []) {
    echo "<font color='#F9FBFC'>No RTA record found for "
        . htmlspecialchars($number, ENT_QUOTES)
        . ".</font>";
    exit;
}

foreach ($rows as $row) {
    $fields = [
        'VEHICLE NO' => $row['REGN_NO'] ?? '',
        'OWNER NAME' => $row['FULLNAME'] ?? '',
        'FATHER NAME' => $row['FATHERNAME'] ?? '',
        'ADDRESS' => $row['FULLADDRESS'] ?? '',
        'CITY' => $row['CITY'] ?? '',
        'PHONE' => $row['PHONE'] ?? '',
        'VEHICLE TYPE' => trim(
            ($row['MKR_CLAS'] ?? '')
            . (($row['COLOUR'] ?? '') !== '' ? ', COLOR: ' . $row['COLOUR'] : '')
            . (($row['VEH_CLASS'] ?? '') !== '' ? ', ' . $row['VEH_CLASS'] : ''),
            ', '
        ),
        'ENGINE NO' => $row['ENG_NO'] ?? '',
        'CHASSIS NO' => $row['CHAS_NO'] ?? '',
    ];

    echo "<table border=1 cellspacing=0 cellpadding=5 style='margin-bottom:18px;'>";
    $i = 0;
    foreach ($fields as $label => $value) {
        $bg = ($i % 2 === 0) ? '#AED1F1' : '#C2E0FB';
        echo "<tr>";
        echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>"
            . htmlspecialchars($label, ENT_QUOTES)
            . "</font></th>";
        echo "<td width=700px bgcolor={$bg}><font size=2 face=verdana>"
            . htmlspecialchars((string)$value, ENT_QUOTES)
            . "</font></td>";
        echo "</tr>";
        $i++;
    }
    echo "</table>";
}
?>
</body>
</html>
