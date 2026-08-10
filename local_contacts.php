<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$TOWN_CITY_OR_VILLAGE=$_POST['TOWN_CITY_OR_VILLAGE'];
$POLICE_STATION_LIMITS=$_POST['POLICE_STATION_LIMITS'];
$NAME=$_POST['NAME'];
$FATHER_NAME=$_POST['FATHER_NAME'];
$AGE=$_POST['AGE'];
$OCCUPATION=$_POST['OCCUPATION'];
$ADDRESS_OF_CONTACT_PERSON=$_POST['ADDRESS_OF_CONTACT_PERSON'];
$CRIME_NO=$_POST['CRIME_NO'];
$YEAR=$_POST['YEAR'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];
$POLICE_STATION=$_POST['POLICE_STATION'];
$PHONE=$_POST['PHONE'];
$sql="insert into FORMS.dbo.LOCAL_CONTACTS_FACILITATORS
(IRKEY, TOWN_CITY_OR_VILLAGE, POLICE_STATION_LIMITS, NAME, FATHER_NAME, 
AGE, OCCUPATION, ADDRESS_OF_CONTACT_PERSON, CRIME_NO, YEAR, SEC_OF_LAW, POLICE_STATION, PHONE)
values('$IRKEY','$TOWN_CITY_OR_VILLAGE','$POLICE_STATION_LIMITS','$NAME','$FATHER_NAME',
'$AGE','$OCCUPATION','$ADDRESS_OF_CONTACT_PERSON','$CRIME_NO','$YEAR',
'$SEC_OF_LAW','$POLICE_STATION','$PHONE')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=local_contacts.php");
?>