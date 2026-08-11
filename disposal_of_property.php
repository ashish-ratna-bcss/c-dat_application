<?php
// One page for both halves of this screen: the form, and the insert.
// Was DISPOSAL_OF_PROPERTY.HTML (form) + disposal_of_property.PHP (handler).
// The de-duplication kept only the handler, so opening this URL ran the INSERT
// against an empty $_POST and wrote a blank row.
// POST only: an insert must never run just because someone opened the page.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<html>
<head>
<title> DISPOSAL OF PROPERTY FORM </title>
<meta charset="utf-8">
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
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
.result{
clear:both;
width:auto;
float:none;
display:inline-block;
background-color:#921215;
color:#fff;
font:bold 16px verdana;
padding:8px 14px;
}
</style>
</head>
<body bgcolor="#80800">
<table>
        <tr>
          <th width="1126" align="center" scope="col">DISPOSAL OF PROPERTY</th>
        </tr>
</table>
<form action="disposal_of_property.php" Method="post">
<div>IRKEY:</div><textarea  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;"></textarea>
     <br/><br/>
<div>PROPERTY_STOLEN:</div><textarea type="text" name="PROPERTY_STOLEN" placeholder="PROPERTY STOLEN"></textarea>
     <br/><br/>
<div>PROPERTY_RECOVERED:</div><textarea type="text" name="PROPERTY_RECOVERED" placeholder="PROPERTY_RECOVERED"></textarea>
<br/><br/>
<div>RECEIVER_NAME:</div><textarea type="text" name="RECEIVER_NAME" placeholder="RECEIVER_NAME"></textarea>
     <br/><br/>
<div>RECEIVER_ADDRESS:</div><textarea type="text" name="RECEIVER_ADDRESS" placeholder="RECEIVER_ADDRESS"></textarea>
<br/><br/>
<div>HOW_SHARE_IS_SPENT:</div><textarea type="text" name="HOW_SHARE_IS_SPENT" placeholder="HOW_SHARE_IS_SPENT"></textarea>
<br/><br/>
<div>REMARKS:</div><textarea type="text" name="REMARKS" placeholder="REMARKS"></textarea>
<br/><br/>
<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" placeholder="CRIME_NO"></textarea>
     <br/><br/>
<div>YEAR:</div><textarea type="text" name="YEAR" placeholder="YEAR"></textarea>
<br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" placeholder="POLICE_STATION"></textarea>
<br/><br/>
	<input type="submit" value="INSERT" style="padding:15px;">
    <br/><br/>

</form>

<?php if ($__submitted): ?>
<p class="result">
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$PROPERTY_STOLEN=$_POST['PROPERTY_STOLEN'];
$PROPERTY_RECOVERED=$_POST['PROPERTY_RECOVERED'];
$RECEIVER_NAME=$_POST['RECEIVER_NAME'];
$RECEIVER_ADDRESS=$_POST['RECEIVER_ADDRESS'];
$HOW_SHARE_IS_SPENT=$_POST['HOW_SHARE_IS_SPENT'];
$REMARKS=$_POST['REMARKS'];
$CRIME_NO=$_POST['CRIME_NO'];
$YEAR=$_POST['YEAR'];
$POLICE_STATION=$_POST['POLICE_STATION'];
$sql="insert into FORMS.dbo.DISPOSAL_OF_PROPERTY
(IRKEY, PROPERTY_STOLEN, PROPERTY_RECOVERED, RECEIVER_NAME, RECEIVER_ADDRESS, HOW_SHARE_IS_SPENT, REMARKS,CRIME_NO,YEAR,POLICE_STATION)
values('$IRKEY', '$PROPERTY_STOLEN', '$PROPERTY_RECOVERED', '$RECEIVER_NAME', '$RECEIVER_ADDRESS',
'$HOW_SHARE_IS_SPENT', '$REMARKS','$CRIME_NO','$YEAR','$POLICE_STATION')";
if(!sqlsrv_query($conn,$sql))
{
echo "not inserted";
}
else
{
echo "inserted";
}
// The old "refresh:30" bounce back to the form is gone: the form is on this
// page now, and header() after output only raises a warning.
?>
</p>
<?php endif; ?>
</body>
</html>
