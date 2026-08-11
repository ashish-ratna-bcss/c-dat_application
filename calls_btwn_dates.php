<?php
// One page for both halves of this screen: the form, and the results.
// Was calls_btwn_dates.htm (form) + calls_btwn_dates.php (handler). The de-duplication kept only the
// handler, so opening this URL ran the query against an undefined $_POST
// key and drew a headings-only table with no box to type in.
// GET shows the form; a submit renders the form and the results below it.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript">
$("document").ready(function() {
	$("#datepickerID").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	}) 
	$("#datepickerID1").datepicker({dateFormat: "yy-mm-dd",
		changeYear: true,
		changeMonth: true,
	})    
	
});
</script>
<script src="SpryAssets/sprymenubar.js" type="text/javascript"></script>
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
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
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.html">Summary Within a State</a></li>
                <li><a href="sum_out_state.php">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="movements.html"> MOVEMENTS </a></li>
		<li><a href="movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
		<li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
		<!-----<li><a href="calls_tot.php">Call Details Total</a></li>---->
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!----<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="bulk_cdat_contacts.php">Bulk Cdat Contacts</a></li>
		<li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.php">Phones used in Imei</a></li>
                <li><a href="imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.php">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
                  </ul>
                </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="vehicle_search.php">Vehicle Search</a></li>
                <li><a href="common_cnts.php">Common Cnts</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="1021" height="157" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="IMAGES/border.jpg" scope="col">CALL DETAILS OF MOBILE NUMBER BETWEEN DATES</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="IMAGES/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="calls_btwn_dates.php">
            <p>
              <label for="SUM" font face="verdana"> Call Details Of Mobile No:</label>
              <input type="text" name="PHONE_NO" id="calls" placeholder="Enter Mobile No" required="required"/>
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
            </p>
              Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
              To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
              
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>

<?php if ($__submitted): ?>
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
audit_require_session();
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
$operator= sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
$state	= sql_safe_alnum($_POST['STATE'] ?? '', 50);
$f_date = sql_safe_alnum($_POST['FROM_DT'] ?? '', 10);
$t_date = sql_safe_alnum($_POST['TO_DT'] ?? '', 10);
audit_log('Call Details Between Dates', 'Search', ['phone_number' => $number, 'from_date' => $f_date, 'state' => $state, 'operator' => $operator]);

$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND convert(char(10),STARTTIME,121) BETWEEN '$f_date' AND '$t_date'";

$sql2 = cdr_sql_enrich_tt($operator, $state);

$sql5="SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION from #temp_cdrs  ORDER BY STARTTIME";



$sql6="SELECT 'CALL DETAILS OF MOBILE NO: '+'$number'+' FROM: '+'$f_date'+' TO: '+'$t_date' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'Calls between dates base');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'Tower enrichment');
$st5 = sqlsrv_query( $conn, $sql5 );
sqlsrv_render_query_error($st5, 'Result ordering');
$st6 = sqlsrv_query( $conn, $sql6 );
sqlsrv_render_query_error($st6, 'Title');

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". h($row['PHONE'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NICK NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>IMEI</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CELLID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['PHONE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OTHER'] ?? '') ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['NICKNAME'] ?? '') ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['STARTTIME'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['DURATION'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['TYPE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['IMEINUMBER'] ?? '') ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['CELLTOWERID'] ?? '') ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". h($row['OPERATOR'] ?? '') ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['AREADESCRIPTION'] ?? '') ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
<?php endif; ?>
</body>
</html>
