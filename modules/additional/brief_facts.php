<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the insert.
// Was BRIEF_FACTS.html (form) + controller/brief_facts.php (handler); only the
// handler survived the move, so the menu link led to a page that tried to
// insert from an empty $_POST.
// POST only, unlike the search screens: an insert must never run off a link.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
layout_begin("Brief Facts");
cdat_sum_page_open();

cdat_sum_entry_card_open(
    'Brief Facts',
    'Enter brief facts for the interrogation report.',
    'brief_facts.php'
);
?>
<div>IRKEY:</div><input  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;">
     <br/><br/>
<div>BRIEF_FACTS1:</div><textarea type="text"  name="BRIEF_FACTS1" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS2:</div><textarea type="text" name="BRIEF_FACTS2" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS3:</div><textarea type="TEXT" name="BRIEF_FACTS3" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<?php
cdat_sum_entry_card_close('INSERT');

if ($__submitted):
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"forms");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$IRKEY=$_POST['IRKEY'];
$BRIEF_FACTS1=$_POST['BRIEF_FACTS1'];
$BRIEF_FACTS2=$_POST['BRIEF_FACTS2'];
$BRIEF_FACTS3=$_POST['BRIEF_FACTS3'];
$sql="insert into FORMS.dbo.BRIEF_FACTS
(IRKEY, BRIEF_FACTS1, BRIEF_FACTS2, BRIEF_FACTS3)
values('$IRKEY', '$BRIEF_FACTS1', '$BRIEF_FACTS2', '$BRIEF_FACTS3')";
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
