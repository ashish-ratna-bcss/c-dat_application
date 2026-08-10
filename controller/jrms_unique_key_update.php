<?php
// One page for both halves of this screen: the form, and the results.
// Was view/jrms_unique_key_update.htm (form) + controller/jrms_unique_key_update.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
</head>

<body bgcolor="#5195BA">
<div align="center">
<table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="140">
        <tr>
          <td width="1265" height="130" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">      </table>
      <p>&nbsp;</p>
      <table width="1021" height="157" align="center">
        <tr>
      <th height="27" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" >JRMS UNIQUE KEY UPDATION</th>
        </tr>
        
        <tr>
        <form id="form1" name="form1" method="post" action="jrms_unique_key_update.php">
                 <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" > JRMS CIN NO'S:            <style>
label textarea{
font: normal 15px courier;
vertical-align: middle;
}
</style>
<label> <textarea rows=2 cols=30 name='CIN_NO'  id="JRMS_UPDATE" placeholder="Enter Cin Number Seperated by comma without space Ex: 123xxx,124xxx,125xxx" required="required"></textarea></label>
Unique Key:
<input type="text" name="UNIQUE_KEY" id="NAME" size="10" required="required"/>
Irkey:
<input type="text" name="IRKEY" id="NAME" size="10" />
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$NUMBER1=$_POST['CIN_NO'];  
$NUMBER2=str_replace(",","','","$NUMBER1");
$UNIQUE_KEY=$_POST['UNIQUE_KEY'];     
$IRKEY=$_POST['IRKEY'];     


$sql="UPDATE JRMS_TOTAL_2012_TO_2017 SET UNIQUE_KEY='$UNIQUE_KEY', IRKEY='$IRKEY', ASONDATE=GETDATE(), APP_OR_MANUAL=  'APPLICATION_ENTRY'
WHERE CIN IN ('$NUMBER2')";
if(!sqlsrv_query($conn,$sql))
{
echo "Not Updated";
}
else
{
echo "Updated";
}
header("refresh:30; url=../view/jrms_unique_key_update.htm");
?>
<?php endif; ?>
</body>
</html>
