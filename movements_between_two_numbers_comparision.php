<?php
// One page for both halves of this screen: the form, and the results.
// Was movements_between_two_numbers_comparision.htm (form) + movements_between_two_numbers_comparision.php (handler). The de-duplication kept only the
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
                <!------<li><a href="calls_tot.php">Call Details Total</a></li>------>
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!------<li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>----->
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
      <table width="1000" height="120" align="center">
        <tr>
          <th height="21" align="center" valign="middle" background="IMAGES/border.jpg" scope="col">COMPARISION OF TWO NUMBERS LOCATION</th>
        </tr>
        <tr>
          <th align="center" valign="middle" background="IMAGES/border.jpg" scope="col"><form id="form1" name="form1" method="POST" action="movements_between_two_numbers_comparision.php">
            <label for="SUM" font face="verdana">Movements of Mobile No:</label>
              <input type="text" name="PHONE_NO" id="calls" placeholder="Enter Mobile No" required="required"/>
              <label for="SUM" font face="verdana">Other No:</label>
              <input type="text" name="OTHER_NO" id="calls" placeholder="Enter Other No" required="required"/>
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
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>

<?php if ($__submitted): ?>
<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['PHONE_NO'];
$number1=$_POST['OTHER_NO'];

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number','$number1')  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


$sql1 ="SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number','$number1')";

$sql2 = cdr_sql_enrich_tt('', '', [
    'with_last_update' => true,
    'with_lat_long' => true,
    'output_table' => '#ttppp',
]);

$sql5="select distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''+A.CELLTOWERID PHONE_CELLTOWERID,
A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM,
A.OTHER,''''+B.CELLTOWERID OTHER_CELLTOWERID,
B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM
into #ttpppp from #ttppp A INNER JOIN
#TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND CONVERT(DATE,A.STARTTIME)=CONVERT(DATE,B.STARTTIME) 
and datepart(hh,convert(datetime,A.STARTTIME))=datepart(hh,convert(datetime,b.STARTTIME)) and 
datepart(mm,convert(datetime,A.STARTTIME))=datepart(mm,convert(datetime,b.STARTTIME)) 
AND datediff(ss,convert(datetime,A.STARTTIME),convert(datetime,b.STARTTIME))<'4'
WHERE A.PHONE='$number'
ORDER BY A.STARTTIME";

$sql7="select distinct *,case when 
phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%'
 then CAST(import.DBO.CALCULATEDISTANCE(left(phone_long,8),left(phone_lat,8),left(other_LONG,8),left(other_LAT,8)) AS INT) else '' end 
DIST FROM #ttpppp
ORDER BY STARTTIME";

$sql6="select 'MOVEMENTS COMPARISION  OF MOBILE NO. '+'$number' + 'AND OTHER NO. '+'$number1' as PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>STARTTIME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DURATION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE AREADESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER AREADESCRIPTION</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE CELLTOWERID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE AZM</font></th>
<th><font size=2 face=verdana color='#F9FBFC'>OTHER CELLTOWERID</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER LAT</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER LONG</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER AZM</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DIST BETWEEN NUMBERS IN KM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['OTHER'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['STARTTIME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DURATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE_AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_CELLTOWERID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE_LAT'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_LONG'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE_AZM'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER_CELLTOWERID'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OTHER_AZM'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DIST'] ."<center></font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st5);
?>
<?php endif; ?>
</body>
</html>
