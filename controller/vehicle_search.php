<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$vehicleNo = trim((string) ($_POST['VEHICLE_NO'] ?? ''));
$hasSearch = $vehicleNo !== '';

$fieldsHtml = cdat_sum_field_text('VEHICLE_NO', 'Vehicle No', $vehicleNo, 'CAF', 'Enter Vehicle No');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Vehicle Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Vehicle Number Search',
            'Look up vehicle registration details by vehicle number.',
            'vehicle_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Search'
        );
    }

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );

    if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
    }

    $number = trim($_POST['VEHICLE_NO']);

    // Use parameterized queries to prevent SQL injection
    $sql8 = "SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";
    $st8 = sqlsrv_query($conn, $sql8);

    $sql9 = "SELECT REGN_NO, FULLNAME AS NAME, FATHERNAME AS FATHER_NAME, 
            FULLADDRESS + ', ' + CITY AS ADDRESS, PHONE AS PHONE_NO,
            MKR_CLAS + ', COLOR: ' + COLOUR + ', ' + VEH_CLASS AS VEHICLE_TYPE, 
            ENG_NO, CHAS_NO, CONVERT(VARCHAR, ISS_DT, 106) AS ISSUED_DATE 
            FROM CDATDUPL.[dbo].[CDAT_RTA] 
            WHERE REGN_NO LIKE ?";
    $params9 = array('%' . $number . '%');
    $st9 = sqlsrv_prepare($conn, $sql9, $params9);
    sqlsrv_execute($st9);

    if ($st9 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $bannerTitle = 'VEHICLE ADDRESS SEARCH';
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No vehicle records found for: ' . $number);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Vehicle Search',
            ['REGN_NO', 'NAME', 'FATHER_NAME', 'ADDRESS', 'PHONE_NO', 'VEHICLE_TYPE', 'ENG_NO', 'CHAS_NO', 'ISSUED_DATE', 'QRCODE'],
            'results_table',
            'vehicle_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $address = (string) ($row['ADDRESS'] ?? '');
            $addrHtml = cdat_sum_address_lines($address);
            $qrData = 'REGNNO: ' . $row['REGN_NO']
                     . ' NAME: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['NAME'])
                     . ' FATHERNAME: ' . $row['FATHER_NAME']
                     . ' PHONE: ' . $row['PHONE_NO']
                     . ' ADDRESS: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $address)
                     . ' VEH_TYPE: ' . $row['VEHICLE_TYPE']
                     . ' ENG_NO: ' . $row['ENG_NO']
                     . ' CHAS_NO: ' . $row['CHAS_NO'];
            $qrSrc = '../qrcode/php/qr_img.php?d=' . urlencode($qrData);
            cdat_sum_table_row([
                ['text' => (string) ($row['REGN_NO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
                ['text' => (string) ($row['PHONE_NO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['VEHICLE_TYPE'] ?? ''),
                ['text' => (string) ($row['ENG_NO'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CHAS_NO'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['ISSUED_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => '<img height="100" width="100" src="' . cdat_sum_h($qrSrc) . '">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    sqlsrv_free_stmt($st9);
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Vehicle Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Vehicle Number Search',
    'Look up vehicle registration details by vehicle number.',
    'vehicle_search.php',
    cdat_sum_field_text('VEHICLE_NO', 'Vehicle No', '', 'CAF', 'Enter Vehicle No'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
