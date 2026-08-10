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
<li><a href="../view/ir_search.htm">Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"PDACT");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_GET['PDACT_KEY'];


$sql0="select distinct PDACT_KEY,IRKEY,NAME,FATHER_NAME,AGE,DISTRICT NATIVE_DISTRICT,STATE NATIVE_STATE INTO #TEMP from PDACT_MAIN_TABLE
WHERE PDACT_KEY='$number'";

$sql2="select A.PDACT_KEY,A.IRKEY,A.NAME,A.FATHER_NAME,A.AGE,NATIVE_DISTRICT,NATIVE_STATE,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE from #TEMP A LEFT JOIN 
FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)";

$sql1="SELECT distinct  PD_ACT_PS,ZONE,FILE_NO,DETENU_NO,CONVERT(VARCHAR(20),ORDER_ISSUED_ON) ORDER_ISSUED_ON,APPROVAL_ORDERS_NO,CONFIRMATION_REVOCATION_ORDERS,CRIME_HEAD,MINOR_HEAD
MODUSOPERENDI,POLICE_STATION,WHETHER_INVOLVED_IN_OTHER_UNIT_CASES,NAME_OF_UNITS,NO_OF_CASES,
CONVERT(VARCHAR(20),DATE_OF_ARREST) PDACT_DATE,CONVERT(VARCHAR(20),DATE_OF_RELEASE) DATE_OF_RELEASE,BRIEF_FACTS FROM PDACT_MAIN_TABLE
WHERE PDACT_KEY='$number'";



$st0 = sqlsrv_query( $conn, $sql0 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st1 = sqlsrv_query( $conn, $sql1 );

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=1010px ><font size=3 face=verdana color='#F9FBFC'>ACCUSED INFORMATION</font></th>
</tr>";
echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center>". $row['PDACT_KEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_DISTRICT'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_STATE'] ."<center></font></td>";
echo "<td height=200px width=200px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";

echo "</tr>";
}
echo "</table>";
echo "</br>";


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=1075px ><font size=3 face=verdana color='#F9FBFC'>PDACT DETAILS</font></th>
</tr>";
echo "<table border=1 cellspacing=0 cellpadding=5>";
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<center>";
echo "<tr>"; 
echo "<th width=50px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PD_ACT_PS</font></th>"; 
echo "<td width=638px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PD_ACT_PS']."<center></font></td>";
echo "</tr>";
echo "</center>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ZONE</font></th>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ZONE']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FILE_NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['FILE_NO']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DETENU_NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DETENU_NO']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ORDER_ISSUED_ON</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['ORDER_ISSUED_ON']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>APPROVAL_ORDERS_NO</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['APPROVAL_ORDERS_NO']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CONFIRMATION_REVOCATION_ORDERS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CONFIRMATION_REVOCATION_ORDERS']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['CRIME_HEAD']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MODUSOPERENDI</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['MODUSOPERENDI']."<center></font></td>";
echo "</tr>";
 

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE_STATION</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['POLICE_STATION']."<center></font></td>"; 
echo "</tr>";


echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>WHETHER_INVOLVED_IN_OTHER_UNIT_CASES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['WHETHER_INVOLVED_IN_OTHER_UNIT_CASES']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NO_OF_CASES</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['NO_OF_CASES']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT_DATE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['PDACT_DATE']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_RELEASE</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['DATE_OF_RELEASE']."<center></font></td>"; 
echo "</tr>";

echo "<tr>"; 
echo "<th width=150px bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BRIEF_FACTS</font></th>"; 
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>".$row['BRIEF_FACTS']."<center></font></td>"; 
echo "</tr>";


}
echo "</table>";
echo "</table>";

?>
</body>
</html>