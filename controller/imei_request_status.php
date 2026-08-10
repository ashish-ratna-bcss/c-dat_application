<?php
// One page for both halves of this screen: the form, and the results.
// Was view/imei_request_status.htm (form) + controller/imei_request_status.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
<style type="text/css">
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>

<body bgcolor="#5195BA">
<div align="center">
  <table width="1323" height="603" border="2">
    <tr>
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="134">
        <tr>
          <td width="1200" height="134" align="center" valign="bottom" background="../assets/images/topborder.jpg">
</tr>


       <table width="442" height="121" align="center">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

        <tr>
          <th height="29" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">IMEI REQUEST STATUS</th>
        </tr>

        <tr>
          <th align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="imei_request_status.php">
            <label for="IMEI" font face="verdana">IMEI NO:</label>
              <input type="text" name="IMEI_NO" id="" placeholder="Enter Imei No" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
</tr>
    </tr>
 </table>
  </div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"LOSTREPORT_HAWKEYE");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['IMEI_NO'];

$sql3 ="SELECT DISTINCT IMEI1,MOBILE_LOST_DATE,COMPLAINT_RECEIVED_DATE,APPLICATION,LRNo ID,MODEL+' '+BRAND MODEL_BRAND,
COMPLAINANT_NAME,COMPLAINANT_PHONE FROM LOSTREPORT_HAWKEYE.DBO.COMPLAINANT_DETAILS
WHERE IMEI1 LIKE '%'+LEFT('$number',14)+'%'";

$sql4 ="SELECT DISTINCT IMEI_NO, [FROM] FROM_DATE, [TO] TO_DATE, REQUESTED_DATE
FROM LOSTREPORT_HAWKEYE.DBO.IMEI_REQUESTED_DETAILS WHERE IMEI_NO LIKE '%'+LEFT('$number',14)+'%'";

$sql5 ="SELECT DISTINCT LEFT(IMEINUMBER,14) IMEINUMBER,PHONE,MIN(STARTTIME) FIRST_CALL,
MAX(STARTTIME) LAST_CALL INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA
WHERE IMEINUMBER LIKE '%'+LEFT('$number',14)+'%'
GROUP BY LEFT(IMEINUMBER,14),PHONE";

$sql6="SELECT DISTINCT LEFT(IMEINUMBER,14)+'0' IMEINUMBER,A.PHONE,
CONVERT(VARCHAR(20),FIRST_CALL) FIRST_CALL,CONVERT(VARCHAR(20),LAST_CALL) LAST_CALL,LAST_CALL LC,
CASE WHEN A.PHONE=C.PHONE 
THEN REPLACE(ISNULL(C.FULLNAME,''),'	','')+', '+REPLACE(ISNULL(C.FULLADDRESS,''),'	','')+' DOA:'+CONVERT(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN A.PHONE LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN A.PHONE LIKE '1800%' AND LEN(A.PHONE)=11 THEN 'TOLL-FREE NUMBER'
WHEN A.PHONE IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE) 
THEN REPLACE(ISNULL(D.FULLNAME+', '+D.FULLADDRESS,''),'	','')+' '+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%' ORDER BY LC DESC";

$sql8="SELECT 'IMEI REQUEST STATUS: '+'$number' as PHONE1";

$sql9="SELECT 'IMEI COMPLAINANT DETAILS OF: '+'$number' as PHONE1";

$sql10="SELECT 'CDR REQUESTED DETAILS OF IMEI NO: '+'$number' as PHONE1";

$sql11="SELECT 'IMEI CDR PHONE NUMBER DETAILS: '+'$number' as PHONE1";

$sql12="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** DATA NOT AVAILABLE ***' end as PHONE FROM #TT";

$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );
$st12 = sqlsrv_query( $conn, $sql12 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBGH'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}
echo"</table><br />";
while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMEI_NO</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>MOBILE LOST DATE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>COMPLAINT DATE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>APPLICATION</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LR/HAWKEYE ID</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>MODEL / BRAND</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>COMPLAINANT NAME</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEI1'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MOBILE_LOST_DATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['COMPLAINT_RECEIVED_DATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['APPLICATION'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ID'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MODEL_BRAND'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['COMPLAINANT_NAME'] ."<center></font></td>"; 
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['COMPLAINANT_PHONE'] ."</font></td>";
echo "</tr>";
}

echo"</table><br />";
while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=2>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMEI_NO</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FROM_DATE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>TO_DATE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>REQUESTED_DATE</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEI_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['FROM_DATE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['TO_DATE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REQUESTED_DATE'] ."<center></font></td>";
echo "</tr>";

}
echo"</table><br />";
while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=2>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IMEINUMBER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IMEINUMBER'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['PHONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FIRST_CALL'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LAST_CALL'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ADDRESS'] ."<center></font></td>";
echo "</tr>";

}
echo "</table><br />";

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#921215'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

sqlsrv_free_stmt( $st3);
?>
<?php endif; ?>
</body>
</html>
