<?php
// One page for both halves of this screen: the form, and the insert.
// Was DISPOSAL_OF_PROPERTY.HTML (form) + controller/disposal_of_property.php
// (handler); only the handler survived the move, so the menu link led to a
// page that tried to insert from an empty $_POST.
// POST only, unlike the search screens: an insert must never run off a link.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Property Details");
?>

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
<?php endif; ?>
<?php layout_end(); ?>
