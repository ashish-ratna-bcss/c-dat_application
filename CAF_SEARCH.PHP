<html>
<head>
<body>
<body bgcolor="#0C5D90">
<li><a href="CAF_SEARCH.HTML"><font color=#FDEFEF>Back</a></li>
<style type="text/css">
a:link , a:visited{
text-decoration: none;
}
</style>
<form action ='CAF_SEARCH.PHP'method='post'>
<b><font size=4 face=verdana color='#F9FBFC'><center>ENTER MOBILE NO: <input type='text' name='PHONE_NO' value='' placeholder='Enter Phone No' required='required'/></b>
<input type ='submit' name='submit' value='Submit'></p>
<?php
$serverName = "UUUU-HP";
$connectionInfo = array( "Database"=>"CAFs");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];

$sql1 ="SELECT 'ftp://192.168.144.70/'+substring(CAFS_PATH,24,50) AS PHONE INTO #T FROM IO_DETAILS WHERE PHONE='$number'";

$sql2 ="UPDATE #T SET PHONE = REPLACE(PHONE,' ','%20')";

$sql3 = "SELECT DISTINCT PHONE,'CAF Available Click Here to Open' as CLICK FROM #T";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );


if ( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {  

echo "<font size=4 face=verdana color='#F9FBFC'><blink><a style='color:#F9FBFC' href=".($row['PHONE']).">".($row['CLICK'])."</a>"; }
else{
echo "<font size=4 face=verdana color='#F9FBFC'><blink>CAF NOT AVAILABLE";
}


sqlsrv_free_stmt( $st3);
?>
</head>
</body>
</html>