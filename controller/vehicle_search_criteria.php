<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$vehicleNo = trim((string) ($_POST['VEHICLE_NO'] ?? ''));
$vehicleSource = (string) ($_POST['VEHICLE_SOURCE'] ?? 'REGN_NO');
$hasSearch = $vehicleNo !== '' && isset($_POST['VEHICLE_SOURCE']);

$sourceOptions = [
    'REGN_NO' => 'VEHICLE_NO',
    'CHAS_NO' => 'CHASSIS_NO',
    'ENG_NO' => 'ENGINE_NO',
    'PHONE' => 'PHONE',
];
$sourceHtml = cdat_sum_searchable_select(
    'VEHICLE_SOURCE',
    'Search Criteria',
    ['' => 'Select criteria'] + $sourceOptions,
    $vehicleSource,
    'Select criteria',
    true
);

$fieldsHtml = $sourceHtml
            . cdat_sum_field_text('VEHICLE_NO', 'Number', $vehicleNo, 'CAF', 'Enter No');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Search Criteria');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Vehicle Search',
            'Search vehicle records by registration, chassis, engine, or phone.',
            'vehicle_search_criteria.php',
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
    $number1 = $_POST['VEHICLE_SOURCE'];

    // Validate search criteria to prevent SQL injection
    $validColumns = array('REGN_NO', 'CHAS_NO', 'ENG_NO', 'PHONE');
    if (!in_array($number1, $validColumns)) {
        die('Invalid search criteria');
    }

    // Use parameterized queries with column name validation
    $sql8 = "SELECT 'VEHICLE ADDRESS SEARCH' as PHONE1";
    $st8 = sqlsrv_query($conn, $sql8);

    // Build query with validated column name
    $sql9 = "SELECT DISTINCT REGN_NO, FULLNAME AS NAME, FATHERNAME AS FATHER_NAME, 
            FULLADDRESS + ', ' + CITY AS ADDRESS, PHONE AS PHONE_NO,
            MKR_CLAS + ', COLOR: ' + COLOUR + ', ' + VEH_CLASS AS VEHICLE_TYPE, 
            ENG_NO, CHAS_NO, CONVERT(VARCHAR, ISS_DT, 106) AS ISSUED_DATE 
            FROM CDATDUPL.[dbo].[CDAT_RTA] 
            WHERE " . $number1 . " LIKE ?";
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
            'Vehicle Criteria Results',
            ['REGN_NO', 'NAME', 'FATHER_NAME', 'ADDRESS', 'PHONE_NO', 'VEHICLE_TYPE', 'ENG_NO', 'CHAS_NO', 'ISSUED_DATE', 'QRCODE'],
            'results_table',
            'vehicle_criteria.csv',
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

layout_begin('Search Criteria');
cdat_sum_page_open();
cdat_sum_search_card(
    'Vehicle Search',
    'Search vehicle records by registration, chassis, engine, or phone.',
    'vehicle_search_criteria.php',
    $sourceHtml . cdat_sum_field_text('VEHICLE_NO', 'Number', '', 'CAF', 'Enter No'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
