<?php
// One page for both halves of this screen: the form, and the results.
// Was view/calls_btwn_dates1.html (form) + controller/calls_btwn_dates1.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);
?>
<?php
require_once __DIR__ . '/includes/layout.php';
layout_begin("Calls Btwn Dates1");
?>


<div align="center">

<table width="1300" border="2">
<tr>
<td>

<!-- MENU -->
<table width="100%">
<tr>
<td align="center" background="../assets/images/topborder.jpg">

</td>
</tr>
</table>

<br><br>

<!-- FORM -->
<table width="800" align="center">
<tr>
<th background="../assets/images/border.jpg">
CALL DETAILS OF MOBILE NUMBER BETWEEN DATES
</th>
</tr>

<tr>
<td align="center" background="../assets/images/border.jpg">

<form method="post" action="calls_btwn_dates1.php">

<label>Mobile No:</label>
<input type="text" name="PHONE_NO" pattern="[0-9]{10}" maxlength="10" placeholder="Enter 10-digit Mobile No" required>

<br>

<label>Operator:</label>
<select name="OPERATOR" required>
<option value="" disabled selected>Select Operator</option>
<option value="AIRTEL_TOWER">AIRTEL</option>
<option value="JIO_TOWER">JIO</option>
<option value="VODAFONE_TOWER">VODAFONE</option>
<option value="IDEA_TOWER">IDEA</option>
</select>

<br>

<label>State:</label>
<select name="STATE" required>
<option value="" disabled selected>Select State</option>
<option value="ANDHRA_PRADESH">ANDHRA PRADESH</option>
<option value="TELANGANA">TELANGANA</option>
<option value="KARNATAKA">KARNATAKA</option>
<option value="TAMILNADU">TAMILNADU</option>
<option value="MAHARASHTRA">MAHARASHTRA</option>
</select>

<br>

<label>Date From:</label>
<input type="text" name="FROM_DT" id="datepickerID" placeholder="yyyy-mm-dd" required>

<label>To:</label>
<input type="text" name="TO_DT" id="datepickerID1" placeholder="yyyy-mm-dd" required>

<br><br>

<input type="submit" value="Submit">

</form>

</td>
</tr>
</table>

</td>
</tr>
</table>

</div>




<?php if ($__submitted): ?>
<li><a href="calls_btwn_dates1.php">
<font color="#FDEFEF">Back</font></a></li>

<?php
require_once __DIR__ . '/cdr_enrichment_sql.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database"=>"CDATDUPL");

$conn = sqlsrv_connect($serverName, $connectionInfo);

if($conn === false){
    die(print_r(sqlsrv_errors(), true));
}

$number = $_POST['PHONE_NO'] ?? '';
$state  = $_POST['STATE'] ?? '';
$f_date = $_POST['FROM_DT'] ?? '';
$t_date = $_POST['TO_DT'] ?? '';

$sql = cdr_sql_calls_between_dates($number, $f_date, $t_date, $state);
$stmt = sqlsrv_query($conn, $sql);

if($stmt === false){
    die(print_r(sqlsrv_errors(), true));
}

echo "<center>
<font size=4 color='#F9FBFC'>
<b>CALL DETAILS OF MOBILE NO: $number FROM: $f_date TO: $t_date</b>
</font></center><br>";

echo "<table border=1 cellpadding=5 align=center>
<tr bgcolor=#921215>
<th><font color=#fff>PHONE</font></th>
<th><font color=#fff>OTHER</font></th>
<th><font color=#fff>STARTTIME</font></th>
<th><font color=#fff>DURATION</font></th>
<th><font color=#fff>TYPE</font></th>
<th><font color=#fff>IMEI</font></th>
<th><font color=#fff>CELLID</font></th>
<th><font color=#fff>OPERATOR</font></th>
<th><font color=#fff>AREA</font></th>
</tr>";

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
echo "<tr>";
echo "<td>".htmlspecialchars($row['PHONE'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['OTHER'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['STARTTIME'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['DURATION'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['TYPE'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['IMEINUMBER'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['CELLTOWERID'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['OPERATOR'] ?? '')."</td>";
echo "<td>".htmlspecialchars($row['AREADESCRIPTION'] ?? '')."</td>";
echo "</tr>";
}

echo "</table>";

?>
<?php endif; ?>
<?php layout_end(); ?>
