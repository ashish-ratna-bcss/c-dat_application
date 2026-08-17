<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="IMEISINPHONE.HTML"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];


$sql1 ="SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE= '$number'";

$sql2 ="SELECT DISTINCT PHONE, IMEINUMBER,SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,
SUM(DURATION) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL INTO #TT FROM #T
GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";

$sql3 ="SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, 
CASE WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.CDATADDRESS) 
THEN ISNULL(C.FULLNAME+', '+C.FULLADDRESS,'')+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE) 
THEN ISNULL(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  A.PHONE LIKE PHONEPREFIX+'%'
ORDER BY LAST_CALL";


$sql4="SELECT 'LIST OF IMEIS USED IN PHONE NO: '+'$number' as PHONE1";

$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );

while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
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
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st3);
?>
</body>
</html>