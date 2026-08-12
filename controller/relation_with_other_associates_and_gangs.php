<?php
// One page for both halves of this screen: the form, and the results.
// Was view/relation_with_other_associates_and_gangs.htm (form) + controller/relation_with_other_associates_and_gangs.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
layout_begin("Relation With Other Associates And Gangs");
cdat_sum_page_open();

cdat_sum_entry_card_open(
    'Relation With Other Associates',
    'Enter associate and gang relationship details.',
    'relation_with_other_associates_and_gangs.php'
);
?>
<div>IRKEY:</div><textarea  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;"></textarea><br/><br/>
<div>GANG:</div><textarea type="text"  name="GANG" placeholder="GANG"></textarea><br/><br/>
<div>CATEGORY:</div><textarea type="text" name="CATEGORY" placeholder="CATEGORY"></textarea><br/><br/>
<div>MEMBER:</div><textarea type="text" name="MEMBER" placeholder="MEMBER"></textarea><br/><br/>
<div>FATHER_NAME:</div><textarea type="text" name="FATHER_NAME" placeholder="FATHER_NAME"></textarea><br/><br/>
<div>AGE:</div><textarea type="text" name="AGE" placeholder="AGE"></textarea><br/><br/>
<div>OCCUPATION:</div><textarea type="text" name="OCCUPATION" placeholder="OCCUPATION"></textarea><br/><br/>
<div>ADDRESS:</div><textarea type="text" name="ADDRESS" placeholder="ADDRESS"></textarea><br/><br/>
<div>PHONE:</div><textarea type="text" name="PHONE" placeholder="PHONE"></textarea><br/><br/>
<div>RELATIONSHIP:</div><textarea type="text" name="RELATIONSHIP" placeholder="RELATIONSHIP"></textarea><br/><br/>
<div>REMARKS:</div><textarea type="text" name="REMARKS" placeholder="REMARKS"></textarea><br/><br/>
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
$GANG=$_POST['GANG']; 
$CATEGORY=$_POST['CATEGORY'];
$MEMBER=$_POST['MEMBER']; 
$FATHER_NAME=$_POST['FATHER_NAME']; 
$AGE=$_POST['AGE']; 
$OCCUPATION=$_POST['OCCUPATION']; 
$ADDRESS =$_POST['ADDRESS']; 
$PHONE =$_POST['PHONE'];
$RELATIONSHIP =$_POST['RELATIONSHIP'];
$REMARKS =$_POST['REMARKS']; 
$sql="insert into FORMS.dbo.RELATIONSHIP_WITH_OTHER_ASSOCIATES
(IRKEY, GANG, CATEGORY, MEMBER, FATHER_NAME, AGE, OCCUPATION,
ADDRESS, PHONE, RELATIONSHIP, REMARKS)
values('$IRKEY','$GANG','$CATEGORY','$MEMBER',
'$FATHER_NAME','$AGE','$OCCUPATION','$ADDRESS',
'$PHONE','$RELATIONSHIP','$REMARKS')";
if(!sqlsrv_query($conn,$sql))
{
cdat_sum_status_message('not inserted', false);
}
else
{
cdat_sum_status_message('inserted');
header("refresh:30; url=relation_with_other_associates_and_gangs.php");
}
endif;

cdat_sum_page_close();
layout_end();
