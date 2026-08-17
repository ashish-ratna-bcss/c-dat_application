<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_text('PHONE', 'Phone', $phone, 'PHONE', 'PHONE', true, 'tel');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Alldata Search');
        cdat_sum_page_open();
        cdat_sum_back_link('alldata.php', 'Back');
        cdat_sum_search_card(
            'All Data Search',
            'Search SDR, RTA, licence, civil supply, suspect, and passport data by phone.',
            'alldata_search.php',
            $fieldsHtml,
            '',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $phone;

    $sql1 = "SELECT 'PHONE NO SEARCH IN SDR:'+'$number' as PHONE1";
    $sql2 = "SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS,CONVERT(VARCHAR,DOA,20) AS DOA   FROM CDATDUPL.dbo.CDATADDRESS
       WHERE PHONE ='$number'";
    $sql3 = "SELECT 'PHONE NO SEARCH IN RTA:'+'$number' as PHONE2";
    $sql4 = "SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS+','+CITY FULLADDRESS,
       REGN_NO+' ENG_NO:'+ENG_NO+' CHAS_NO:'+CHAS_NO+' MKR_NAME: '+MKR_NAME+' MKR_CLAS: '+MKR_CLAS AS VEHICLE_DETAILS
	   FROM CDATDUPL.dbo.CDAT_RTA WHERE PHONE ='$number'";
    $sql5 = "SELECT 'PHONE NO SEARCH IN LICENCE_DATA:'+'$number' as PHONE3";
    $sql6 = "SELECT  PHONE,LICENCE_NO,FULLNAME,FATHER_NAME AS FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS FROM CDATDUPL.dbo.CDAT_LICENCE
       WHERE PHONE ='$number'";
    $sql7 = "SELECT 'PHONE NO SEARCH IN CIVILSUPPLY_DATA:'+'$number' as PHONE3";
    $sql8 = "SELECT  PHONE,FULLNAME,NAME_OFFICE+', '+FULLADDRESS+' '+DISTRICT AS FULLADDRESS,RATION_CARD_NO,UID_NO AADHAR_DETAILS FROM CDATDUPL.dbo.CDAT_CIVILSUPPLY
       WHERE PHONE ='$number'";
    $sql9 = "SELECT 'PHONE NO SEARCH IN CDATSUSPECT_DATA:'+'$number' as PHONE5";
    $sql10 = "SELECT  PHONE,NAME AS FULLNAME,ROLE,FATHER_NAME AS FATHERNAME,ADDRESS AS FULLADDRESS,
       CRIME_NO+'/'+YEAR+' OF PS '+PS+' MO: '+MO AS CRIME_DETAILS FROM CDATDUPL.dbo.CDATSUSPECT2 
       WHERE PHONE ='$number'";
    $sql11 = "SELECT 'PHONE NO SEARCH IN PASSPORT_DATA:'+'$number' as PHONE6";
    $sql12 = "select distinct PHONE,FILE_NUMBER,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS from cdatdupl.dbo.cdat_passport
WHERE PHONE='$number'";

    $st1 = sqlsrv_query($conn, $sql1);
    $stMT2 = sqlsrv_query($conn, $sql2);
    $st3 = sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    $st5 = sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);
    $st7 = sqlsrv_query($conn, $sql7);
    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);
    $st12 = sqlsrv_query($conn, $sql12);

    cdat_sum_results_open();

    $b1 = $st1 ? sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b1['PHONE1'] ?? ('PHONE NO SEARCH IN SDR:' . $number)));
    $sdr = cdat_sum_fetch_all($stMT2);
    if (empty($sdr)) {
        cdat_sum_empty_state('No SDR records found.');
    } else {
        cdat_sum_generic_table_open('SDR', ['PHONE', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS', 'DOA'], 'all_sdr', 'all_sdr.csv', count($sdr));
        foreach ($sdr as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['text' => (string) ($row['DOB'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                ['text' => (string) ($row['DOA'] ?? ''), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $b3 = $st3 ? sqlsrv_fetch_array($st3, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b3['PHONE2'] ?? ('PHONE NO SEARCH IN RTA:' . $number)));
    $rta = cdat_sum_fetch_all($st4);
    if (empty($rta)) {
        cdat_sum_empty_state('No RTA records found.');
    } else {
        cdat_sum_generic_table_open('RTA', ['PHONE', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS', 'VEHICLE_DETAILS'], 'all_rta', 'all_rta.csv', count($rta));
        foreach ($rta as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['text' => (string) ($row['DOB'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['VEHICLE_DETAILS'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $b5 = $st5 ? sqlsrv_fetch_array($st5, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b5['PHONE3'] ?? ('PHONE NO SEARCH IN LICENCE_DATA:' . $number)));
    $lic = cdat_sum_fetch_all($st6);
    if (empty($lic)) {
        cdat_sum_empty_state('No licence records found.');
    } else {
        cdat_sum_generic_table_open('Licence', ['PHONE', 'LICENCE_NO', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS'], 'all_lic', 'all_licence.csv', count($lic));
        foreach ($lic as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['LICENCE_NO'] ?? ''),
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['text' => (string) ($row['DOB'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $b7 = $st7 ? sqlsrv_fetch_array($st7, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b7['PHONE3'] ?? ('PHONE NO SEARCH IN CIVILSUPPLY_DATA:' . $number)));
    $cs = cdat_sum_fetch_all($st8);
    if (empty($cs)) {
        cdat_sum_empty_state('No civil supply records found.');
    } else {
        cdat_sum_generic_table_open('Civil Supply', ['PHONE', 'FULLNAME', 'FULLADDRESS', 'RATION_CARD_NO', 'AADHAR'], 'all_cs', 'all_civil.csv', count($cs));
        foreach ($cs as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FULLNAME'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['RATION_CARD_NO'] ?? ''),
                (string) ($row['AADHAR_DETAILS'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $b9 = $st9 ? sqlsrv_fetch_array($st9, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b9['PHONE5'] ?? ('PHONE NO SEARCH IN CDATSUSPECT_DATA:' . $number)));
    $sus = cdat_sum_fetch_all($st10);
    if (empty($sus)) {
        cdat_sum_empty_state('No suspect records found.');
    } else {
        cdat_sum_generic_table_open('Suspect', ['PHONE', 'FULLNAME', 'ROLE', 'FATHERNAME', 'FULLADDRESS', 'CRIME_DETAILS'], 'all_sus', 'all_suspect.csv', count($sus));
        foreach ($sus as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['ROLE'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRIME_DETAILS'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $b11 = $st11 ? sqlsrv_fetch_array($st11, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b11['PHONE6'] ?? ('PHONE NO SEARCH IN PASSPORT_DATA:' . $number)));
    $pp = cdat_sum_fetch_all($st12);
    if (empty($pp)) {
        cdat_sum_empty_state('No passport records found.');
    } else {
        cdat_sum_generic_table_open('Passport', ['PHONE', 'FILE_NUMBER', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS'], 'all_pp', 'all_passport.csv', count($pp));
        foreach ($pp as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['FILE_NUMBER'] ?? ''),
                (string) ($row['FULLNAME'] ?? ''),
                (string) ($row['FATHERNAME'] ?? ''),
                ['text' => (string) ($row['DOB'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['FULLADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
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
    exit;
}

layout_begin('Alldata Search');
cdat_sum_page_open();
cdat_sum_back_link('alldata.php', 'Back');
cdat_sum_search_card(
    'All Data Search',
    'Search SDR, RTA, licence, civil supply, suspect, and passport data by phone.',
    'alldata_search.php',
    cdat_sum_field_text('PHONE', 'Phone', '', 'PHONE', 'PHONE', true, 'tel'),
    '',
    'Submit'
);
cdat_sum_page_close();
layout_end();
