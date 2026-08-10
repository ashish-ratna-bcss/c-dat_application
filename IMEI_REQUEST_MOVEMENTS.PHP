<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="IMEI_REQUEST_MOVEMENTS.HTM">Back</a></li>
		</br>
<li><a href="home_imei.PHP"><font color=#FDEFEF>HOME</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['PHONE_NO'];


$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A WITH (NOLOCK) LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,CONVERT(DATE,STARTTIME),20) DATE,CONVERT(VARCHAR,CONVERT(TIME,STARTTIME),20) TIME,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WITH (NOLOCK) WHERE PHONE='$number' ";



$sql2 = cdr_sql_enrich_tt('', '', [
    'with_lat_long' => true,
    'with_state_col' => true,
    'with_date_time_cols' => true,
]);

$sql5="SELECT PHONE,OTHER,NICKNAME,DATE,TIME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,STATE,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs  ORDER BY STARTTIME";

$sql6="select 'CALL DETAILS OF MOBILE NO. '+'$number'as PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>IMEI</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CELLID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TYPE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OPERATOR'] ."</font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATE'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AREADESCRIPTION'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>

<script src="DROP DOWN FILTER\jquery.min.js"></script>
    <script src="DROP DOWN FILTER\ddtf.js"></script>
    <script>
        $('#mytable').ddTableFilter();
    </script> 
</body>
</html>