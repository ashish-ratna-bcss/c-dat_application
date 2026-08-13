<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_phone($phone, 'CAF');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Caf Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'CAF Search',
            'Look up CAF documents by mobile number.',
            'caf_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    $serverName = "UUUU-HP";
    $connectionInfo = array("Database" => "CAFs");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $phone;

    $sql1 = "SELECT 'ftp://192.168.144.70/'+substring(CAFS_PATH,24,50) AS PHONE INTO #T FROM IO_DETAILS WHERE PHONE='$number'";

    $sql2 = "UPDATE #T SET PHONE = REPLACE(PHONE,' ','%20')";

    $sql3 = "SELECT DISTINCT PHONE,'CAF Available Click Here to Open' as CLICK FROM #T";

    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st3 = sqlsrv_query($conn, $sql3);

    $row = ($st3 !== false) ? sqlsrv_fetch_array($st3, SQLSRV_FETCH_ASSOC) : false;
    if ($row) {
        $href = (string) ($row['PHONE'] ?? '');
        $click = (string) ($row['CLICK'] ?? 'CAF Available Click Here to Open');
        cdat_sum_results_open();
        cdat_sum_report_banner('CAF Search');
        echo '<p class="sum-status sum-status--success"><a href="' . cdat_sum_h($href) . '">' . cdat_sum_h($click) . '</a></p>';
        cdat_sum_results_close();
    } else {
        cdat_sum_empty_state('CAF NOT AVAILABLE');
    }

    if ($st3) {
        sqlsrv_free_stmt($st3);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Caf Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'CAF Search',
    'Look up CAF documents by mobile number.',
    'caf_search.php',
    cdat_sum_field_phone('', 'CAF'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
