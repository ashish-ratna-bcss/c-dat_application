<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="CELLID_SEARCH.html"><font color='#FDEFEF'>Back</a></li></b></b>
<form action='CELLID_SEARCH.php' method='post'>
<b><font size=4 face=verdana color='#F9FBFC'>Enter Celltower ID: <input type="text" name="CELLID" value=<?php echo $_POST['CELLID'] ?> />
Operator : <select name="OPERATOR">
<option value="<?php echo $_POST['OPERATOR'] ?>"><?php echo $_POST['OPERATOR'] ?></option>
<option value="AIRCEL_TOWER">AIRCEL_TOWER</option>
<option value="AIRTEL_TOWER">AIRTEL_TOWER</option>
<option value="BPL_TOWER">BPL_TOWER</option>
<option value="CELLONE_TOWER">CELLONE_TOWER</option>
<option value="ETISALAT_TOWER">ETISALAT_TOWER</option>
<option value="IDEA_TOWER">IDEA_TOWER</option>
<option value="JIO_TOWER">JIO_TOWER</option>
<option value="MTS_TOWER">MTS_TOWER</option>
<option value="RELIANCE_TOWER">RELIANCE_TOWER</option>
<option value="TATA_TOWER">TATA_TOWER</option>
<option value="UNINOR_TOWER">UNINOR_TOWER</option>
<option value="VIDEOCON_TOWER">VIDEOCON_TOWER</option>
<option value="VODAFONE_TOWER">VODAFONE_TOWER</option>
</select>

State: <select name="STATE">
<option value="<?php echo $_POST['STATE'] ?>"><?php echo $_POST['STATE'] ?></option>
<option value="ANDAMAN AND NICOBAR ISLANDS">ANDAMAN AND NICOBAR ISLANDS</option>
<option value="ANDHRA PRADESH">ANDHRA PRADESH</option>
<option value="ARUNACHAL PRADESH">ARUNACHAL PRADESH</option>
<option value="ASSAM">ASSAM</option>
<option value="BIHAR">BIHAR</option>
<option value="CHENNAI">CHENNAI</option>
<option value="CHHATTISGARH">CHHATTISGARH</option>
<option value="DELHI">DELHI</option>
<option value="GUJARAT">GUJARAT</option>
<option value="HARYANA">HARYANA</option>
<option value="HIMACHAL PRADESH">HIMACHAL PRADESH</option>
<option value="JAMMU_KASHMIR">JAMMU_KASHMIR</option>
<option value="JHARKHAND">JHARKHAND</option>
<option value="KARNATAKA">KARNATAKA</option>
<option value="KERALA">KERALA</option>
<option value="KOLKATA">KOLKATA</option>
<option value="MADHYA PRADESH">MADHYA PRADESH</option>
<option value="MAHARASHTRA">MAHARASHTRA</option>
<option value="MANIPUR">MANIPUR</option>
<option value="MEGHALAYA">MEGHALAYA</option>
<option value="MIZORAM">MIZORAM</option>
<option value="MUMBAI">MUMBAI</option>
<option value="NAGALAND">NAGALAND</option>
<option value="NORTH_EAST">NORTH_EAST</option>
<option value="ORISSA">ORISSA</option>
<option value="PUNJAB">PUNJAB</option>
<option value="RAJASTHAN">RAJASTHAN</option>
<option value="TELANGANA">TELANGANA</option>
<option value="TAMILNADU">TAMILNADU</option>
<option value="TRIPURA">TRIPURA</option>
<option value="UP_EAST">UP_EAST</option>
<option value="UP_WEST">UP_WEST</option>
<option value="WEST BENGAL">WEST BENGAL</option>
</select>




<input type ="submit" value ="submit">
<p> </p>

<?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$cellid     = trim($_POST['CELLID'] ?? '');
$operator   = trim($_POST['OPERATOR'] ?? '');
$state      = trim($_POST['STATE'] ?? '');
 
$cellidEsc = str_replace("'", "''", $cellid);
// Escape the LIKE wildcards (%, _) in the user's input so they are matched as
// literal characters, then wrap with % ... % for a "contains" search: typing part
// of a Cell ID returns every tower whose ID contains it
// (e.g. TOWER_1 -> TOWER_1, TOWER_10..19, TOWER_100). The '_' in synthetic tower
// IDs would otherwise act as a single-char wildcard and skip matches.
$likeLiteral = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cellidEsc);
$likePattern = '%' . $likeLiteral . '%';
$sql1 ="select DISTINCT CELLTOWERID,BTS_ID,AREADESCRIPTION,SITEADDRESS,LAT,LONG,AZIMUTH,OPERATOR,STATE, OTYPE
from cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE CELLTOWERID LIKE '{$likePattern}'";


if (!empty($operator)) {
    $cleanOp = str_ireplace('_TOWER', '', $operator);
    if (strtolower($cleanOp) === 'cellone') {
        $cleanOp = 'BSNL';
    }
    $sql1 .= " AND OPERATOR ILIKE '%$cleanOp%'";
}

if (!empty($state)) {
    $sql1 .= " AND STATE ILIKE '%$state%'";
}

// Ordering must reference a SELECTed column because of SELECT DISTINCT (PostgreSQL);
// LASTUPDATE isn't in the select list, so order by CELLTOWERID instead.
$sql1 .= " ORDER BY CELLTOWERID";

$st1 = sqlsrv_query( $conn, $sql1 );

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CELLTOWERID</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>BTS_ID</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AREA DESCRIPTION</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>SITE ADDRESS</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAT</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LONG</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>AZIMUTH</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OPERATOR</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>STATE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTYPE</font</th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>qrcode</font></th>
</tr>";

$__rowcount = 0;
while( $row = sqlsrv_fetch_array( $st1, SQLSRV_FETCH_ASSOC) ) {
$__rowcount++;
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['CELLTOWERID'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['BTS_ID'] ."<center></font></td>";
echo "<td width=200px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AREADESCRIPTION'] ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['SITEADDRESS'] ."</font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['LAT'] ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['LONG'] ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['AZIMUTH'] ."<center></font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['OPERATOR'] ."<center></font></td>";
echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>". $row['STATE'] ."</font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". $row['OTYPE'] ."</font></td>";
echo "<td>";?> <?php echo '<img height="100" width="100" src="qrcode/php/qr_img.php?d='.'CELLTOWERID: '.$row["CELLTOWERID"].' SITEADDRESS:'. preg_replace('/[^A-Za-z0-9\-:]/',' ',$row["SITEADDRESS"]).' LAT:'.$row["LAT"].' '.'LONG:'.$row["LONG"].' AZIMUTH: '.$row["AZIMUTH"].'"></img>'; ?> <?php "</td>";
echo "</tr>";
}
if ($__rowcount === 0) {
    echo "<tr><td colspan=11 bgcolor=#FFF3CD><center><font size=2 face=verdana color='#921215'>No records found for Cell ID '".htmlspecialchars($cellid)."'. Try clearing the Operator/State filters, or enter only part of the Cell ID.</font></center></td></tr>";
}
echo"</table>";

sqlsrv_free_stmt( $st1);
?>
</body>
</html>
