<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$NUMBER1=$_POST['CIN_NO'];  
$NUMBER2=str_replace(",","','","$NUMBER1");
$UNIQUE_KEY=$_POST['UNIQUE_KEY'];     
$IRKEY=$_POST['IRKEY'];     


$sql="UPDATE JRMS_TOTAL_2012_TO_2017 SET UNIQUE_KEY='$UNIQUE_KEY', IRKEY='$IRKEY', ASONDATE=GETDATE(), APP_OR_MANUAL=  'APPLICATION_ENTRY'
WHERE CIN IN ('$NUMBER2')";
if(!sqlsrv_query($conn,$sql))
{
echo "Not Updated";
}
else
{
echo "Updated";
}
header("refresh:30; url=jrms_unique_key_update.htm");
?>