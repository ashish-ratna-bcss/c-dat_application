<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/cdr_enrichment_sql.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('D&n Loc IMEI');
    cdat_sum_page_open();
    cdat_sum_back_link('day%26nightloc_imei.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$number = $_POST['PHONE_NO'];

$sql1 = "SELECT * INTO #TEMP FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WHERE 
(CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00') 
AND PHONE='$number'";

$sql2 = cdr_sql_enrich_location_temp('#TEMP', '#TT1');

$sql4 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T FROM #TT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sql5 = "SELECT TOP 10 * FROM #T ORDER BY CALLS DESC";

$sql6 = "SELECT 'DAY LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sql7 = "SELECT 'NIGHT LOCATION OF MOBILE NO: '+'$number' as PHONE1";

$sql8 = "SELECT * INTO #T1 FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WHERE 
(CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00') 
AND PHONE='$number'";

$sql9 = cdr_sql_enrich_location_temp('#T1', '#T3');

$sql11 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T4 FROM #T3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sql12 = "SELECT TOP 10 * FROM #T4 ORDER BY CALLS DESC";

sqlsrv_query($conn, $sql1);
sqlsrv_query($conn, $sql2);
sqlsrv_query($conn, $sql4);
$st5 = sqlsrv_query($conn, $sql5);
$st6 = sqlsrv_query($conn, $sql6);
$st7 = sqlsrv_query($conn, $sql7);
sqlsrv_query($conn, $sql8);
sqlsrv_query($conn, $sql9);
sqlsrv_query($conn, $sql11);
$st12 = sqlsrv_query($conn, $sql12);

$dayBanner = 'DAY LOCATION OF MOBILE NO: ' . $number;
if ($st6 && ($b6 = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $dayBanner = (string) ($b6['PHONE1'] ?? $dayBanner);
}
$nightBanner = 'NIGHT LOCATION OF MOBILE NO: ' . $number;
if ($st7 && ($b7 = sqlsrv_fetch_array($st7, SQLSRV_FETCH_ASSOC))) {
    $nightBanner = (string) ($b7['PHONE1'] ?? $nightBanner);
}
$dayRows = cdat_sum_fetch_all($st5);
$nightRows = cdat_sum_fetch_all($st12);

cdat_sum_results_open();
cdat_sum_report_banner($dayBanner);
if (empty($dayRows)) {
    cdat_sum_empty_state('No day location records found.');
} else {
    cdat_sum_generic_table_open(
        'Day Location',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'day_loc_imei_table',
        'day_location_imei.csv',
        count($dayRows)
    );
    foreach ($dayRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

cdat_sum_report_banner($nightBanner);
if (empty($nightRows)) {
    cdat_sum_empty_state('No night location records found.');
} else {
    cdat_sum_generic_table_open(
        'Night Location',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'night_loc_imei_table',
        'night_location_imei.csv',
        count($nightRows)
    );
    foreach ($nightRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
