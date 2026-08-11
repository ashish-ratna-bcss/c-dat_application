<?php
// One page for both halves of this screen: the form, and the results.
// Was sum_isd_cnts.htm (form) + sum_isd_cnts.php (handler). The de-duplication kept only the
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
		<!----<li><a href="calls_tot.php">Call Details Total</a></li>--->
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
      <div align="center">
        <table width="633" height="130">
          <tr>
            <th height="26" background="IMAGES/border.jpg" scope="col">ISD SUMMARY OF MOBILE NUMBER</th>
            <td>          
          </tr>
          <tr>
            <th background="IMAGES/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="sum_isd_cnts.php">
              <label for="ISD_SUM" font="font" face="verdana">Mobile No:</label>
              <input type="text" name="PHONE_NO" id="ISD_SUM" placeholder="Enter Mobile No" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
            </form></th>
              </th>
            </tr>
        </table>
      </div>
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
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];

$sql1 ="select DISTINCT *  INTO #XX from CDATDUPL.DBO.CDATPCSUSPECT where phone='$number'";


$sql3 ="SELECT * INTO #TEMP FROM CDAT_DETAILS1 WHERE LEN(OTHER)>10 AND DURATION>'0' AND PHONE='$number'";

$sql4="SELECT DISTINCT * INTO #TT FROM #TEMP";

$sql5="SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT',
COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL,
CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL INTO #RESULT FROM #TT 
GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

$sql6="SELECT A.PHONE,CASE WHEN A.OTHER=B.PHONE THEN OTHER+', - '+NICKNAME ELSE OTHER END AS OTHER,[IN],[OUT],CALLS,DUR,FIRSTCALL,LASTCALL,
ISNULL(AREADESCRIPTION,'CODE N/A') AS ADDRESS INTO #WITHADDRESS FROM #RESULT A 
left join CDATDUPL.DBO.cdatsuspect B on a.other=B.phone 
left join CDATDUPL.DBO.cdatphonearea C on '00'+other like phoneprefix+'%' 
WHERE A.OTHER NOT LIKE '1800%'
group by a.PHONE, B.PHONE,other,[IN],[OUT],calls,dur, FIRSTCALL,
LASTCALL, nickname,AREADESCRIPTION";


$sql7="SELECT  * FROM #WITHADDRESS where  ADDRESS!=' JUNK-COULD BE bulk SMS or VOIP calls' ORDER BY calls DESC";

$sql8="SELECT 'ISD CONTACTS OF MOBILE NO: '+'$number' as PHONE1";

$sql9="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

$sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

$sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";



$st1 = sqlsrv_query( $conn, $sql1 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5 >
<tr bgcolor=#921215>
<th ><font size=3 face=verdana color='#F9FBFC'>PHONE</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font</th>
<!-- <th><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font</th> -->
<th><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NICKNAME'] ."<center></font></td>";
/* echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_UPDATED'] ."<center></font></td>"; */
echo "<td width=500px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";
}

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>OTHER</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>IN</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>OUT</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>CALLS</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>DUR</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTHER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IN'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OUT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CALLS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DUR'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRSTCALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LASTCALL'] ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "</tr>";

}
echo"</table>";

sqlsrv_free_stmt( $st7);
?>
<?php endif; ?>
</body>
</html>
