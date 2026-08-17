<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$crimeHead = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$hasSearch = $crimeHead !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a crime head and try again.');
$fieldsHtml = cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHead, 'CRIME_HEAD', 'Enter crime head');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IR Search By Head');
        cdat_sum_page_open();
        cdat_sum_back_link('ir_search.php', 'Back');
        cdat_sum_search_card(
            'IR Search By Crime Head',
            'Search IR records by crime head.',
            'ir_search_by_head.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "10.10.46.14\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $number1 = $crimeHead;

    $sql8 = "SELECT 'DETAILS OF : '+'$number1' as PHONE1";


    $sql9 = "SELECT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
ELSE '' END PDACT_KEY,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN [IMAGE] ELSE (SELECT [IMAGE] FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS [IMAGE],
NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION  FROM FORMS..IR_PARTICULARS A
INNER JOIN FORMS..OFFENCE_DETAILS B ON  B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' AND 
 B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%' AND
ltrim(rtrim('$number1'))!='' and len(replace('$number1',' ',''))>'4' AND A.IRKEY=B.IRKEY
LEFT JOIN FORMS..IMAGE_TABLE C ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),C.IRKEY)";

    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);

    $banner = 'DETAILS OF : ' . $number1;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No IR records found for: ' . $crimeHead);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'IR Search By Head',
            ['IRKEY', 'PDACT', 'IMAGE', 'ACCUSED NAME', 'ALIAS NAME', 'FATHER NAME', 'AGE', 'POLICE STATION', 'CRIME HEAD', 'MO'],
            'results_table',
            'ir_search_by_head.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $pdactText = (string) ($row['PDACT'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => $pdactText !== '' ? '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactText) . '</a>' : ''],
                ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['ALIAS_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['POLICE_STATION'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
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

layout_begin('IR Search By Head');
cdat_sum_page_open();
cdat_sum_back_link('ir_search.php', 'Back');
cdat_sum_search_card(
    'IR Search By Crime Head',
    'Search IR records by crime head.',
    'ir_search_by_head.php',
    cdat_sum_field_text('CRIME_HEAD', 'Crime Head', '', 'CRIME_HEAD', 'Enter crime head'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
