<html>
<head>
</head>
<body background="IMAGES/emp.png">
<li><a href="training_module1.php"><font color=#FDEFEF>Back</a></li></p>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TRAINING_DB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['EMPLOYEE_SEARCH_NO'])){

$number=$_POST['EMPLOYEE_SEARCH'];
$number1=$_POST['EMPLOYEE_SEARCH_NO'];
$number2=$_POST['EMPLOYEE_SEARCH_RANK'];

$sql8="SELECT 'EMPLOYEE SEARCH IN PWDMS' as PHONE1";

$sql9="SELECT DISTINCT EMPLOYEE_ID,NAME,[RANK],[ROLE],GENERAL_NO,WING_NAME,ZONE_NAME,DIVISION_NAME,
POLICE_STATION FROM TRAINING_DB.DBO.TRAINING_STRENGTH_PARTICULARS WHERE $number like '%'+'$number1'+'%' 
AND RANK LIKE '%'+'$number2'+'%'";

$sql10="SELECT 'EMPLOYEE SEARCH IN TRAINING DATA' as PHONE1";


$sql11="SELECT DISTINCT EMPLOYEE_ID,GENERAL_NO,NAMES NAME,PS_NAME POLICE_STATION,PH_NO PHONE_NO,ZONE,
RANK,COURSE_NAME,START_DATE,END_DATE FROM TRNG_ATT_WITH_EMPID WHERE $number like '%'+'$number1'+'%' AND 
RANK LIKE '%'+'$number2'+'%'";

$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query($conn, $sql10 );
$st11 = sqlsrv_query($conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#00008B'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
/* <tr bgcolor=#921215> */ 
<tr  bgcolor=#00008B>
<th><font size=3 face=verdana color='#F9FBFC'>EMP ID</th>
<th><font size=3 face=verdana color='#F9FBFC'>GEN NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>RANK</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ROLE</font></th>

<th><font size=3 face=verdana color='#F9FBFC'>WING</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ZONE NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DIVISION NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['EMPLOYEE_ID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['GENERAL_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['RANK'] ."<center></font></td>";
echo "<td width=150px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['ROLE'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['WING_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['ZONE_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['DIVISION_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['POLICE_STATION'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="qrcode/php/qr_img.php?d='.'EMP_ID: '.$row["EMPLOYEE_ID"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' RANK:'.$row["RANK"]. ' ROLE:'.$row["ROLE"].' GEN_NO:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["GENERAL_NO"]).' PS: '.$row["POLICE_STATION"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}
echo "</table>";

echo "</br>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#00008B'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
/* <tr bgcolor=#921215> */ 
<tr bgcolor=#00008B>
<th><font size=3 face=verdana color='#F9FBFC'>EMP ID</th>
<th><font size=3 face=verdana color='#F9FBFC'>GEN NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>RANK</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ZONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>COURSE NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>START DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>END DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['EMPLOYEE_ID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana><center>". $row['GENERAL_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['RANK'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['PHONE_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['ZONE'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['POLICE_STATION'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['COURSE_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['START_DATE'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['END_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="qrcode/php/qr_img.php?d='.'EMP_ID: '.$row["EMPLOYEE_ID"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' RANK:'.$row["RANK"].' GEN_NO:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["GENERAL_NO"]).' PS: '.$row["POLICE_STATION"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
</body>
</html>