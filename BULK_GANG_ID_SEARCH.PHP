<html>
<head>
</head>
<body bgcolor="#0C5D90">
<script>
//*function bigImg(x) { 
x.style.height="400px";
x.style.width="400px";
}
function normalImg(x) { 
x.style.height="200px";
x.style.width="220px";
}
</script>;*//
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

if (isset($_POST['IRKEY'])){

$number= $_POST['IRKEY'];

$number2 = str_replace(",","','","$number");

echo "<font size=4 face=verdana  color='#F9FBFC'><td><center><b>BULK IR SEARCH<center></td></font></br>";


$sql10="SELECT PHOTO_ID_1,BASE64_IMAGE FROM GANG_FILES_MASTER_TABLE.DBO.IMAGES_BASE64_FORMAT  WHERE PHOTO_ID_1 IN ('$number2')";

$st10 = sqlsrv_query( $conn, $sql10);

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'IR.PHP?IRKEY='.($row['PHOTO_ID_1']).">". $row['PHOTO_ID_1'] ."<center></font></td>";
echo "<td height=100px width=100px>";?> <?php echo '<img onmouseover="bigImg(this)" onmouseout="normalImg(this)" height="100" width="100" src="'.cdat_base64_image_src($row['BASE64_IMAGE']).'"></img>' ?> <?php "</td>";
echo "</tr>";
}

}
echo "</table>";
?>
</body>
</html>
</html>