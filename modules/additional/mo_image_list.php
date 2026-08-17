<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the results.
// Was view/mo_image_list.html (form) + controller/mo_image_list.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
layout_begin("Mo Image List");
cdat_sum_page_open();

cdat_sum_entry_card_open(
    'MO Image List',
    'Upload and view MO-linked images.',
    'mo_image_list.php',
    'post',
    'multipart/form-data'
);
?>
<div>MO_KEY:</div><input type="text" name="MO_KEY" required="required" autocomplete="off" />
<br/><br/>
<?= cdat_sum_field_image('image', 'Image') ?>
<?php
cdat_sum_entry_card_close('insert', 'insert');

if ($__submitted):
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
cdat_sum_status_message('please select an Image', false);
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
cdat_sum_status_message('not inserted', false);
}
else
{
cdat_sum_status_message('inserted');
}
}
$sql1="select IMAGE from CDATDUPL.DBO.MO_IMAGE_TABLE";
$result=sqlsrv_query($conn,$sql1);
if($result)
{
echo '<div class="sum-image-gallery">';
while($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) )
{
echo '<img height="300" width="300" src="'.cdat_base64_image_src($row['IMAGE']).'">';
}
echo '</div>';
}
}
endif;

cdat_sum_page_close();
layout_end();
