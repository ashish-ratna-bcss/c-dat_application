<html>
<head>
</head>
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
    background-color: #F9FBFC;
    padding: 20px;
}
</style>


<li><a href="ALLDATA.PHP">Back</a></li></p>
<form action ='ALLDATA_SEARCH.php'method='post'>
<b><font size=4 face=verdana text-align:'center' color='#ffb84d'>ENTER PHONE_NO : </b>
 <div class="form-group">
    <label class="sr-only" for="exampleInputEmail3"></label>
    <input type="TEXT" name="PHONE" placeholder="PHONE"> 
 <input type="submit" value="Submit">


<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
	}
if (isset($_POST['PHONE'])){
$number=$_POST['PHONE'];

$sql1="SELECT 'PHONE NO SEARCH IN SDR:'+'$number' as PHONE1";
$sql2="SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS,CONVERT(VARCHAR,DOA,20) AS DOA   FROM CDATDUPL.dbo.CDATADDRESS
       WHERE PHONE ='$number'";
$sql3="SELECT 'PHONE NO SEARCH IN RTA:'+'$number' as PHONE2";
$sql4="SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS+','+CITY FULLADDRESS,
       REGN_NO+' ENG_NO:'+ENG_NO+' CHAS_NO:'+CHAS_NO+' MKR_NAME: '+MKR_NAME+' MKR_CLAS: '+MKR_CLAS AS VEHICLE_DETAILS
	   FROM CDATDUPL.dbo.CDAT_RTA WHERE PHONE ='$number'";
$sql5="SELECT 'PHONE NO SEARCH IN LICENCE_DATA:'+'$number' as PHONE3";
$sql6="SELECT  PHONE,LICENCE_NO,FULLNAME,FATHER_NAME AS FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS FROM CDATDUPL.dbo.CDAT_LICENCE
       WHERE PHONE ='$number'";
$sql7="SELECT 'PHONE NO SEARCH IN CIVILSUPPLY_DATA:'+'$number' as PHONE3";
$sql8="SELECT  PHONE,FULLNAME,NAME_OFFICE+', '+FULLADDRESS+' '+DISTRICT AS FULLADDRESS,RATION_CARD_NO,UID_NO AADHAR_DETAILS FROM CDATDUPL.dbo.CDAT_CIVILSUPPLY
       WHERE PHONE ='$number'";
$sql9="SELECT 'PHONE NO SEARCH IN CDATSUSPECT_DATA:'+'$number' as PHONE5";
$sql10="SELECT  PHONE,NAME AS FULLNAME,ROLE,FATHER_NAME AS FATHERNAME,ADDRESS AS FULLADDRESS,
       CRIME_NO+'/'+YEAR+' OF PS '+PS+' MO: '+MO AS CRIME_DETAILS FROM CDATDUPL.dbo.CDATSUSPECT2 
       WHERE PHONE ='$number'";
$sql11="SELECT 'PHONE NO SEARCH IN PASSPORT_DATA:'+'$number' as PHONE6";
$sql12="select distinct PHONE,FILE_NUMBER,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS from cdatdupl.dbo.cdat_passport
WHERE PHONE='$number'";

$st1 = sqlsrv_query( $conn, $sql1 );
$stMT2 = sqlsrv_query( $conn, $sql2 );
$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st6 = sqlsrv_query( $conn, $sql6 );
$st7 = sqlsrv_query( $conn, $sql7 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st11 = sqlsrv_query( $conn, $sql11 );
$st12 = sqlsrv_query( $conn, $sql12 );

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

while( $row = sqlsrv_fetch_array( $stMT2, SQLSRV_FETCH_ASSOC) ){
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOB'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOA'] ."<center></font></td>";

echo "<tr>";
}
echo"</table>";


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
echo"</table>";

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
echo"</table>";

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
echo"</table>";

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
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['ROLE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['CRIME_DETAILS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table>";

echo "<tr>";
while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#000066'><td><center><b>". $row['PHONE6'] ."<center></td></font></br>";

}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FILE_NUMBER</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLNAME</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FATHERNAME</font</th>
<th bgcolor=#ffb84d><font size=3 face=verdana color='#F9FBFC'>DOB</font</th>
<th bgcolor=#ffb84d><font size=2 face=verdana color='#F9FBFC'>FULLADDRESS</font</th>
</tr>";

while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['PHONE'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FILE_NUMBER'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLNAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FATHERNAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['DOB'] ."<center></font></td>";
echo "<td width=150px bgcolor=#F9FBFC><font size=1 face=verdana><center>". $row['FULLADDRESS'] ."<center></font></td>";
echo "</tr>";
}
echo"</table>";


}

?>
</body>
</html>