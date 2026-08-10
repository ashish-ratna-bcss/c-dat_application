<?php
// One page for both halves of this screen: the form, and the results.
// Was view/namesearch.htm (form) + controller/namesearch.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="../assets/vendor/jquery-ui-1.10.4.custom/css/dark-hive/jquery-ui-1.10.4.custom.min.css">
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../assets/vendor/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js"></script>
<body>
<style>
input[type=text], select {
    width: 30%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 20%;
    background-color: ORANGE;
    color: white;
    padding: 3px 10px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type=submit]:hover {

    background-color: #45a049;
}

div {
    border-radius: 20px;
    background-color: #f2f2f2;
    padding: 20px;
}
</style>
<body>


<form NAME="PSFORMS" action="namesearch.php" onsubmit="validateForm()" method="post">
  <div class="form-group">
    <label class="sr-only" for="exampleInputEmail3"></label>
    <input type="TEXT" name="NAME" placeholder="NAME"> <input type="TEXT"name="ADDRESS" placeholder="ADDRESS">
 <input type="submit" value="Submit">

  </div>



</form>



<?php if ($__submitted): ?>
<style>
input[type=text], select {
    width: 30%;
    padding: 3px 10px;
    margin: 4px 0;
    display: inline-block;
    border: 1px solid #ccc;
    border-radius: 2px;
    box-sizing: border-box;
}

input[type=submit] {
    width: 20%;
    background-color: ORANGE;
    color: white;
    padding: 3px 10px;
    margin: 4px 0;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

input[type=submit]:hover {

    background-color: #45a049;
}

div {
    border-radius: 20px;
    background-color: #f2f2f2;
    padding: 20px;
}
</style>


</p>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
if (isset($_POST['NAME']))
if (isset($_POST['ADDRESS']))
{
$NAME 	= $_POST['NAME'];
$ADDRESS= $_POST['ADDRESS'];

$sql1="SELECT 'NAME SEARCH IN SDR:'+'$NAME' as PHONE1";
$sql2="SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS,CONVERT(VARCHAR,DOA,20) AS DOA   FROM CDATDUPL.dbo.CDATADDRESS
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' OR DISTRICT LIKE '%'+'$ADDRESS'+'%')";
$sql3="SELECT 'NAME SEARCH IN RTA:'+'$NAME' as PHONE2";
$sql4="SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS+','+CITY FULLADDRESS,
       REGN_NO+' ENG_NO:'+ENG_NO+' CHAS_NO:'+CHAS_NO+' MKR_NAME: '+MKR_NAME+' MKR_CLAS: '+MKR_CLAS AS VEHICLE_DETAILS
	   FROM CDATDUPL.dbo.CDAT_RTA 
	   WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' OR DISTRICT LIKE '%'+'$ADDRESS'+'%')";
$sql5="SELECT 'NAME SEARCH IN LICENCE_DATA:'+'$NAME' as PHONE3";
$sql6="SELECT  PHONE,LICENCE_NO,FULLNAME,FATHER_NAME AS FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS FROM CDATDUPL.dbo.CDAT_LICENCE
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND FULLADDRESS LIKE '%'+'$ADDRESS'+'%'";
$sql7="SELECT 'NAME SEARCH IN CIVILSUPPLY_DATA:'+'$NAME' as PHONE3";
$sql8="SELECT  PHONE,FULLNAME,NAME_OFFICE+', '+FULLADDRESS+' '+DISTRICT AS FULLADDRESS,RATION_CARD_NO,UID_NO AADHAR_DETAILS FROM CDATDUPL.dbo.CDAT_CIVILSUPPLY
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE'%'+'$ADDRESS' 
OR DISTRICT LIKE '%'+'$ADDRESS'+'%' OR NAME_OFFICE LIKE '%'+'$ADDRESS'+'%')";
$sql9="SELECT 'NAME SEARCH IN CDATSUSPECT_DATA:'+'$NAME' as PHONE5";
$sql10="SELECT  PHONE,NAME AS FULLNAME,ROLE,FATHER_NAME AS FATHERNAME,ADDRESS AS FULLADDRESS,
       CRIME_NO+'/'+YEAR+' OF PS '+PS+' MO: '+MO AS CRIME_DETAILS FROM CDATDUPL.dbo.CDATSUSPECT2 
       WHERE NAME LIKE '%'+'$NAME'+'%' AND (ADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' )";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query($conn,$sql10 );
{
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FATHERNAME</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>DOB</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>DOA</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st2, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOB'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOA'] ."<center></font></td>";
echo "</tr>";
}
echo"</table><br />";
{
echo "<tr>";
while( $row = sqlsrv_fetch_array( $st3, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE2'] ."<center></td></font></br>";

}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FATHERNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>DOB</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>VEHICLE_DETAILS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st4, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOB'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['VEHICLE_DETAILS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table><br />";

{
echo "<tr>";
while( $row = sqlsrv_fetch_array( $st5, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE3'] ."<center></td></font></br>";

}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>LICENCE_NO</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FATHERNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>DOB</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st6, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['LICENCE_NO'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOB'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table><br />";
{
	echo "<tr>";
while( $row = sqlsrv_fetch_array( $st7, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE3'] ."<center></td></font></br>";

}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>RATION_CARD_NO</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>AADHAR</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['RATION_CARD_NO'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['AADHAR_DETAILS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table><br />";

{
	echo "<tr>";
while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE5'] ."<center></td></font></br>";

}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>ROLE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FATHERNAME</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>CRIME_DETAILS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>".  $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['ROLE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['CRIME_DETAILS'] ."<center></font></td>";
echo "</tr>";


}
echo"</table><br />";
}
}
}
}
}
}

?>
</div>
<?php endif; ?>
</body>
</html>