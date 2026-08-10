<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Rowdysheeter Ps Wise Search Php");
?>

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
<li><a href="ir_search.php">Back</a></li>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number=$_POST['POLICE_STATION'];


$sql0="SELECT DISTINCT IRKEY,PDACT_KEY,NAME,AGE,FATHER_NAME,PHONE,PRESENT_ADDRESS,LAT_P PRESENT_ADDRESS_LAT,
LONG_P PRESENT_ADDRESS_LONG,PERMANENT_ADDRESS,LAT PERMANENT_ADD_LAT,LONG PERMANENT_ADD_LONG,ID_PROOF_TYPE+' '+ID_NO IDPROOF,
COMMUNAL_NONCOMMUNAL COMMUNAL_STATUS,LATEST_BIND_OVER_DATE BIND_OVER_DATE,POLICE_STATION,PRESENT_ACTIVITY,DATE_OF_OPENING_RWD INTO #TEMP FROM ROWDY_SHEETER_DATA1 
WHERE POLICE_STATION LIKE '%$number%'";

$sql1="select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,PHONE,PRESENT_ADDRESS,PERMANENT_ADDRESS,PRESENT_ACTIVITY,IDPROOF,COMMUNAL_STATUS,
CONVERT(VARCHAR(20),DATE_OF_OPENING_RWD) AS DATE_OF_OPENING_RWD,POLICE_STATION,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";


$st0 = sqlsrv_query( $conn, $sql0 );
$st1 = sqlsrv_query( $conn, $sql1 );



echo "<table border=1 cellspacing=0 cellpadding=5>
<tr  bgcolor=#921215>
<th width=1320px ><font size=3 face=verdana color='#F9FBFC'>ROWDY SHEET INFORMATION</font></th>
</tr>";
echo "</table>";

echo "<table width=815px border=1 cellspacing=0 cellpadding=5>
<tr>

<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PERMANENT_ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ACTIVITY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IDPROOF</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DATE OF OPENING RWD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT KEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>

</tr>";

while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";

echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PRESENT_ADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PERMANENT_ADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['PRESENT_ACTIVITY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IDPROOF'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['DATE_OF_OPENING_RWD'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana ><center><a href=".'pdact_main.php?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT_KEY'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td height=150px width=150px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="200" width="220" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";

echo "</tr>";
}
echo "</table>";


?>
<?php layout_end(); ?>
