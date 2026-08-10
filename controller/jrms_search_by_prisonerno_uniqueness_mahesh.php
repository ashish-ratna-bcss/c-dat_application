<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("JRMS Search By Prisonerno Uniqueness Mahesh");
?>

<li><a href="jrms_search_by_prisonerno_uniqueness.php"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$f_PRI= $_POST['PRISONERNO'];
$f_JAIL= $_POST['JAILNAME'];


$sql1 ="SET DATEFORMAT DMY SELECT DISTINCT  AUTO_KEY,CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,TYPEOFRELEASE,CRIMENOS,HEADOFCRIME,
MOBILENO,SEC_OF_LAW,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
IDPROOF_TYPE,IDPROOF_NO,RLDTORDER,
JAILREFID,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (PRISONERNO LIKE '%'+'$f_PRI'+'%' AND JAILNAME LIKE '%'+'$f_JAIL'+'%')";


$sql2 ="SELECT AUTO_KEY,CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,CRIMENOS,HEADOFCRIME,MOBILENO,IDPROOF,IDPROOF_TYPE,IDPROOF_NO,
JAILREFID,ADDR_DURING_RELEASE,TYPEOFRELEASE,RLDTORDER,SEC_OF_LAW,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM IRFORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM IRFORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM IRFORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY CIN,RELEASE_DATE DESC";

$sql6="SELECT 'JAIL DATA OF PRISONERNO : '+'$f_PRI' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CIN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>UNIQUE_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRISONERNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PSARRESTED</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHERSNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIMENOS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>HEADOFCRIME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDPROOF</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDR_DURING_RELEASE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>JAILNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADD_TO_JAIL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>RELEASEDT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>Operation</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CIN'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['UNIQUE_KEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PRISONERNO'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PSARRESTED'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHERSNAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIMENOS'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['HEADOFCRIME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'cdatcnts2.php?PHONE_NO='.($row['MOBILENO']).">". $row['MOBILENO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IDPROOF'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDR_DURING_RELEASE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['JAILNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADD_TO_JAIL'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['RELEASE_DATE'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>
<a href=".'jrms_uniqueness_update.php?AUTO_KEY='.($row['AUTO_KEY']).">EDIT/UPDATE</font></td>";
echo "</tr>";
}
echo"</table>";

?>
<?php layout_end(); ?>
