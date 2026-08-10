<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Cis Data Name Search Php1");
?>

<li><a href="jrms.php"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$NAME = $_POST['NAME'];
$POLICE_STATION= $_POST['POLICE_STATION'];
$DISTRICT= $_POST['DISTRICT'];




$sql6="SELECT 'ACCUSED ARRESTED FROM' + '$POLICE_STATION' +' OF '+ '$DISTRICT' +' DISTRICT '+' BY NAME '+'$NAME' AS PHONE";



$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

?>
<?php layout_end(); ?>
