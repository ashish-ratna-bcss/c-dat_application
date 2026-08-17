<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the insert.
// Was DISPOSAL_OF_PROPERTY.HTML (form) + controller/disposal_of_property.php
// (handler); only the handler survived the move, so the menu link led to a
// page that tried to insert from an empty $_POST.
// POST only, unlike the search screens: an insert must never run off a link.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
layout_begin("Property Details");
cdat_sum_page_open();

cdat_sum_entry_card_open(
    'Disposal of Property',
    'Enter property disposal and recovery details.',
    'disposal_of_property.php'
);
?>
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
<?php
cdat_sum_entry_card_close('INSERT');

if ($__submitted):
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
cdat_sum_status_message('not inserted', false);
}
else
{
cdat_sum_status_message('inserted');
}
endif;

cdat_sum_page_close();
layout_end();
