<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$criteria = trim((string) ($_POST['EMPLOYEE_SEARCH'] ?? ''));
$searchNo = trim((string) ($_POST['EMPLOYEE_SEARCH_NO'] ?? ''));
$rank = trim((string) ($_POST['EMPLOYEE_SEARCH_RANK'] ?? ''));
$hasSearch = $criteria !== '' && $searchNo !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter search criteria and a number and try again.');

$criteriaOptions = [
    '' => 'Select search criteria',
    'EMPLOYEE_ID' => 'EMPLOYEE ID',
    'GENERAL_NO' => 'GENERAL NO',
    'NAME' => 'NAME',
];
$rankOptions = [
    '' => 'Select rank',
    'INSPECTOR' => 'INSPECTOR',
    'SI' => 'SI',
    'ASI' => 'ASI',
    'HC' => 'HC',
    'PC' => 'PC',
    'HG' => 'HG',
];

$fieldsHtml = cdat_sum_searchable_select('EMPLOYEE_SEARCH', 'Search criteria', $criteriaOptions, $criteria, 'Select search criteria', true)
            . cdat_sum_field_text('EMPLOYEE_SEARCH_NO', 'Emp Search', $searchNo, 'CAF', 'Emp Search')
            . cdat_sum_searchable_select('EMPLOYEE_SEARCH_RANK', 'Rank', $rankOptions, $rank, 'Select rank', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Trainings');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Employee Search',
            'Search training / PWDMS employee records.',
            'training_module1.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'TRAINING_DB');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $number = $criteria;
    $number1 = $searchNo;
    $number2 = $rank;

    $sql8 = "SELECT 'EMPLOYEE SEARCH IN PWDMS' as PHONE1";
    $sql9 = "SELECT DISTINCT EMPLOYEE_ID,NAME,[RANK],[ROLE],GENERAL_NO,WING_NAME,ZONE_NAME,DIVISION_NAME,
POLICE_STATION FROM TRAINING_DB.DBO.TRAINING_STRENGTH_PARTICULARS WHERE $number like '%'+'$number1'+'%'
AND RANK LIKE '%'+'$number2'+'%'";
    $sql10 = "SELECT 'EMPLOYEE SEARCH IN TRAINING DATA' as PHONE1";
    $sql11 = "SELECT DISTINCT EMPLOYEE_ID,GENERAL_NO,NAMES NAME,PS_NAME POLICE_STATION,PH_NO PHONE_NO,ZONE,
RANK,COURSE_NAME,START_DATE,END_DATE FROM TRNG_ATT_WITH_EMPID WHERE $number like '%'+'$number1'+'%' AND
RANK LIKE '%'+'$number2'+'%'";

    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);

    $banner1 = 'EMPLOYEE SEARCH IN PWDMS';
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner1 = (string) ($b['PHONE1'] ?? $banner1);
    }
    $rows1 = cdat_sum_fetch_all($st9);

    cdat_sum_results_open();
    cdat_sum_report_banner($banner1);
    if (empty($rows1)) {
        cdat_sum_empty_state('No PWDMS employee records found.');
    } else {
        cdat_sum_generic_table_open(
            'PWDMS',
            ['EMP ID', 'GEN NO', 'NAME', 'RANK', 'ROLE', 'WING', 'ZONE NAME', 'DIVISION NAME', 'PS', 'QRCODE'],
            'training_pwdms_table',
            'training_pwdms.csv',
            count($rows1)
        );
        foreach ($rows1 as $row) {
            $qrData = 'EMP_ID: ' . $row['EMPLOYEE_ID']
                    . ' NAME:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['NAME'])
                    . ' RANK:' . $row['RANK']
                    . ' ROLE:' . $row['ROLE']
                    . ' GEN_NO:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['GENERAL_NO'])
                    . ' PS: ' . $row['POLICE_STATION'];
            cdat_sum_table_row([
                ['text' => (string) ($row['EMPLOYEE_ID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['GENERAL_NO'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['RANK'] ?? ''),
                (string) ($row['ROLE'] ?? ''),
                (string) ($row['WING_NAME'] ?? ''),
                (string) ($row['ZONE_NAME'] ?? ''),
                (string) ($row['DIVISION_NAME'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                ['html' => '<img height="100" width="100" src="' . htmlspecialchars(CDAT_BASE . '/qrcode/php/qr_img.php?d=' . urlencode($qrData), ENT_QUOTES) . '" alt="">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $banner2 = 'EMPLOYEE SEARCH IN TRAINING DATA';
    if ($st10 && ($b2 = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
        $banner2 = (string) ($b2['PHONE1'] ?? $banner2);
    }
    $rows2 = cdat_sum_fetch_all($st11);
    cdat_sum_report_banner($banner2);
    if (empty($rows2)) {
        cdat_sum_empty_state('No training attendance records found.');
    } else {
        cdat_sum_generic_table_open(
            'Training Data',
            ['EMP ID', 'GEN NO', 'NAME', 'RANK', 'PHONE NO', 'ZONE', 'PS', 'COURSE NAME', 'START DATE', 'END DATE', 'QRCODE'],
            'training_att_table',
            'training_att.csv',
            count($rows2)
        );
        foreach ($rows2 as $row) {
            $qrData = 'EMP_ID: ' . $row['EMPLOYEE_ID']
                    . ' NAME:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['NAME'])
                    . ' RANK:' . $row['RANK']
                    . ' GEN_NO:' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', (string) $row['GENERAL_NO'])
                    . ' PS: ' . $row['POLICE_STATION'];
            cdat_sum_table_row([
                ['text' => (string) ($row['EMPLOYEE_ID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['GENERAL_NO'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['RANK'] ?? ''),
                ['text' => (string) ($row['PHONE_NO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['ZONE'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                (string) ($row['COURSE_NAME'] ?? ''),
                ['text' => (string) ($row['START_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['END_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => '<img height="100" width="100" src="' . htmlspecialchars(CDAT_BASE . '/qrcode/php/qr_img.php?d=' . urlencode($qrData), ENT_QUOTES) . '" alt="">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();

    if ($st9) {
        sqlsrv_free_stmt($st9);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Trainings');
cdat_sum_page_open();
cdat_sum_search_card(
    'Employee Search',
    'Search training / PWDMS employee records.',
    'training_module1.php',
    cdat_sum_searchable_select('EMPLOYEE_SEARCH', 'Search criteria', $criteriaOptions, '', 'Select search criteria', true)
        . cdat_sum_field_text('EMPLOYEE_SEARCH_NO', 'Emp Search', '', 'CAF', 'Emp Search')
        . cdat_sum_searchable_select('EMPLOYEE_SEARCH_RANK', 'Rank', $rankOptions, '', 'Select rank', false),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
