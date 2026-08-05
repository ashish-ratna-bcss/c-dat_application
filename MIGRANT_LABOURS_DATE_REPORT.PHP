<html>
<head>
</head>
<body bgcolor="#80800">
<li><a href="MIGRANT_LABOURS_DATE_REPORT.HTML"><font color=#FDEFEF>Back</a></li>
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
</body>
</html>