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
           <li><a href="home.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="sum_home.php">Summary Total</a></li>
                <li><a href="sum_between_dates.php">Summary Between Dates</a></li>
                <li><a href="sum_isd_cnts.php">Summary of ISD Contacts</a></li>
                <li><a href="sum_new_nos.php">Summary of New Contacts</a></li>
                <li><a href="../view/sum_in_state.html">Summary Within a State</a></li>
                <li><a href="../view/sum_out_state.htm">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="../view/movements.html"> MOVEMENTS </a></li>
		<li><a href="calls_tot.php">Call Details Total</a></li>
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <li><a href="../view/calls_bt_nos.htm">Calls Between Two Numbers</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
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
                <li><a href="../view/address.htm">Single Address</a></li>
                <li><a href="bulkaddress.php">Bulk Addresses</a></li>
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
                    <li><a href="wanted1.php">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="../view/cellid_search.htm">Cellid Search</a></li>
                <li><a href="../view/vehicle_search.html">Vehicle Search</a></li>
                <li><a href="common_cnts.php">Common Cnts</a></li>
                <li><a href="admin_activity_log.php">User Activity</a></li>
                <li><a href="admin_sql_console.php">SQL Query Console</a></li>
                </ul>
                </li>
                </ul>
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="972" height="165">
        <tr>
          <th height="29" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" >COMMON CONTACTS OF MOBILE NUMBERS</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="common_cnts.php">
                 <th width="764" align="center" valign="middle" bgcolor="#A9D1F5" class="CDAT" scope="col" > MOBILE NUMBERS:            <style>
label textarea{
font: normal 15px courier;
vertical-align: middle;
}
</style>
<label> <textarea rows=3 cols=50 name='PHONE_NO'  id="COMMON_NOS" placeholder="Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx" required="required"></textarea></label>
<select name="STRING">
<option value=">" SELECTED>></option>
<option value="=">=</option></b>
</select> 
<b>  MORE THAN NO : <select name="NO">
<option value="1" SELECTED>1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
<option value="8">8</option>
<option value="9">9</option>
<option value="10">10</option>
<option value="11">11</option>
<option value="12">12</option>
<option value="13">14</option>
<option value="14">14</option>
<option value="15">15</option>
<option value="16">16</option>
<option value="17">17</option>
<option value="18">18</option>
<option value="19">19</option>
<option value="20">20</option></b>
</select> 
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
</body>
</html>
