<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="SUM_HOME.HTML"><font color=#FDEFEF>Back</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];

$sql3 ="SELECT * INTO #TT FROM CDAT_DETAILS1 WHERE PHONE='$number'";

$sqlimei="SELECT DISTINCT IMEINUMBER,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,COUNT(*) TOTAL_CALLS  FROM #TT
GROUP BY IMEINUMBER ORDER BY TOTAL_CALLS DESC";


$sqlimsi="SELECT DISTINCT IMSINUMBER,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,COUNT(*) TOTAL_CALLS FROM #TT
GROUP BY IMSINUMBER ORDER BY TOTAL_CALLS DESC";


$sql4 ="SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT',
COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL,
CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL INTO #RESULT FROM #TT 
GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

$sql5 ="SELECT * INTO #RESULT1 FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
SELECT DISTINCT OTHER  FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR)
AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

$sql6="SELECT DISTINCT A.PHONE PHONE,CASE WHEN OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT) THEN OTHER+' - '+J.NICKNAME  
ELSE OTHER END   AS  OTHER,[IN],
[OUT],CALLS, DUR,
FIRSTCALL,LASTCALL,
ISNULL((CASE WHEN OTHER=C.PHONE
THEN ISNULL(C.FULLNAME,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
when isnumeric(other)=0 then 'customer care'
WHEN OTHER=D.PHONE
THEN ISNULL(D.FULLNAME,'')
ELSE AREADESCRIPTION END)+','+ (CASE WHEN OTHER=C.PHONE
THEN ISNULL(C.FULLADDRESS,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
when isnumeric(other)=0 then 'customer care'
WHEN OTHER =D.PHONE
THEN ISNULL(D.FULLADDRESS,'')
ELSE AREADESCRIPTION END )+' DOA:'+
CASE WHEN OTHER=C.PHONE THEN CONVERT(VARCHAR,C.DOA)
WHEN OTHER=D.PHONE THEN CONVERT(VARCHAR,D.DOA)
END,'') SDR_DATA ,
ISNULL((CASE WHEN A.OTHER=F.PHONE AND F.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(F.FULLNAME,'')+' '+ISNULL(F.FULLADDRESS,'') END),'')
RTA_DATA,
ISNULL((CASE WHEN A.OTHER=G.PHONE AND G.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(G.FULLNAME,'')+' '+ISNULL(G.FULLADDRESS,'') END),'')
CIVIL_SUPPLY_DATA,
ISNULL((CASE WHEN A.OTHER=H.PHONE AND H.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(H.FULLNAME,'')+' '+ISNULL(H.FULLADDRESS,'') END),'')
LICENCE_DATA,
ISNULL((CASE WHEN A.OTHER=I.PHONE AND I.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(I.NAME,'')+' '+ISNULL(I.ADDRESS,'') END),'') 
GAS_DATA_ADDRESS FROM #RESULT1 A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI 
CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN CDATDUPL..CDAT_RTA F ON A.OTHER=F.PHONE
LEFT JOIN CDATDUPL..CDAT_CIVILSUPPLY G ON A.OTHER=G.PHONE
LEFT JOIN CDATDUPL..CDAT_LICENCE H ON A.OTHER=H.PHONE
LEFT JOIN CDATDUPL..CDAT_GAS_DETAILS I ON A.OTHER=I.PHONE
LEFT JOIN CDATDUPL..CDATSUSPECT J ON A.OTHER=J.PHONE
ORDER BY DUR DESC";

$sql8="SELECT 'SUMMARY OF MOBILE NO: '+'$number' as PHONE1";

$sql9="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

$sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA:'+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

$sql12="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** CDRs NOT AVAILABLE ***' end as PHONE FROM #RESULT";


$sqlD1 ="SELECT * INTO #DTEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00') 
AND PHONE='$number'";

$sqlD2 = cdr_sql_enrich_location_temp('#DTEMP', '#DTT1');

$sqlD4="SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #DT FROM #DTT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";


$sqlD5="SELECT TOP 10 * FROM #DT";

$sqlD6="SELECT 'DAY LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sqlN7="SELECT 'NIGHT LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sqlN8 ="SELECT * INTO #DT1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00') 
AND PHONE='$number'";

$sqlN9 = cdr_sql_enrich_location_temp('#DT1', '#DT3');

$sqlN11="SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #DT4 FROM #DT3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sqlN12="SELECT TOP 10 * FROM #DT4";

$stimei = sqlsrv_query( $conn, $sqlimei );
$stimsi = sqlsrv_query( $conn, $sqlimsi );


$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );


$stimei = sqlsrv_query( $conn, $sqlimei );
$stimsi = sqlsrv_query( $conn, $sqlimsi );


$st5 = sqlsrv_query( $conn, $sql5 );
$stmt = sqlsrv_query( $conn, $sql6 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );
$st12 = sqlsrv_query( $conn, $sql12 );

$stD1 = sqlsrv_query( $conn, $sqlD1 );
$stD2 = sqlsrv_query( $conn, $sqlD2 );
$stD4 = sqlsrv_query( $conn, $sqlD4 );
$stD5 = sqlsrv_query( $conn, $sqlD5 );
$stD6 = sqlsrv_query( $conn, $sqlD6 );
$stD7 = sqlsrv_query( $conn, $sqlN7 );
$stD8 = sqlsrv_query( $conn, $sqlN8 );
$stD9 = sqlsrv_query( $conn, $sqlN9 );
$stD11 = sqlsrv_query( $conn, $sqlN11 );
$stD12 = sqlsrv_query( $conn, $sqlN12 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMEINUMBER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>TOTAL_CALLS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $stimei, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TOTAL_CALLS'] ."<center></font></td>";
echo "</tr>";
}

echo"</table><br />";


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMSINUMBER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>TOTAL_CALLS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $stimsi, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMSINUMBER'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TOTAL_CALLS'] ."<center></font></td>";
echo "</tr>";
}

echo"</table><br />";


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>NICKNAME</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_UPDATED</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";
}

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=2>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>OTHER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IN</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>OUT</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>CALLS</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>DUR</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>SDR_DATA</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>RTA_DATA</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>CIVIL_SUPPLY_DATA</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LICENCE_DATA</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>GAS_DATA</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRSTCALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LASTCALL'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['SDR_DATA'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['RTA_DATA'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['CIVIL_SUPPLY_DATA'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['LICENCE_DATA'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['GAS_DATA_ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table><br /><br />";

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}


while( $row = sqlsrv_fetch_array( $stD6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $stD5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></td>";
echo "<td width=15px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

} 
echo"</table></br>";
while( $row = sqlsrv_fetch_array( $stD7, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $stD12, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></td>";
echo "<td width=800px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></td>";
echo "<td width=15px><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

} 
echo"</table>";



sqlsrv_free_stmt( $stmt);
?>
</body>
</html>