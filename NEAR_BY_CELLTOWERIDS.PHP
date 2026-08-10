<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="NEAR_BY_CELLTOWERIDS.PHP">Back</a></li>
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
audit_require_session();
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$LAT = sql_safe_float($_POST['LAT'] ?? '0');
$LONG = sql_safe_float($_POST['LONG'] ?? '0');
$RANGE = sql_safe_float($_POST['RANGE'] ?? '10000');



$sql1="SELECT 'NEAR BY CELLID SEARCH: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";


$sql2 ="declare @lat decimal(14,10),@long decimal (14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='$RANGE'
SELECT CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,SITEADDRESS,OPERATOR,LAT,LONG,AZIMUTH,OTYPE,STATE,CONVERT(VARCHAR,LASTUPDATE,20) LASTUPDATE
FROM dbo.CELLTOWERfiltered WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY OPERATOR,DIST,OTYPE";


$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'Title query');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'Nearby towers');

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". h($row['PHONE1'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DIST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SITEADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZIMUTH</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTYPE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LASTUPDATE</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['CELLTOWERID'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['DIST'] ?? '') ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['BR'] ?? '') ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['AREADESCRIPTION'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['SITEADDRESS'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OPERATOR'] ?? '') ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['LAT'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['LONG'] ?? '') ."<center></td>";
echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['AZIMUTH'] ?? '') ."<center></font></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OTYPE'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['STATE'] ?? '') ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['LASTUPDATE'] ?? '') ."</td>";
echo "</tr>";

} 
echo"</table></br>";

sqlsrv_free_stmt( $st2);
?>
</body>
</html>