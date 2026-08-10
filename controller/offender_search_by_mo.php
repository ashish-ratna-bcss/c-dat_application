<?php
// One page for both halves of this screen: the form, and the results.
// Was view/offender_search_by_mo.htm (form) + controller/offender_search_by_mo.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Offender Search By MO");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg">
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="800" height="100" align=center>
        <tr>
          <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER SEARCH BY SUB CLASSIFICATION</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="offender_search_by_mo.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> MO SUB CLASSIFICATION:            <label for="textfield"></label>
            <input type="text" name="MO" id="NAME" placeholder="SUB CLASSIFICATION" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>


<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['MO'])){

$number=$_POST['MO'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT MO_KEY,ACC_NAME AS ACCUSED_NAME,FATHER_NAME,AGE,MO1,MO2,POLICE_STATION FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION
WHERE (MO1 LIKE '%'+REPLACE('$number',' ','%')+'%' OR MO2 LIKE '%'+REPLACE('$number',' ','%')+'%' OR CRIME_HEAD LIKE '%'+REPLACE('$number',' ','%')+'%' )";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_SUB_CLASSIFICATION1</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_SUB_CLASSIFICATION2</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'offender_fd.php?MO_KEY='.($row['MO_KEY']).">". $row['MO_KEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ACCUSED_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO1'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO2'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>

dy>
<?php endif; ?>
<?php layout_end(); ?>
