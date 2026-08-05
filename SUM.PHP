<html>
<head>
</head>
<body bgcolor="#0C5D90">
<li><a href="SUM_HOME.html"><font color=#FDEFEF>Back</a></li>
<?php
set_time_limit(0);
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';

audit_log('Summary Total', 'Search', ['phone_number' => $_POST['PHONE_NO'] ?? '']);
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
if ($number === '') {
    die('<center><font color="white">Phone number required</font></center>');
}

$sql3 ="SELECT * INTO #TT FROM CDAT_DETAILS WITH (NOLOCK) WHERE PHONE='$number' and isnumeric(other)=1";

$sql4 ="SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT',
COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL,
CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL INTO #RESULT FROM #TT 
GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

$sql5 ="SELECT * INTO #RESULT1 FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
SELECT DISTINCT OTHER  FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR)
AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

$sql6 ="SELECT PHONE, OTHER, [IN], [OUT], CALLS, DUR, FIRSTCALL, LASTCALL
FROM #RESULT1 ORDER BY CALLS DESC";

$sql8="SELECT 'SUMMARY OF MOBILE NO: '+'$number' as PHONE1";

$sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
FROM CDATDUPL.DBO.CDATPCSUSPECT A WITH (NOLOCK) LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

$sql12="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** CDRs NOT AVAILABLE ***' end as PHONE FROM #RESULT";

$st3 = sqlsrv_query( $conn, $sql3 );
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$stmt = sqlsrv_query( $conn, $sql6 );
$st8 = sqlsrv_query( $conn, $sql8 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st12 = sqlsrv_query( $conn, $sql12 );

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$contactRows = [];
$lookupPhones = [$number];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $contactRows[] = $row;
    $lookupPhones[] = $row['OTHER'] ?? '';
}

$phoneAreaPrefixes = cdat_load_phonearea_prefixes($conn);
$suspectMap = cdat_fetch_suspect_nickname_map($conn, $lookupPhones);
$cdatAddressMap = cdat_fetch_cdataddress_map($conn, $lookupPhones);
$otherStateMap = cdat_fetch_other_state_address_map($conn, $lookupPhones);
$headerPhoneArea = cdat_phonearea_lookup($phoneAreaPrefixes, $number);

$headerRow = [
    'PHONE' => $number,
    'FIRST_CALL' => '',
    'LAST_CALL' => '',
    'NICKNAME' => '',
    'ADDRESS' => cdat_format_sum_header_address($number, $cdatAddressMap, $otherStateMap, $headerPhoneArea),
];
if ($st10 && ($stats = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
    $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
    $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
    if (!empty($stats['NICKNAME'])) {
        $headerRow['NICKNAME'] = $stats['NICKNAME'];
    }
}

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE1'] ."<center></td></font></br>";
}

echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>NICKNAME</font</th>
<!-- <th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_UPDATED</font</th> -->
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($headerRow['PHONE']) ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($headerRow['FIRST_CALL']) ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($headerRow['LAST_CALL']) ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($headerRow['NICKNAME']) ."<center></font></td>";
echo "<td width=450px bgcolor=#C2E0FB><font size=1 face=verdana>". htmlspecialchars($headerRow['ADDRESS']) ."</font></td>";
echo "</tr>";

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=2>
<tr>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>PHONE</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>OTHER</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>IN</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>OUT</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>CALLS</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>DUR</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>FIRST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>LAST_CALL</font</th>
<th bgcolor=#921215><font size=2 face=verdana color='#F9FBFC'>ADDRESS</font</th>
</tr>";

foreach ($contactRows as $row) {
    $other = trim((string) ($row['OTHER'] ?? ''));
    $otherLabel = $other;
    if (isset($suspectMap[$other]) && $suspectMap[$other] !== '') {
        $otherLabel = $other . ' - ' . $suspectMap[$other];
    }
    $address = cdat_format_sum_contact_address(
        $other,
        (int) ($row['IN'] ?? 0),
        (int) ($row['OUT'] ?? 0),
        $row['DUR'] ?? 0,
        $cdatAddressMap,
        $otherStateMap,
        cdat_phonearea_lookup($phoneAreaPrefixes, $other)
    );

echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['PHONE'] ?? '') ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana>". htmlspecialchars($otherLabel) ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['IN'] ?? '')) ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['OUT'] ?? '')) ."<center></font></td>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['CALLS'] ?? '')) ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['DUR'] ?? '')) ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['FIRSTCALL'] ?? '')) ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars((string) ($row['LASTCALL'] ?? '')) ."<center></font></td>";
echo "<td width=400px bgcolor=#AED1F1><font size=1 face=verdana>". htmlspecialchars($address) ."</font></td>";
echo "</tr>";

}
echo"</table><br /><br />";


while( $row = sqlsrv_fetch_array( $st12, SQLSRV_FETCH_ASSOC) ) {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}

sqlsrv_free_stmt( $stmt);
?>
</body>
</html>
