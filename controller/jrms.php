<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
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
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<link href="../assets/spry/sprymenubarvertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
	
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>

</head>

<body bgcolor="#5195BA">
<?php
require_once("dbcontroller.php");
$db_handle = new DBController();
$query ="SELECT distinct HEADOFCRIME FROM JRMS..JRMS_TOTAL_2012_TO_2017 
WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";
$results = $db_handle->runQuery($query);
?>
<div align="center">
  <table width="1323" height="100" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">
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
		<li><a href="../view/movements_between_two_numbers.html">Movements Btwn Two Nos</a></li>
		<li><a href="movements_between_two_numbers_comparision.php">Movements Btwn Two Nos Comparision</a></li> 
		<!----<li><a href="calls_tot.php">Call Details Total</a></li>--->
                <li><a href="calls_btwn_dates.php">Calls Between Dates</a></li>
                <!----<li><a href="../view/calls_bt_nos.htm">Calls Between Two Numbers</a></li>---->
              </ul>
            </li>
            <li><a href="#" class="MenuBarItemSubmenu">Cdat</a>
              <ul>
                <li><a href="cdatcnts.php">Cdat Cnts</a></li>
		<li><a href="../view/bulk_cdat_contacts.htm">Bulk Cdat Contacts</a></li>
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
          <li><a href="jrms_name_search.php" class="MenuBarItemSubmenu">JRMS NAME SEARCH</a>            </li>
          <li><a href="jrms_search_by_dates.php" class="MenuBarItemSubmenu">JRMS SEARCH BY DATE</a>            </li>
          </ul>
</tr>
</table>

      <p>&nbsp;</p>
<table width="1021" height="163" align="center">
        <tr>
          <th height="31" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">JAIL RELEASE BETWEEN DATES</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="jrms_search.php">
                      Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
              To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
              CrimeHead: <select name="CRIMEHEAD">
<option value="">Select CrimeHead</option>
<?php
foreach($results as $HEADOFCRIME) {
?>
<option value="<?php echo $HEADOFCRIME["HEADOFCRIME"]; ?>"> <?php echo $HEADOFCRIME["HEADOFCRIME"]; ?> </option>
<?php
}
?>
</select>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
          </form></th>
        </tr>
     
 
 
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}




$sql9="SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,PSARRESTED,SUBSTRING(NAME,1,CHARINDEX('/',NAME)-1) NAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE CONVERT(DATE,RELEASEDT) =(SELECT DISTINCT MAX(CONVERT(DATE,RELEASEDT)) FROM JRMS..JRMS_TOTAL_2012_TO_2017) AND HEADOFCRIME!='' AND JAILNAME IN ('CHERLAPALLI','CHANCHALGUDA')";

$sql10="SELECT PRISONERNO,PSARRESTED,NAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND isnumeric(IDPROOF)=1 AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND isnumeric(IDPROOF)=1 AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY RELEASE_DATE DESC";

$sql8="SELECT DISTINCT 'RECENTLY RELEASED ACCUSED FROM JAIL (CHERLAPALLI AND CHANCHALGUDA)'  +' ON '+RELEASE_DATE as PHONE1 FROM #TEMP";


$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st8 = sqlsrv_query( $conn, $sql8 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PSARRESTED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIMENOS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HEADOFCRIME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDPROOF</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDR_DURING_RELEASE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>JAILNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADD_TO_JAIL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRFORM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PSARRESTED'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIMENOS'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['HEADOFCRIME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'cdatcnts2.php?PHONE_NO='.($row['PHONE']).">". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IDPROOF'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDR_DURING_RELEASE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['JAILNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADD_TO_JAIL'] ."</font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['PHOTO']).'"></img>' ?> <?php "</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRFORM'] ."</font></td>";
echo "</tr>";
}
?> 
 </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>
</body>
</html>dy>
</html>
