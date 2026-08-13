<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Bulk Irsearch Irkey');
    cdat_sum_page_open();
    cdat_sum_back_link('bulk_irkey.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['IRKEY'])) {
    $number = $_POST['IRKEY'];
    $number2 = str_replace(",", "','", "$number");

    $sql9 = "SELECT DISTINCT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE 
WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1') 
ELSE '' END PDACT_KEY,NAME,ALIAS_NAME,FATHER_NAME,AGE,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST INTO #TEMP FROM IRFORMS..IR_PARTICULARS A
INNER JOIN IRFORMS..OFFENCE_DETAILS B ON A.IRKEY IN ('$number2') AND B.IRKEY IN ('$number2')
ORDER BY DATE_OF_ARREST DESC";

    $sql10 = "SELECT A.*,IMAGE,B.CCNO FROM #TEMP A LEFT JOIN IRFORMS..IMAGE_TABLE B ON A.IRKEY=B.IRKEY order by POLICE_STATION,CRIME_NO,YEAR";

    sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $rows = cdat_sum_fetch_all($st10);

    cdat_sum_results_open();
    cdat_sum_report_banner('BULK IR SEARCH');
    if (empty($rows)) {
        cdat_sum_empty_state('No IR records found for the given keys.');
    } else {
        cdat_sum_generic_table_open(
            'Bulk IR Search',
            ['IRKEY', 'PDACT', 'IMAGE', 'ACCUSED NAME', 'ALIAS NAME', 'FATHER NAME', 'CRIME HEAD', 'MO', 'DOA', 'AGE', 'PRESENT ADDRESS', 'CRIME NO', 'YEAR', 'SEC_OF_LAW', 'POLICE STATION'],
            'results_table',
            'bulk_irsearch.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $pdact = (string) ($row['PDACT'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="ir.php?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => $pdact !== '' ? '<a href="pdact_main.php?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdact) . '</a>' : ''],
                ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['ALIAS_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
                ['text' => (string) ($row['DATE_OF_ARREST'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRIME_NO'] ?? ''),
                (string) ($row['YEAR'] ?? ''),
                (string) ($row['SEC_OF_LAW'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();
} else {
    cdat_sum_empty_state('Enter IR keys to search.');
}

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
