<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="../view/jrms_datewise_search_uniqueness.htm"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "10.10.46.14\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];


$sql1 ="SET DATEFORMAT DMY SELECT DISTINCT  CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,
MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date')";


$sql2 ="SELECT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRFORM FROM #TEMP ORDER BY RELEASE_DATE DESC";

$sql6="SELECT 'ACCUSED RELEASED FROM: '+'$f_date'+' TO: '+'$t_date' AS PHONE";


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
<th bgcolor=#921215><font size=4 face=verdana color='#F9FBFC'>IRKEY</font></th>
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
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRFORM</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CIN'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['UNIQUE_KEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PSARRESTED'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHERSNAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIMENOS'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['HEADOFCRIME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'cdatcnts2.php?PHONE_NO='.($row['PHONE']).">". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IDPROOF'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ADDR_DURING_RELEASE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['JAILNAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['ADD_TO_JAIL'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['RELEASE_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['PHOTO']).'"></img>' ?> <?php "</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRFORM']).">". $row['IRFORM'] ."</font></td>";
echo "</tr>";


}
echo"</table>";

?>
</body>
</html>