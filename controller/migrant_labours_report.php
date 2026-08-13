<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Migrant Labours Report');
    cdat_sum_page_open();
    cdat_sum_back_link('migrant_labours_date_report.php', 'Back to date search');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "MIGRANT_LABOURS_FORM");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql11 = "SELECT DISTINCT POLICE_STATION,
NAME,NATIVE_STATE,NATIVE_DISTRICT,PHONE,WORK_STATUS,
PART_OF_LABOUR_CAMP,URGENT,PROBLEM_CASES,REMARKS INTO #TEMP FROM MIGRANT_LABOUR_TABLE WHERE POLICE_STATION!='' AND NAME NOT LIKE ''";

$sql12 = "SELECT DISTINCT 'HYDERABAD TOTAL COUNT:'+CONVERT(VARCHAR(20),COUNT(*)) AS PHONE1 FROM #TEMP";

$sql13 = "Select distinct *,ROW_NUMBER() OVER(ORDER BY POLICE_STATION) SLNO from #temp";

sqlsrv_query($conn, $sql11);
$st12 = sqlsrv_query($conn, $sql12);
$st13 = sqlsrv_query($conn, $sql13);

$banner = 'HYDERABAD TOTAL COUNT';
if ($st12 && ($b = sqlsrv_fetch_array($st12, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE1'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st13);

cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No migrant labour records found.');
} else {
    cdat_sum_generic_table_open(
        'Migrant Labours Report',
        ['NAME OF THE POLICE STATION', 'SLNO', 'NAME OF THE MIGRANT WORKER', 'NATIVE STATE', 'NATIVE DISTRICT', 'MOBILE NUMBER', 'WORKING STATUS', 'IS HE PART OF LABOUR CAMP', 'IS URGENT', 'PROBLEM CASES', 'REMARKS'],
        'results_table',
        'migrant_labours_report.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            (string) ($row['POLICE_STATION'] ?? ''),
            ['text' => (string) ($row['SLNO'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['NAME'] ?? ''),
            (string) ($row['NATIVE_STATE'] ?? ''),
            (string) ($row['NATIVE_DISTRICT'] ?? ''),
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['WORK_STATUS'] ?? ''),
            (string) ($row['PART_OF_LABOUR_CAMP'] ?? ''),
            (string) ($row['URGENT'] ?? ''),
            (string) ($row['PROBLEM_CASES'] ?? ''),
            (string) ($row['REMARKS'] ?? ''),
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
