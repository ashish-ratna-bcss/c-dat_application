<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="ir_search.php"><font color=#FDEFEF>Back</a></li>
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
<script type="text/javascript" src="ajax/libs/jquery/1/jquery.min.js"></script>
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


$sql8="select 'HABITUAL OFFENDERS' PHONE1";

$sql9="SELECT IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, PRESENT_ADDRESS, ARRESTED_IN_CRIMEHEAD, MO, CRIME_NO, YEAR, SEC_OF_LAW, POLICE_STATION, count1, image FROM IRFORMS..HABITUAL_OFFENDERS ORDER BY COUNT1 desc";

$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ALIAS NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARRESTED CRIME NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARRESTED YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARRESTED SEC_OF_LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ARRESTED CRIME HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>TOTAL NUMBER OF CRIMES INVOLVED</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ALIAS_NAME'] ."<center></font></td>";
echo "<td height=200px width=200px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['image']).'"></img>' ?> <?php "</td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PRESENT_ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIME_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['YEAR'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['ARRESTED_IN_CRIMEHEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['count1'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);

?>
</body>
</html>