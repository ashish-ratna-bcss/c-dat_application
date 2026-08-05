<?php
$serverName = "CPHYDERABAD1\DAU_HYD";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if(isset($_POST['insert']))
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
{
if(getimagesize($_FILES['image']['tmp_name'])==FALSE)
{
echo "please select an Image";
}
else
$image=addslashes($_FILES['image']['tmp_name']);
$image=file_get_contents($image);
$image=base64_encode($image);
$IRKEY=$_POST['IRKEY'];    
$CATEGORY=$_POST['CATEGORY'];    
$CCNO=$_POST['CCNO'];  
$sql="insert into FORMS.dbo.IMAGE_TABLE (IRKEY, CATEGORY, CCNO, IMAGE)
VALUES('$IRKEY','$CATEGORY','$CCNO','$image')";
$sql1="select IMAGE from FORMS.DBO.IMAGE_TABLE WHERE IRKEY='$IRKEY' AND CATEGORY='$CATEGORY'";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
if(sqlsrv_query($conn,$sql1))
$result=sqlsrv_query($conn,$sql1);
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) ) 
{
echo '<img height="300" width="300" src="'.cdat_base64_image_src($row['IMAGE']).'">';
}
}
?>