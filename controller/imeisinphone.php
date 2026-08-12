<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/sql_safe.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IMEIs used in Phone');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'IMEIs Used in Phone Number',
            'List IMEI numbers used with a mobile number.',
            'imeisinphone.php',
            cdat_sum_field_phone($phone, 'IMEI_IN_PHONE'),
            'BTN_CDAT',
            'Search'
        );
    }

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );

    if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
    }

    $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');

    // Use parameterized queries to prevent SQL injection
    $sql1 = "SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE = ?";
    $params1 = array($number);
    $st1 = sqlsrv_prepare($conn, $sql1, $params1);
    sqlsrv_execute($st1);
    sqlsrv_render_query_error($st1, 'Phone CDR lookup');

    $sql2 = "SELECT DISTINCT PHONE, IMEINUMBER,
            SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
            SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT',
            COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
            CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL,
            CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL 
            INTO #TT FROM #T
            GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";
    $st2 = sqlsrv_query($conn, $sql2);
    sqlsrv_render_query_error($st2, 'IMEI aggregation');

    $sql3 = "SELECT A.PHONE, IMEINUMBER, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, 
            CASE WHEN C.PHONE IS NOT NULL
            THEN COALESCE(C.FULLNAME + ', ' + C.FULLADDRESS, '') + ' ' + COALESCE(C.CATEGORY_TYPE, '')
            WHEN D.PHONE IS NOT NULL
            THEN COALESCE(D.FULLNAME + ', ' + D.FULLADDRESS, '') + ' ' + COALESCE(D.CATEGORY_TYPE, '')
            ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
            LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
            LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX + '%'
            ORDER BY LAST_CALL";
    $st3 = sqlsrv_query($conn, $sql3);
    sqlsrv_render_query_error($st3, 'Address join');

    $sql4 = "SELECT 'LIST OF IMEIS USED IN PHONE NO: ' + ? as PHONE1";
    $params4 = array($number);
    $st4 = sqlsrv_prepare($conn, $sql4, $params4);
    sqlsrv_execute($st4);

    $bannerTitle = 'LIST OF IMEIS USED IN PHONE NO: ' . $number;
    if ($st4 && ($bannerRow = sqlsrv_fetch_array($st4, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st3);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'IMEIs used in Phone',
            ['PHONE', 'IMEINUMBER', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS'],
            'contact_results_table',
            'imeis_in_phone.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st3) {
        sqlsrv_free_stmt($st3);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IMEIs used in Phone');
cdat_sum_page_open();
cdat_sum_search_card(
    'IMEIs Used in Phone Number',
    'List IMEI numbers used with a mobile number.',
    'imeisinphone.php',
    cdat_sum_field_phone('', 'IMEI_IN_PHONE'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
