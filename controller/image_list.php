<?php
// One page for both halves of this screen: the form, and the results.
// Was view/image_list.html (form) + controller/image_list.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<html>
<head>
<title> IMAGE FORM </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$("document").ready(function() {
	$("#datepickerID").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	}) 
	$("#datepickerID1").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})    
	
});
</script>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="../assets/css/style.css" >
<style>
input[type=text], select {
    width: 25%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 15%;
    background-color: ORANGE;
    color: white;
    padding: 4px 15px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 25px;
}

div {
    border-radius: 5px;
    background-color: #f2f2f2;
    padding: 5px;
    width: 40%;
    border-radius: 4px;
    float:left;
    width:200px;
    text-align: left;
        }
       textarea{
       width: 25%;
       height: 25px;
       }
</style>
<style type="test/css">
div{
float:left;
width:200px;
text-align=left;
}
</style>
</head>
<body bgcolor="#80800">
<table>
    <tr>
    <th width="1126" align="center" scope="col">IMAGE LIST</th>
    </tr>
</table>
<form action="image_list.php" Method="post" enctype="multipart/form-data">
<div>IRKEY:</div><textarea type="text"  name="IRKEY"></textarea>
    <br/><br/>
<div>CATEGORY:</div><textarea type="text"  name="CATEGORY"></textarea>
    <br/><br/>
<div>CCNO:</div><textarea type="text"  name="CCNO"></textarea>
    <br/><br/>
<input type="file" name="image"/>
	<br/><br/>
<input type="submit" value="insert">
    <br/><br/>
     </form>

<?php if ($__submitted): ?>
<?php
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
echo "please select an Image";
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
echo "not inserted";
}
else
{
echo "inserted";
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
?>
<?php endif; ?>
</body>
</html>