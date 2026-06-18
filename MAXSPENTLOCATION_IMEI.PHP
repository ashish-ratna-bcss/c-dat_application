<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="MAXSPEENTLOCATION_IMEI.HTML"><font color=#FDEFEF>Back</a></li>
	</br>
<li><a href="HOME_IMEI.HTML"><font color=#FDEFEF>HOME</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];


$sql1 ="SELECT * INTO #TEMP FROM LOSTREPORT_HAWKEYE..LOST_REPORT_CDR_DATA WHERE 
PHONE='$number'";

$sql2 ="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,A.CELLTOWERID,CASE WHEN A.CELLTOWERID in(
SELECT DISTINCT CELLTOWERID FROM CDATDUPL.DBO.CDATCELLTOWERAREANEW b WHERE a.state_key=b.state_key AND a.provider_key=b.provider_key) 
THEN SITEADDRESS ELSE '' END AS AREADESCRIPTION,LAT,LONG,AZIMUTH  INTO #TT FROM #TEMP A
LEFT JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID and a.state_key=b.state_key and a.provider_key=b.provider_key";

$sql3 ="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,''SITEADDRESS,''LAT,''LONG,''AZM INTO #TT1 FROM #TEMP WHERE STARTTIME NOT IN(
SELECT DISTINCT STARTTIME FROM #TT) 
UNION
SELECT * FROM #TT ORDER BY STARTTIME";

$sql4="SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T FROM #TT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";


$sql5="SELECT * FROM #T ORDER BY CALLS DESC";

$sql6="SELECT 'MAXSPENT LOCATION OF MOBILE NO: '+'$number' as PHONE1";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );


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

sqlsrv_free_stmt( $st5);
?>
</body>
</html>