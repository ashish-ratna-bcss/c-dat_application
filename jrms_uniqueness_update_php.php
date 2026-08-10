<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$AUTO_KEY=$_POST['AUTO_KEY'];
$PSARRESTED=$_POST['PSARRESTED']; 
$NAME=$_POST['NAME'];
$FATHER_NAME=$_POST['FATHER_NAME'];
$GENDER=$_POST['GENDER'];
$PRISONERNO=$_POST['PRISONERNO'];
$TYPEOFRELEASE=$_POST['TYPEOFRELEASE'];  
$JAILNAME=$_POST['JAILNAME'];
$ADD_DT=$_POST['ADD_DT'];
$REL_DT=$_POST['REL_DT'];
$ADD_DUR_REL=$_POST['ADD_DUR_REL'];
$HEADOFCRIME=$_POST['HEADOFCRIME'];
$IDENTIFICATIONMARK=$_POST['IDENTIFICATIONMARK'];
$PLACE_OF_MARK=$_POST['PLACE_OF_MARK'];
$RELEASE_DT_ORDER=$_POST['RELEASE_DT_ORDER'];
$CRIME_NOS=$_POST['CRIME_NOS'];
$PHONE=$_POST['PHONE'];
$JAILREFID=$_POST['JAILREFID'];
$DISTRICT=$_POST['DISTRICT'];
$DOB_DT=$_POST['DOB_DT'];
$IDPROOF_TYPE=$_POST['IDPROOF_TYPE'];
$IDPROOF_NO=$_POST['IDPROOF_NO'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];

$sql="update jrms.dbo.JRMS_TOTAL_2012_TO_2017
set PSArrested ='$PSARRESTED',
Name ='$NAME',
FathersName ='$FATHER_NAME',
Gender ='$GENDER',
PrisonerNo ='$PRISONERNO',
TypeofRelease='$TYPEOFRELEASE',
JailName ='$JAILNAME',
Admission_to_Jail =convert(varchar(10),cast('$ADD_DT' as date),103),
ReleaseDt =convert(varchar(10),cast('$REL_DT' as date),103),
Addr_DuringRelease ='$ADD_DUR_REL',
HeadofCrime ='$HEADOFCRIME',
IdentificationMark ='$IDENTIFICATIONMARK',
PlaceofIdentificationMark ='$PLACE_OF_MARK',
RlDtOrder ='$RELEASE_DT_ORDER',
CrimeNos ='$CRIME_NOS',
MobileNo ='$PHONE',
JailRefId ='$JAILREFID',
DISTRICT ='$DISTRICT',
DOB_AGE ='$DOB_DT',
IDPROOF_TYPE ='$IDPROOF_TYPE',
IDPROOF_NO ='$IDPROOF_NO',
SEC_OF_LAW ='$SEC_OF_LAW'
WHERE AUTO_KEY='$AUTO_KEY'";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "RECORD HAS BEEN UPDATED";
}
header("refresh:30; url=irreport.html");
?>