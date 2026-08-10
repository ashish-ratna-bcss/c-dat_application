<?php
// One page for both halves of this screen: the form, and the results.
// Was view/imei_request_traced_details.html (form) + controller/imei_request_traced_details.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
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
      <td width="1349" height="595" align="left" valign="top"><table width="1313" height="140">
        <tr>
          <td width="1265" height="130" align="center" valign="bottom" background="../assets/images/topborder.jpg"><ul id="MenuBar1" class="MenuBarHorizontal">      </table>
      <p>&nbsp;</p>
      <table width="1021" height="157" align="center">
        <tr>
          <th height="25" align="center" valign="middle" background="../assets/images/border.jpg" scope="col">IMEI'S TRACED BETWEEN REQUEST DATES</th>
        </tr>
        <tr>
          <th width="782" align="center" valign="middle" background="../assets/images/border.jpg" scope="col"><form id="form1" name="form1" method="post" action="imei_request_traced_details.php">
        
            Request From Date: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
              Request To Date:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
              <input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />
              
          </form></th>
        </tr>
      </table>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>
    </tr>
  </table>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
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
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'imei_request_sum.php?PHONE_NO='.($row['PHONE']).">".$row['PHONE']."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'imei_request_movements.php?PHONE_NO='.($row['PHONE']).">".$row['PHONE']."<center></font></td>";
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
<?php endif; ?>
</body>
</html>
