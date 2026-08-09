<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="OFFENDER_SEARCH_BY_MO.HTML"><font color=#FDEFEF>Back</a></li>
<form action ='OFFENDER_SEARCH_BY_MO.PHP' method='post'>
<b><font size=3 face=verdana color='#F9FBFC'>MO SUB CLASSIFICATION: </b>
<input type='text' name='MO' value=''>
<input type ='submit' value='Submit'/>

<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['MO'])){

$number=$_POST['MO'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT MO_KEY,ACC_NAME AS ACCUSED_NAME,FATHER_NAME,AGE,MO1,MO2,POLICE_STATION FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION
WHERE (MO1 LIKE '%'+REPLACE('$number',' ','%')+'%' OR MO2 LIKE '%'+REPLACE('$number',' ','%')+'%' OR CRIME_HEAD LIKE '%'+REPLACE('$number',' ','%')+'%' )";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_SUB_CLASSIFICATION1</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_SUB_CLASSIFICATION2</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'OFFENDER_FD.PHP?MO_KEY='.($row['MO_KEY']).">". $row['MO_KEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ACCUSED_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO1'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO2'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
</body>
</html>dy>
</html>