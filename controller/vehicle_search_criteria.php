<?php
// One page for both halves of this screen: the form, and the results.
// Was view/vehicle_search_criteria.htm (form) + controller/vehicle_search_criteria.php (handler).
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
		<li><a href="../controller/calls_tot.php">Call Details Total</a></li>
                <li><a href="../controller/calls_btwn_dates.php">Calls Between Dates</a></li>
                <li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>
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
          </ul>
         </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="625" height="124">
        <tr>
          <th height="26" bgcolor="#A9D1F5" class="CDAT" scope="col">VEHICLE SEARCH</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="vehicle_search_criteria.php">
                 <th width="555" height="90" bgcolor="#A9D1F5" class="CDAT" scope="col">
Select search criteria : <label for="textfield"></label><select name="VEHICLE_SOURCE">
<option value="REGN_NO">VEHICLE_NO</option>
<option value="CHAS_NO">CHASSIS_NO</option>
<option value="ENG_NO">ENGINE_NO</option>
<option value="PHONE">PHONE</option>
</select>
            <input type="text" name="VEHICLE_NO" id="CAF" placeholder="Enter No" required="required"/>

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
</p>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['VEHICLE_SOURCE'])){

$number=$_POST['VEHICLE_NO'];
$number1=$_POST['VEHICLE_SOURCE'];

$sql8="SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";

$sql9="SELECT DISTINCT REGN_NO, FULLNAME AS NAME,FATHERNAME AS FATHER_NAME,FULLADDRESS+', '+CITY AS ADDRESS,PHONE AS PHONE_NO,MKR_CLAS+', COLOR: '+COLOUR+', '+VEH_CLASS AS 
VEHICLE_TYPE, ENG_NO,CHAS_NO,CONVERT(VARCHAR,ISS_DT,106) AS ISSUED_DATE FROM CDATDUPL.[dbo].[CDAT_RTA] WHERE $number1 LIKE '%'+'$number'";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5 id=mytable>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>REGN_NO</th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>VEHICLE_TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ENG_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CHAS_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ISSUED_DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['REGN_NO'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=0.5 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['PHONE_NO'] ."</font></td>";
echo "<td width=200px bgcolor=#AED1F1><font size=1 face=verdana>". $row['VEHICLE_TYPE'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ENG_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['CHAS_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ISSUED_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d='.'REGNNO: '.$row["REGN_NO"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' FATHERNAME:'.$row["FATHER_NAME"]. ' PHONE:'.$row["PHONE_NO"].' ADDRESS:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["ADDRESS"]).' VEH_TYPE: '.$row["VEHICLE_TYPE"].' ENG_NO: '.$row["ENG_NO"].' CHAS_NO: '.$row["CHAS_NO"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
<script src="../assets/vendor/drop-down-filter/jquery.min.js"></script>
    <script src="../assets/vendor/drop-down-filter/ddtf.js"></script>
    <script>
        $('#mytable').ddTableFilter();
    </script>
<?php endif; ?>
</body>
</html>
