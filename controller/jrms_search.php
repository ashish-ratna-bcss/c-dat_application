<?php
// One page for both halves of this screen: the form, and the results.
// Was view/jrms_search.html (form) + controller/jrms_search.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS Search");
?>
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="center" valign="top"><table width="1313" height="148">
        <tr>
          <td width="1305" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg">
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
        <form id="form1" name="form1" method="post" action="jrms_search.php">
                 <th width="555" bgcolor="#A9D1F5" class="CDAT" scope="col"> NAME:           <label for="textfield"></label>
            <input type="text" name="NAME" id="NAME" placeholder="Enter NAME" required="required"/>
	CRIME HEAD:            	<label for="textfield"></label>
            <input type="text" name="CRIME_HEAD" id="CRIME_HEAD" placeholder="Enter CRIME HEAD" required="required"/>
            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
      <p class="MenuBarItemHover">&nbsp;</p></td>
    </tr>
  </table>
</div>


<?php if ($__submitted): ?>
<?php
$serverName = "10.10.46.14\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$CRIMEHEAD= $_POST['CRIMEHEAD'];
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];


$sql1 ="SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date') AND HEADOFCRIME LIKE '%'+'$CRIMEHEAD'+'%' AND HEADOFCRIME!='' ";

$sql11 ="select distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED INTO #COUNT from JRMS..JRMS_TOTAL_2012_TO_2017
GROUP BY UNIQUE_KEY";

$sql2 ="SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,NO_OF_TIMES_RELEASED 
NO_OF_TIMES_RELEASED,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP A 
LEFT JOIN #COUNT B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

$sql6="SELECT 'ACCUSED RELEASED FROM: '+'$f_date'+' TO: '+'$t_date' + ' UNDER CRIME HEAD ' + '$CRIMEHEAD' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st11 = sqlsrv_query( $conn, $sql11);
$st2 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PSARRESTED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHERSNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIMENOS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HEADOFCRIME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIMES INVOLVED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDPROOF</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDR_DURING_RELEASE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>JAILNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADD_TO_JAIL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RELEASEDT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRFORM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PSARRESTED'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHERSNAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIMENOS'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['HEADOFCRIME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><a href=".'jrms_search_for_uniquekey.php?UNIQUE_KEY='.($row['UNIQUE_KEY'])."><center>". $row['NO_OF_TIMES_RELEASED'] ."</center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'cdatcnts2.php?PHONE_NO='.($row['PHONE']).">". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IDPROOF'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDR_DURING_RELEASE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['JAILNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADD_TO_JAIL'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['RELEASE_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['PHOTO']).'"></img>' ?> <?php "</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRFORM'] ."</font></td>";
echo "</tr>";


}
echo"</table>";

?>
<?php endif; ?>
<?php layout_end(); ?>
