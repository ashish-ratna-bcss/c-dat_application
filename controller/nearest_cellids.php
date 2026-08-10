<?php
// One page for both halves of this screen: the form, and the results.
// Was view/nearest_cellids.html (form) + controller/nearest_cellids.php (handler).
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
          </ul></td>
        </tr>
      </table>
      <p>&nbsp;</p>
      <table width="1126" height="144" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">NEAR BY CELLID SEARCH</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="nearest_cellids.php">
            <label  font face="verdana">LAT:</label>
              <input type="text" name="LAT" id="LAT" placeholder="Enter LAT" required="required"/>
             <label  font face="verdana">LONG:</label>
              <input type="text" name="LONG" id="LONG" placeholder="Enter LONG" required="required"/>
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
<?php
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/sql_safe.php';
audit_require_session();
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$LAT = sql_safe_float($_POST['LAT'] ?? '0');
$LONG = sql_safe_float($_POST['LONG'] ?? '0');




$sql1="SELECT 'NEAR BY CELLID SEARCH: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";


$sql2 ="declare @lat decimal(14,10),@long decimal (14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='10000'
SELECT CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,SITEADDRESS,OPERATOR,LAT,LONG,AZIMUTH,OTYPE,STATE,
DENSE_RANK()
over (PARTITION by operator order by CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)) as RANK,
CONVERT(VARCHAR,LASTUPDATE,20) LASTUPDATE
INTO #T FROM dbo.celltowerfiltered WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY OPERATOR,DIST,OTYPE";

$sql3="select distinct *,CASE WHEN RANK=1 THEN 'A' WHEN RANK='2' THEN 'B' END AS CATEGORY from #T
where rank<3  and otype not like '%cdma%'
order by otype,operator,CATEGORY";

$st1 = sqlsrv_query( $conn, $sql1 );
sqlsrv_render_query_error($st1, 'Title query');
$st2 = sqlsrv_query( $conn, $sql2 );
sqlsrv_render_query_error($st2, 'Nearest towers');
$st3 = sqlsrv_query( $conn, $sql3 );
sqlsrv_render_query_error($st3, 'Tower ranking');

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". h($row['PHONE1'] ?? '') ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CATEGORY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DIST</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTYPE</font></th>
</tr>";


while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['CELLTOWERID'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['CATEGORY'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". h($row['DIST'] ?? '') ."<center></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". h($row['BR'] ?? '') ."<center></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OPERATOR'] ?? '') ."<center></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana>". h($row['OTYPE'] ?? '') ."<center></td>";
echo "</tr>";

} 
echo"</table></br>";

sqlsrv_free_stmt( $st3);
?>
<?php endif; ?>
</body>
</html>
