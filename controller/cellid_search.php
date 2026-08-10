<?php
// One page for both halves of this screen: the form, and the results.
// Was view/cellid_search.htm (form) + controller/cellid_search.php (handler).
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
<style type="text/css">
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
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
		<!----<li><a href="../controller/calls_tot.php">Call Details Total</a></li>---->
                <li><a href="../controller/calls_btwn_dates.php">Calls Between Dates</a></li>
                <!----<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>----->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="../controller/cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
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
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="1126" height="126" align="center">
        <tr>
          <th height="27" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">CELLID SEARCH</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="cellid_search.php">
            <label for="SUM" font face="verdana">CELLID:</label>
              <input type="text" name="CELLID" id="calls" placeholder="Enter Cellid" required="required"/>
              Operator : <select name="OPERATOR">
<option value=""></option>
<option value="AIRCEL_TOWER">AIRCEL_TOWER</option>
<option value="AIRTEL_TOWER">AIRTEL_TOWER</option>
<option value="BPL_TOWER">BPL_TOWER</option>
<option value="CELLONE_TOWER">CELLONE_TOWER</option>
<option value="ETISALAT_TOWER">ETISALAT_TOWER</option>
<option value="IDEA_TOWER">IDEA_TOWER</option>
<option value="JIO_TOWER">JIO_TOWER</option>
<option value="MTS_TOWER">MTS_TOWER</option>
<option value="RELIANCE_TOWER">RELIANCE_TOWER</option>
<option value="TATA_TOWER">TATA_TOWER</option>
<option value="UNINOR_TOWER">UNINOR_TOWER</option>
<option value="VIDEOCON_TOWER">VIDEOCON_TOWER</option>
<option value="VODAFONE_TOWER">VODAFONE_TOWER</option>
</select>
              State : 
<select name="STATE">
<option value=""></option>
<option value="ANDAMAN AND NICOBAR ISLANDS">ANDAMAN AND NICOBAR ISLANDS</option>
<option value="ANDHRA PRADESH">ANDHRA PRADESH</option>
<option value="ARUNACHAL PRADESH">ARUNACHAL PRADESH</option>
<option value="ASSAM">ASSAM</option>
<option value="BIHAR">BIHAR</option>
<option value="CHENNAI">CHENNAI</option>
<option value="CHHATTISGARH">CHHATTISGARH</option>
<option value="DELHI">DELHI</option>
<option value="GUJARAT">GUJARAT</option>
<option value="HARYANA">HARYANA</option>
<option value="HIMACHAL PRADESH">HIMACHAL PRADESH</option>
<option value="JAMMU_KASHMIR">JAMMU_KASHMIR</option>
<option value="JHARKHAND">JHARKHAND</option>
<option value="KARNATAKA">KARNATAKA</option>
<option value="KERALA">KERALA</option>
<option value="KOLKATA">KOLKATA</option>
<option value="MADHYA PRADESH">MADHYA PRADESH</option>
<option value="MAHARASHTRA">MAHARASHTRA</option>
<option value="MANIPUR">MANIPUR</option>
<option value="MEGHALAYA">MEGHALAYA</option>
<option value="MIZORAM">MIZORAM</option>
<option value="MUMBAI">MUMBAI</option>
<option value="NAGALAND">NAGALAND</option>
<option value="NORTH_EAST">NORTH_EAST</option>
<option value="ORISSA">ORISSA</option>
<option value="PUNJAB">PUNJAB</option>
<option value="RAJASTHAN">RAJASTHAN</option>
<option value="TAMILNADU">TAMILNADU</option>
<option value="TRIPURA">TRIPURA</option>
<option value="UP_EAST">UP_EAST</option>
<option value="UP_WEST">UP_WEST</option>
<option value="WEST BENGAL">WEST BENGAL</option>
</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
</b></b>
<?php echo $_POST['CELLID'] ?> />
Operator : <select name="OPERATOR">
<option value="<?php echo $_POST['OPERATOR'] ?>"><?php echo $_POST['OPERATOR'] ?></option>
<option value="AIRCEL_TOWER">AIRCEL_TOWER</option>
<option value="AIRTEL_TOWER">AIRTEL_TOWER</option>
<option value="BPL_TOWER">BPL_TOWER</option>
<option value="CELLONE_TOWER">CELLONE_TOWER</option>
<option value="ETISALAT_TOWER">ETISALAT_TOWER</option>
<option value="IDEA_TOWER">IDEA_TOWER</option>
<option value="JIO_TOWER">JIO_TOWER</option>
<option value="MTS_TOWER">MTS_TOWER</option>
<option value="RELIANCE_TOWER">RELIANCE_TOWER</option>
<option value="TATA_TOWER">TATA_TOWER</option>
<option value="UNINOR_TOWER">UNINOR_TOWER</option>
<option value="VIDEOCON_TOWER">VIDEOCON_TOWER</option>
<option value="VODAFONE_TOWER">VODAFONE_TOWER</option>
</select>

State: <select name="STATE">
<option value="<?php echo $_POST['STATE'] ?>"><?php echo $_POST['STATE'] ?></option>
<option value="ANDAMAN AND NICOBAR ISLANDS">ANDAMAN AND NICOBAR ISLANDS</option>
<option value="ANDHRA PRADESH">ANDHRA PRADESH</option>
<option value="ARUNACHAL PRADESH">ARUNACHAL PRADESH</option>
<option value="ASSAM">ASSAM</option>
<option value="BIHAR">BIHAR</option>
<option value="CHENNAI">CHENNAI</option>
<option value="CHHATTISGARH">CHHATTISGARH</option>
<option value="DELHI">DELHI</option>
<option value="GUJARAT">GUJARAT</option>
<option value="HARYANA">HARYANA</option>
<option value="HIMACHAL PRADESH">HIMACHAL PRADESH</option>
<option value="JAMMU_KASHMIR">JAMMU_KASHMIR</option>
<option value="JHARKHAND">JHARKHAND</option>
<option value="KARNATAKA">KARNATAKA</option>
<option value="KERALA">KERALA</option>
<option value="KOLKATA">KOLKATA</option>
<option value="MADHYA PRADESH">MADHYA PRADESH</option>
<option value="MAHARASHTRA">MAHARASHTRA</option>
<option value="MANIPUR">MANIPUR</option>
<option value="MEGHALAYA">MEGHALAYA</option>
<option value="MIZORAM">MIZORAM</option>
<option value="MUMBAI">MUMBAI</option>
<option value="NAGALAND">NAGALAND</option>
<option value="NORTH_EAST">NORTH_EAST</option>
<option value="ORISSA">ORISSA</option>
<option value="PUNJAB">PUNJAB</option>
<option value="RAJASTHAN">RAJASTHAN</option>
<option value="TAMILNADU">TAMILNADU</option>
<option value="TRIPURA">TRIPURA</option>
<option value="UP_EAST">UP_EAST</option>
<option value="UP_WEST">UP_WEST</option>
<option value="WEST BENGAL">WEST BENGAL</option>
</select>




<input type ="submit" value ="submit">
<p> </p>

<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$cellid 	= trim($_POST['CELLID'] ?? '');
$operator	= trim($_POST['OPERATOR'] ?? '');
$state		= trim($_POST['STATE'] ?? '');

$cellidEsc = str_replace("'", "''", $cellid);
$likePattern = (strpos($cellid, '%') !== false || strpos($cellid, '_') !== false)
    ? $cellidEsc
    : $cellidEsc . '%';
$opFilter = $operator !== '' ? "AND OPERATOR='".str_replace("'", "''", $operator)."'" : '';
$stateFilter = $state !== '' ? "AND STATE='".str_replace("'", "''", $state)."'" : '';

$sql1 ="select DISTINCT CELLTOWERID,BTS_ID,AREADESCRIPTION,SITEADDRESS,LAT,LONG,AZIMUTH,OPERATOR,STATE, OTYPE 
from cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE CELLTOWERID LIKE '{$likePattern}' {$opFilter} {$stateFilter}
ORDER BY LASTUPDATE DESC";

$st1 = sqlsrv_query( $conn, $sql1 );

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BTS_ID</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SITE ADDRESS</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZIMUTH</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTYPE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>qrcode</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['BTS_ID'] ."<center></font></td>";
echo "<td width=200px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['SITEADDRESS'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AZIMUTH'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OPERATOR'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['STATE'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTYPE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d='.'CELLTOWERID: '.$row["CELLTOWERID"].' SITEADDRESS:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["SITEADDRESS"]).' LAT:'.$row["LAT"].' '.'LONG:'.$row["LONG"].' AZIMUTH: '.$row["AZIMUTH"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
<?php endif; ?>
</body>
</html>
