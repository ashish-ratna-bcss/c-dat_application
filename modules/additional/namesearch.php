<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$address = trim((string) ($_POST['ADDRESS'] ?? ''));
$hasSearch = $name !== '' || $address !== '';
$fieldsHtml = cdat_sum_field_text('NAME', 'Name', $name, 'NAME', 'NAME', false)
            . cdat_sum_field_text('ADDRESS', 'Address', $address, 'ADDRESS', 'ADDRESS', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Namesearch');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Name Search',
            'Search SDR, RTA, licence, civil supply, and suspect data by name and address.',
            'namesearch.php',
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
    $NAME = $name;
    $ADDRESS = $address;

    $sql1 = "SELECT 'NAME SEARCH IN SDR:'+'$NAME' as PHONE1";
    $sql2 = "SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS,CONVERT(VARCHAR,DOA,20) AS DOA   FROM CDATDUPL.dbo.CDATADDRESS
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' OR DISTRICT LIKE '%'+'$ADDRESS'+'%')";
    $sql3 = "SELECT 'NAME SEARCH IN RTA:'+'$NAME' as PHONE2";
    $sql4 = "SELECT  PHONE,FULLNAME,FATHERNAME,CONVERT(VARCHAR,DOB,20) AS DOB,FULLADDRESS+','+CITY FULLADDRESS,
       REGN_NO+' ENG_NO:'+ENG_NO+' CHAS_NO:'+CHAS_NO+' MKR_NAME: '+MKR_NAME+' MKR_CLAS: '+MKR_CLAS AS VEHICLE_DETAILS
	   FROM CDATDUPL.dbo.CDAT_RTA
	   WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' OR DISTRICT LIKE '%'+'$ADDRESS'+'%')";
    $sql5 = "SELECT 'NAME SEARCH IN LICENCE_DATA:'+'$NAME' as PHONE3";
    $sql6 = "SELECT  PHONE,LICENCE_NO,FULLNAME,FATHER_NAME AS FATHERNAME,CONVERT(VARCHAR,DOB,20) DOB,FULLADDRESS FROM CDATDUPL.dbo.CDAT_LICENCE
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND FULLADDRESS LIKE '%'+'$ADDRESS'+'%'";
    $sql7 = "SELECT 'NAME SEARCH IN CIVILSUPPLY_DATA:'+'$NAME' as PHONE3";
    $sql8 = "SELECT  PHONE,FULLNAME,NAME_OFFICE+', '+FULLADDRESS+' '+DISTRICT AS FULLADDRESS,RATION_CARD_NO,UID_NO AADHAR_DETAILS FROM CDATDUPL.dbo.CDAT_CIVILSUPPLY
       WHERE FULLNAME LIKE '%'+'$NAME'+'%' AND (FULLADDRESS LIKE'%'+'$ADDRESS'
OR DISTRICT LIKE '%'+'$ADDRESS'+'%' OR NAME_OFFICE LIKE '%'+'$ADDRESS'+'%')";
    $sql9 = "SELECT 'NAME SEARCH IN CDATSUSPECT_DATA:'+'$NAME' as PHONE5";
    $sql10 = "SELECT  PHONE,NAME AS FULLNAME,ROLE,FATHER_NAME AS FATHERNAME,ADDRESS AS FULLADDRESS,
       CRIME_NO+'/'+YEAR+' OF PS '+PS+' MO: '+MO AS CRIME_DETAILS FROM CDATDUPL.dbo.CDATSUSPECT2
       WHERE NAME LIKE '%'+'$NAME'+'%' AND (ADDRESS LIKE '%'+'$ADDRESS'+'%' OR CITY  LIKE '%'+'$ADDRESS'+'%' )";

    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st3 = sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    $st5 = sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);
    $st7 = sqlsrv_query($conn, $sql7);
    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);

    cdat_sum_results_open();

    $b1 = $st1 ? sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC) : null;
    cdat_sum_report_banner((string) ($b1['PHONE1'] ?? ('NAME SEARCH IN SDR:' . $NAME)));
    $sdr = cdat_sum_fetch_all($st2);
    if (empty($sdr)) {
        cdat_sum_empty_state('No SDR records found.');
    } else {
        cdat_sum_generic_table_open('SDR', ['PHONE', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS', 'DOA'], 'name_sdr', 'name_sdr.csv', count($sdr));
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
    cdat_sum_report_banner((string) ($b3['PHONE2'] ?? ('NAME SEARCH IN RTA:' . $NAME)));
    $rta = cdat_sum_fetch_all($st4);
    if (empty($rta)) {
        cdat_sum_empty_state('No RTA records found.');
    } else {
        cdat_sum_generic_table_open('RTA', ['PHONE', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS', 'VEHICLE_DETAILS'], 'name_rta', 'name_rta.csv', count($rta));
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
    cdat_sum_report_banner((string) ($b5['PHONE3'] ?? ('NAME SEARCH IN LICENCE_DATA:' . $NAME)));
    $lic = cdat_sum_fetch_all($st6);
    if (empty($lic)) {
        cdat_sum_empty_state('No licence records found.');
    } else {
        cdat_sum_generic_table_open('Licence', ['PHONE', 'LICENCE_NO', 'FULLNAME', 'FATHERNAME', 'DOB', 'FULLADDRESS'], 'name_lic', 'name_licence.csv', count($lic));
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
    cdat_sum_report_banner((string) ($b7['PHONE3'] ?? ('NAME SEARCH IN CIVILSUPPLY_DATA:' . $NAME)));
    $cs = cdat_sum_fetch_all($st8);
    if (empty($cs)) {
        cdat_sum_empty_state('No civil supply records found.');
    } else {
        cdat_sum_generic_table_open('Civil Supply', ['PHONE', 'FULLNAME', 'FULLADDRESS', 'RATION_CARD_NO', 'AADHAR'], 'name_cs', 'name_civil.csv', count($cs));
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
    cdat_sum_report_banner((string) ($b9['PHONE5'] ?? ('NAME SEARCH IN CDATSUSPECT_DATA:' . $NAME)));
    $sus = cdat_sum_fetch_all($st10);
    if (empty($sus)) {
        cdat_sum_empty_state('No suspect records found.');
    } else {
        cdat_sum_generic_table_open('Suspect', ['PHONE', 'FULLNAME', 'ROLE', 'FATHERNAME', 'FULLADDRESS', 'CRIME_DETAILS'], 'name_sus', 'name_suspect.csv', count($sus));
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

    cdat_sum_results_close();
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Namesearch');
cdat_sum_page_open();
cdat_sum_search_card(
    'Name Search',
    'Search SDR, RTA, licence, civil supply, and suspect data by name and address.',
    'namesearch.php',
    cdat_sum_field_text('NAME', 'Name', '', 'NAME', 'NAME', false)
        . cdat_sum_field_text('ADDRESS', 'Address', '', 'ADDRESS', 'ADDRESS', false),
    '',
    'Submit'
);
cdat_sum_page_close();
layout_end();
