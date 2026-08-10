<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY']; 
$GANG=$_POST['GANG']; 
$CATEGORY=$_POST['CATEGORY'];
$MEMBER=$_POST['MEMBER']; 
$FATHER_NAME=$_POST['FATHER_NAME']; 
$AGE=$_POST['AGE']; 
$OCCUPATION=$_POST['OCCUPATION']; 
$ADDRESS =$_POST['ADDRESS']; 
$PHONE =$_POST['PHONE'];
$RELATIONSHIP =$_POST['RELATIONSHIP'];
$REMARKS =$_POST['REMARKS']; 
$sql="insert into FORMS.dbo.RELATIONSHIP_WITH_OTHER_ASSOCIATES
(IRKEY, GANG, CATEGORY, MEMBER, FATHER_NAME, AGE, OCCUPATION,
ADDRESS, PHONE, RELATIONSHIP, REMARKS)
values('$IRKEY','$GANG','$CATEGORY','$MEMBER',
'$FATHER_NAME','$AGE','$OCCUPATION','$ADDRESS',
'$PHONE','$RELATIONSHIP','$REMARKS')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
header("refresh:30; url=relation_with_other_associates_and_gangs.php");
?>