<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$crimeHead = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$hasSearch = $name !== '' && $crimeHead !== '';

$fieldsHtml = cdat_sum_field_text('NAME', 'Name of the Offender', $name, 'NAME', 'Enter NAME')
            . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHead, 'CRIME_HEAD', 'Enter CRIME HEAD');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Name Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender Search By Name',
            'Search offender records by name and crime head.',
            'name_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $number = $name;
    $number1 = $crimeHead;

    $sql8 = "SELECT 'DETAILS OF : '+'$number' as PHONE1";

    $sql9 = "SELECT DISTINCT NICKNAME ACCUSED_NAME,ROLE,FNAME,ADDRESS,STATE,CRIME_NO,YEAR,SEC_OF_LAW,UNIT,CRIME_HEAD,MO,ORGANISATION 
FROM CDATDUPL..CDATSUSPECT WHERE NICKNAME LIKE '%'+REPLACE('$number',' ','%')+'%' AND CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' AND 
ltrim(rtrim('$number'))!='' and len(replace('$number',' ',''))>'5'";

    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);

    $banner = 'DETAILS OF : ' . $number;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No offender records found for: ' . $name);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Offender Name Search',
            ['ACCUSED NAME', 'ROLE', 'FNAME', 'ADDRESS', 'STATE', 'CRIME_NO', 'YEAR', 'SEC_OF_LAW', 'UNIT', 'CRIME_HEAD', 'MO', 'ORGANISATION'],
            'results_table',
            'name_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                (string) ($row['ACCUSED_NAME'] ?? ''),
                (string) ($row['ROLE'] ?? ''),
                (string) ($row['FNAME'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['STATE'] ?? ''),
                ['text' => (string) ($row['CRIME_NO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['YEAR'] ?? ''),
                (string) ($row['SEC_OF_LAW'] ?? ''),
                (string) ($row['UNIT'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
                (string) ($row['ORGANISATION'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

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

layout_begin('Name Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender Search By Name',
    'Search offender records by name and crime head.',
    'name_search.php',
    cdat_sum_field_text('NAME', 'Name of the Offender', '', 'NAME', 'Enter NAME')
        . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', '', 'CRIME_HEAD', 'Enter CRIME HEAD'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
