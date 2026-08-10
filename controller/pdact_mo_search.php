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
$number=$_POST['MO'];


$sql0="select distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI into #temp from PDACT_MAIN_TABLE WHERE (CRIME_HEAD LIKE '%$number%' OR MINOR_HEAD LIKE '%$number%'
OR MODUSOPERENDI LIKE '%$number%' OR CRIME_HEAD_SEARCH LIKE '%$number%')";

$sql1="select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CRIME_HEAD,MINOR_HEAD,MODUSOPERENDI,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";


$st0 = sqlsrv_query( $conn, $sql0 );
$st1 = sqlsrv_query( $conn, $sql1 );


echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=1320px ><font size=3 face=verdana color='#F9FBFC'>ACCUSED INFORMATION</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT_KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_DISTRICT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NATIVE_STATE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PD_ACT_PS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE_OF_PDACT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'CRIME_HEAD</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center><a href=".'pdact_main.php?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT_KEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td height=150px width=150px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_DISTRICT'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NATIVE_STATE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PD_ACT_PS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DATE_OF_PDACT'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";

echo "</tr>";
}
echo "</table>";


?>
</body>
</html>