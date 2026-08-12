<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: day%26nightloc_btwn_dates.php');
    exit;
}

if (!$isAjax) {
    layout_begin('Day / Night Between Dates');
    cdat_sum_page_open();
    cdat_sum_back_link('day%26nightloc_btwn_dates.php');
}

$serverName = 'CPHYDERABAD1\\DAU_HYD_2023';
$connectionInfo = ['Database' => 'CDATDUPL'];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$number = $_POST['PHONE_NO'];
$f_date = $_POST['FROM_DT'];
$t_date = $_POST['TO_DT'];

$sql1 = "SELECT * INTO #TEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00') 
AND PHONE='$number' AND  convert(char(10),STARTTIME,121) BETWEEN '$f_date' AND '$t_date'";

$sql2 = cdr_sql_enrich_location_temp('#TEMP', '#TT1');

$sql4 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T FROM #TT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sql5 = 'SELECT TOP 10 * FROM #T order by calls desc';

$sql6 = "SELECT 'DAY LOCATION OF MOBILE NO: '+'$number'+' BETWEEN '+'$f_date'+' AND '+'$t_date' as PHONE1";

$sql7 = "SELECT 'NIGHT LOCATION OF MOBILE NO: '+'$number'+' BETWEEN '+'$f_date'+' AND '+'$t_date' as PHONE1";

$sql8 = "SELECT * INTO #T1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00') 
AND PHONE='$number' AND  convert(char(10),STARTTIME,121) BETWEEN '$f_date' AND '$t_date'";

$sql9 = cdr_sql_enrich_location_temp('#T1', '#T3');

$sql11 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T4 FROM #T3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

$sql12 = 'SELECT TOP 10 * FROM #T4 order by calls desc';

$st1 = sqlsrv_query($conn, $sql1);
$st2 = sqlsrv_query($conn, $sql2);
$st4 = sqlsrv_query($conn, $sql4);
$st5 = sqlsrv_query($conn, $sql5);
$st6 = sqlsrv_query($conn, $sql6);
$st7 = sqlsrv_query($conn, $sql7);
$st8 = sqlsrv_query($conn, $sql8);
$st9 = sqlsrv_query($conn, $sql9);
$st11 = sqlsrv_query($conn, $sql11);
$st12 = sqlsrv_query($conn, $sql12);

$dayTitle = 'Day Location of Mobile No: ' . $number . ' Between ' . $f_date . ' And ' . $t_date;
$nightTitle = 'Night Location of Mobile No: ' . $number . ' Between ' . $f_date . ' And ' . $t_date;
if ($st6 && ($row = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $dayTitle = (string) ($row['PHONE1'] ?? $dayTitle);
}
if ($st7 && ($row = sqlsrv_fetch_array($st7, SQLSRV_FETCH_ASSOC))) {
    $nightTitle = (string) ($row['PHONE1'] ?? $nightTitle);
}

$dayRows = cdat_sum_fetch_all($st5);
$nightRows = cdat_sum_fetch_all($st12);

echo '<div class="sum-results">';

cdat_sum_report_banner($dayTitle);
if (empty($dayRows)) {
    cdat_sum_empty_state('No day location records found');
} else {
    cdat_sum_generic_table_open(
        'Day Locations',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'day_loc_btwn_table',
        'day_location_between_dates.csv',
        count($dayRows)
    );
    foreach ($dayRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

cdat_sum_report_banner($nightTitle);
if (empty($nightRows)) {
    cdat_sum_empty_state('No night location records found');
} else {
    cdat_sum_generic_table_open(
        'Night Locations',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'night_loc_btwn_table',
        'night_location_between_dates.csv',
        count($nightRows)
    );
    foreach ($nightRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

echo '</div>';

if ($st5) {
    sqlsrv_free_stmt($st5);
}

if ($isAjax) {
    exit;
}

cdat_sum_page_close();
layout_end();
