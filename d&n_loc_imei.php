<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="day%26nightloc_imei.html"><font color=#FDEFEF>Back</a></li>
	</br>
<li><a href="home_imei.php"><font color=#FDEFEF>HOME</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];


$sql1 ="SELECT * INTO #TEMP FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WHERE 
(CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00') 
AND PHONE='$number'";

$sql2 = cdr_sql_enrich_location_temp('#TEMP', '#TT1');

$sql4="SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T FROM #TT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";


$sql5="SELECT TOP 10 * FROM #T ORDER BY CALLS DESC";

$sql6="SELECT 'DAY LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sql7="SELECT 'NIGHT LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sql8 ="SELECT * INTO #T1 FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WHERE 
(CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00') 
AND PHONE='$number'";

$sql9 = cdr_sql_enrich_location_temp('#T1', '#T3');

$sql11="SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T4 FROM #T3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sql12="SELECT TOP 10 * FROM #T4 ORDER BY CALLS DESC";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st11 = sqlsrv_query( $conn, $sql11 );
$st12 = sqlsrv_query( $conn, $sql12 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></td>";
echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

} 
echo"</table></br>";
while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></td>";
echo "<td width=15px><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

} 
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
</body>
</html>