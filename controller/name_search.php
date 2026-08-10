<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="../view/name_search.htm"><font color=#FDEFEF>Back</a></li>
<form action ='name_search.php'method='post'>
<b><font size=3 face=verdana color='#F9FBFC'>NAME OF THE OFFENDER : </b>
<input type='text' name='NAME' value=''>
<b><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD : </b>
<input type='text' name='CRIME_HEAD' value=''>
<input type ='submit' value='Submit'/>

<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['NAME'])){

$number=$_POST['NAME'];
$number1=$_POST['CRIME_HEAD'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT NICKNAME ACCUSED_NAME,ROLE,FNAME,ADDRESS,STATE,CRIME_NO,YEAR,SEC_OF_LAW,UNIT,CRIME_HEAD,MO,ORGANISATION 
FROM CDATDUPL..CDATSUSPECT WHERE NICKNAME LIKE '%'+REPLACE('$number',' ','%')+'%' AND CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' AND 
ltrim(rtrim('$number'))!='' and len(replace('$number',' ',''))>'5'";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ROLE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEC_OF_LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>UNIT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ORGANISATION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ACCUSED_NAME'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ROLE'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['YEAR'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['UNIT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ORGANISATION'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
</body>
</html>dy>
</html>