<html>
<head>
</head>
<body bgcolor="#0C5D90">
<p><a href="cdatcnts.php"><font color=#FDEFEF>BACK</a></p>
<style type="text/css">
a:link , a:visited{
text-decoration: none;
}
</style>
<body bgcolor="#BDBDBD">
<?php
set_time_limit(0);
require_once __DIR__ . '/activity_logger.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
audit_log('CDAT Contacts', 'Search', ['phone_number' => $_POST['PHONE_NO'] ?? '']);
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

$sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

$sql4="SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE='$number' and other!=''";

$sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL,
MO, CATEGORY, INC_OFFICER INTO #TT from #XX a
left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
 group by a.phone, A.other, nickname,ROLE, MO, CATEGORY, INC_OFFICER order by  calls desc, other";

$sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: '+'$number' as PHONE";

$st10=sqlsrv_query($conn, $sql10);
$st4 = sqlsrv_query( $conn, $sql4 );
$st5 = sqlsrv_query( $conn, $sql5 );
$st8 = sqlsrv_query( $conn, $sql8 );

if ($st5 === false) {
    die(print_r(sqlsrv_errors(), true));
}

$phoneAreaPrefixes = cdat_load_phonearea_prefixes($conn);
$cdatAddressMap = cdat_fetch_cdataddress_map($conn, [$number]);
$otherStateMap = cdat_fetch_other_state_address_map($conn, [$number]);
$defaultImage = cdat_default_suspect_image($conn);
$suspectProfile = cdat_fetch_suspect_profile_map($conn, [$number]);
$searchedSuspect = $suspectProfile[$number] ?? null;

$headerRow = [
    'PHONE' => $number,
    'FIRST_CALL' => '',
    'LAST_CALL' => '',
    'NICKNAME' => $searchedSuspect['nickname_label'] ?? '',
    'MO' => $searchedSuspect['mo'] ?? '',
    'CAT' => $searchedSuspect['category'] ?? '',
    'ADDRESS' => cdat_format_sum_header_address($number, $cdatAddressMap, $otherStateMap, cdat_phonearea_lookup($phoneAreaPrefixes, $number)),
    'INC_OFFICER' => $searchedSuspect['inc_officer'] ?? '',
    'IMAGE' => $defaultImage,
];
if ($st10 && ($stats = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
    $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
    $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
    if ($searchedSuspect === null) {
        $headerRow['NICKNAME'] = $stats['NICKNAME'] ?? '';
        $headerRow['MO'] = $stats['MO'] ?? '';
        $headerRow['CAT'] = $stats['CATEGORY'] ?? '';
        $headerRow['INC_OFFICER'] = $stats['INC_OFFICER'] ?? '';
    }
}

$headerImages = cdat_fetch_suspect_image_map($conn, [$number]);
if (isset($headerImages[$number])) {
    $headerRow['IMAGE'] = $headerImages[$number];
}

$contactRows = [];
$lookupPhones = [$number];
$stContacts = sqlsrv_query($conn, 'SELECT * FROM #TT ORDER BY CALLS DESC, OTHER');
if ($stContacts) {
    while ($row = sqlsrv_fetch_array($stContacts, SQLSRV_FETCH_ASSOC)) {
        $contactRows[] = $row;
        $lookupPhones[] = $row['OTHER'] ?? '';
    }
}

$contactAddressMap = cdat_fetch_cdataddress_map($conn, $lookupPhones);
$contactOtherStateMap = cdat_fetch_other_state_address_map($conn, $lookupPhones);
$contactSuspectMap = cdat_fetch_suspect_profile_map($conn, array_column($contactRows, 'OTHER'));
$irFormsMap = cdat_fetch_ir_forms_map($conn, array_column($contactRows, 'OTHER'));
$contactImageMap = cdat_fetch_suspect_image_map($conn, array_column($contactRows, 'OTHER'));

$displayContacts = [];
foreach ($contactRows as $row) {
    $other = trim((string) ($row['OTHER'] ?? ''));
    $address = cdat_format_cdatcnts_tt_address(
        $other,
        $row['CALLS'] ?? 0,
        $row['DUR'] ?? 0,
        $contactAddressMap,
        $phoneAreaPrefixes
    );
    if (isset($contactOtherStateMap[$other])) {
        $address = cdat_format_cdatcnts_other_state_address($contactOtherStateMap[$other]);
    }

    $suspect = $contactSuspectMap[$other] ?? null;

    $displayContacts[] = [
        'PHONE' => $row['PHONE'] ?? '',
        'OTHER' => $other,
        'NICKNAME' => $suspect['nickname_label'] ?? ($row['NICKNAME'] ?? ''),
        'MO' => $suspect['mo'] ?? ($row['MO'] ?? ''),
        'CAT' => $suspect['category'] ?? ($row['CATEGORY'] ?? ''),
        'IN' => $row['IN'] ?? '',
        'OUT' => $row['OUT'] ?? '',
        'CALLS' => $row['CALLS'] ?? '',
        'DUR' => $row['DUR'] ?? '',
        'FIRST_CALL' => $row['FIRST_CALL'] ?? '',
        'LAST_CALL' => $row['LAST_CALL'] ?? '',
        'ADDRESS' => $address,
        'INC_OFFICER' => $suspect['inc_officer'] ?? ($row['INC_OFFICER'] ?? ''),
        'IRFORMS' => $irFormsMap[$other] ?? '',
        'IMAGE' => $contactImageMap[$other] ?? $defaultImage,
    ];
}

$noContactsMsg = count($displayContacts) >= 1 ? '' : "*** NO CDAT CONTACTS TO $number ***";

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<font size=4 face=verdana color='#F9FBFC'><td><center><b>". $row['PHONE'] ."<center></td></font></br>";
}
echo "<table border=1 cellspacing=0 cellpadding=5>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICKNAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CAT</font></th>
<!-- <th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_UPDATED</font></th> -->
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
</tr>";

$row = $headerRow;
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['PHONE']) ."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['FIRST_CALL']) ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['LAST_CALL']) ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['NICKNAME']) ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['MO']) ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['CAT']) ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". htmlspecialchars($row['ADDRESS']) ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['INC_OFFICER']) ."<center></font></td>";
echo "</tr>";

echo"</table><br />";

echo "<table border=1 cellspacing=0 cellpadding=3>
<tr>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>PHONE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OTHER</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IMAGE</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>NICK NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>MO</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CAT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IN</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>OUT</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>CALLS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>DUR</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>FIRST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>LAST_CALL</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>ADDRESS</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IO NAME</font></th>
<th bgcolor=#921215><font size=3 face=verdana color='#F9FBFC'>IR</font></th>
</tr>";

foreach ($displayContacts as $row) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['PHONE']) ."<center></font></td>";
echo "<td width=50px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'CDATCNTS2.PHP?PHONE_NO='.urlencode($row['OTHER']).">".htmlspecialchars($row['OTHER'])."<center></font></td>";
echo "<td>";?> <?php echo '<img  height="100" width="100" src="'.cdat_base64_image_src($row['IMAGE']).'"></img>' ?> <?php "</td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['NICKNAME']) ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['MO']) ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['CAT']) ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars((string) $row['IN']) ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars((string) $row['OUT']) ."<center></font></td>";
echo "<td width=0px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars((string) $row['CALLS']) ."<center></font></td>";
echo "<td width=0px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars((string) $row['DUR']) ."<center></font></td>";
echo "<td width=125px bgcolor=#AED1F1><font size=1 face=verdana><center>". htmlspecialchars($row['FIRST_CALL']) ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". htmlspecialchars($row['LAST_CALL']) ."<center></font></td>";
echo "<td width=500px bgcolor=#AED1F1><font size=1 face=verdana>". htmlspecialchars($row['ADDRESS']) ."</font></td>";
echo "<td width=100px bgcolor=#C2E0FB><font size=1 face=verdana>". htmlspecialchars($row['INC_OFFICER']) ."</font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><a href=".'CDAT_IRFORM.PHP?OTHER_NO='.urlencode($row['OTHER'])."><center>". htmlspecialchars($row['IRFORMS']) ."<center></font></td>";
echo "</tr>";


}
echo"</table><br />";

if ($noContactsMsg !== '') {
echo "<blink><font size=4 face=verdana color='#F9FBFC'><td><center><b>". htmlspecialchars($noContactsMsg) ."<center></td></font></br>";
}

?>
</body>
</html>