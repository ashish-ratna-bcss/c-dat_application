<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="rta_nike.HTML">Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['REGN_NO'];

$sql1="SELECT DISTINCT REGN_NO,FULLNAME,FATHERNAME,FULLADDRESS,PHONE FROM CDATDUPL..CDAT_RTA
WHERE REGN_NO='$number'";
$sql6="select 'RTA DETAIL OF VEHICLE. '+'$number'as PHONE";



$st1 = sqlsrv_query( $conn, $sql1 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=250px ><font size=3 face=verdana color='#F9FBFC'>VEHICLE DETAILS</font></th>
</tr>";
echo "</table>";
echo "<table border=1 cellspacing=0 cellpadding=5>";


while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {

echo "<tr>";
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>VEHICLE NO</font></th>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['REGN_NO'] ."<center></font></td>";
echo "</tr>";
echo "<tr>";
echo "<th width=150px bgcolor=#921215 ><font size=3 face=verdana color='#F9FBFC'>REGN NO</font></th>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REGN_NO'] ."<center></font></td>";
echo "</tr>";
}
echo "</table>";

?>
</body>
</html>