<?php
// One page for both halves of this screen: the form, and the results.
// Was view/imei_request_movements.htm (form) + controller/imei_request_movements.php (handler).
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
  <table width="950" height="603" border="2">
    <tr>
      <td width="1000" height="595" align="left" valign="top"><table width="1313" height="130">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="../controller/home_imei.php">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="../controller/imei_request_sum.php">Summary Total</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="imei_request_movements.php"> MOVEMENTS </a></li>
	          </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="../controller/cdatcnts.php">Cdat Cnts</a></li>
		              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="imei_request_status.php">Phones used in Imei</a></li>
                <li><a href="imei_request_status.php">Imeis used in phone</a></li>
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
                <li><a href="../view/day%26nightloc_imei.html">Top 10 Day Night Loc</a></li>
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
      <table width="442" height="121" align="center">
<tr>
<th height="29" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">MOVEMENTS OF LOST MOBILE NUMBER</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="imei_request_movements.php">
            <label for="SUM" font face="verdana">Lost Mobile No:</label>
     <input type="text" name="PHONE_NO" id="SUM" placeholder="Enter Mobile No" required="required"/>
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
</br>
<li><a href="home_imei.php"><font color=#FDEFEF>HOME</a></li>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['PHONE_NO'];


$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A WITH (NOLOCK) LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,CONVERT(DATE,STARTTIME),20) DATE,CONVERT(VARCHAR,CONVERT(TIME,STARTTIME),20) TIME,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WITH (NOLOCK) WHERE PHONE='$number' ";



$sql2 = cdr_sql_enrich_tt('', '', [
    'with_lat_long' => true,
    'with_state_col' => true,
    'with_date_time_cols' => true,
]);

$sql5="SELECT PHONE,OTHER,NICKNAME,DATE,TIME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,STATE,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs  ORDER BY STARTTIME";

$sql6="select 'CALL DETAILS OF MOBILE NO. '+'$number'as PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>IMEI</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CELLID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>AZM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TYPE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['OPERATOR'] ."</font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['STATE'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AREADESCRIPTION'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AZM'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>

<script src="../assets/vendor/drop-down-filter/jquery.min.js"></script>
    <script src="../assets/vendor/drop-down-filter/ddtf.js"></script>
    <script>
        $('#mytable').ddTableFilter();
    </script>
<?php endif; ?>
</body>
</html>