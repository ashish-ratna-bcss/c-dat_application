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
<script src="SpryAssets/SpryMenuBar.js" type="text/javascript"></script>
<link href="SpryAssets/SpryMenuBarHorizontal.css" rel="stylesheet" type="text/css" />
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
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="HOME.PHP">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="SUM_BETWEEN_DATES.PHP">Summary Between Dates</a></li>
                <li><a href="SUM_ISD_CNTS.PHP">Summary of ISD Contacts</a></li>
                <li><a href="SUM_NEW_NOS.php">Summary of New Contacts</a></li>
                <li><a href="SUM_IN_STATE.html">Summary Within a State</a></li>
                <li><a href="SUM_OUT_STATE.HTM">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="MOVEMENTS.html"> MOVEMENTS </a></li>
		<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS.html">Movements Btwn Two Nos</a></li>
		<li><a href="movements_between_two_numbers_comparision.PHP">Movements Btwn Two Nos Comparision</a></li>
		<!----<li><a href="calls_tot.PHP">Call Details Total</a></li>-->
                <li><a href="calls_btwn_dates.PHP">Calls Between Dates</a></li>
                <!----<li><a href="CALLS_BT_NOS.htm">Calls Between Two Numbers</a></li>--->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="CDATCNTS.php">Cdat Cnts</a></li>
		<li><a href="BULK_CDAT_CONTACTS.HTM">Bulk Cdat Contacts</a></li>
                <li><a href="otherscdat.php">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imeisearch.PHP">Phones used in Imei</a></li>
                <li><a href="IMEISINPHONE.php">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="ADDRESS.htm">Single Address</a></li>
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
                <li><a href="CELLID_SEARCH.htm">Cellid Search</a></li>
                <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
                <li><a href="COMMON_CNTS.php">Common Cnts</a></li>
                <li><a href="ADMIN_ACTIVITY_LOG.PHP">User Activity</a></li>
                <li><a href="ADMIN_SQL_CONSOLE.PHP">SQL Query Console</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="862" height="158" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col">SUMMARY BETWEEN DATES</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="IMAGES/BORDER.jpg" scope="col"><form id="form1" name="form1" method="post" action="SUM_BTWN_DATES.PHP">
            <p>
              <label for="SUM" font face="verdana"> Mobile No:</label>
              <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"/>
              Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy-mm-dd" required="required"/>
              To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy-mm-dd" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
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
</body>
</html>
