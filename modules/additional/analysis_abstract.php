<?php
require_once __DIR__ . '/../common/bootstrap.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$TYPE_OF_DATA=$_POST['TYPE_OF_DATA'];
$SOURCE=$_POST['SOURCE'];
$NO_OF_CDRS_OR_PLACES=$_POST['NO_OF_CDRS_OR_PLACES'];
$NUMBERS=$_POST['NUMBERS'];
$CRIME_NO=$_POST['CRIME_NO'];
$YEAR=$_POST['YEAR'];
$CRIME_HEAD=$_POST['CRIME_HEAD'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];
$POLICE_STATION=$_POST['POLICE_STATION'];
$IO=$_POST['IO'];
$PHONE_IO=$_POST['PHONE_IO'];
$BRIEF_FACTS=$_POST['BRIEF_FACTS'];
$DATE=$_POST['DATE'];
$sql="insert into CDATDUPL.dbo.ANALYSIS_ABSTRACT (TYPE_OF_DATA, SOURCE, NO_OF_CDRS_OR_PLACES,NUMBER, CRIME_NO, YEAR, CRIME_HEAD,SEC_OF_LAW, POLICE_STATION, IO, PHONE_IO,BRIEF_FACTS, DATE,DATE_OF_ENTRY) 
VALUES('$TYPE_OF_DATA','$SOURCE','$NO_OF_CDRS_OR_PLACES', 
'$NUMBERS','$CRIME_NO','$YEAR','$CRIME_HEAD','$SEC_OF_LAW','$POLICE_STATION','$IO','$PHONE_IO','$BRIEF_FACTS','$DATE',GETDATE())";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=analysis_abstract.php");
?>