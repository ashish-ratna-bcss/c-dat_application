<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$regnNo = trim((string) ($_POST['REGN_NO'] ?? ''));
$hasSearch = $regnNo !== '';
$fieldsHtml = cdat_sum_field_text('REGN_NO', 'Vehicle No', $regnNo, 'CAF', 'Enter Vehicle No');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Rta Nike');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Vehicle Number Search',
            'Look up RTA vehicle details by registration number.',
            'rta_nike.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    require_once CDAT_COMMON . '/sql_safe.php';

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $number = strtoupper(trim((string)($_POST['REGN_NO'] ?? '')));
    $number = preg_replace('/[^A-Z0-9]/', '', $number);
    if ($number === '') {
        cdat_sum_empty_state('Invalid vehicle number.');
        if ($isAjax) {
            exit;
        }
        cdat_sum_page_close();
        layout_end();
        exit;
    }
    $numberSql = str_replace("'", "''", $number);

    // Exact match first (fast on distributed RTA). Prefix fallback for partial plates.
    $sql1 = "SELECT TOP 20 REGN_NO, FULLNAME, FATHERNAME, FULLADDRESS, PHONE, CITY, MKR_CLAS, COLOUR, VEH_CLASS, ENG_NO, CHAS_NO
FROM CDATDUPL..CDAT_RTA
WHERE REGN_NO = '{$numberSql}'";
    $st1 = sqlsrv_query($conn, $sql1);
    $rows = [];
    if ($st1 !== false) {
        while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
        }
    }
    if ($rows === []) {
        $sql1 = "SELECT TOP 20 REGN_NO, FULLNAME, FATHERNAME, FULLADDRESS, PHONE, CITY, MKR_CLAS, COLOUR, VEH_CLASS, ENG_NO, CHAS_NO
FROM CDATDUPL..CDAT_RTA
WHERE REGN_NO LIKE '{$numberSql}%'";
        $st1 = sqlsrv_query($conn, $sql1);
        if ($st1 !== false) {
            while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
                $rows[] = $row;
            }
        }
    }

    if ($rows === []) {
        cdat_sum_empty_state('No RTA record found for ' . $number . '.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner('RTA DETAIL OF VEHICLE. ' . $number);
        cdat_sum_generic_table_open(
            'Vehicle Details',
            ['VEHICLE NO', 'OWNER NAME', 'FATHER NAME', 'ADDRESS', 'CITY', 'PHONE', 'VEHICLE TYPE', 'ENGINE NO', 'CHASSIS NO'],
            'results_table',
            'rta_nike.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $vehType = trim(
                ($row['MKR_CLAS'] ?? '')
                . (($row['COLOUR'] ?? '') !== '' ? ', COLOR: ' . $row['COLOUR'] : '')
                . (($row['VEH_CLASS'] ?? '') !== '' ? ', ' . $row['VEH_CLASS'] : ''),
                ', '
            );
            cdat_sum_table_row([
                ['text' => (string) ($row['REGN_NO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['CITY'] ?? ''),
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) $vehType,
                ['text' => (string) ($row['ENG_NO'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CHAS_NO'] ?? ''), 'class' => 'sum-cell-num'],
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

layout_begin('Rta Nike');
cdat_sum_page_open();
cdat_sum_search_card(
    'Vehicle Number Search',
    'Look up RTA vehicle details by registration number.',
    'rta_nike.php',
    cdat_sum_field_text('REGN_NO', 'Vehicle No', '', 'CAF', 'Enter Vehicle No'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
