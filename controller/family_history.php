<?php
// One page for both halves of this screen: the form, and the results.
// Was view/family_history.html (form) + controller/family_history.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Family History');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'Family History',
    'Enter family member details for an IR record.',
    basename(__FILE__)
);
?>
<div>IRKEY:</div><textarea  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;" required="required"></textarea><br/><br/>
<div>RELATIONSHIP:</div><textarea type="text"  name="RELATIONSHIP" placeholder="RELATION" required="required"></textarea><br/><br/>
<div>NAME:</div><textarea type="text" name="NAME" placeholder="NAME" required="required"></textarea><br/><br/>
<div>FATHER_OR_SPOUSE:</div><textarea type="text" name="FATHER_OR_SPOUSE" required="required" placeholder="FATHER/SPOUSE"></textarea><br/><br/>
<div>OCCUPATION:</div><textarea type="text" name="OCCUPATION" required="required" placeholder="OCCUPATION"></textarea><br/><br/>
<div>PHONE:</div><textarea type="text" name="PHONE" placeholder="PHONE"></textarea><br/><br/>
<div>AGE:</div><textarea type="text" name="AGE" placeholder="AGE"></textarea><br/><br/>
<div>CRIMINAL_BACKGROUND:</div><textarea type="text" name="CRIMINAL_BACKGROUND" placeholder="CRIMINAL BACKGROUND"></textarea><br/><br/>
<div>STATUS:</div><textarea type="text" name="STATUS" placeholder="STATUS LIKE ALIVE OR EXPIRED"></textarea><br/><br/>
<div>PRESENT_ADDRESS:</div><textarea type="text" name="PRESENT_ADDRESS" required="required" placeholder="PRESENT ADDRESS"></textarea><br/><br/>
<div>PERMANENT_ADDRESS:</div><textarea type="text" name="PERMANENT_ADDRESS" placeholder="PERMANENT ADDRESS"></textarea><br/><br/>
<?php
cdat_sum_entry_card_close('insert');

if ($__submitted):
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY']; 
$RELATIONSHIP=$_POST['RELATIONSHIP']; 
$NAME=$_POST['NAME'];
$FATHER_OR_SPOUSE =$_POST['FATHER_OR_SPOUSE']; 
$OCCUPATION =$_POST['OCCUPATION']; 
$PHONE=$_POST['PHONE']; 
$AGE =$_POST['AGE']; 
$CRIMINAL_BACKGROUND =$_POST['CRIMINAL_BACKGROUND']; 
$STATUS =$_POST['STATUS'];
$PRESENT_ADDRESS =$_POST['PRESENT_ADDRESS'];
$PERMANENT_ADDRESS =$_POST['PERMANENT_ADDRESS'];
$sql="insert into FORMS.dbo.FAMILY_HISTORY 
(IRKEY,RELATIONSHIP, NAME, FATHER_OR_SPOUSE, OCCUPATION, PHONE, AGE, 
CRIMINAL_BACKGROUND, STATUS, PRESENT_ADDRESS, PERMANENT_ADDRESS
)values('$IRKEY','$RELATIONSHIP','$NAME','$FATHER_OR_SPOUSE','$OCCUPATION',
'$PHONE','$AGE','$CRIMINAL_BACKGROUND','$STATUS','$PRESENT_ADDRESS','$PERMANENT_ADDRESS')
";
if(!sqlsrv_query($conn,$sql))
{
cdat_sum_status_message('not inserted', false);
}
else
{
header("refresh:3; url=family_history.php");
cdat_sum_status_message('inserted', true);
}
endif;

cdat_sum_page_close();
layout_end();
