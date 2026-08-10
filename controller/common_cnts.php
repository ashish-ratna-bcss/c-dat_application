<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Common Contacts");
?>

<li><a href="common_cnts.php">Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number= $_POST['PHONE_NO'];

$number2 = str_replace(",","','","$number");
$number3 = str_replace(",","' INSERT INTO #A1 SELECT '","$number");

echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>ADDRESSES OF MOBILE NOS<center></td></font></br>";

$address1= "CREATE TABLE #A1 (PHONE NVARCHAR (20) NULL)";

$address2= "INSERT INTO #A1 SELECT '$number3'";

$address3= "SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL,MAX(STARTTIME) AS LAST_CALL, 
	MAX(A.ASONDATE) AS LAST_UPDATED,NICKNAME+'_'+ROLE+' MO:'+MO NICKNAME INTO #A2
FROM CDATDUPL.DBO.CDATPCSUSPECT A 
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2')
GROUP BY A.PHONE,NICKNAME,MO,ROLE";

$address4 = "SELECT DISTINCT A.PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME INTO #A3 FROM #A1 A
LEFT JOIN #A2 B ON A.PHONE=B.PHONE";

$address5= "SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE,DOA, EFF_FROM_DATE INTO #A4   FROM CDATDUPL.DBO.CDATADDRESS 
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

$address6 = "INSERT INTO #A4
SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

$address7 = "select DISTINCT A.PHONE,ISNULL(CONVERT(VARCHAR,FIRST_CALL,20),'NIL')  AS FIRST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_CALL,20),'NIL') AS LAST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_UPDATED,20),'NIL') AS LAST_UPDATED,ISNULL(NICKNAME,'NIL') AS NICKNAME,
CASE WHEN A.PHONE IN (SELECT PHONE FROM #A4) THEN FULLNAME+', '+B.FULLADDRESS+', DOA: '+CONVERT(VARCHAR,DOA,106)+', LAST UPDATE: '+CONVERT(VARCHAR,EFF_FROM_DATE,106)
ELSE AREADESCRIPTION END AS ADDRESS INTO #A5 FROM #A3 A
LEFT JOIN #A4 B ON A.PHONE=B.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 
	THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'CODE NOT AVAILABLE' END END
 LIKE PHONEPREFIX+'%' ORDER BY A.PHONE";

$address8 = "SELECT PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME,
 CASE WHEN ADDRESS IS NULL AND LEN(PHONE)<>10 THEN 'JUNK OR VOIP CALL' 
 WHEN ADDRESS IS NULL AND SUBSTRING(PHONE,1,1) IN ('6','7','8','9') AND LEN(ADDRESS)>=10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS 
 END AS ADDRESS FROM #A5";



$sql1 = "SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number2')";

$sql2 = "select phone,other,count(other)as count1 into #common_numbertable1 from #t
group by other,phone having (count(other))>1 order by other,phone";

$sql3="select other,phone,count(other) count1 into #common_numbertable2 from #common_numbertable1
group by other,phone order by other";

$sql4 = "SELECT distinct
   OTHER, 
   (SELECT  phone+ ', ' 
    FROM #common_numbertable2 US
    WHERE US.other = SS.other
    FOR XML PATH('')) [phones],(select sum(count1) from #common_numbertable2 xx where xx.other = SS.other)  totalnumberofphones
into #common_numbertable3 FROM #common_numbertable2 SS
GROUP BY SS.other
ORDER BY 1";
$sql5="delete from #common_numbertable3 where totalnumberofphones=1";
$sql6="drop table #common_numbertable1";
$sql7="drop table #common_numbertable2";
$sql8="update #common_numbertable3 set phones=left(phones,len(phones)-1)+'.'";
$sql9="select distinct a.OTHER,A.PHONES,A.totalnumberofphones PHONE_COUNT,E.NICKNAME+'_'+ROLE OTHERS_NICKNAME,E.MO OTHERS_MO,
CASE WHEN A.OTHER=C.PHONE THEN ISNULL
(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+
', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', LAST_UPDATED: '+
ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.OTHER=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')
+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS 
OTHER_ADDRESS FROM #common_numbertable3 A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL AND LEN(A.OTHER)>='10'
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER)=10 THEN A.OTHER ELSE CASE WHEN LEN(A.OTHER)>10 THEN 
'00'+A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT E ON A.OTHER=E.PHONE
where len(a.other)='10' and isnumeric(a.other)='1' and a.other like '[6-9]%'
ORDER BY PHONE_COUNT DESC,OTHER DESC";

$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$at1 = sqlsrv_query( $conn, $address1 );
$at2 = sqlsrv_query( $conn, $address2 );
$at3 = sqlsrv_query( $conn, $address3 );
$at4 = sqlsrv_query( $conn, $address4 );
$at5 = sqlsrv_query( $conn, $address5 );
$at6 = sqlsrv_query( $conn, $address6 );
$at7 = sqlsrv_query( $conn, $address7 );
$at8 = sqlsrv_query( $conn, $address8 );

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $at8, SQLSRV_FETCH_ASSOC) ) {
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
echo"</br>";

echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>COMMON CONTACTS<center></td></font></br>";

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>COMMON CONTACT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONES</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE_COUNT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHERS_NICKNAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHERS_MO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER_ADDRESS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OTHER'] ."<center></font></td>";
echo "<td width=200px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['PHONES'] ."<center></font></td>";
echo "<td width=40px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE_COUNT'] ."<center></font></td>";
echo "<td width=40px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OTHERS_NICKNAME'] ."<center></font></td>";
echo "<td width=40px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OTHERS_MO'] ."<center></font></td>";
echo "<td width=300px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_ADDRESS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table>";

sqlsrv_free_stmt( $at8);
sqlsrv_free_stmt( $st9);
?>
<?php layout_end(); ?>
