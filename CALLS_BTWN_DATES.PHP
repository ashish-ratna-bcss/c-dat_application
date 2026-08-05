<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="CALLS_BTWN_DATES.html"><font color=#FDEFEF>Back</a></li>
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
audit_require_session();
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
$operator= sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
$state	= sql_safe_alnum($_POST['STATE'] ?? '', 50);
$f_date = sql_safe_alnum($_POST['FROM_DT'] ?? '', 10);
$t_date = sql_safe_alnum($_POST['TO_DT'] ?? '', 10);
audit_log('Call Details Between Dates', 'Search', ['phone_number' => $number, 'from_date' => $f_date, 'state' => $state, 'operator' => $operator]);

$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND convert(char(10),STARTTIME,121) BETWEEN '$f_date' AND '$t_date'";

$sql2 = cdr_sql_enrich_tt($operator, $state);

$sql5="SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION from #temp_cdrs  ORDER BY STARTTIME";



$sql6="SELECT 'CALL DETAILS OF MOBILE NO: '+'$number'+' FROM: '+'$f_date'+' TO: '+'$t_date' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'Calls between dates base');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'Tower enrichment');
$st5 = sqlsrv_query( $conn, $sql5 );
sqlsrv_render_query_error($st5, 'Result ordering');
$st6 = sqlsrv_query( $conn, $sql6 );
sqlsrv_render_query_error($st6, 'Title');

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". h($row['PHONE'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NICK NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>IMEI</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CELLID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['PHONE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OTHER'] ?? '') ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['NICKNAME'] ?? '') ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['STARTTIME'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['DURATION'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['TYPE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['IMEINUMBER'] ?? '') ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['CELLTOWERID'] ?? '') ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". h($row['OPERATOR'] ?? '') ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['AREADESCRIPTION'] ?? '') ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
</body>
</html>