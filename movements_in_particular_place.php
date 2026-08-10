<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="MOVEMENTS_IN_PARTICULAR_PLACE.HTM">Back</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE'];
$LAT = $_POST['LAT'];
$LONG = $_POST['LONG'];
$RANGE = $_POST['RANGE'];



$sql1="SELECT 'MOVEMENTS IN: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql2 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' ";

$sql3 = cdr_sql_enrich_tt('', '', ['with_last_update' => true, 'with_lat_long' => true, 'output_table' => '#TTP']);

$sql4 ="declare @lat decimal(14,10),@long decimal(14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='$RANGE'
SELECT PHONE,OTHER,STARTTIME,DURATION,TYPE,CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,OPERATOR,LAT,LONG,AZM
FROM #TTP WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY STARTTIME";


$st1 = sqlsrv_query( $conn, $sql1);
$st2 = sqlsrv_query( $conn, $sql10);
$st3 = sqlsrv_query( $conn, $sql2);
$st4 = sqlsrv_query( $conn, $sql3);
$st5 = sqlsrv_query( $conn, $sql4);

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DURATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TYPE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DIST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZIMUTH</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OTHER'] ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STARTTIME'] ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['TYPE'] ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DIST'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['BR'] ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OPERATOR'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></td>";
echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

} 
echo"</table></br>";

sqlsrv_free_stmt( $st2);
?>
</body>
</html>