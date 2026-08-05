<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="CELLID_SEARCH.html"><font color='#FDEFEF'>Back</a></li></b></b>
<form action='CELLID_SEARCH.php' method='post'>
<b><font size=4 face=verdana color='#F9FBFC'>Enter Celltower ID: <input type="text" name="CELLID" value="<?php echo htmlspecialchars($_POST['CELLID'] ?? '', ENT_QUOTES); ?>" />
Operator : <select name="OPERATOR">
<option value="<?php echo htmlspecialchars($_POST['OPERATOR'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($_POST['OPERATOR'] ?? '', ENT_QUOTES); ?></option>
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
<option value="<?php echo htmlspecialchars($_POST['STATE'] ?? '', ENT_QUOTES); ?>"><?php echo htmlspecialchars($_POST['STATE'] ?? '', ENT_QUOTES); ?></option>
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
require_once __DIR__ . '/sql_safe.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$cellid = trim($_POST['CELLID'] ?? '');
$operator = trim($_POST['OPERATOR'] ?? '');
$state = trim($_POST['STATE'] ?? '');

if ($cellid === '') {
    echo "<font color='#F9FBFC'>Enter a Celltower ID to search.</font>";
    exit;
}

$cellidEsc = str_replace("'", "''", $cellid);
$likeLiteral = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cellidEsc);

// Prefer exact then prefix lookups (index-friendly on remote cellids).
// Leading-wildcard contains-search is only a fallback and is hard-capped.
$filters = '';
if ($operator !== '') {
    $cleanOp = str_ireplace('_TOWER', '', $operator);
    if (strtolower($cleanOp) === 'cellone') {
        $cleanOp = 'BSNL';
    }
    $cleanOp = str_replace("'", "''", $cleanOp);
    $filters .= " AND OPERATOR ILIKE '%{$cleanOp}%'";
}
if ($state !== '') {
    $stateEsc = str_replace("'", "''", $state);
    $filters .= " AND STATE ILIKE '%{$stateEsc}%'";
}

$selectList = "CELLTOWERID,BTS_ID,AREADESCRIPTION,SITEADDRESS,LAT,LONG,AZIMUTH,OPERATOR,STATE,OTYPE";
$limit = 50;

$sqlExact = "SELECT TOP {$limit} {$selectList}
FROM cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE CELLTOWERID = '{$cellidEsc}'{$filters}
ORDER BY CELLTOWERID";

$sqlBts = "SELECT TOP {$limit} {$selectList}
FROM cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE BTS_ID = '{$cellidEsc}'{$filters}
ORDER BY CELLTOWERID";

$sqlPrefix = "SELECT TOP {$limit} {$selectList}
FROM cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE CELLTOWERID LIKE '{$likeLiteral}%'{$filters}
ORDER BY CELLTOWERID";

$st1 = sqlsrv_query($conn, $sqlExact);
$rows = [];
if ($st1 !== false) {
    while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
}

$matchMode = 'exact';
if ($rows === []) {
    $st1 = sqlsrv_query($conn, $sqlBts);
    $matchMode = 'bts_id';
    if ($st1 !== false) {
        while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
    }
}

// Prefix only for reasonably long inputs — short stems force a slow remote scan.
if ($rows === [] && strlen($cellid) >= 10) {
    $st1 = sqlsrv_query($conn, $sqlPrefix);
    $matchMode = 'prefix';
    if ($st1 !== false) {
        while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
    }
}

echo "<font size=2 face=verdana color='#F9FBFC'>Match: {$matchMode} &nbsp;|&nbsp; Showing up to {$limit} rows"
    . " &nbsp;|&nbsp; Tip: paste the full Celltower ID for fastest results</font></br>";

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
foreach ($rows as $row) {
    $__rowcount++;
    echo "<tr>";
    echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['CELLTOWERID'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>" . htmlspecialchars((string)($row['BTS_ID'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=200px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['AREADESCRIPTION'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>" . htmlspecialchars((string)($row['SITEADDRESS'] ?? ''), ENT_QUOTES) . "</font></td>";
    echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['LAT'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['LONG'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['AZIMUTH'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana><center>" . htmlspecialchars((string)($row['OPERATOR'] ?? ''), ENT_QUOTES) . "<center></font></td>";
    echo "<td width=100px bgcolor=#AED1F1><font size=1 face=verdana>" . htmlspecialchars((string)($row['STATE'] ?? ''), ENT_QUOTES) . "</font></td>";
    echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>" . htmlspecialchars((string)($row['OTYPE'] ?? ''), ENT_QUOTES) . "</font></td>";
    echo "<td>";
    // QR generation is expensive; only for the first 10 hits.
    if ($__rowcount <= 10) {
        $qr = 'CELLTOWERID: ' . ($row['CELLTOWERID'] ?? '')
            . ' SITEADDRESS:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string)($row['SITEADDRESS'] ?? ''))
            . ' LAT:' . ($row['LAT'] ?? '')
            . ' LONG:' . ($row['LONG'] ?? '')
            . ' AZIMUTH: ' . ($row['AZIMUTH'] ?? '');
        echo '<img height="100" width="100" src="qrcode/php/qr_img.php?d=' . rawurlencode($qr) . '"></img>';
    } else {
        echo '<font size=1 face=verdana color="#666">—</font>';
    }
    echo "</td>";
    echo "</tr>";
}
if ($__rowcount === 0) {
    echo "<tr><td colspan=11 bgcolor=#FFF3CD><center><font size=2 face=verdana color='#921215'>No records found for Cell ID '"
        . htmlspecialchars($cellid, ENT_QUOTES)
        . "'. Use the full Celltower ID or BTS ID (at least 10 characters for prefix search). Clear Operator/State if set.</font></center></td></tr>";
}
echo "</table>";
?>
</body>
</html>
