<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="IMEISEARCH.HTML"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['IMEI_NO'];

$sql1 ="SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE IMEINUMBER = '$number'";

$sql2 ="SELECT DISTINCT PHONE, IMEINUMBER,SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', COUNT(PHONE) AS CALLS,
SUM(DURATION) AS DUR, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL INTO #TT FROM #T
GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";

$sql3 ="SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, 
case when A.PHONE in(select distinct phone from cdatdupl.dbo.cdataddress) 
then isnull(c.FULLname+', '+c.FULLaddress,'')+' '+isnull(c.CATEGORY_type,'')
when A.PHONE in(select distinct phone from cdatdupl.dbo.address_other_state) 
then isnull(d.FULLname+', '+d.FULLaddress,'')+' '+isnull(d.CATEGORY_type,'')
else areadescription end as ADDRESS FROM #tt A
left join cdatdupl.dbo.cdataddress c on a.PHONE=c.phone AND C.EFF_TO_DATE IS NULL
left join cdatdupl.dbo.address_other_state d on a.PHONE=d.phone AND D.EFF_TO_DATE IS NULL
left join cdatdupl.dbo.cdatphonearea e on  A.PHONE LIKE phoneprefix+'%'
order by LAST_CALL";

$sql4="SELECT 'LIST OF PHONE NOs USED IN IMEI: '+'$number' as PHONE1";


$sql5="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO PHONES ARE AVAILABLE IN IMEI $number ***' end as PHONE FROM #tt";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );

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
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table></br></br>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

sqlsrv_free_stmt( $st3);
?>
</body>
</html>