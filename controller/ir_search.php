<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$crimeHead = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$hasSearch = $name !== '' && $crimeHead !== '';

$fieldsHtml = cdat_sum_field_text('NAME', 'Name of the Offender', $name, 'NAME', 'Enter NAME')
            . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHead, 'CRIME_HEAD', 'Enter CRIME HEAD');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IR Search By Name');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender IR Search By Name',
            'Search IR records by offender name and crime head.',
            'ir_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    $serverName = "10.10.46.14\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        // die( print_r( sqlsrv_errors(), true));
    }

    // Use parameterized queries to prevent SQL injection
    $sql8 = "SELECT 'DETAILS OF : ' + ? as PHONE1";
    $params8 = array($name);
    $st8 = sqlsrv_prepare($conn, $sql8, $params8);
    sqlsrv_execute($st8);

    $sql9 = "SELECT DISTINCT A.IRKEY,
                    (CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
                    WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,
                    CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
                    WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
                    WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
                    ELSE '' END PDACT_KEY,
                    NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,
                    CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST 
                    FROM FORMS..IR_PARTICULARS A
                    INNER JOIN FORMS..OFFENCE_DETAILS B ON A.NAME LIKE '%' + REPLACE(?, ' ', '%') + '%' 
                    AND (B.CRIME_HEAD LIKE '%' + REPLACE(?, ' ', '%') + '%' OR 
                    B.MO LIKE '%' + REPLACE(?, ' ', '%') + '%') 
                    AND LTRIM(RTRIM(?)) != '' 
                    AND LEN(REPLACE(?, ' ', '')) > '4' 
                    AND A.IRKEY = B.IRKEY 
                    ORDER BY DATE_OF_ARREST DESC";

    $params9 = array($name, $crimeHead, $crimeHead, $name, $name);
    $st9 = sqlsrv_prepare($conn, $sql9, $params9);
    sqlsrv_execute($st9);

    if ($st9 === false) {
        //  die(print_r(sqlsrv_errors(), true));
    }

    $bannerTitle = 'DETAILS OF : ' . $name;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No records found for the search criteria');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'IR Search Results',
            ['IRKEY', 'PDACT', 'ACCUSED NAME', 'ALIAS NAME', 'FATHER NAME', 'AGE', 'PRESENT ADDRESS', 'CRIME NO', 'YEAR', 'SEC_OF_LAW', 'POLICE STATION', 'CRIME HEAD', 'MO', 'DOA'],
            'results_table',
            'ir_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $pdactText = (string) ($row['PDACT'] ?? '');
            $addressHtml = cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? ''));

            $irKeyCell = [
                'html' => '<a href="ir.php?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>',
                'class' => 'sum-cell-num',
            ];
            $pdactCell = $pdactText !== ''
                ? [
                    'html' => '<a href="pdact_main.php?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactText) . '</a>',
                ]
                : '';

            cdat_sum_table_row([
                $irKeyCell,
                $pdactCell,
                (string) ($row['NAME'] ?? ''),
                (string) ($row['ALIAS_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => $addressHtml !== '' ? $addressHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRIME_NO'] ?? ''),
                (string) ($row['YEAR'] ?? ''),
                (string) ($row['SEC_OF_LAW'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
                (string) ($row['DATE_OF_ARREST'] ?? ''),
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

layout_begin('IR Search By Name');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender IR Search By Name',
    'Search IR records by offender name and crime head.',
    'ir_search.php',
    cdat_sum_field_text('NAME', 'Name of the Offender', '', 'NAME', 'Enter NAME')
        . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', '', 'CRIME_HEAD', 'Enter CRIME HEAD'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
