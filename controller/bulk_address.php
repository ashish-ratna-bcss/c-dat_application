<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Bulk Address");
?>

<li><a href="bulkaddress.php"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number= $_POST['PHONE_NO'];

$number2 = str_replace(",","','","$number");
$number3 = str_replace(",","' INSERT INTO #T1 SELECT '","$number");

echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>ADDRESSES OF MOBILE NOS<center></td></font></br>";


$sql1= "CREATE TABLE #T1 (PHONE NVARCHAR (20) NULL)";

$sql2= "INSERT INTO #T1 SELECT '$number3'";

$sql3= "SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL,MAX(STARTTIME) AS LAST_CALL, MAX(A.ASONDATE) AS LAST_UPDATED,NICKNAME INTO #T2
FROM CDATDUPL.DBO.CDATPCSUSPECT A 
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2')
GROUP BY A.PHONE,NICKNAME";

$sql4 = "SELECT DISTINCT A.PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME INTO #T3 FROM #T1 A
LEFT JOIN #T2 B ON A.PHONE=B.PHONE";

$sql5= "SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE,DOA, EFF_FROM_DATE INTO #T4   FROM CDATDUPL.DBO.CDATADDRESS 
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

$sql6 = "INSERT INTO #T4
SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

$sql7 = "select DISTINCT A.PHONE,ISNULL(CONVERT(VARCHAR,FIRST_CALL,20),'NIL')  AS FIRST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_CALL,20),'NIL') AS LAST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_UPDATED,20),'NIL') AS LAST_UPDATED,ISNULL(NICKNAME,'NIL') AS NICKNAME,
CASE WHEN A.PHONE IN (SELECT PHONE FROM #T4) THEN FULLNAME+', '+B.FULLADDRESS+', DOA: '+CONVERT(VARCHAR,DOA,106)+', LAST UPDATE: '+CONVERT(VARCHAR,EFF_FROM_DATE,106)
ELSE AREADESCRIPTION END AS ADDRESS INTO #T5 FROM #T3 A
LEFT JOIN #T4 B ON A.PHONE=B.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'CODE NOT AVAILABLE' END END
 LIKE PHONEPREFIX+'%' ORDER BY A.PHONE";

$sql8 = "SELECT PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME,
 CASE WHEN ADDRESS IS NULL AND LEN(PHONE)<>10 THEN 'JUNK OR VOIP CALL' 
 WHEN ADDRESS IS NULL AND SUBSTRING(PHONE,1,1) IN ('7','8','9') AND LEN(ADDRESS)>=10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS 
 END AS ADDRESS FROM #T5";



$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st8);
?>
<?php layout_end(); ?>
