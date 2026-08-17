<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('PDACT');
cdat_sum_page_open();

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array('Database' => 'PDACT');
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql9 = "select distinct top 10 PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
CONVERT(VARCHAR(20),Date_Of_Arrest) AS DATE_OF_PDACT,CRIME_HEAD_SEARCH  into #temp from PDACT_MAIN_TABLE
order by DATE_OF_PDACT desc";

$sql10 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,CRIME_HEAD_SEARCH,
CONVERT(VARCHAR(20),DATE_OF_PDACT) AS DATE_OF_PDACT,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ORDER BY DATE_OF_PDACT DESC";

$sql8 = "SELECT DISTINCT 'RECENT ARRESTED PDACT CRIMINALS'  as PHONE1 FROM #TEMP";

sqlsrv_query($conn, $sql9);
$st10 = sqlsrv_query($conn, $sql10);
$st8 = sqlsrv_query($conn, $sql8);

$banner = 'RECENT ARRESTED PDACT CRIMINALS';
if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE1'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st10);

if (empty($rows)) {
    cdat_sum_empty_state('No recent PDACT records found.');
} else {
    cdat_sum_results_open();
    cdat_sum_report_banner($banner);
    cdat_sum_generic_table_open(
        'Recent PDACT',
        ['PDACT_KEY', 'IRKEY', 'NAME', 'FATHER_NAME', 'AGE', 'NATIVE_DISTRICT', 'NATIVE_STATE', 'PD_ACT_PS', 'CRIME_HEAD', 'DATE_OF_PDACT', 'IMAGE'],
        'results_table',
        'pdact_recent.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
        $irKey = (string) ($row['IRKEY'] ?? '');
        cdat_sum_table_row([
            ['html' => '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactKey) . '</a>'],
            ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHER_NAME'] ?? ''),
            ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['NATIVE_DISTRICT'] ?? ''),
            (string) ($row['NATIVE_STATE'] ?? ''),
            (string) ($row['PD_ACT_PS'] ?? ''),
            (string) ($row['CRIME_HEAD_SEARCH'] ?? ''),
            ['text' => (string) ($row['DATE_OF_PDACT'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
    cdat_sum_results_close();
}

sqlsrv_close($conn);
cdat_sum_page_close();
layout_end();
