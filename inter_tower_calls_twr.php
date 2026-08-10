<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="suspect_search.php"><font color='#FDEFEF'>Back</a></li></b></b>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$POLICE_STATION	= $_POST['Police_station'];
$CRIME_NO	= $_POST['CRIME_NO'];
$YEAR		= $_POST['YEAR'];
$OFF_DATE       = $_POST['OFF_DATE'];
$HH1		= $_POST['hh1'];
$MM1		= $_POST['mm1'];
$SS1		= $_POST['ss1'];
$HH2		= $_POST['hh2'];
$MM2		= $_POST['mm2'];
$SS2		= $_POST['ss2'];

$sql0="SELECT 'INTER TOWER CALLS SEARCH IN TOWER DUMP OF PS:'+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO'+'/'+'$YEAR' as SEARCH";

$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";
$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";

$sql1="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,CALL_TYPE,IMEINUMBER INTO #TEMP1 FROM TWRMDB_MASTER_CDAT 
where crkey=(SELECT DISTINCT CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE')";

$sql11="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,CALL_TYPE,IMEINUMBER INTO #TEMP11 FROM #TEMP1 
where convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and 
(select distinct max(Timing) from #time))";

$sql2="select distinct PHONE,OTHER,STARTTIME INTO #TEMP2 from #TEMP11 INTERSECT select distinct OTHER,PHONE,STARTTIME from #TEMP11";

$sql3="SELECT DISTINCT A.PHONE,A.OTHER,A.STARTTIME,B.DURATION,B.CALL_TYPE,B.IMEINUMBER INTO #TEMP3 FROM #TEMP2 A
INNER JOIN TWRMDB..TWRMDB_MASTER_CDAT B ON A.PHONE=B.PHONE AND A.OTHER=B.OTHER AND A.STARTTIME=B.STARTTIME";

$sql4="SELECT DISTINCT A.PHONE PHONE,A.OTHER OTHER,CONVERT(VARCHAR,A.STARTTIME,20) STARTTIME,A.DURATION DURATION,A.CALL_TYPE CALL_TYPE,A.IMEINUMBER IMEINUMBER,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #TEMP3 A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WWIFI CALL' END END LIKE PHONEPREFIX+'%' ORDER BY STARTTIME";

$st0 = sqlsrv_query( $conn, $sql0);
$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st3 = sqlsrv_query( $conn, $sql1 );
$st11 = sqlsrv_query( $conn,$sql11 );
$st4 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql3 );
$st6 = sqlsrv_query( $conn, $sql4 );



while( $row = sqlsrv_fetch_array( $st0, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['SEARCH'] ."<center></td></font></br>";
} 

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DURATION</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALL_TYPE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMEINUMBER</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OTHER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALL_TYPE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
</body>
</html>