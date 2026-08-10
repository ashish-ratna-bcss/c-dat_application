<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
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
$CRIME_NOS=$_POST['CRIME_NOS'];
$PHONE=$_POST['PHONE'];
$DISTRICT=$_POST['DISTRICT'];
$DOB_DT=$_POST['DOB_DT'];
$IDPROOF_TYPE=$_POST['IDPROOF_TYPE'];
$IDPROOF_NO=$_POST['IDPROOF_NO'];
$SEC_OF_LAW=$_POST['SEC_OF_LAW'];

$sql="set dateformat mdy insert into JRMS..JRMS_TOTAL_2012_TO_2017 (CIN,PSArrested, Name, FathersName, Gender, PrisonerNo, TypeofRelease, JailName, 
Admission_to_Jail, ReleaseDt, Addr_DuringRelease, HeadofCrime, 
IdentificationMark, PlaceofIdentificationMark, CrimeNos, 
MobileNo, DISTRICT, ASONDATE,DOB_AGE,IDPROOF_TYPE,IDPROOF_NO,SEC_OF_LAW,REMARKS)
VALUES ((SELECT DISTINCT MAX(CIN)+1 FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE REMARKS LIKE '%JRMS_ENTRY_FORM%'),'$PSARRESTED','$NAME','$FATHER_NAME','$GENDER','$PRISONERNO','$TYPEOFRELEASE',
'$JAILNAME',convert(varchar(10),cast('$ADD_DT' as date),103),convert(varchar(10),cast('$REL_DT' as date),103),'$ADD_DUR_REL','$HEADOFCRIME','$IDENTIFICATIONMARK',
'$PLACE_OF_MARK','$CRIME_NOS','$PHONE','$DISTRICT',GETDATE(),'$DOB_DT','$IDPROOF_TYPE','$IDPROOF_NO','$SEC_OF_LAW','JRMS_ENTRY_FORM')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/irreport.html");
?>