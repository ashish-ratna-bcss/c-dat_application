<?php
// One page for both halves of this screen: the form, and the results.
// Was view/name_search.htm (form) + controller/name_search.php (handler).
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
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
           <li><a href="../controller/home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="../controller/sum_home.php">Summary Total</a></li>
                <li><a href="../controller/sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="../controller/sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="../controller/sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.php">Summary Within a State</a></li>
                <li><a href="sum_out_state.php">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="movements.php"> MOVEMENTS </a></li>
		<li><a href="movements_between_two_numbers.php">Movements Btwn Two Nos</a></li>
		<li><a href="../controller/movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
		<li><a href="../controller/calls_tot.php">Call Details Total</a></li>
                <li><a href="../controller/calls_btwn_dates.php">Calls Between Dates</a></li>
                <li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="../controller/cdatcnts.php">Cdat Cnts</a></li>
                <li><a href="../controller/otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="../controller/imeisearch.php">Phones used in Imei</a></li>
                <li><a href="../controller/imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.php">Single Address</a></li>
                <li><a href="../controller/bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="../view/day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="../view/day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="../controller/wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
               <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="vehicle_search.php">Vehicle Search</a></li>
                <li><a href="../controller/common_cnts.php">Common Cnts</a></li>
                <li><a href="../controller/admin_activity_log.php">User Activity</a></li>
                <li><a href="../controller/admin_sql_console.php">SQL Query Console</a></li>
                </ul>
            </li>
                </ul>
                </td>
        </tr>
       <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="800" height="100">
        <tr>
          <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER SEARCH BY NAME</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="name_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> NAME OF THE OFFENDER:            <label for="textfield"></label>
            <input type="text" name="NAME" id="NAME" placeholder="Enter NAME" required="required"/>
	CRIME HEAD:            	<label for="textfield"></label>
            <input type="text" name="CRIME_HEAD" id="CRIME_HEAD" placeholder="Enter CRIME HEAD" required="required"/>
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
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['NAME'])){

$number=$_POST['NAME'];
$number1=$_POST['CRIME_HEAD'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT NICKNAME ACCUSED_NAME,ROLE,FNAME,ADDRESS,STATE,CRIME_NO,YEAR,SEC_OF_LAW,UNIT,CRIME_HEAD,MO,ORGANISATION 
FROM CDATDUPL..CDATSUSPECT WHERE NICKNAME LIKE '%'+REPLACE('$number',' ','%')+'%' AND CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' AND 
ltrim(rtrim('$number'))!='' and len(replace('$number',' ',''))>'5'";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ROLE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEC_OF_LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>UNIT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ORGANISATION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ACCUSED_NAME'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ROLE'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['YEAR'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['UNIT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ORGANISATION'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>

dy>
<?php endif; ?>
</body>
</html>
