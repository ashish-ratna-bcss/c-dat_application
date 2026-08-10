<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY']; 
$RELATIONSHIP=$_POST['RELATIONSHIP']; 
$NAME=$_POST['NAME'];
$FATHER_OR_SPOUSE =$_POST['FATHER_OR_SPOUSE']; 
$OCCUPATION =$_POST['OCCUPATION']; 
$PHONE=$_POST['PHONE']; 
$AGE =$_POST['AGE']; 
$CRIMINAL_BACKGROUND =$_POST['CRIMINAL_BACKGROUND']; 
$STATUS =$_POST['STATUS'];
$PRESENT_ADDRESS =$_POST['PRESENT_ADDRESS'];
$PERMANENT_ADDRESS =$_POST['PERMANENT_ADDRESS'];
$sql="insert into FORMS.dbo.FAMILY_HISTORY 
(IRKEY,RELATIONSHIP, NAME, FATHER_OR_SPOUSE, OCCUPATION, PHONE, AGE, 
CRIMINAL_BACKGROUND, STATUS, PRESENT_ADDRESS, PERMANENT_ADDRESS
)values('$IRKEY','$RELATIONSHIP','$NAME','$FATHER_OR_SPOUSE','$OCCUPATION',
'$PHONE','$AGE','$CRIMINAL_BACKGROUND','$STATUS','$PRESENT_ADDRESS','$PERMANENT_ADDRESS')
";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=../view/family_history.html");
?>