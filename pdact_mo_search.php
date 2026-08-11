<?php
// One page for both halves of this screen: the form, and the results.
// Was pdact_mo_search.htm (form) + pdact_mo_search.php (handler). The de-duplication kept only the
// handler, so opening this URL ran the query against an undefined $_POST
// key and drew a headings-only table with no box to type in.
// GET shows the form; a submit renders the form and the results below it.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
	$("#datepickerID").datepicker({dateFormat: "yyyy",
		
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
		<!----<li><a href="calls_tot.php">Call Details Total</a></li>-->
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!----<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>--->
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
                <li><a href="admin_activity_log.php">User Activity</a></li>
                <li><a href="admin_sql_console.php">SQL Query Console</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="862" height="158" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="IMAGES/border.jpg" scope="col">PDACT SEARCH BY MO</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="IMAGES/border.jpg" scope="col"><form id="form1" name="form1" method="POST" action="pdact_mo_search.php">
            <p>
            MO:
<input type="TEXT" name="MO" placeholder="PLZ ENTER THE MO">
 <input type="submit" value="Submit">

              </p>
          </form>
            <div align="justify">
              <table width="734" height="25">
                <tr>
                  <th width="40" scope="col">&nbsp;</th>
                  <th width="8" scope="col">&nbsp;</th>
                  <th width="79" scope="col">&nbsp;</th>
                  <th width="368" scope="col">&nbsp;</th>
                  </tr>
              </table>
            </div></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"PDACT");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['MO'];


$sql0="select distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI into #temp from PDACT_MAIN_TABLE WHERE (CRIME_HEAD LIKE '%$number%' OR MINOR_HEAD LIKE '%$number%'
OR MODUSOPERENDI LIKE '%$number%' OR CRIME_HEAD_SEARCH LIKE '%$number%')";

$sql1="select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";


$st0 = sqlsrv_query( $conn, $sql0 );
$st1 = sqlsrv_query( $conn, $sql1 );


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=1320px ><font size=3 face=verdana color='#F9FBFC'>ACCUSED INFORMATION</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PD_ACT_PS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_PDACT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'CRIME_HEAD</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center><a href=".'PDACT_MAIN.PHP?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT_KEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center><a href=".'IR.PHP?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td height=150px width=150px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_DISTRICT'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_STATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PD_ACT_PS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DATE_OF_PDACT'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";

echo "</tr>";
}
echo "</table>";


?>
<?php endif; ?>
</body>
</html>
