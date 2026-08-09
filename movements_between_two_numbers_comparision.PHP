<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.html">Back</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['PHONE_NO'];
$number1=$_POST['OTHER_NO'];

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number','$number1')  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number','$number1')";

$sql2 = cdr_sql_enrich_tt('', '', [
    'with_last_update' => true,
    'with_lat_long' => true,
    'output_table' => '#ttppp',
]);

$sql5="select distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''+A.CELLTOWERID PHONE_CELLTOWERID,
A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM,
A.OTHER,''''+B.CELLTOWERID OTHER_CELLTOWERID,
B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM
into #ttpppp from #ttppp A INNER JOIN
#TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND CONVERT(DATE,A.STARTTIME)=CONVERT(DATE,B.STARTTIME) 
and datepart(hh,convert(datetime,A.STARTTIME))=datepart(hh,convert(datetime,b.STARTTIME)) and 
datepart(mm,convert(datetime,A.STARTTIME))=datepart(mm,convert(datetime,b.STARTTIME)) 
AND datediff(ss,convert(datetime,A.STARTTIME),convert(datetime,b.STARTTIME))<'4'
WHERE A.PHONE='$number'
ORDER BY A.STARTTIME";

$sql7="select distinct *,case when 
phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%'
 then CAST(import.DBO.CALCULATEDISTANCE(left(phone_long,8),left(phone_lat,8),left(other_LONG,8),left(other_LAT,8)) AS INT) else '' end 
DIST FROM #ttpppp
ORDER BY STARTTIME";

$sql6="select 'MOVEMENTS COMPARISION  OF MOBILE NO. '+'$number' + 'AND OTHER NO. '+'$number1' as PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DURATION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE AREADESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER AREADESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE CELLTOWERID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE AZM</font></th>
<th><font size=2 face=verdana color='#F9FBFC'>OTHER CELLTOWERID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER AZM</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DIST BETWEEN NUMBERS IN KM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['OTHER'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE_AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_CELLTOWERID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE_LAT'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_LONG'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_AZM'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER_CELLTOWERID'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_AZM'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DIST'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
</body>
</html>