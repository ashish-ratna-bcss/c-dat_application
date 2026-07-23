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
<link href="SpryAssets/SpryMenuBarVertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
	
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>

</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="100" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="IMAGES/TOPBORDER.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
            <li><a href="HOME.html">Home</a>              </li>
            <li><a href="#" class="MenuBarItemSubmenu">Summary</a>
              <ul>
                <li><a href="SUM_HOME.HTML">Summary Total</a></li>
                <li><a href="SUM_BETWEEN_DATES.html">Summary Between Dates</a></li>
                <li><a href="SUM_ISD_CNTS.html">Summary of ISD Contacts</a></li>
                <li><a href="SUM_NEW_NOS.html">Summary of New Contacts</a></li>
                <li><a href="SUM_IN_STATE.html">Summary Within a State</a></li>
                <li><a href="SUM_OUT_STATE.html">Summary other than a state</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Call Details</a>
              <ul>
                <li><a href="MOVEMENTS.html"> MOVEMENTS </a></li>
		<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS.html">Movements Btwn Two Nos</a></li>
		<li><a href="MOVEMENTS_BETWEEN_TWO_NUMBERS_COMPARISION.html">Movements Btwn Two Nos Comparision</a></li> 
		<!----<li><a href="CALLS_TOT.html">Call Details Total</a></li>--->
                <li><a href="CALLS_BTWN_DATES.html">Calls Between Dates</a></li>
                <!----<li><a href="CALLS_BT_NOS.html">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="CDATCNTS.HTML">Cdat Cnts</a></li>
		<li><a href="BULK_CDAT_CONTACTS.HTML">Bulk Cdat Contacts</a></li>
		<li><a href="OTHERSCDAT.HTML">Others Cdat</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Imei Search</a>
              <ul>
                <li><a href="IMEISEARCH.HTML">Phones used in Imei</a></li>
                <li><a href="IMEISINPHONE.HTML">Imeis used in phone</a></li>
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Address</a>
              <ul>
                <li><a href="ADDRESS.HTML">Single Address</a></li>
                <li><a href="BULKADDRESS.HTML">Bulk Addresses</a></li>
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
                    <li><a href="wanted1.html">List - 1</a></li>
                  </ul>
                </li>
            <li><a href="#" class="MenuBarItemSubmenu">Others</a>
              <ul>
                <li><a href="CELLID_SEARCH.HTML">Cellid Search</a></li>
                <li><a href="VEHICLE_SEARCH.HTML">Vehicle Search</a></li>
                <li><a href="COMMON_CNTS.HTML">Common Cnts</a></li>
                </ul>
            </li>
          </ul></td>
        </tr>

  </table>
        <table width="1307" height="35">
<tr>

            <td width="214" rowspan="2" valign="top">
              <blockquote>
                <ul class="MenuBarVertical">
          <li><a href="PDACT_SEARCH.HTML" class="MenuBarItemSubmenu">PDACT NAME SEARCH</a>            </li>
          <li><a href="PDACT_MO_SEARCH.HTML" class="MenuBarItemSubmenu">PDACT SEARCH BY MO</a>            </li>
          <li><a href="PDACT_PS_WISE_SEARCH.PHP" class="MenuBarItemSubmenu">PDACT SEARCH BY PS</a>            </li>
          </ul>

</tr>
  
   
 
 
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"PDACT");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}




$sql9="select distinct top 10 PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT,CRIME_HEAD_SEARCH  into #temp from PDACT_MAIN_TABLE 
order by DATE_OF_PDACT desc";

$sql10="select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,CRIME_HEAD_SEARCH,
CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ORDER BY DATE_OF_PDACT DESC";

$sql8="SELECT DISTINCT 'RECENT ARRESTED PDACT CRIMINALS'  as PHONE1 FROM #TEMP";


$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st8 = sqlsrv_query( $conn, $sql8 );


while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PD_ACT_PS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_PDACT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center><a href=".'PDACT_MAIN.PHP?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT_KEY'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'IR.PHP?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_DISTRICT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NATIVE_STATE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['PD_ACT_PS'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['CRIME_HEAD_SEARCH'] ."</font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DATE_OF_PDACT'] ."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "</tr>";
}
?> 
 </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"SpryAssets/SpryMenuBarDownHover.gif", imgRight:"SpryAssets/SpryMenuBarRightHover.gif"});
</script>

</table>
</body>
</html>dy>
</html>
