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

$sql0="SELECT 'OTHER STATE NUMBERS SEARCH IN TOWER DUMP OF PS: '+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO:'+'/'+'$YEAR' as SEARCH";

$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";

$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";

$sql1 ="select distinct a.PHONE,AREADESCRIPTION,STATE from twrmdb..TWRMDB_MASTER_CDAT a inner join cdatdupl..cdatphonearea b
on phone like '[7-9]%' and len(phone)='10' and left(phone,5) = phoneprefix and b.STATE_KEY!='1' AND crkey=(SELECT DISTINCT CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE') AND convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and (select distinct max(Timing) from #time))";

$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st0 = sqlsrv_query( $conn, $sql0);
$st3 = sqlsrv_query( $conn, $sql1 );

while( $row = sqlsrv_fetch_array( $st0, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['SEARCH'] ."<center></td></font></br>";
}

echo "</br>";

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREADESCRIPTION</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font</th>
</tr>";
while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATE'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
</body>
</html>