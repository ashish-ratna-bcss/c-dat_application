<html>
<head></head>
<body bgcolor="#0C5D90">

<li><a href="CALLS_BTWN_DATES1.HTML">
<font color="#FDEFEF">Back</font></a></li>

<?php

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database"=>"CDATDUPL");

$conn = sqlsrv_connect($serverName, $connectionInfo);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

/* INPUT */
$number = $_POST['PHONE_NO'];
$state  = $_POST['STATE'];
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];

/* QUERY WITHOUT OPERATOR */
$sql = "
SELECT 
    A.PHONE,
    A.OTHER,
    CONVERT(VARCHAR, A.STARTTIME, 20) AS STARTTIME,
    A.DURATION,

    CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,

    A.IMEINUMBER,
    A.CELLTOWERID,

    ISNULL(
        (SELECT TOP 1 SITEADDRESS 
         FROM CDATDUPL.DBO.CDATCELLTOWERAREANEW B
         WHERE LTRIM(RTRIM(A.CELLTOWERID)) = LTRIM(RTRIM(B.CELLTOWERID))
         AND B.STATE = ?
        ),
    'NO LOCATION') AS AREADESCRIPTION

FROM CDATDUPL.DBO.CDATPCSUSPECT A

WHERE 
    A.PHONE = ?
    AND CONVERT(DATE, A.STARTTIME) BETWEEN ? AND ?

ORDER BY A.STARTTIME
";

$params = array($state, $number, $f_date, $t_date);

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt === false){
    die(print_r(sqlsrv_errors(), true));
}

/* HEADER */
echo "<center>
<font size=4 color='#F9FBFC'>
<b>CALL DETAILS OF MOBILE NO: $number FROM: $f_date TO: $t_date</b>
</font></center><br>";

/* TABLE */
echo "<table border=1 cellpadding=5 align=center>
<tr bgcolor=#921215>
<th><font color=#fff>PHONE</font></th>
<th><font color=#fff>OTHER</font></th>
<th><font color=#fff>STARTTIME</font></th>
<th><font color=#fff>DURATION</font></th>
<th><font color=#fff>TYPE</font></th>
<th><font color=#fff>IMEI</font></th>
<th><font color=#fff>CELLID</font></th>
<th><font color=#fff>AREA</font></th>
</tr>";

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){

echo "<tr>";
echo "<td>".$row['PHONE']."</td>";
echo "<td>".$row['OTHER']."</td>";
echo "<td>".$row['STARTTIME']."</td>";
echo "<td>".$row['DURATION']."</td>";
echo "<td>".$row['TYPE']."</td>";
echo "<td>".$row['IMEINUMBER']."</td>";
echo "<td>".$row['CELLTOWERID']."</td>";
echo "<td>".$row['AREADESCRIPTION']."</td>";
echo "</tr>";
}

echo "</table>";

?>
</body>
</html>