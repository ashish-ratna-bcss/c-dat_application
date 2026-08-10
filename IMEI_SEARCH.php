<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="imeisearch.PHP"><font color=#FDEFEF>Back</a></li>
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
audit_require_session();
audit_log('IMEI Search', 'Search', ['imei_number' => $_POST['IMEI_NO'] ?? '']);
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = sql_safe_imei($_POST['IMEI_NO'] ?? '');

$sql1 ="SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE IMEINUMBER = '$number'";

$sql2 ="SELECT DISTINCT PHONE, IMEINUMBER,SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,
SUM(DURATION) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL INTO #TT FROM #T
GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";

$sql3 ="SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, 
CASE WHEN C.PHONE IS NOT NULL
THEN COALESCE(C.FULLNAME+', '+C.FULLADDRESS,'')||' '||COALESCE(C.CATEGORY_TYPE,'')
WHEN D.PHONE IS NOT NULL
THEN COALESCE(D.FULLNAME+', '+D.FULLADDRESS,'')||' '||COALESCE(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX+'%'
ORDER BY LAST_CALL";

$sql4="SELECT 'LIST OF PHONE NOs USED IN IMEI: '+'$number' as PHONE1";


$sql5="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO PHONES ARE AVAILABLE IN IMEI $number ***' end as PHONE FROM #tt";


$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'IMEI lookup');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'IMEI aggregation');
$st3 = sqlsrv_query( $conn, $sql3 );
sqlsrv_render_query_error($st3, 'IMEI address join');
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );

while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>". h($row['PHONE1'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMEINUMBER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OUT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['PHONE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['IMEINUMBER'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['IN'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['OUT'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['CALLS'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['DUR'] ?? '') ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['FIRST_CALL'] ?? '') ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['LAST_CALL'] ?? '') ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". h($row['ADDRESS'] ?? '') ."</font></td>";
echo "</tr>";

}
echo"</table></br></br>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

sqlsrv_free_stmt( $st3);
?>
</body>
</html>