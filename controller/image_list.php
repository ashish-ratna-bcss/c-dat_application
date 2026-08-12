<?php
// One page for both halves of this screen: the form, and the results.
// Was view/image_list.html (form) + controller/image_list.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
layout_begin("Image List");
cdat_sum_page_open();

cdat_sum_entry_card_open(
    'Image List',
    'Upload and view images linked to an IR record.',
    'image_list.php',
    'post',
    'multipart/form-data'
);
?>
<div>IRKEY:</div><textarea type="text"  name="IRKEY"></textarea>
    <br/><br/>
<div>CATEGORY:</div><textarea type="text"  name="CATEGORY"></textarea>
    <br/><br/>
<div>CCNO:</div><textarea type="text"  name="CCNO"></textarea>
    <br/><br/>
<?= cdat_sum_field_image('image', 'Image') ?>
<?php
cdat_sum_entry_card_close('insert', 'insert');

if ($__submitted):
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
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
$IRKEY=$_POST['IRKEY'] ?? '';
$CATEGORY=$_POST['CATEGORY'] ?? '';
$CCNO=$_POST['CCNO'] ?? '';
if(!$hasImage)
{
cdat_sum_status_message('please select an Image', false);
}
else
{
$image=addslashes($_FILES['image']['tmp_name']);
$image=file_get_contents($image);
$image=base64_encode($image);
$sql="insert into FORMS.dbo.IMAGE_TABLE (IRKEY, CATEGORY, CCNO, IMAGE)
VALUES('$IRKEY','$CATEGORY','$CCNO','$image')";
if(!sqlsrv_query($conn,$sql))
{
cdat_sum_status_message('not inserted', false);
}
else
{
cdat_sum_status_message('inserted');
header("refresh:30; url=image_list.php");
}
}
$sql1="select IMAGE from FORMS.DBO.IMAGE_TABLE WHERE IRKEY='$IRKEY' AND CATEGORY='$CATEGORY'";
$result=sqlsrv_query($conn,$sql1);
if($result)
{
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) )
{
echo '<img height="300" width="300" src="'.cdat_base64_image_src($row['IMAGE']).'">';
}
}
}
endif;

cdat_sum_page_close();
layout_end();
