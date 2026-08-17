<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('PDACT Ps Wise Search Php');
    cdat_sum_page_open();
    cdat_sum_back_link('pdact_ps_wise_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "PDACT");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$number = $_POST['PDACT_PS'];

$sql0 = "select distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT into #temp from PDACT_MAIN_TABLE WHERE PD_ACT_PS LIKE '%$number%'";

$sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

sqlsrv_query($conn, $sql0);
$st1 = sqlsrv_query($conn, $sql1);
$rows = cdat_sum_fetch_all($st1);

cdat_sum_results_open();
cdat_sum_report_banner('ACCUSED INFORMATION');
if (empty($rows)) {
    cdat_sum_empty_state('No PDACT records found.');
} else {
    cdat_sum_generic_table_open(
        'PDACT Search By PS',
        ['PDACT_KEY', 'IRKEY', 'NAME', 'IMAGE', 'FATHER_NAME', 'AGE', 'NATIVE_DISTRICT', 'NATIVE_STATE', 'PD_ACT_PS', 'DATE_OF_PDACT'],
        'results_table',
        'pdact_ps_search.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
        $irKey = (string) ($row['IRKEY'] ?? '');
        cdat_sum_table_row([
            ['html' => '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactKey) . '</a>'],
            ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['NAME'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 120, 120), 'class' => 'sum-cell-img'],
            (string) ($row['FATHER_NAME'] ?? ''),
            ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['NATIVE_DISTRICT'] ?? ''),
            (string) ($row['NATIVE_STATE'] ?? ''),
            (string) ($row['PD_ACT_PS'] ?? ''),
            ['text' => (string) ($row['DATE_OF_PDACT'] ?? ''), 'class' => 'sum-cell-date'],
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
