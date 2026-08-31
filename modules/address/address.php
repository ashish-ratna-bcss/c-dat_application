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
        layout_begin('Single Address');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Address of Mobile No',
            'Look up the registered address for a mobile number.',
            'address.php',
            cdat_sum_field_phone($phone, 'ADDRESS'),
            'BTN_CDAT',
            'Search'
        );
    }
    $conn = get_cdat_pdo();
    $number = sql_safe_phone($phone);
    if ($number === '') {
        cdat_sum_empty_state('Enter a valid mobile number and try again.');
        if ($isAjax) {
            exit;
        }
        cdat_sum_page_close();
        layout_end();
        exit;
    }

    $sql8 = 'SELECT ? AS PHONE1';
    $st8 = $conn->prepare($sql8);
    $st8->execute(['ADDRESS OF MOBILE NO: ' . $number]);

    $sql9 = "CREATE TEMP TABLE temp_t AS SELECT ? AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,'' AS MO,'' AS LAST_UPDATED,'' AS INC_OFFICER";
    $st9 = $conn->prepare($sql9);
    $st9->execute([$number]);

    $sql10 = "CREATE TEMP TABLE temp_s AS SELECT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME || '_' || B.ROLE AS NICKNAME,MO,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED,
    INC_OFFICER 
     FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE,B.NICKNAME,MO,B.ROLE, INC_OFFICER";
    $st10 = $conn->prepare($sql10);
    $st10->execute([$number]);

    $sql11 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
    CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
    CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
    CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
    CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
    CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'') || ', ' || COALESCE(C.FULLADDRESS,'') || ', DOA: ' || COALESCE(TO_CHAR((C.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'') || ', ' || COALESCE(C.CATEGORY_TYPE,'') || ', ' || (CASE WHEN C.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.OPERATOR END)
    WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'') || ', ' || COALESCE(D.FULLADDRESS,'') || ',DOA: ' || COALESCE(TO_CHAR((D.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'') || ', ' || COALESCE(D.CATEGORY_TYPE,'') || ', ' || (CASE WHEN D.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS,
    CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM temp_t A
    LEFT JOIN CDATADDRESS C  ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
    LEFT JOIN ADDRESS_OTHER_STATE D  ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
    LEFT JOIN CDATPHONEAREA ON CASE WHEN LENGTH(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LENGTH(A.PHONE)>10 THEN '00' || A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
    LIKE phoneprefix || '%'
    LEFT JOIN temp_s B ON  A.PHONE=B.PHONE";

    $st11 = $conn->query($sql11);

    $bannerTitle = 'ADDRESS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = $st8->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st11);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Address',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'MO', 'LAST_UPDATED', 'ADDRESS', 'IO NAME', 'QRCODE'],
            'results_table',
            'address.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $address = (string) ($row['ADDRESS'] ?? '');
            $addrHtml = cdat_sum_address_lines($address);
            $qrSrc = CDAT_BASE . '/qrcode/php/qr_img.php?d=' . urlencode(
                'PHONE NO:' . $number . '  ' . 'ADDRESS: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $address)
            );
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                (string) ($row['MO'] ?? ''),
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['INC_OFFICER'] ?? ''),
                ['html' => '<img height="100" width="100" src="' . cdat_sum_h($qrSrc) . '">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st11) {
        $st11 = null;
    }

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Single Address');
cdat_sum_page_open();
cdat_sum_search_card(
    'Address of Mobile No',
    'Look up the registered address for a mobile number.',
    'address.php',
    cdat_sum_field_phone('', 'ADDRESS'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
