<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="../view/ir_search_by_head_gender.htm"><font color=#FDEFEF>Back</a></li>
<form action ='ir_search_by_head_gender.php'method='post'>
<b><font size=3 face=verdana color='#F9FBFC'>GENDER : </b>
<select name="GENDER" placeholder="PLZ Select Gender">
<option value=""></option>
<option value="FEMALE">FEMALE</option>
<option value="MALE">MALE</option>
<option value="TRANSGENDER">TRANSGENDER</option>
</select>
<b><font size=3 face=verdana color='#F9FBFC'>CRIME_HEAD : </b>
<input type='text' name='CRIME_HEAD' value=''>
<input type ='submit' value='Submit'/>

<?php
$serverName = "10.10.46.14\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['GENDER'])){

$number=$_POST['GENDER'];
$number1=$_POST['CRIME_HEAD'];

$sql8="SELECT 'DETAILS OF : '+'$number' as PHONE1";

$sql9="SELECT DISTINCT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
ELSE '' END PDACT_KEY,NAME,ALIAS_NAME,FATHER_NAME,AGE,SEX,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST FROM FORMS..IR_PARTICULARS A
INNER JOIN FORMS..OFFENCE_DETAILS B ON A.SEX ='$number' AND (B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' OR 
B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%') AND 
ltrim(rtrim('$number'))!=''  AND A.IRKEY=B.IRKEY ORDER BY DATE_OF_ARREST DESC";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IRKEY</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PDACT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ACCUSED NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ALIAS NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FATHER NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEX</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PRESENT ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME NO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>YEAR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SEC_OF_LAW</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>POLICE STATION</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CRIME HEAD</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DOA</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'ir.php?IRKEY='.($row['IRKEY']).">". $row['IRKEY'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center><a href=".'pdact_main.php?PDACT_KEY='.($row['PDACT_KEY']).">". $row['PDACT'] ."<center></font></td>";
echo "<td width=25px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=10px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['ALIAS_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AGE'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['SEX'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['PRESENT_ADDRESS'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['CRIME_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['YEAR'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['SEC_OF_LAW'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['POLICE_STATION'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['CRIME_HEAD'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['MO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['DATE_OF_ARREST'] ."<center></font></td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
</body>
</html>dy>
</html>