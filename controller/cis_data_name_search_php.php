<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Cis Data Name Search Php");
?>

<li><a href="jrms.php"><font color=#FDEFEF>Back</a></li>
<script>
function bigImg(x) { 
x.style.height="300px";
x.style.width="300px";
}
function normalImg(x) { 
x.style.height="100px";
x.style.width="100px";
}
</script>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$NAME = $_POST['NAME'];
$POLICE_STATION= $_POST['POLICE_STATION'];
$DISTRICT= $_POST['DISTRICT'];


$sql1 ="SELECT DISTINCT  Fir_No, POLICE_STATION, District, Name, FatherName, Age, Caste, Present_Add, 
Premenant_Add, folder_name, picture_name, PATH, image FROM CIS_DATA_BASE..CIS_COMPLETE_DATA
WHERE POLICE_STATION LIKE '%'+'$POLICE_STATION'+'%' AND DISTRICT LIKE '%'+'$DISTRICT'+'%' AND NAME LIKE '%'+'$NAME'+'%' ";



$sql6="SELECT 'ACCUSED ARRESTED FROM ' + '$POLICE_STATION' +' OF '+ '$DISTRICT' +' DISTRICT '+' BY NAME '+'$NAME' AS PHONE";


$st1 = sqlsrv_query( $conn, $sql1 );
$st6 = sqlsrv_query( $conn, $sql6 );

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIR_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHERNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CASTE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT_ADD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PERMANANT_ADD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['Fir_No'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['District'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['Name'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FatherName'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['Age'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['Caste'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['Present_Add'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['Premenant_Add'] ."</font></td>";
echo "<td height=100 width=100px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="100" width="100" src="'.cdat_base64_image_src($row['image']).'"></img>' ?> <?php "</td>";

}
echo"</table>";

?>
<?php layout_end(); ?>
