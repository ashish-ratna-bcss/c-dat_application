<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? $_GET['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_phone($phone);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IMEI Request Movements');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Movements Of Lost Mobile Number',
            'View CDR movements for a lost-report mobile number.',
            'imei_request_movements.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once __DIR__ . '/cdr_enrichment_sql.php';
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $phone;

    $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A WITH (NOLOCK) LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";

    $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,CONVERT(DATE,STARTTIME),20) DATE,CONVERT(VARCHAR,CONVERT(TIME,STARTTIME),20) TIME,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA WITH (NOLOCK) WHERE PHONE='$number' ";

    $sql2 = cdr_sql_enrich_tt('', '', [
        'with_lat_long' => true,
        'with_state_col' => true,
        'with_date_time_cols' => true,
    ]);

    $sql5 = "SELECT PHONE,OTHER,NICKNAME,DATE,TIME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,STATE,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs  ORDER BY STARTTIME";

    $sql6 = "select 'CALL DETAILS OF MOBILE NO. '+'$number'as PHONE";

    sqlsrv_query($conn, $sql1);
    sqlsrv_query($conn, $sql2);
    $st5 = sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);

    $banner = 'CALL DETAILS OF MOBILE NO. ' . $number;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st5);

    if (empty($rows)) {
        cdat_sum_empty_state('No movements found for: ' . $number);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'IMEI Request Movements',
            ['PHONE', 'OTHER', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'STATE', 'AREA DESCRIPTION', 'LAT', 'LONG', 'AZM'],
            'results_table',
            'imei_movements.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['TYPE'] ?? ''),
                ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['OPERATOR'] ?? ''),
                (string) ($row['STATE'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZM'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IMEI Request Movements');
cdat_sum_page_open();
cdat_sum_search_card(
    'Movements Of Lost Mobile Number',
    'View CDR movements for a lost-report mobile number.',
    'imei_request_movements.php',
    cdat_sum_field_phone(),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
