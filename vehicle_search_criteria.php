<?php
// Search CDAT_RTA by a chosen column. Distinct from VEHICLE_SEARCH.php, which
// only ever matches a registration number.
//
// Two things were wrong with this page. Its form posted to VEHICLE_SEARCH.PHP,
// so the results block below never ran from it; and that block is guarded on
// $_POST['VEHICLE_SOURCE'] -- the column to search -- which the form never had
// a field for. So the query could not run from anywhere. The selector is now
// on the form and the form posts to itself.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

// VEHICLE_SOURCE goes into the WHERE clause as a column name, where quoting
// cannot help. Only these are accepted; anything else falls back to REGN_NO.
$CDAT_RTA_COLUMNS = [
    'REGN_NO'     => 'Registration No',
    'FULLNAME'    => 'Owner Name',
    'FATHERNAME'  => 'Father Name',
    'PHONE'       => 'Phone No',
    'ENG_NO'      => 'Engine No',
    'CHAS_NO'     => 'Chassis No',
    'FULLADDRESS' => 'Address',
];
$source = $_POST['VEHICLE_SOURCE'] ?? '';
if (!isset($CDAT_RTA_COLUMNS[$source])) {
    $source = 'REGN_NO';
}

require_once __DIR__ . '/cdat_chrome.php';
cdat_page_top('Vehicle Search Criteria');
?>
<form action="vehicle_search_criteria.php" method="post" class="cdat-form">
<b><font size=4 face=verdana>SEARCH BY:</b>
<select name="VEHICLE_SOURCE">
<?php foreach ($CDAT_RTA_COLUMNS as $col => $label): ?>
  <option value="<?= $col ?>"<?= $col === $source ? ' selected="selected"' : '' ?>><?= $label ?></option>
<?php endforeach; ?>
</select>
&nbsp;<b><font size=4 face=verdana>VALUE:</b>
<input type="text" name="VEHICLE_NO" value="<?= htmlspecialchars($_POST['VEHICLE_NO'] ?? '', ENT_QUOTES) ?>"
       placeholder="Enter Vehicle No" required="required">
<input type="submit" value="Submit"/>
</form>

<?php if ($__submitted): ?>
<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$number=$_POST['VEHICLE_NO'];
$number1=$source;

$sql8="SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";

$sql9="SELECT DISTINCT REGN_NO, FULLNAME AS NAME,FATHERNAME AS FATHER_NAME,FULLADDRESS+', '+CITY AS ADDRESS,PHONE AS PHONE_NO,MKR_CLAS+', COLOR: '+COLOUR+', '+VEH_CLASS AS
VEHICLE_TYPE, ENG_NO,CHAS_NO,CONVERT(VARCHAR,ISS_DT,106) AS ISSUED_DATE FROM CDATDUPL.[dbo].[CDAT_RTA] WHERE $number1 LIKE '%'+'$number'";


$st8 = sqlsrv_query( $conn, $sql8 );
$st9 = sqlsrv_query( $conn, $sql9 );
sqlsrv_render_query_error($st9, 'Vehicle search');

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5 id=mytable>
<tr bgcolor=#921215>
<th><font size=3 face=verdana color='#F9FBFC'>REGN_NO</th>
<th><font size=3 face=verdana color='#F9FBFC'>NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>FATHER_NAME</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>PHONE_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>VEHICLE_TYPE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ENG_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>CHAS_NO</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>ISSUED_DATE</font></th>
<th><font size=3 face=verdana color='#F9FBFC'>QRCODE</font></th>
</tr>";

$rows = 0;
while( $row = sqlsrv_fetch_array( $st9, SQLSRV_FETCH_ASSOC) ) {
$rows++;
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['REGN_NO'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['NAME'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['FATHER_NAME'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=0.5 face=verdana>". $row['ADDRESS'] ."</font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['PHONE_NO'] ."</font></td>";
echo "<td width=200px bgcolor=#AED1F1><font size=1 face=verdana>". $row['VEHICLE_TYPE'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ENG_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana>". $row['CHAS_NO'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['ISSUED_DATE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="qrcode/php/qr_img.php?d='.'REGNNO: '.$row["REGN_NO"].' NAME:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["NAME"]).' FATHERNAME:'.$row["FATHER_NAME"]. ' PHONE:'.$row["PHONE_NO"].' ADDRESS:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["ADDRESS"]).' VEH_TYPE: '.$row["VEHICLE_TYPE"].' ENG_NO: '.$row["ENG_NO"].' CHAS_NO: '.$row["CHAS_NO"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}

echo"</table>";
if ($rows === 0) {
    echo "<p class='cdat-note'>No vehicle matched that search.</p>";
}

sqlsrv_free_stmt( $st9);
?>
<!-- Backslashes and raw spaces in these paths: the directory really is
     "DROP DOWN FILTER", so the URL needs the spaces encoded. -->
<script src="DROP%20DOWN%20FILTER/jquery.min.js"></script>
<script src="DROP%20DOWN%20FILTER/ddtf.js"></script>
<script>
    $('#mytable').ddTableFilter();
</script>
<?php endif; ?>
<?php cdat_page_bottom(); ?>
