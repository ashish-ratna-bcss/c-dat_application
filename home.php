<?php
// Login gate. The .htaccess extension fallback resolves every spelling of this
// page (.php / .html / .htm, any case) to this one file, so guarding it here
// protects all of them. Must run before any output, or the redirect is lost.
require_once __DIR__ . '/activity_logger.php';
audit_require_session();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="css_sparkle1.css">
<style type="text/css">
.FONT {
	color: #CFF;
	font-size: 24px;
	font-weight: bold;
	font-family: Verdana, Geneva, sans-serif;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
<tr>
  <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="home.php">Home</a>              </li>
            <li><a href="home.php" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="sum_in_state.html">Summary Within a State</a></li>
                <li><a href="sum_out_state.htm">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="home.php" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
		<li><a href="movements.html"> MOVEMENTS </a></li>
		<li><a href="movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
		<li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li>
                <!----<li><a href="calls_tot.php">Call Details Total</a></li>---->
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!-----<li><a href="calls_bt_nos.htm">Calls Between Two Numbers</a></li>--->
              </ul>
            </li>
            <li><a href="home.php" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="bulk_cdat_contacts.htm">Bulk Cdat Contacts</a></li>
		<li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="home.php" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.php">Phones used in Imei</a></li>
                <li><a href="imeisinphone.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="home.php" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="address.htm">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="day%26nightloc.html">Top 10 Day Night Loc</a></li>
                <li><a href="day%26nightloc_btwn_dates.html">Top 10 Day Night Loc Between Dates</a></li>
               </ul>
            </li>
                <li><a href="#" class="MenuBarItemSubmenu">Offenders List</a>
                  <ul>
                    <li><a href="habitual.php">Habitual Offenders List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="cellid_search.htm">Cellid Search</a></li>
                <li><a href="vehicle_search.html">Vehicle Search</a></li>
                <li><a href="common_cnts.php">Common Cnts</a></li>
                <li><a href="admin_activity_log.php">User Activity</a></li>
                <li><a href="admin_sql_console.php">SQL Query Console</a></li>
		<li><a href="tower_home.php">Tower Dump Reports (Under Development)</a></li>
		<li><a href="auth.html">IR FORMS</a></li>
		<li><a href="ir_search.htm">IR Form Search By Name</a></li>
		<li><a href="training_module1.htm">TRAININGS</a></li>
              </ul>
            </li>
            <!-- <li><a href="home_ir.php" style="color: #FFD700; font-weight: bold;">IR Home</a></li>
            <li><a href="logout.php" style="color: #FF6347; font-weight: bold;">Logout</a></li> -->
          </ul></td>
        </tr>
      </table>
<marquee behavior="scroll" direction="right"> <font color="YELLOW" face=verdana size='2'><b> *** PLEASE MAIL RAW DATA TO cdranalysiswing@gmail.com TO VIEW REPORTS *** </b></font></marquee> 
        <table width="1307" height="347">
          <tr>
            <td width="214" rowspan="2" valign="top">
              <blockquote>
                <ul class="MenuBarVertical">
          <li><a href="admin_upload.php">DATA UPLOAD</a></li>
          <li><a href="address.htm">Address Search</a></li>
          <li><a href="cdatcnts.php">Cdat Cnts</a></li>
          <li><a href="cellid_search.htm">Cellid Search</a></li>
          <li><a href="common_cnts.php">Common Cnts</a></li>
          <li><a href="imeisearch.php">IMEI Search</a></li>
          <li><a href="vehicle_search_criteria.htm">Vehicle Search</a></li>
	  <li><a href="ir_module.php" class="confirm_selection">Interrogation Report Search</a></li>
	  <li><a href="offender_search_by_mo.htm" class="confirm_selection">Offender Search By MO</a></li>
	  <li><a href="jrms_main_page1.php" class="confirm_selection">JRMS</a></li>
	  <li><a href="pdact_main_page_search.php" class="confirm_selection">PDACT</a></li>
	  <li><a href="rowdysheeter_ps_wise_search.php" class="confirm_selection">Rowdy Sheeter Search By PS</a></li>
          <li><a href="home_ir.php" class="confirm_selection">Interrogation Report Home</a></li>
          <li><a href="logout.php" class="confirm_selection">Logout</a></li>
        </ul>
        <p>&nbsp; </p>
      </blockquote></td>
            <td width="1015" height="24" align="left" valign="top"><p align="center" class="FONT">CALL DATA ANALYSIS TOOL</p></td>
            <td width="62" rowspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td height="310" align="left" valign="top"><div align="center"><img src="IMAGES/ANALYSIS1.jpg" width="950" height="250" /></div></td>
          </tr>

       </table>
     <table align="center"> <tr align="center">
<td width="1015" height="24" align="center" valign="top"><font size="6" font color="YELLOW">*** mail to <font color="#B22222">natgrid-hyd@tspolice.gov.in</font> for Suspect Image search ***</font></td>

<marquee behavior="scroll" direction="left"> <font face=verdana size='2' class="confirm_selection"><!-----<b> *** NOW YOU CAN VIEW UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINTS LIST  (ADDED IN OFFENDERS LIST TAB) *** </b></font></marquee>
<!-----<marquee behavior="scroll" direction="left"> <font face=verdana size='2' class="confirm_selection"><b> *** NOW YOU CAN VIEW HABITUAL OFFENDERS LIST  (ADDED IN OFFENDERS LIST TAB) *** </b></font></marquee>--> 
</tr>
</table>
            

<p>&nbsp;</p>
    
  <script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
var MenuBar2 = new Spry.Widget.MenuBar("MenuBar2", {imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});

</script>
</body>
</html>
