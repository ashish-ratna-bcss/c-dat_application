<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="imei_request_traced_details.html"><font color=#FDEFEF>Back</a></li>
		</br>
<li><a href="home_imei.php"><font color=#FDEFEF>HOME</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"LOSTREPORT_HAWKEYE");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];

$sql3 ="SET DATEFORMAT DMY SELECT DISTINCT LEFT(A.IMEINUMBER,14) IMEINUMBER, A.PHONE,MIN(STARTTIME) FC,
MAX(STARTTIME) LC,MAX(B.MOBILE_LOST_DATE) MOBILE_LOST_DATE
INTO #TR FROM LOST_REPORT_CDR_DATA  A
INNER JOIN COMPLAINANT_DETAILS B ON LEFT(A.IMEINUMBER,14)=LEFT(B.IMEI1,14)
WHERE LEFT(A.IMEINUMBER,14) IN (
SELECT DISTINCT LEFT(IMEI_NO,14) IMEINUMBER FROM IMEI_REQUESTED_DETAILS
WHERE CONVERT(DATE,REQUESTED_DATE) BETWEEN '$f_date' AND '$t_date')
GROUP BY LEFT(IMEINUMBER,14),PHONE";

$sql4 ="SELECT DISTINCT ROW_NUMBER() OVER(ORDER BY IMEINUMBER DESC) SLNO,A.IMEINUMBER,A.PHONE,
CASE WHEN A.PHONE=C.PHONE 
THEN REPLACE(ISNULL(C.FULLNAME,''),'	','')+', '+REPLACE
(ISNULL(C.FULLADDRESS,''),'	','')+' DOA:'+CONVERT
(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN A.PHONE LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN A.PHONE LIKE '1800%' AND LEN(A.PHONE)=11 THEN 'TOLL-FREE
NUMBER'
WHEN A.PHONE IN
('121','111','198','123','139','122','199','12345') THEN 
'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM 
CDATDUPL.DBO.ADDRESS_OTHER_STATE) 
THEN REPLACE(ISNULL(D.FULLNAME+', '+D.FULLADDRESS,''),'	','')+'
'+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS,
CONVERT(VARCHAR(20),A.FC) FIRST_CALL,CONVERT(VARCHAR(20),A.LC)  LAST_CALL,LC,
A.MOBILE_LOST_DATE,B.COMPLAINANT_NAME,
B.APPLICATION,B.LRNO ID,B.BRAND+' '+B.Model MODEL,'TRACED' TRACED FROM #TR A
INNER JOIN COMPLAINANT_DETAILS B ON  LEFT(A.IMEINUMBER,14)=LEFT(B.IMEI1,14) 
AND A.MOBILE_LOST_DATE=B.Mobile_Lost_Date
AND CONVERT(DATE,A.FC)>CONVERT(DATE,A.MOBILE_LOST_DATE)
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%' 
ORDER BY SLNO,LC DESC";


$sql5="SELECT 'LR/HAWKEYE IMEI TRACED REPORT FROM: '+'$f_date' +' TO '+'$t_date' as PHONE1";

/* */

$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );

while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5 >
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>SLNO</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMEINUMBER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE NO USED</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>SUMMARY OF PHONE NO USED</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>MOVEMENTS OF PHONE NO USED</font</th>
<th width='10%' bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE ADDRESS</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>MOBILE LOST DATE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>COMPLAINANT NAME</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>APPLICATION</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LR/HAWKEYE ID</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>MODEL / BRAND</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>TRACED STATUS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<tr width=50px>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['SLNO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'IMEI_REQUEST_SUM.PHP?PHONE_NO='.($row['PHONE']).">".$row['PHONE']."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'IMEI_REQUEST_MOVEMENTS.PHP?PHONE_NO='.($row['PHONE']).">".$row['PHONE']."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['MOBILE_LOST_DATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['COMPLAINANT_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['APPLICATION'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ID'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MODEL'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['TRACED'] ."<center></font></td>"; 
echo "</tr>";
}

sqlsrv_free_stmt( $st3);
?>
</body>
</html>