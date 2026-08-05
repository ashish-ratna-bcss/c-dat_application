<?php 
// DB credentials.
$serverName = "CPHYDERABAD1\DAU_HYD";
$connectionInfo = array( "Database"=>"CPMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
?>