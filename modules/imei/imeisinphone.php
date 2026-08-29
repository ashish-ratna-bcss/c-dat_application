<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/sql_safe.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and try again.');

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
    $conn = get_cdat_pdo();

        $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');

    // Use parameterized queries to prevent SQL injection
    $sql1 = "CREATE TEMP TABLE temp_t AS SELECT * FROM CDATPCSUSPECT WHERE PHONE = ?";
    $params1 = array($number);
    $st1 = $conn->prepare($sql1);
    $st1->execute($params1);
    
    

    $sql2 = "CREATE TEMP TABLE temp_tt AS SELECT DISTINCT PHONE, IMEINUMBER,
            SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
            SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS \"OUT\",
            COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
            TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,
            TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL 
             FROM temp_t
            GROUP BY PHONE, IMEINUMBER ORDER BY LAST_CALL";
    $st2 = $conn->query($sql2);
    

    $sql3 = "SELECT A.PHONE, IMEINUMBER, \"IN\", \"OUT\", CALLS, DUR, FIRST_CALL, LAST_CALL, 
            CASE WHEN C.PHONE IS NOT NULL
            THEN COALESCE(C.FULLNAME || ', ' || C.FULLADDRESS, '') || ' ' || COALESCE(C.CATEGORY_TYPE, '')
            WHEN D.PHONE IS NOT NULL
            THEN COALESCE(D.FULLNAME || ', ' || D.FULLADDRESS, '') || ' ' || COALESCE(D.CATEGORY_TYPE, '')
            ELSE AREADESCRIPTION END AS ADDRESS FROM temp_tt A
            LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
            LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATPHONEAREA E ON A.PHONE LIKE PHONEPREFIX || '%'
            ORDER BY LAST_CALL";
    $st3 = $conn->query($sql3);
    

    $sql4 = "SELECT 'LIST OF IMEIS USED IN PHONE NO: ' || ? as PHONE1";
    $params4 = array($number);
    $st4 = $conn->prepare($sql4);
    $st4->execute($params4);
    

    $bannerTitle = 'LIST OF IMEIS USED IN PHONE NO: ' . $number;
    if ($st4 && ($bannerRow = $st4->fetch(PDO::FETCH_ASSOC))) {
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
        $st3 = null;
    }
    $conn = null;

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
