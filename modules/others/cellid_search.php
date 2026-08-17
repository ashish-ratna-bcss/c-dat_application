<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$cellid = trim((string) ($_POST['CELLID'] ?? ''));
$operator = trim((string) ($_POST['OPERATOR'] ?? ''));
$state = trim((string) ($_POST['STATE'] ?? ''));
$hasSearch = $cellid !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a Cell ID and try again.');

$fieldsHtml = cdat_sum_field_text('CELLID', 'Cell ID', $cellid, 'calls', 'Enter Cellid')
            . cdat_sum_field_operator($operator)
            . cdat_sum_field_call_state($state);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Cell ID Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Cell ID Search',
            'Search cell tower details by Cell ID, operator, and state.',
            'cellid_search.php',
            $fieldsHtml,
            'BTN_SUM',
            'Search'
        );
    }

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );
    if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
    }

    $cellidEsc = str_replace("'", "''", $cellid);
    $likePattern = (strpos($cellid, '%') !== false || strpos($cellid, '_') !== false)
        ? $cellidEsc
        : $cellidEsc . '%';
    $opNorm = strtoupper(preg_replace('/_TOWER$/i', '', $operator));
    $stNorm = strtoupper($state);
    $opFilter = $operator !== ''
        ? "AND UPPER(REPLACE(OPERATOR, '_TOWER', '')) = '".str_replace("'", "''", $opNorm)."'"
        : '';
    $stateFilter = $state !== ''
        ? "AND UPPER(STATE) = '".str_replace("'", "''", $stNorm)."'"
        : '';

    $sql1 ="select DISTINCT CELLTOWERID,BTS_ID,AREADESCRIPTION,SITEADDRESS,LAT,LONG,AZIMUTH,OPERATOR,STATE, OTYPE, LASTUPDATE
from cdatdupl.dbo.CDATCELLTOWERAREANEW
WHERE CELLTOWERID LIKE '{$likePattern}' {$opFilter} {$stateFilter}
ORDER BY LASTUPDATE DESC";

    $st1 = sqlsrv_query( $conn, $sql1 );
    $rows = cdat_sum_fetch_all($st1);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner('Cell ID Search: ' . $cellid);
        cdat_sum_generic_table_open(
            'Cell Tower Results',
            ['CELLTOWERID', 'BTS_ID', 'AREA DESCRIPTION', 'SITE ADDRESS', 'LAT', 'LONG', 'AZIMUTH', 'OPERATOR', 'STATE', 'OTYPE', 'QRCODE'],
            'results_table',
            'cellid_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $areaHtml = cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? ''));
            $siteHtml = cdat_sum_address_lines((string) ($row['SITEADDRESS'] ?? ''));
            $qrSrc = CDAT_BASE . '/qrcode/php/qr_img.php?d=' . urlencode(
                'CELLTOWERID: ' . $row['CELLTOWERID']
                . ' SITEADDRESS:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['SITEADDRESS'])
                . ' LAT:' . $row['LAT']
                . ' LONG:' . $row['LONG']
                . ' AZIMUTH: ' . $row['AZIMUTH']
            );
            cdat_sum_table_row([
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['BTS_ID'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => $areaHtml !== '' ? $areaHtml : '—', 'class' => 'sum-address-cell'],
                ['html' => $siteHtml !== '' ? $siteHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZIMUTH'] ?? ''),
                (string) ($row['OPERATOR'] ?? ''),
                (string) ($row['STATE'] ?? ''),
                (string) ($row['OTYPE'] ?? ''),
                ['html' => '<img height="100" width="100" src="' . cdat_sum_h($qrSrc) . '">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st1) {
        sqlsrv_free_stmt($st1);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Cell ID Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Cell ID Search',
    'Search cell tower details by Cell ID, operator, and state.',
    'cellid_search.php',
    cdat_sum_field_text('CELLID', 'Cell ID', '', 'calls', 'Enter Cellid')
        . cdat_sum_field_operator()
        . cdat_sum_field_call_state(),
    'BTN_SUM',
    'Search'
);
cdat_sum_page_close();
layout_end();
