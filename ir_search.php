<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="SpryAssets/sprymenubar.js" type="text/javascript"></script>
<link href="SpryAssets/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="IMAGES/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
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
		<li><a href="calls_tot.php">Call Details Total</a></li>
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <li><a href="calls_bt_nos.php">Calls Between Two Numbers</a></li>
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
		<li><a href="ir_module.php">IR FORMS</a></li>
		<li><a href="ir_search.php">IR Search By Name</a></li>
                </ul>
            </li>
                </ul>
                </td>
        </tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p>
      <p class="MenuBarItemHover">&nbsp;</p>
      <table width="800" height="100" align=center>
        <tr>
          <th height="27" bgcolor="#A9D1F5" class="CDAT" scope="col">OFFENDER IR SEARCH BY NAME</th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="ir_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> NAME OF THE OFFENDER:            <label for="textfield"></label>
            <input type="text" name="NAME" id="NAME" placeholder="Enter NAME" required="required"/>
	CRIME HEAD:            	<label for="textfield"></label>
            <input type="text" name="CRIME_HEAD" id="CRIME_HEAD" placeholder="Enter CRIME HEAD" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <!-- Results half, merged in from the old IR_SEARCH.PHP. Its own
     isset($_POST[...]) guard keeps it quiet until the form is submitted. -->
<?php
$serverName = "10.10.46.14\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['NAME'])){

$number=$_POST['NAME'];
$number1=$_POST['CRIME_HEAD'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
ELSE '' END PDACT_KEY,NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST FROM FORMS..IR_PARTICULARS A
INNER JOIN FORMS..OFFENCE_DETAILS B ON A.NAME LIKE '%'+REPLACE('$number',' ','%')+'%' AND (B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' OR 
B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%') AND 
ltrim(rtrim('$number'))!='' and len(replace('$number',' ',''))>'4' AND A.IRKEY=B.IRKEY ORDER BY DATE_OF_ARREST DESC";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ALIAS NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEC_OF_LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DOA</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'IR.PHP?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'pdact_main.PHP?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ALIAS_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PRESENT_ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['CRIME_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['YEAR'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DATE_OF_ARREST'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>

</body>
</html>
