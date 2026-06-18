<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS.HTML">Back</a></li>
<?php
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
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number'  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND OTHER='$number1'";

$sql2 ="SELECT DISTINCT A.PHONE,OTHER,CASE WHEN other in (select phone from CDATDUPL.DBO.cdatsuspect) THEN nickname  ELSE ' ' END   as  NICKNAME,
STARTTIME,DURATION,TYPE,A.IMEINUMBER,A.CELLTOWERID,OPERATOR,
(CASE WHEN A.CELLTOWERID=B.CELLTOWERID THEN MAX(SITEADDRESS) ELSE '' END +', LAST_UPDATE:'+CONVERT(VARCHAR,LASTUPDATE,20)) AS AREADESCRIPTION,LAT,LONG,AZIMUTH AS AZM INTO #TTP FROM #TT A
INNER JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID AND A.STATE_KEY=B.STATE_KEY AND A.PROVIDER_KEY=B.PROVIDER_KEY
left join CDATDUPL.DBO.cdatsuspect c on a.other=c.phone   
WHERE b.lastupdate=(select distinct max(lastupdate) from cdatdupl..cdatcelltowerareanew x where x.CELLTOWERID=B.CELLTOWERID 
and x.PROVIDER_KEY=b.PROVIDER_KEY AND X.STATE_KEY=b.state_KEY)
GROUP BY A.PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,A.IMEINUMBER,A.CELLTOWERID,B.CELLTOWERID,LASTUPDATE,OPERATOR,A.STATE_KEY,B.STATE_KEY,
A.PROVIDER_KEY,B.PROVIDER_KEY,LAT,LONG,AZIMUTH";

$sql3 ="SELECT A.PHONE,OTHER,CASE WHEN other in (select phone from CDATDUPL.DBO.cdatsuspect) THEN nickname  ELSE ' ' END   as  NICKNAME,
STARTTIME,DURATION,TYPE,A.IMEINUMBER,CELLTOWERID,' ' AS OPT,' ' AREADESCRIPTION,' 'LAT,' 'LONG,' 'AZM INTO #TTPP FROM #TT A 
left join CDATDUPL.DBO.cdatsuspect c on a.other=c.phone   
WHERE STARTTIME NOT IN(SELECT DISTINCT STARTTIME FROM #TTP)";

$sql4="SELECT * into #temp_cdrs FROM #TTP
UNION
SELECT * FROM #TTPP";

$sql5="SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs  ORDER BY STARTTIME";

$sql6="select 'CALL DETAILS OF MOBILE NO. '+'$number' + 'AND OTHER NO. '+'$number1' as PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
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
<th><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TYPE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['OPERATOR'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
</body>
</html>