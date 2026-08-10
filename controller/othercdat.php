<html>
<head>
</head>
<body bgcolor="#0C5D90">
<a href="otherscdat.php"><font color=#FDEFEF>BACK</a>
<style type="text/css">
a:link , a:visited{
text-decoration: none;
}
</style>
<body bgcolor="#BDBDBD">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$number 	= $_POST['PHONE_NO'];


$sql1="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

$sql2="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

$sql3="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')
+', '+ISNULL(C.CATEGORY_TYPE,'') WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')
+', '+ISNULL(D.CATEGORY_TYPE,'') ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE  AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
 LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

$sql4="SELECT DISTINCT OTHER INTO #TEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number'
AND LEN(OTHER)>=10 AND ISNUMERIC(OTHER)=1 AND SUBSTRING(OTHER,1,1) IN ('7','8','9')
AND OTHER NOT IN (SELECT DISTINCT OTHER FROM CDAT_IMPORT.dbo.CALLCENTER_NOS)";

$sql5 = "SELECT DISTINCT PHONE, OTHER,SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT',COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FC, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LC INTO #TEMP1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE OTHER IN
(SELECT DISTINCT OTHER FROM #TEMP)AND PHONE!='$number'
GROUP BY PHONE, OTHER ORDER BY OTHER";
 
$sql6 = "SELECT OTHER AS PHONE, A.PHONE AS OTHER, C.NICKNAME, CATEGORY,[IN],[OUT],CALLS,DUR, FC AS FIRST_CALL,LC AS LAST_CALL,INC_OFFICER INTO #TEMP2 FROM #TEMP1 A
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.PHONE=C.PHONE";

$sql7 = "SELECT DISTINCT A.PHONE,OTHER,NICKNAME,CATEGORY,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL, INC_OFFICER FROM #TEMP2 A ORDER BY PHONE, CALLS DESC";


$sql8 ="SELECT 'OTHERS CDAT CONTACTS OF MOBILE NO: '+'$number' as PHONE";

$sql9="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO OTHERS OF $number ***' end as CNTS FROM #TEMP2";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=500px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";
}

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=3>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CDAT PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICK NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OUT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'cdatcnts2.php?PHONE_NO='.($row['OTHER']).">".$row['OTHER']."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CATEGORY'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=125px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=130px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['INC_OFFICER'] ."</font></td>";
echo "</tr>";


}
echo"</table><br />";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['CNTS'] ."<center></td></font></br>";
}

sqlsrv_free_stmt( $st2);
sqlsrv_free_stmt( $st7);
?>
</body>
</html>