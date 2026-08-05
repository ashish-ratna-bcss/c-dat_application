<html>
<head></head>
<body bgcolor="#0C5D90">

<li><a href="CALLS_BTWN_DATES1.html">
<font color="#FDEFEF">Back</font></a></li>

<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database"=>"CDATDUPL");

$conn = sqlsrv_connect($serverName, $connectionInfo);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

$number = $_POST['PHONE_NO'] ?? '';
$state  = $_POST['STATE'] ?? '';
$f_date = $_POST['FROM_DT'] ?? '';
$t_date = $_POST['TO_DT'] ?? '';

$sql = cdr_sql_calls_between_dates($number, $f_date, $t_date, $state);
$stmt = sqlsrv_query($conn, $sql);

if($stmt === false){
    die(print_r(sqlsrv_errors(), true));
}

echo "<center>
<font size=4 color='#F9FBFC'>
<b>CALL DETAILS OF MOBILE NO: $number FROM: $f_date TO: $t_date</b>
</font></center><br>";

echo "<table border=1 cellpadding=5 align=center>
<tr bgcolor=#921215>
<th><font color=#fff>PHONE</font></th>
<th><font color=#fff>OTHER</font></th>
<th><font color=#fff>STARTTIME</font></th>
<th><font color=#fff>DURATION</font></th>
<th><font color=#fff>TYPE</font></th>
<th><font color=#fff>IMEI</font></th>
<th><font color=#fff>CELLID</font></th>
<th><font color=#fff>OPERATOR</font></th>
<th><font color=#fff>AREA</font></th>
</tr>";

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
echo "<tr>";
echo "<td>".htmlspecialchars($row['PHONE'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['OTHER'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['STARTTIME'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['DURATION'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['TYPE'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['IMEINUMBER'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['CELLTOWERID'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['OPERATOR'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['AREADESCRIPTION'] ?? '')."</td>";
echo "</tr>";
}

echo "</table>";

?>
</body>
</html>
