<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="SUSPECT_SEARCH.PHP"><font color='#FDEFEF'>Back</a></li></b></b>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$PHONE_NO 	= $_POST['PHONE_NO'];
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

$sql0="SELECT 'MOBILE NO: '+'$PHONE_NO'+' SEARCH IN TOWER DUMP OF PS: '+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO:'+'/'+'$YEAR' as SEARCH";

$ADD0="SELECT 'ADDRESS OF MOBILE NO: '+'$PHONE_NO' as PHONE1";

$ADD1="SELECT  '$PHONE_NO' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,''LAST_UPDATED,''INC_OFFICER INTO #T";

$ADD2="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,MO,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$PHONE_NO' GROUP BY A.PHONE,B.NICKNAME,MO, INC_OFFICER";

$ADD3="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS,
CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";



$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";
$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";


$sql1 ="select distinct A.phone,other,CONVERT(VARCHAR,starttime,20) starttime,duration,imeinumber,call_type,
CASE WHEN A.OTHER=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.OTHER=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS
from TWRMDB_MASTER_CDAT A 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER)=10 THEN A.OTHER ELSE CASE WHEN LEN(A.OTHER)>10 THEN '00'+A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
where A.phone='$PHONE_NO' and crkey=(SELECT DISTINCT 
CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE') AND convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and (select distinct max(Timing) from #time))
ORDER BY STARTTIME DESC";



$st0 = sqlsrv_query( $conn, $sql0);
$AD0 = sqlsrv_query( $conn, $ADD0);
$AD1 = sqlsrv_query( $conn, $ADD1);
$AD2 = sqlsrv_query( $conn, $ADD2);
$AD3 = sqlsrv_query( $conn, $ADD3);
$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st3 = sqlsrv_query( $conn, $sql1 );

while( $row = sqlsrv_fetch_array( $AD0, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}



echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>

</tr>";

while( $row = sqlsrv_fetch_array( $AD3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['INC_OFFICER'] ."<center></font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="qrcode\php\qr_img.php?d='.'PHONE NO:'.$PHONE_NO.'  '.'ADDRESS: '. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["ADDRESS"]) .'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

echo "</table>";

echo "</br>";

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
</tr>";
while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['phone'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['other'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['starttime'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['duration'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['imeinumber'] ."<center></font></td>";
echo "<td width=250px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['call_type'] ."<center></font></td>";
echo "<td width=350px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
</body>
</html>