<html>
<head>
</head>
<body bgcolor="#0C5E95">
<p><a href="RETRIEVE.html">BACK</a></p>
<body>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"FORMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
die( print_r( sqlsrv_errors(), true));
}
$NAME=$_POST['NAME'];
$FATHER_NAME=$_POST['FATHER_NAME'];
$sql="SELECT A.IRKEY,NAME,FATHER_NAME,B.[IMAGE] FROM IR_PARTICULARS A INNER JOIN IMAGE_TABLE  B 
ON A.IRKEY=B.IRKEY AND A.CATEGORY=B.CATEGORY
WHERE A.NAME LIKE '%'+'$NAME'+'%' AND A.FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%'";
$sql1="SELECT case when count(NAME)>=1 THEN '' ELSE '*** NO RECORD FOUND TO NAME:$NAME AND FATHER NAME:$FATHER_NAME ***' end as DETAILS 
FROM [FORMS].[dbo].[IR_PARTICULARS] WHERE NAME LIKE '%'+'$NAME'+'%' AND FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%'";
$st1 = sqlsrv_query( $conn, $sql);
$st2 = sqlsrv_query( $conn, $sql1 );
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
</tr>";
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['IRKEY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="300" width="300" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "</tr>";

}
echo"</table><br />";
while( $row = sqlsrv_fetch_array($st2, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['DETAILS'] ."<center></td></font></br>";
}

?>
</body>
</html>