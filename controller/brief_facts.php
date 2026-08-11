<?php
// One page for both halves of this screen: the form, and the insert.
// Was BRIEF_FACTS.html (form) + controller/brief_facts.php (handler); only the
// handler survived the move, so the menu link led to a page that tried to
// insert from an empty $_POST.
// POST only, unlike the search screens: an insert must never run off a link.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Brief Facts");
?>

<table>
        <tr>
          <th width="1126" align="center" scope="col">BRIEF FACTS</th>
        </tr>
</table>
<form action="brief_facts.php" Method="post">
<div>IRKEY:</div><input  type="text" name="IRKEY" placeholder="IRKEY" style="float:center;">
     <br/><br/>
<div>BRIEF_FACTS1:</div><textarea type="text"  name="BRIEF_FACTS1" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS2:</div><textarea type="text" name="BRIEF_FACTS2" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<div>BRIEF_FACTS3:</div><textarea type="TEXT" name="BRIEF_FACTS3" placeholder="BRIEF_FACTS"></textarea>
<br/><br/>
<input type="submit" value="INSERT" style="padding:15px;">
    <br/><br/>

</form>

<?php if ($__submitted): ?>
<?php
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
