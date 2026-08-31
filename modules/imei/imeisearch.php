<?php
require_once __DIR__ . '/../common/bootstrap.php';
// Must run before any output: audit_require_* redirects with
// header(), which is lost once the layout has started printing.
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/sql_safe.php';

$isAjax = cdat_sum_is_ajax();
$imei = trim((string) ($_POST['IMEI_NO'] ?? ''));
$hasSearch = $imei !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter an IMEI number and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Phones used in IMEI');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'IMEI Search',
            'List phone numbers used with an IMEI.',
            'imeisearch.php',
            cdat_sum_field_imei($imei),
            'BTN_CDAT',
            'Search'
        );
    }

    audit_log('IMEI Search', 'Search', ['imei_number' => $_POST['IMEI_NO'] ?? '']);
    $conn = get_cdat_pdo();

        $number = sql_safe_imei($_POST['IMEI_NO'] ?? '');

    // Use parameterized queries to prevent SQL injection
    $sql1 = "CREATE TEMP TABLE temp_t AS SELECT * FROM CDATPCSUSPECT WHERE IMEINUMBER = ?";
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
    

    $sql4 = "SELECT 'LIST OF PHONE NOs USED IN IMEI: ' || ? as PHONE1";
    $params4 = array($number);
    $st4 = $conn->prepare($sql4);
    $st4->execute($params4);
    

    $sql5 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE '*** NO PHONES ARE AVAILABLE IN IMEI ' || ? || ' ***' END as PHONE FROM temp_tt";
    $st5 = $conn->prepare($sql5);
    $st5->execute([$number]);

    $bannerTitle = 'LIST OF PHONE NOs USED IN IMEI: ' . $number;
    if ($st4 && ($bannerRow = $st4->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st3);

    $emptyMsg = '';
    if ($st5 && ($emptyRow = $st5->fetch(PDO::FETCH_ASSOC))) {
        $emptyMsg = trim((string) ($emptyRow['PHONE'] ?? ''));
    }

    if ($emptyMsg !== '' || empty($rows)) {
        cdat_sum_empty_state($emptyMsg !== '' ? $emptyMsg : 'Records not found');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Phones used in IMEI',
            ['PHONE', 'IMEINUMBER', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS'],
            'contact_results_table',
            'imei_search.csv',
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

layout_begin('Phones used in IMEI');
cdat_sum_page_open();
cdat_sum_search_card(
    'IMEI Search',
    'List phone numbers used with an IMEI.',
    'imeisearch.php',
    cdat_sum_field_imei(),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
