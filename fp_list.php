<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="ir_module.php"><font color=#FDEFEF>Back</a></li>
<script>
function bigImg(x) { 
x.style.height="450px";
x.style.width="450px";
}
function normalImg(x) { 
x.style.height="200px";
x.style.width="220px";
}
</script>
<script type="text/javascript" src="jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript">
$(function() {
    $(this).bind("contextmenu", function(e) {
        e.preventDefault();
    });
}); 
</script>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}


$sql8="select 'UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINT LIST' PHONE1";

$sql9="select SNO, POLICE_STATION, ZONE, CRIME_NO, SECTION, TIN_NO, DATE_OF_IDENTITY, 
LOSS_OF_PROPERTY, NAME_AND_PARTICULARS, IRKEY, CCNO, DOA, REMARKS,IMAGE  from IRFORMS..FINGERPRINT_MATCHED_UNDETECTED_CASES_WITHIMAGE
ORDER BY ZONE,IRKEY";

$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ZONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SECTION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TIN_NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_IDENTITY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LOSS_OF_PROPERTY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME_AND_PARTICULARS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CCNO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DOA</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>REMARKS</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ZONE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['SECTION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['TIN_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DATE_OF_IDENTITY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LOSS_OF_PROPERTY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME_AND_PARTICULARS'] ."<center></font></td>";
echo "<td height=200px width=200px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'IR.PHP?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CCNO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DOA'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['REMARKS'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);

?>
</body>
</html>dy>
</html>