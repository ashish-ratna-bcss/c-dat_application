<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="SUM_NEW_NOS.HTML"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];
$date = $_POST['FROM_DT'];

$sql3 ="SELECT * INTO #TT FROM CDAT_DETAILS1 WHERE PHONE='$number' AND STARTTIME>'$date' AND OTHER NOT IN
(SELECT DISTINCT OTHER FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND 
STARTTIME < '$date')";

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

$sql6="SELECT DISTINCT A.PHONE,
CASE WHEN OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT) THEN OTHER+' - '+NICKNAME  
ELSE OTHER END   AS  OTHER,[IN],[OUT],CALLS,DUR,
FIRSTCALL,LASTCALL,
CASE WHEN OTHER=C.PHONE 
THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+' '+CONVERT(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL'
WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER'
WHEN OTHER IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE) 
THEN ISNULL(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #RESULT1 A
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON OTHER=B.PHONE 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
 LIKE PHONEPREFIX+'%' ORDER BY CALLS DESC";

$sql8="SELECT 'SUMMARY OF MOBILE NO: '+'$number '+' (NEW CONTACTS FROM: '+'$date'+')' as PHONE1";

$sql9="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

$sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
 LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";


$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$stmt = sqlsrv_query( $conn, $sql6 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<!-- <th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th> -->
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
/* echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>"; */
echo "<td width=500px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";
}

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>IN</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OUT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRSTCALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LASTCALL'] ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $stmt);
?>
</body>
</html>