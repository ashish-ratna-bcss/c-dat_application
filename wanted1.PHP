<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.FONT {
	font-family: "Lucida Console", Monaco, monospace;
	font-weight: bold;
	color: #CFF;
	font-size: 24px;
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
            <li><a href="HOME.PHP">Home</a>              </li>
            <li><a href="HOME.PHP" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="SUM_BETWEEN_DATES.PHP">Summary Between Dates</a></li>
                <li><a href="SUM_ISD_CNTS.PHP">Summary of ISD Contacts</a></li>
                <li><a href="SUM_NEW_NOS.php">Summary of New Contacts</a></li>
                <li><a href="SUM_IN_STATE.html">Summary Within a State</a></li>
                <li><a href="SUM_OUT_STATE.PHP">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="HOME.PHP" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="MOVEMENTS.html"> MOVEMENTS </a></li>
		<li><a href="calls_tot.PHP">Call Details Total</a></li>
                <li><a href="calls_btwn_dates.PHP">Calls Between Dates</a></li>
                <li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>
              </ul>
            </li>
            <li><a href="HOME.PHP" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="CDATCNTS.php">Cdat Cnts</a></li>
		<li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="HOME.PHP" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.PHP">Phones used in Imei</a></li>
                <li><a href="IMEISINPHONE.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="HOME.PHP" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="ADDRESS.php">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
              </ul>
            </li>
             <li><a href="#" class="MenuBarItemSubmenu">Day Night Loc</a>
               <ul>
                <li><a href="DAY%26NIGHTLOC.HTML">Top 10 Day Night Loc</a></li>
                <li><a href="DAY%26NIGHTLOC_BTWN_DATES.HTML">Top 10 Day Night Loc Between Dates</a></li>
               </ul>
            </li>
                <li><a href="#" class="MenuBarItemSubmenu">Wanted</a>
                  <ul>
                    <li><a href="wanted1.PHP">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="cellid_search.php">Cellid Search</a></li>
                <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
                <li><a href="COMMON_CNTS.php">Common Cnts</a></li>
                <li><a href="ADMIN_ACTIVITY_LOG.PHP">User Activity</a></li>
                <li><a href="ADMIN_SQL_CONSOLE.PHP">SQL Query Console</a></li>
              </ul>
            </li>
          </ul></td>
        </tr>
      </table>
<marquee behavior="scroll" direction="left"> <font color="YELLOW" face=verdana size='2'><b> *** PLEASE MAIL RAW DATA TO crimelab@hyd.tspolice.gov.in TO VIEW REPORTS *** </b></font></marquee> 
        <!--------<marquee behavior="scroll" direction="left"> <font color="YELLOW" face=verdana size='2'><b> *** IF YOU HAVE ANY SUGGESTIONS OR CHANGES ON THIS SITE PLEASE SHARE WITH ANALYSIS WING *** </b></font></marquee>------->
        <table width="1307" height="355">
          <tr>
            <td width="214" rowspan="2" valign="top">
              <blockquote>
                <ul class="MenuBarVertical">
          <li><a href="ADDRESS.php">Address Search</a>            </li>
          <li><a href="calls_tot.PHP">Call Details</a>            </li>
          <li><a href="CDATCNTS.php">Cdat Cnts</a></li>
          <li><a href="cellid_search.php">Cellid Search</a></li>
          <li><a href="COMMON_CNTS.php">Common Cnts</a></li>
                <li><a href="ADMIN_ACTIVITY_LOG.PHP">User Activity</a></li>
                <li><a href="ADMIN_SQL_CONSOLE.PHP">SQL Query Console</a></li>
          <li><a href="imeisearch.PHP">IMEI Search</a>            </li>
          <li><a href="sum_home.php">Summary</a></li>
          <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
         </ul>
        <p>&nbsp; </p>
      </blockquote></td>
            <td width="850" height="24" align="left" valign="top"><p align="center" class="FONT">WANTED</p></td>
            <td width="227" rowspan="2">&nbsp;</td>
          </tr>
          <tr>
            <td height="321" align="left" valign="top"><div align="center"><img src="IMAGES/wanted1.jpg" width="850" height="312" /></div></td>
          </tr>
        </table>
        <p>&nbsp;</p>
    
  <script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
var MenuBar2 = new Spry.Widget.MenuBar("MenuBar2", {imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>
</body>
</html>
