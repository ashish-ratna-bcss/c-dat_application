<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$number1 = trim((string) ($_POST['OTHER_NO'] ?? ''));
$hasSearch = $number !== '' && $number1 !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter both mobile numbers and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Movements Between Two Nos');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Call Details Between Numbers',
            'View call movements between two mobile numbers.',
            'movements_between_two_numbers.php',
            cdat_sum_field_phone($number) . cdat_sum_field_other_phone($number1)
        );
    }

    set_time_limit(0);
    require_once CDAT_COMMON . '/cdr_enrichment_sql.php';

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number'  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";

    $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND OTHER='$number1'";

    $sql2 = cdr_sql_enrich_tt('', '', ['with_last_update' => true, 'with_lat_long' => true]);

    $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION,LAT,LONG,AZM from #temp_cdrs  ORDER BY STARTTIME";

    $sql6 = "select 'CALL DETAILS OF MOBILE NO. '+'$number' + 'AND OTHER NO. '+'$number1' as PHONE";

    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st5 = sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);

    $bannerTitle = "CALL DETAILS OF MOBILE NO. {$number} AND OTHER NO. {$number1}";
    if ($st6 && ($bannerRow = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st5);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Call Details Between Numbers',
            ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION', 'LAT', 'LONG', 'AZM'],
            'results_table',
            'movements_between_two.csv',
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
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZM'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
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

layout_begin('Movements Between Two Nos');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details Between Numbers',
    'View call movements between two mobile numbers.',
    'movements_between_two_numbers.php',
    cdat_sum_field_phone() . cdat_sum_field_other_phone()
);
cdat_sum_page_close();
layout_end();
