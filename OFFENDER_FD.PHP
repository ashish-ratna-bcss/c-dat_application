<html>
<head>
</head>
<body bgcolor="#0C5D90">
<script>
function bigImg(x) { 
x.style.height="400px";
x.style.width="400px";
}
function normalImg(x) { 
x.style.height="200px";
x.style.width="220px";
}
</script>;
<script type="text/javascript" src="ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript">
$(function() {
    $(this).bind("contextmenu", function(e) {
        e.preventDefault();
    });
}); 
</script>
<script type="text/JavaScript"> 
    function killCopy(e){ return false } 
    function reEnable(){ return true } 
    document.onselectstart=new Function ("return false"); 
    if (window.sidebar)
    { 
        document.onmousedown=killCopy; 
        document.onclick=reEnable; 
    } 
</script>
<script language="javascript">
document.onmousedown=disableclick;
status="Right Click Disabled";
function disableclick(e)
{
if(event.button="2")
{
alter(status);
return false;
}
}
</script>
<li><a href="OFFENDER_SEARCH_BY_MO.HTML">Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_GET['MO_KEY'];


$sql0="SELECT ACC_NAME,IMAGE FROM COMPLETE_MO_CLASSIFICATION A LEFT JOIN MO_IMAGE_TABLE B ON A.MO_KEY=B.MO_KEY WHERE A.MO_KEY='$number'";

$sql1="SELECT DISTINCT MO_KEY, PHONE, ROLE, CATEGORY, ACC_NAME, FATHER_NAME, DATE_OF_BIRTH, AGE, FULLADDRESS, CITY_OR_DISTRICT, STATE, 
ID_PROOF, CRIME_HEAD, MO1, MO2, CRIME_NO, Year, SEC_OF_LAW, DATE_OF_ARREST, 
PLACE_OF_OFF, off_lat, off_long, POLICE_STATION, PS_DIVISION, PS_ZONE, 
INC_OFFICER, OFFICIAL_MAILID FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION WHERE MO_KEY='$number'";


$st0 = sqlsrv_query( $conn, $sql0 );
$st1 = sqlsrv_query( $conn, $sql1 );
echo "<form>";
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=800px ><font size=3 face=verdana color='#F9FBFC'>OFFENDERS LIST</font></th>
</tr>";
echo "</table>";
echo "</br>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st0, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['ACC_NAME'] ."<center></font></td>";
echo "<td height=200px width=200px>";?> <center><?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="data:image; base64,'.$row["IMAGE"].'"></img>' ?></center> <?php "</td>";
echo "</tr>";
}
echo "</table>";
echo "</br>";


echo "<table border=1 cellspacing=0 cellpadding=5>";
echo "<tr  bgcolor=#921215>";
echo "<th width=785px ><font size=3 face=verdana color='#F9FBFC'>OFFENDER PARTICULARS</font></th>";
echo "</tr>";
echo "</table>";

echo "<table width=800px border=1 cellspacing=0 cellpadding=5>";
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<center>";
echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO_KEY</font></th>"; 
echo "<td width=638px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MO_KEY']."<center></font></td>";
echo "</tr>";
echo "</center>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ACC_NAME']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['FATHER_NAME']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_BIRTH</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DATE_OF_BIRTH']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['AGE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['FULLADDRESS']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CITY_OR_DISTRICT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CITY_OR_DISTRICT']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['STATE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDPROOF</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ID_PROOF']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CRIME_HEAD']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO SUB CLASSIFICATION1</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MO1']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO SUB CLASSIFICATION2</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MO2']."<center></font></td>"; 
echo "</tr>";
echo "<tr>";   
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CRIME_NO']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>YEAR</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['Year']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEC_OF_LAW</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['SEC_OF_LAW']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_ARREST</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DATE_OF_ARREST']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PLACE_OF_OFF</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PLACE_OF_OFF']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OFFENCE LAT</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['off_lat']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OFFENCE LONG</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['off_long']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['POLICE_STATION']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PS_DIVISION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PS_DIVISION']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PS_ZONE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PS_ZONE']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>INC_OFFICER</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['INC_OFFICER']."<center></font></td>"; 
echo "</tr>";
echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OFFICIAL_MAILID</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['OFFICIAL_MAILID']."<center></font></td>"; 
echo "</tr>";
}
echo "</table>";
echo "</br>";

echo "</form>";

?>
</body>
</html>