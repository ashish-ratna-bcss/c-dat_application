<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
{
// PHP 8 throws ValueError on getimagesize('') where PHP 7 returned false, so
// confirm a file actually arrived before inspecting it.
$hasImage = isset($_POST['insert'])
    && isset($_FILES['image'])
    && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    && getimagesize($_FILES['image']['tmp_name']) !== FALSE;
if(!$hasImage)
{
echo "please select an Image";
}
else
{
$image=addslashes($_FILES['image']['tmp_name']);
$image=file_get_contents($image);
$image=base64_encode($image);
$MO_KEY=$_POST['MO_KEY'] ?? '';
$sql="insert into CDATDUPL.dbo.MO_IMAGE_TABLE (MO_KEY, IMAGE)
VALUES('$MO_KEY','$image')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
}
$sql1="select IMAGE from CDATDUPL.DBO.MO_IMAGE_TABLE";
$result=sqlsrv_query($conn,$sql1);
if($result)
{
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) )
{
echo '<img height="300" width="300" src="'.cdat_base64_image_src($row['IMAGE']).'">';
}
}
}
?>
