<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="../view/jrms_search_uniqueness.html"><font color=#FDEFEF>Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$NAME = $_POST['NAME'];
$FATHER_NAME= $_POST['FATHER_NAME'];
$PHONE = $_POST['PHONE'];
$AADHAAR_NO = $_POST['AADHAAR_NO'];
$VOTER_ID = $_POST['VOTER_ID'];


$sql1 ="SET DATEFORMAT DMY SELECT DISTINCT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,
MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS.dbo.JRMS_TOTAL_2012_TO_2017
WHERE  NAME LIKE '%'+'$NAME'+'%' AND FATHERSNAME LIKE '%'+'$FATHER_NAME'+'%' AND (MOBILENO like '%'+'$PHONE'+'%' OR MOBILENO IS NULL) and 
(NAME LIKE '%'+'$AADHAAR_NO'+'%' ) and (NAME LIKE '%'+'$VOTER_ID'+'%' )";

$sql2 ="SET DATEFORMAT DMY SELECT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME, ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY CIN, RELEASE_DATE DESC";

$sql6="SELECT 'ACCUSED RELEASED FROM JAIL'+' '+'$NAME'+' '+'$FATHER_NAME'+'$AADHAAR_NO' AS PHONE";

$sql7="SELECT 'INTERROGATION REPORT MATCHED TO SEARCH CRITERIA' AS PHONE";

$sql8="SELECT DISTINCT A.IRKEY,A.NAME,A.FATHER_NAME,A.AADHAR_NO,A.PRESENT_ADDRESS,
CONVERT(VARCHAR(20),B.CRIME_NO)+'/'+CONVERT(VARCHAR(20),B.YEAR) CRNO,
B.CRIME_HEAD,B.POLICE_STATION,A.MOBILE PHONE
INTO #TEMP1 FROM FORMS..IR_PARTICULARS A
left JOIN FORMS..OFFENCE_DETAILS B ON A.IRKEY=B.IRKEY
WHERE NAME LIKE '%'+'$NAME'+'%' AND FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%' AND (MOBILE like '%'+'$PHONE'+'%') and 
(AADHAR_NO LIKE '%'+'$AADHAAR_NO'+'%') and (VOTERID LIKE '%'+'$VOTER_ID'+'%')";


$sql9="SELECT DISTINCT * FROM #TEMP1";

$sql10="SELECT case when count(cin)>=1 THEN '' ELSE '*** JRMS RECORDS NOT AVAILABLE ***' end as PHONE FROM #TEMP";

$sql11="SELECT case when count(IRKEY)>=1 THEN '' ELSE '*** IR RECORDS NOT AVAILABLE ***' end as PHONE FROM #TEMP1";



$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CIN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>UNIQUE_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRISONERNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHERSNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PSARRESTED</font>
<th bgcolor=#921215><font size=3 face=verdana 
color='#F9FBFC'>CRIMENOS</font></th>
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
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CIN'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['UNIQUE_KEY'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PRISONERNO'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHERSNAME'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PSARRESTED'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIMENOS'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['HEADOFCRIME'] ."<center></font></td>";
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

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AADHAR_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT_ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AADHAR_NO'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PRESENT_ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRNO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'cdatcnts2.php?PHONE_NO='.($row['PHONE']).">". $row['PHONE'] ."<center></font></td>";
echo "</tr>";
}
echo"</table>";
while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
?>
</body>
</html>