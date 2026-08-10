<?php
// One page for both halves of this screen: the form, and the results.
// Was view/caf_search.html (form) + controller/caf_search.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Caf Search");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="625" height="96">
        <tr>
          <th height="23" bgcolor="#A9D1F5" class="CDAT" scope="col">CAF SEARCH</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="caf_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> MOBILE NO:            <label for="textfield"></label>
            <input type="text" name="PHONE_NO" id="CAF" placeholder="Enter Mobile No" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>


<?php if ($__submitted): ?>
<style type="text/css">
a:link , a:visited{
text-decoration: none;
}
</style>
<?php
$serverName = "UUUU-HP";
$connectionInfo = array( "Database"=>"CAFs");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];

$sql1 ="SELECT 'ftp://192.168.144.70/'+substring(CAFS_PATH,24,50) AS PHONE INTO #T FROM IO_DETAILS WHERE PHONE='$number'";

$sql2 ="UPDATE #T SET PHONE = REPLACE(PHONE,' ','%20')";

$sql3 = "SELECT DISTINCT PHONE,'CAF Available Click Here to Open' as CLICK FROM #T";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );


if ( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {  

echo "<font size=4 face=verdana color='#F9FBFC'><blink><a style='color:#F9FBFC' href=".($row['PHONE']).">".($row['CLICK'])."</a>"; }
else{
echo "<font size=4 face=verdana color='#F9FBFC'><blink>CAF NOT AVAILABLE";
}


sqlsrv_free_stmt( $st3);
?>
<?php endif; ?>
<?php layout_end(); ?>
