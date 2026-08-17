<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$mo = trim((string) ($_POST['MO'] ?? ''));
$hasSearch = $mo !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter MO sub classification and try again.');

$fieldsHtml = cdat_sum_field_text('MO', 'MO Sub Classification', $mo, 'NAME', 'SUB CLASSIFICATION');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Offender Search By MO');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender Search By Sub Classification',
            'Search offenders by MO sub classification.',
            'offender_search_by_mo.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Search'
        );
    }

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );

    if( $conn === false ) {
       // die( print_r( sqlsrv_errors(), true));
    }

    $number = trim($_POST['MO']);

    // Use parameterized queries to prevent SQL injection
    $sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";
    $params8 = array($number);
    $st8 = sqlsrv_prepare($conn, $sql8, $params8);
    sqlsrv_execute($st8);

    $sql9 = "SELECT DISTINCT MO_KEY, ACC_NAME AS ACCUSED_NAME, FATHER_NAME, AGE, MO1, MO2, POLICE_STATION 
            FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION
            WHERE (MO1 LIKE ? OR MO2 LIKE ? OR CRIME_HEAD LIKE ?)";
    $searchPattern = '%' . str_replace(' ', '%', $number) . '%';
    $params9 = array($searchPattern, $searchPattern, $searchPattern);
    $st9 = sqlsrv_prepare($conn, $sql9, $params9);
    sqlsrv_execute($st9);

    if ($st9 === false) {
      //  die(print_r(sqlsrv_errors(), true));
    }

    $bannerTitle = 'DETAILS OF : ' . $number;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No records found for: ' . $number);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Offender MO Results',
            ['MO_KEY', 'ACCUSED NAME', 'FATHER_NAME', 'AGE', 'MO_SUB_CLASSIFICATION1', 'MO_SUB_CLASSIFICATION2', 'POLICE_STATION'],
            'results_table',
            'offender_mo.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $moKey = (string) ($row['MO_KEY'] ?? '');
            cdat_sum_table_row([
                [
                    'html' => '<a href="' . htmlspecialchars(cdat_page('offender_fd.php')) . '?MO_KEY=' . cdat_sum_h(urlencode($moKey)) . '">' . cdat_sum_h($moKey) . '</a>',
                    'class' => 'sum-cell-num',
                ],
                (string) ($row['ACCUSED_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['MO1'] ?? ''),
                (string) ($row['MO2'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st9) {
        sqlsrv_free_stmt($st9);
    }
    if ($conn) {
        sqlsrv_close($conn);
    }

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Offender Search By MO');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender Search By Sub Classification',
    'Search offenders by MO sub classification.',
    'offender_search_by_mo.php',
    cdat_sum_field_text('MO', 'MO Sub Classification', '', 'NAME', 'SUB CLASSIFICATION'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
