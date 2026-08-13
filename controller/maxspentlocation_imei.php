<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? $_GET['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_phone($phone, 'calls');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Maxspentlocation IMEI');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Maxspent Locations',
            'Find most-spent cell locations for a lost-report mobile number.',
            'maxspentlocation_imei.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once __DIR__ . '/cdr_enrichment_sql.php';
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $phone;

    $sql1 = "SELECT * INTO #TEMP FROM LOSTREPORT_HAWKEYE..LOST_REPORT_CDR_DATA WHERE
PHONE='$number'";

    $sql2 = cdr_sql_enrich_location_temp('#TEMP', '#TT1');

    $sql4 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #T FROM #TT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

    $sql5 = "SELECT * FROM #T ORDER BY CALLS DESC";

    $sql6 = "SELECT 'MAXSPENT LOCATION OF MOBILE NO: '+'$number' as PHONE1";

    sqlsrv_query($conn, $sql1);
    sqlsrv_query($conn, $sql2);
    sqlsrv_query($conn, $sql4);
    $st5 = sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);

    $banner = 'MAXSPENT LOCATION OF MOBILE NO: ' . $number;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st5);

    if (empty($rows)) {
        cdat_sum_empty_state('No max-spent locations found for: ' . $number);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Maxspent Locations',
            ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
            'results_table',
            'imei_maxspent.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZM'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Maxspentlocation IMEI');
cdat_sum_page_open();
cdat_sum_search_card(
    'Maxspent Locations',
    'Find most-spent cell locations for a lost-report mobile number.',
    'maxspentlocation_imei.php',
    cdat_sum_field_phone('', 'calls'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
