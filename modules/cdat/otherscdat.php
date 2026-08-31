<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Others Cdat');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Others CDAT Contacts',
            'Find CDAT contacts of others linked to a mobile number.',
            'otherscdat.php',
            cdat_sum_field_phone($number),
            'BTN_CDAT',
            'Search'
        );
    }
    require_once CDAT_COMMON . '/sql_safe.php';
    $number = sql_safe_phone($number);
    if ($number === '') {
        cdat_sum_empty_state('Enter a valid mobile number and try again.');
        if ($isAjax) {
            exit;
        }
        cdat_sum_page_close();
        layout_end();
        exit;
    }
    $conn = get_cdat_pdo();

    // Use parameterized queries to prevent SQL injection
    $sql1 = "CREATE TEMP TABLE temp_t AS SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED";
    $params1 = array($number);
    $st1 = $conn->prepare($sql1);
    $st1->execute($params1);
    

    $sql2 = "CREATE TEMP TABLE temp_s AS SELECT A.PHONE, TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL, TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL, B.NICKNAME, TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
    $params2 = array($number);
    $st2 = $conn->prepare($sql2);
    $st2->execute($params2);
    

    $sql3 = "SELECT DISTINCT A.PHONE,
            CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
            CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
            CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
            CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
            CASE WHEN A.PHONE = C.PHONE THEN COALESCE(C.FULLNAME, '') || ', ' || COALESCE(C.FULLADDRESS, '') || ', ' || COALESCE(C.CATEGORY_TYPE, '') 
            WHEN A.PHONE = D.PHONE THEN COALESCE(D.FULLNAME, '') || ', ' || COALESCE(D.FULLADDRESS, '') || ', ' || COALESCE(D.CATEGORY_TYPE, '') 
            ELSE COALESCE(AREADESCRIPTION, '') END AS ADDRESS 
            FROM temp_t A
            LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
            LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATPHONEAREA ON CASE WHEN LENGTH(A.PHONE) = 10 THEN A.PHONE 
            ELSE CASE WHEN LENGTH(A.PHONE) > 10 THEN '00' || A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
            LIKE PHONEPREFIX || '%'
            LEFT JOIN temp_s B ON A.PHONE = B.PHONE";
    $st3 = $conn->query($sql3);

    $sql4 = "CREATE TEMP TABLE temp_jrms_temp AS SELECT DISTINCT OTHER FROM CDATPCSUSPECT WHERE PHONE = ?
            AND LENGTH(OTHER) >= 10 AND OTHER ~ '^[0-9]+$' AND SUBSTRING(OTHER,1,1) IN ('7','8','9')";
    $params4 = array($number);
    $st4 = $conn->prepare($sql4);
    $st4->execute($params4);
    

    $sql5 = "CREATE TEMP TABLE temp_temp1 AS SELECT DISTINCT PHONE, OTHER,
            SUM(CASE WHEN INCOMING = '1' THEN 1 ELSE 0 END) AS \"IN\",
            SUM(CASE WHEN INCOMING = '0' THEN 1 ELSE 0 END) AS \"OUT\",
            COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
            TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FC, TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LC 
             FROM CDATPCSUSPECT WHERE OTHER IN
            (SELECT DISTINCT OTHER FROM temp_jrms_temp) AND PHONE != ?
            GROUP BY PHONE, OTHER ORDER BY OTHER";
    $params5 = array($number);
    $st5 = $conn->prepare($sql5);
    $st5->execute($params5);
    

    $sql6 = "CREATE TEMP TABLE temp_temp2 AS SELECT OTHER AS PHONE, A.PHONE AS OTHER, C.NICKNAME, CATEGORY, \"IN\", \"OUT\", CALLS, DUR, FC AS FIRST_CALL, LC AS LAST_CALL, INC_OFFICER FROM temp_temp1 A
            LEFT JOIN CDATSUSPECT C ON A.PHONE = C.PHONE";
    $st6 = $conn->query($sql6);

    $sql7 = "SELECT DISTINCT A.PHONE, OTHER, NICKNAME, CATEGORY, \"IN\", \"OUT\", CALLS, DUR, FIRST_CALL, LAST_CALL, INC_OFFICER 
            FROM temp_temp2 A ORDER BY PHONE, CALLS DESC";
    $st7 = $conn->query($sql7);

    $sql8 = "SELECT 'OTHERS CDAT CONTACTS OF MOBILE NO: ' || ? as PHONE";
    $params8 = array($number);
    $st8 = $conn->prepare($sql8);
    $st8->execute($params8);
    

    $sql9 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE ? END as CNTS FROM temp_temp2";
    $st9 = $conn->prepare($sql9);
    $st9->execute(['*** NO CDAT CONTACTS TO OTHERS OF ' . $number . ' ***']);

    $bannerTitle = 'OTHERS CDAT CONTACTS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = $st8->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $headerRows = cdat_sum_fetch_all($st3);
    $contactRows = cdat_sum_fetch_all($st7);

    $noContactsMsg = '';
    if ($st9 && ($cntRow = $st9->fetch(PDO::FETCH_ASSOC))) {
        $noContactsMsg = (string) ($cntRow['CNTS'] ?? '');
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($bannerTitle);

    if (!empty($headerRows)) {
        cdat_sum_generic_table_open(
            'Subject',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'LAST_UPDATED', 'ADDRESS'],
            'results_table',
            'others_cdat_subject.csv',
            count($headerRows)
        );
        foreach ($headerRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($noContactsMsg !== '') {
        cdat_sum_empty_state($noContactsMsg);
    } elseif (!empty($contactRows)) {
        cdat_sum_generic_table_open(
            'CDAT Contacts',
            ['OTHER', 'CDAT PHONE', 'NICK NAME', 'CAT', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'IO NAME'],
            'contact_results_table',
            'others_cdat_contacts.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            $other = (string) ($row['OTHER'] ?? '');
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                [
                    'html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts2.php')) . '?PHONE_NO=' . urlencode($other) . '">' . cdat_sum_h($other) . '</a>',
                    'class' => 'sum-cell-other',
                ],
                (string) ($row['NICKNAME'] ?? ''),
                (string) ($row['CATEGORY'] ?? ''),
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['INC_OFFICER'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    cdat_sum_results_close();

    if ($st2) {
        $st2 = null;
    }
    if ($st7) {
        $st7 = null;
    }
    $conn = null;

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Others Cdat');
cdat_sum_page_open();
cdat_sum_search_card(
    'Others CDAT Contacts',
    'Find CDAT contacts of others linked to a mobile number.',
    'otherscdat.php',
    cdat_sum_field_phone(),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
