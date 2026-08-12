<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

$phoneNo = trim((string) ($_POST['PHONE_NO'] ?? ''));
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$operatorIn = trim((string) ($_POST['OPERATOR'] ?? ''));
$stateIn = trim((string) ($_POST['STATE'] ?? ''));
$hasSearch = $phoneNo !== '' && $fromDt !== '' && $toDt !== '';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Call Details Between Dates');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Call Details Between Dates',
            'Search call details for a mobile number within a date range.',
            'calls_btwn_dates.php',
            cdat_sum_field_phone($phoneNo)
            . cdat_sum_field_date_native('FROM_DT', 'From Date', $fromDt)
            . cdat_sum_field_date_native('TO_DT', 'To Date', $toDt)
            . cdat_sum_field_text('OPERATOR', 'Operator', $operatorIn, 'OPERATOR', 'Operator', false)
            . cdat_sum_field_text('STATE', 'State', $stateIn, 'STATE', 'State', false)
        );
    }

    require_once __DIR__ . '/activity_logger.php';
    require_once __DIR__ . '/sql_safe.php';
    require_once __DIR__ . '/cdr_enrichment_sql.php';

    audit_require_session();

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Sanitize input
    $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
    $operator = sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
    $state = sql_safe_alnum($_POST['STATE'] ?? '', 50);
    $f_date = sql_safe_alnum($_POST['FROM_DT'] ?? '', 10);
    $t_date = sql_safe_alnum($_POST['TO_DT'] ?? '', 10);

    // Audit log
    audit_log('Call Details Between Dates', 'Search', [
        'phone_number' => $number,
        'from_date' => $f_date,
        'to_date' => $t_date,
        'state' => $state,
        'operator' => $operator
    ]);

    // Use parameterized queries to prevent SQL injection
    $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
            CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
            IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  
            INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT 
            WHERE PHONE = ? AND convert(char(10),STARTTIME,121) BETWEEN ? AND ?";
    $params1 = array($number, $f_date, $t_date);
    $st1 = sqlsrv_prepare($conn, $sql1, $params1);
    sqlsrv_execute($st1);
    sqlsrv_render_query_error($st1, 'Calls between dates base');

    // Use the cdr_sql_enrich_tt function
    $sql2 = cdr_sql_enrich_tt($operator, $state);
    $st2 = sqlsrv_query($conn, $sql2);
    sqlsrv_render_query_error($st2, 'Tower enrichment');

    $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION 
             FROM #temp_cdrs ORDER BY STARTTIME";
    $st5 = sqlsrv_query($conn, $sql5);
    sqlsrv_render_query_error($st5, 'Result ordering');

    $sql6 = "SELECT 'CALL DETAILS OF MOBILE NO: ' + ? + ' FROM: ' + ? + ' TO: ' + ? AS PHONE";
    $params6 = array($number, $f_date, $t_date);
    $st6 = sqlsrv_prepare($conn, $sql6, $params6);
    sqlsrv_execute($st6);
    sqlsrv_render_query_error($st6, 'Title');

    $bannerTitle = '';
    while ($row = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC)) {
        $bannerTitle = (string) ($row['PHONE'] ?? '');
    }

    $resultRows = cdat_sum_fetch_all($st5);

    if (empty($resultRows)) {
        cdat_sum_empty_state();
    } else {
        echo '<div class="sum-results">';
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Call Details',
            ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION'],
            'contact_results_table',
            'calls_between_dates.csv',
            count($resultRows)
        );
        foreach ($resultRows as $row) {
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
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
        echo '</div>';
    }

    if ($st5) {
        sqlsrv_free_stmt($st5);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Call Details Between Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details Between Dates',
    'Search call details for a mobile number within a date range.',
    'calls_btwn_dates.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date_native('FROM_DT', 'From Date')
    . cdat_sum_field_date_native('TO_DT', 'To Date')
    . cdat_sum_field_text('OPERATOR', 'Operator', '', 'OPERATOR', 'Operator', false)
    . cdat_sum_field_text('STATE', 'State', '', 'STATE', 'State', false)
);
cdat_sum_page_close();
layout_end();
