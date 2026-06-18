<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$USERNAME=$_POST['USERNAME'];
$PASSWORD=$_POST['PASSWORD'];
$sql="SELECT * FROM LOGINS WHERE USERNAME='$USERNAME' AND PASSWORD='$PASSWORD'";
$st1=sqlsrv_query($conn,$sql);
$row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC);
$count=sqlsrv_has_rows($st1);
if($count==1)
{
header("refresh:0; url=HOME_IR_MODIFIED.HTML");
} else
{
echo "<font size=4 face=verdana color='#921215'>NO PASSWORD MATCHED</font></br>";
}
?>