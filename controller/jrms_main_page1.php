<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS");
?>
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
          <td width="1265" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg"></td>
        </tr>

  </table>
        <table width="1307" height="35">
<tr>

            <td width="214" rowspan="2" valign="top">
              <blockquote>
                

</tr>
  
   
 
 
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}




$sql9="SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,PSARRESTED,case when name not like '%/%' 
then name else SUBSTRING(NAME,1,CHARINDEX('/',NAME)-1) end NAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
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


</table>
<?php layout_end(); ?>
