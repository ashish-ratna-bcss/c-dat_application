<?php
// One page for both halves of this screen: the form, and the results.
// Was view/training_module1.htm (form) + controller/training_module1.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TRAINING MODULE</title>
<script src="../assets/spry/sprymenubar.js" type="text/javascript"></script>
<link href="../assets/spry/sprymenubarhorizontal.css" rel="stylesheet" type="text/css" />
</head>
<body background="../assets/images/emp.png">
<div align="center">
      <table width="625" height="124">
<br>
&nbsp;
/*TRAINING WING background="../assets/images/emp.png"* /
        <tr>
          <th height="26" bgcolor="#00008B" class="CDAT" scope="col"><font color="white">EMPLOYEE SEARCH</font></th>
        </tr>
        <tr>
        <form id="form1" name="form1" method="post" action="training_module1.php">
                 <th width="555" height="90" bgcolor="#F5DEB3" class="CDAT" scope="col">

Select search criteria : <select type="text" name="EMPLOYEE_SEARCH">
<option value=""></option>
<option value="EMPLOYEE_ID">EMPLOYEE ID</option>
<option value="GENERAL_NO">GENERAL NO</option>
<option value="NAME">NAME</option>
</select>
            <input type="text" name="EMPLOYEE_SEARCH_NO" id="CAF" placeholder="Emp Search" required="required"/>
<br><br>
Select Rank : <select type="text" name="EMPLOYEE_SEARCH_RANK">
<option value=""></option>
<option value="INSPECTOR">INSPECTOR</option>
<option value="SI">SI</option>
<option value="ASI">ASI</option>
<option value="HC">HC</option>
<option value="PC">PC</option>
<option value="HG">HG</option>
</select>

            <input type="submit" name="BTN_CDAT" id="BTN_CDAT" value="Submit" /></th>
        </form></tr>
      </table>
    </tr>
  </table>
 <table width="625" height="347">
          <tr>
            <td height="310" align="centre" valign="top"><div align="center"><img src="../assets/images/training_db1.gif" width="600" height="350" /></div></td>
          </tr>

       </table>

</div>
<script type="text/javascript">
var MenuBar1 = new Spry.Widget.MenuBar("MenuBar1", {imgDown:"../assets/spry/sprymenubardownhover.gif", imgRight:"../assets/spry/sprymenubarrighthover.gif"});
</script>

<?php if ($__submitted): ?>
</p>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TRAINING_DB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

if (isset($_POST['EMPLOYEE_SEARCH_NO'])){

$number=$_POST['EMPLOYEE_SEARCH'];
$number1=$_POST['EMPLOYEE_SEARCH_NO'];
$number2=$_POST['EMPLOYEE_SEARCH_RANK'];

$sql8="SELECT 'EMPLOYEE SEARCH IN PWDMS' as PHONE1";

$sql9="SELECT DISTINCT EMPLOYEE_ID,NAME,[RANK],[ROLE],GENERAL_NO,WING_NAME,ZONE_NAME,DIVISION_NAME,
POLICE_STATION FROM TRAINING_DB.DBO.TRAINING_STRENGTH_PARTICULARS WHERE $number like '%'+'$number1'+'%' 
AND RANK LIKE '%'+'$number2'+'%'";

$sql10="SELECT 'EMPLOYEE SEARCH IN TRAINING DATA' as PHONE1";


$sql11="SELECT DISTINCT EMPLOYEE_ID,GENERAL_NO,NAMES NAME,PS_NAME POLICE_STATION,PH_NO PHONE_NO,ZONE,
RANK,COURSE_NAME,START_DATE,END_DATE FROM TRNG_ATT_WITH_EMPID WHERE $number like '%'+'$number1'+'%' AND 
RANK LIKE '%'+'$number2'+'%'";

$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query($conn, $sql10 );
$st11 = sqlsrv_query($conn, $sql11 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#00008B'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
/* <tr bgcolor=#921215> */ 
<tr  bgcolor=#00008B>
<th><font size=3 face=verdana color='#F9FBFC'>EMP ID</th>
<th><font size=3 face=verdana color='#F9FBFC'>GEN NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>RANK</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ROLE</font></th>

<th><font size=3 face=verdana color='#F9FBFC'>WING</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ZONE NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>DIVISION NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['EMPLOYEE_ID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['GENERAL_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['RANK'] ."<center></font></td>";
echo "<td width=150px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['ROLE'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['WING_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['ZONE_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['DIVISION_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['POLICE_STATION'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d='.'EMP_ID: '.$row["EMPLOYEE_ID"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' RANK:'.$row["RANK"]. ' ROLE:'.$row["ROLE"].' GEN_NO:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["GENERAL_NO"]).' PS: '.$row["POLICE_STATION"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}
echo "</table>";

echo "</br>";

while( $row = sqlsrv_fetch_array( $st10, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#00008B'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
/* <tr bgcolor=#921215> */ 
<tr bgcolor=#00008B>
<th><font size=3 face=verdana color='#F9FBFC'>EMP ID</th>
<th><font size=3 face=verdana color='#F9FBFC'>GEN NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>RANK</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ZONE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>COURSE NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>START DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>END DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

while( $row = sqlsrv_fetch_array( $st11, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['EMPLOYEE_ID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana><center>". $row['GENERAL_NO'] ."<center></font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['RANK'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['PHONE_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['ZONE'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['POLICE_STATION'] ."</font></td>";
echo "<td width=50px bgcolor=#F5DEB3><font size=1 face=verdana>". $row['COURSE_NAME'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['START_DATE'] ."</font></td>";
echo "<td width=50px bgcolor=#FFEFD5><font size=1 face=verdana>". $row['END_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="../qrcode/php/qr_img.php?d='.'EMP_ID: '.$row["EMPLOYEE_ID"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' RANK:'.$row["RANK"].' GEN_NO:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["GENERAL_NO"]).' PS: '.$row["POLICE_STATION"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

sqlsrv_free_stmt( $st9);
}
?>
<?php endif; ?>
</body>
</html>
