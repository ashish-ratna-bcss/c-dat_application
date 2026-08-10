<?php
// One page for both halves of this screen: the form, and the results.
// Was view/migrant_labours_date_report.htm (form) + controller/migrant_labours_date_report.php (handler).
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
<link href="../assets/spry/sprymenubarvertical.css" rel="stylesheet" type="text/css" />
<style type="text/css">
	
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
}
.aa{
width: 800px;
height: 400px;
background-color: rgba(0,0,0,0.4);
margin:0 auto;
margin-top:40px;
padding-top:10px;
padding-left: 10px;
border-radius: 15px;
-webkit-border-radius: 15px;
-moz-border-radius:15px;
color:white;
font-weight:bolder;
box-shadow: inset -4px -4px rgba(0,0,0,0.4);
font-size:18px;
}
.aa input[type="text"]{
width:100px;
height:35px;
border:0;
border-radius:5px;
-webkit-border-radius:5px;
-o- border-radius:5px;
-moz-border-radius:5px;
padding-left: 10px;
}
.aa input[type="submit"]{
width:200px;
height:35px;
border:0;
border-radius:5px;
-webkit-border-radius:5px;
-o- border-radius:5px;
-moz-border-radius:5px;
background-color: orange;
font-weight: bolder;
}
</style>

</head>

<body bgcolor="#808000">
  <p>&nbsp;</p>
      <p>&nbsp;</p></td>
<div class="aa">
<table width="600" height="300" align="center">
        <tr>
          <th height="10" align="center" valign="middle"  scope="col">TROUBLE MONGER MIGRANTS LABOURS BETWEEN ENTRY DATES </th>
        </tr>
        <tr>
          <th width="150" align="center" valign="middle" scope="col"><form id="form1" name="form1" method="post" action="migrant_labours_date_report.php">
                      Date From: 
              <input type="text" name="FROM_DT" id="datepickerID" size="10" placeholder="yyyy/mm/dd" required="required"/>
                      Time From  HH:MM:SS
              <input name="hh1" style="width:40px;" type="number" id="number1"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm1" style="width:40px;" type="number" id="number2"  min="00" max="59" value="00" required="required"/>
             :
	     <input name="ss1" style="width:40px;" type="number" id="number3"  min="00" max="59" value="00" required="required"/>
<br/><br/>

              Date To:
              <input type="text" name="TO_DT" id="datepickerID1" size="10" placeholder="yyyy/mm/dd" required="required"/>
Time To  HH:MM:SS
              <input name="hh2" style="width:40px;" type="number" id="number1"  min="00" max="23" value="00" required="required"/>
             :
              <input name="mm2" style="width:40px;" type="number" id="number2"  min="00" max="59" value="00" required="required"/>
             :
	     <input name="ss2" style="width:40px;" type="number" id="number3"  min="00" max="59" value="00" required="required"/>
<br/><br/>


<input type="submit" name="BTN_SUM" id="BTN_SUM" value="Submit" />     
</form></th>
        </tr>     

 </table>
</div>
      <p>&nbsp;</p>
      <p>&nbsp;</p></td>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"MIGRANT_LABOURS_FORM");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];
$HH1		= $_POST['hh1'];
$MM1		= $_POST['mm1'];
$SS1		= $_POST['ss1'];
$HH2		= $_POST['hh2'];
$MM2		= $_POST['mm2'];
$SS2		= $_POST['ss2'];

$sql1 ="select distinct POLICE_STATION,
NAME,NATIVE_STATE,NATIVE_DISTRICT,PHONE,WORK_STATUS,
PART_OF_LABOUR_CAMP,URGENT,PROBLEM_CASES,REMARKS,ZONE,DIVISION   INTO #TEMP from migrant_labour_table
WHERE (CONVERT(DATETIME,ENTRY_DATE) BETWEEN '$f_date $HH1:$MM1:$SS1' AND '$t_date $HH2:$MM2:$SS2') ORDER BY POLICE_STATION";

$sql2 ="select distinct *,ROW_NUMBER() OVER(ORDER BY POLICE_STATION) SLNO  from #TEMP
ORDER BY POLICE_STATION";


$sql6="SELECT 'TROUBLE MONGERS MIGRANT DATA <br/>
 FROM: '+'$f_date $HH1:$MM1:$SS1'+' TO: '+'$t_date $HH2:$MM2:$SS2' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5 id=mytable class=w3-table-all>
<tr>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>NAME OF THE POLICE STATION</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>SLNO</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>NAME OF THE MIGRANT WORKER</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>NATIVE STATE</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>NATIVE DISTRICT</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>MOBILE NUMBER</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>WORKING STATUS</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>IS HE PART OF LABOUR CAMP</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>IS URGENT</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>PROBLEM CASES</font></th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>REMARKS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SLNO'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NATIVE_STATE'] ."<center></font></td>";
echo "<td width=125px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_DISTRICT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". $row['WORK_STATUS'] ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PART_OF_LABOUR_CAMP'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['URGENT'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PROBLEM_CASES'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REMARKS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table>";

?>
<?php endif; ?>
</body>
</html>
</html>
