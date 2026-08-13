<?php
// Must run before any output: audit_require_* redirects with
// header(), which is lost once the layout has started printing.
require_once __DIR__ . '/activity_logger.php';
audit_require_session();

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/sql_safe.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';

$isAjax = cdat_sum_is_ajax();
$number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
$operator = sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
$state = sql_safe_alnum($_POST['STATE'] ?? '', 50);

if (!$isAjax) {
    layout_begin('Call Details');
    cdat_sum_page_open();
    cdat_sum_back_link('calls_tot.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array('Database' => 'CDATDUPL');
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' ";

$sql2 = cdr_sql_enrich_tt($operator, $state, ['with_last_update' => true]);

$sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION from #temp_cdrs  ORDER BY STARTTIME";

$sql6 = "select 'CALL DETAILS OF MOBILE NO. '+'$number'as PHONE";

$st1 = sqlsrv_query($conn, $sql1);
sqlsrv_render_query_error($st1, 'Call details base');
$st2 = sqlsrv_query($conn, $sql2);
sqlsrv_render_query_error($st2, 'Tower enrichment');
$st5 = sqlsrv_query($conn, $sql5);
sqlsrv_render_query_error($st5, 'Call details ordering');
$st6 = sqlsrv_query($conn, $sql6);
sqlsrv_render_query_error($st6, 'Call details result');

$banner = 'CALL DETAILS OF MOBILE NO. ' . $number;
if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE'] ?? $banner);
}

$rows = cdat_sum_fetch_all($st5);
cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No call details found.');
} else {
    cdat_sum_generic_table_open(
        'Call Details',
        ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION'],
        'calldetails_table',
        'calldetails.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
            (string) ($row['NICKNAME'] ?? ''),
            ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['TYPE'] ?? ''),
            ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['OPERATOR'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();

if ($st5) {
    sqlsrv_free_stmt($st5);
}
sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
