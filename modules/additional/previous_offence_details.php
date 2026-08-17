<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the results.
// Was view/previous_offence_details.htm (form) + controller/previous_offence_details.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Previous Offence Details');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'Previous Offence Details',
    'Enter previous offence details for an IR record.',
    basename(__FILE__)
);
?>
<div>IRKEY:</div><textarea  type="text" name="IRKEY" required="required" placeholder="IRKEY" style="float:center;"></textarea><br/><br/>
<div>DISTRICT:</div><textarea type="text"  required="required" name="DISTRICT" placeholder="DISTRICT"></textarea><br/><br/>
<div>CONFESSED_POLICE_STATION:</div><textarea type="text" required="required" name="CONFESSED_POLICE_STATION" placeholder="CONFESSED_PS"></textarea><br/><br/>
<div>CONFESSED_CRIME_NO:</div><textarea type="text" required="required" name="CONFESSED_CRIME_NO" placeholder="CONFESSED_CRIME_NO"></textarea><br/><br/>
<div>CONFESSED_YEAR:</div><textarea type="text" required="required" name="CONFESSED_YEAR" placeholder="CONFESSED_YEAR"></textarea><br/><br/>
<div>CONFESSED_SEC_OF_LAW:</div><textarea type="text" required="required" name="CONFESSED_SEC_OF_LAW" placeholder="SEC OF LAW"></textarea><br/><br/>

<div>CONFESSED_DATE_OF_ARREST:</div><input type="TEXT" name="DATE" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/><br/><br/>
<div>ASSOCIATES:</div><textarea type="text" name="ASSOCIATES" placeholder="ASSOCIATES"></textarea><br/><br/>
<div>PROPERTY_STOLEN:</div><textarea type="text" name="PROPERTY_STOLEN" placeholder="PROPERTY STOLEN"></textarea><br/><br/>
<div>PROPERTY_RECOVERED:</div><textarea type="text" name="PROPERTY_RECOVERED" placeholder="PROPERTY_RECOVERED"></textarea><br/><br/>
<div>REMARKS:</div><textarea type="text" name="REMARKS" placeholder="REMARKS"></textarea><br/><br/>
<div>CRIME_NO:</div><textarea type="text" name="CRIME_NO" placeholder="CRIME_NO"></textarea><br/><br/>
<div>YEAR:</div><textarea type="text" name="YEAR" placeholder="YEAR"></textarea><br/><br/>
<div>POLICE_STATION:</div><textarea type="text" name="POLICE_STATION" placeholder="POLICE_STATION"></textarea><br/><br/>
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
$DISTRICT=$_POST['DISTRICT']; 
$CONFESSED_POLICE_STATION=$_POST['CONFESSED_POLICE_STATION'];
$CONFESSED_CRIME_NO =$_POST['CONFESSED_CRIME_NO']; 
$CONFESSED_YEAR =$_POST['CONFESSED_YEAR']; 
$CONFESSED_SEC_OF_LAW=$_POST['CONFESSED_SEC_OF_LAW']; 
$CONFESSED_DATE_OF_ARREST=$_POST['DATE']; 
$ASSOCIATES =$_POST['ASSOCIATES']; 
$PROPERTY_STOLEN =$_POST['PROPERTY_STOLEN']; 
$PROPERTY_RECOVERED =$_POST['PROPERTY_RECOVERED'];
$REMARKS =$_POST['REMARKS'];
$CRIME_NO =$_POST['CRIME_NO']; 
$YEAR =$_POST['YEAR'];
$POLICE_STATION =$_POST['POLICE_STATION'];  
$sql="insert into FORMS.dbo.PREVIOUS_OFFENCE_DETAILS 
(IRKEY, DISTRICT, CONFESSED_POLICE_STATION, CONFESSED_CRIME_NO, CONFESSED_YEAR, 
CONFESSED_SEC_OF_LAW,CONFESSED_DOA, ASSOCIATES, PROPERTY_STOLEN, PROPERTY_RECOVERED, REMARKS, 
CRIME_NO, YEAR, POLICE_STATION)
values('$IRKEY','$DISTRICT','$CONFESSED_POLICE_STATION','$CONFESSED_CRIME_NO','$CONFESSED_YEAR',
'$CONFESSED_SEC_OF_LAW','$CONFESSED_DATE_OF_ARREST','$ASSOCIATES','$PROPERTY_STOLEN','$PROPERTY_RECOVERED','$REMARKS',
'$CRIME_NO','$YEAR','$POLICE_STATION')";
if(!sqlsrv_query($conn,$sql))
{
cdat_sum_status_message('not inserted', false);
}
else
{
header("refresh:3; url=previous_offence_details.php");
cdat_sum_status_message('inserted', true);
}
endif;

cdat_sum_page_close();
layout_end();
