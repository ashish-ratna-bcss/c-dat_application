<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Dump");
?>

<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
 $sql="select distinct crime_no from offence_details";
 $st1 = sqlsrv_query( $conn, $sql);
?>
<select>
<?php while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
?>
<option value=""></option>
<option><?php echo $row["crime_no"] ; ?></option>
<?php
}
?>
</select>
<?php layout_end(); ?>
