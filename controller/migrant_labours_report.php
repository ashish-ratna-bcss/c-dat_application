<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Migrant Labours Report");
?>


<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"MIGRANT_LABOURS_FORM");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}




$sql11="SELECT DISTINCT POLICE_STATION,
NAME,NATIVE_STATE,NATIVE_DISTRICT,PHONE,WORK_STATUS,
PART_OF_LABOUR_CAMP,URGENT,PROBLEM_CASES,REMARKS INTO #TEMP FROM MIGRANT_LABOUR_TABLE WHERE POLICE_STATION!='' AND NAME NOT LIKE ''";

$sql12="SELECT DISTINCT 'HYDERABAD TOTAL COUNT:'+CONVERT(VARCHAR(20),COUNT(*)) AS PHONE1 FROM #TEMP";

$sql13="Select distinct *,ROW_NUMBER() OVER(ORDER BY POLICE_STATION) SLNO from #temp";


$st11 = sqlsrv_query( $conn, $sql11 );
$st12 = sqlsrv_query( $conn, $sql12 );
$st13 = sqlsrv_query( $conn, $sql13 );

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5 id=mytable class=w3-table-all>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME OF THE POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SLNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME OF THE MIGRANT WORKER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MOBILE NUMBER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>WORKING STATUS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IS HE PART OF LABOUR CAMP</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IS URGENT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PROBLEM CASES</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REMARKS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st13, SQLSRV_FETCH_ASSOC) ) {
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
sqlsrv_free_stmt( $st11);
sqlsrv_free_stmt( $st12);
sqlsrv_free_stmt( $st13);


?>
</tbody>
        </table>
</div>
<?php layout_end(); ?>
