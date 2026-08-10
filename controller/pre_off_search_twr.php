<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Pre Off Search Twr");
?>

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

$sql0="SELECT 'PREVIOUS OFFENDER SEARCH IN TOWER DUMP OF PS: '+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO'+'/'+'$YEAR'+' ON '+'$OFF_DATE'+' BETWEEN '+
'$HH1'+':'+'$MM1'+':'+'$SS1'+' AND '+'$HH2'+':'+'$MM2'+':'+'$SS2' as SEARCH";

$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";
$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";

$sql1 ="select distinct A.phone,other,CONVERT(VARCHAR,starttime,20) starttime,duration,imeinumber,call_type,
CASE WHEN A.OTHER=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.OTHER=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS
INTO #SUS from TWRMDB_MASTER_CDAT A 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER)=10 THEN A.OTHER ELSE CASE WHEN LEN(A.OTHER)>10 THEN '00'+A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
where crkey=(SELECT DISTINCT 
CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE') AND convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and (select distinct max(Timing) from #time)) AND
A.PHONE IN (SELECT DISTINCT PHONE FROM CDATDUPL.DBO.CDATSUSPECT WHERE PHONE LIKE '[7-9]%' AND LEN(PHONE)=10)
ORDER BY STARTTIME DESC";

$sql2="select distinct a.phone,other,CONVERT(VARCHAR,starttime,20) starttime,A.duration,A.imeinumber,A.call_type,A.ADDRESS,nickname,crime_no,crime_head,mo,unit from #sus a left join cdatdupl.dbo.cdatsuspect b on a.phone=b.phone
ORDER BY STARTTIME DESC";

$st0 = sqlsrv_query( $conn, $sql0);
$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st3 = sqlsrv_query( $conn, $sql1 );
$st4 = sqlsrv_query( $conn, $sql2 );

while( $row = sqlsrv_fetch_array( $st0, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['SEARCH'] ."<center></td></font></br>";
} 

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DURATION</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMEINUMBER</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLTYPE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE ADDRESS</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_NO</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>UNIT</font</th>
</tr>";
while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['phone'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['other'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['starttime'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['duration'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['imeinumber'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['call_type'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['nickname'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['crime_no'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['crime_head'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['mo'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['unit'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
<?php layout_end(); ?>
