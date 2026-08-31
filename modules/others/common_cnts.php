<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phoneNo = (string) ($_POST['PHONE_NO'] ?? '');
$stringOp = (string) ($_POST['STRING'] ?? '>');
$noVal = (string) ($_POST['NO'] ?? '1');
$hasSearch = trim($phoneNo) !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter at least one mobile number and try again.');

$stringHtml = cdat_sum_searchable_select(
    'STRING',
    'Compare',
    ['>' => '>', '=' => '='],
    $stringOp === '=' ? '=' : '>',
    'Select compare',
    false
);

$noOptions = ['' => 'Select number'];
for ($i = 1; $i <= 20; $i++) {
    $noOptions[(string) $i] = (string) $i;
}
$noHtml = cdat_sum_searchable_select(
    'NO',
    'More Than No',
    $noOptions,
    $noVal !== '' ? $noVal : '1',
    'Select number',
    false
);

$fieldsHtml = cdat_sum_field_textarea(
    'PHONE_NO',
    'Mobile Numbers',
    $phoneNo,
    'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
) . $stringHtml . $noHtml;

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Common Contacts');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Common Contacts of Mobile Numbers',
            'Find shared contacts across multiple mobile numbers.',
            'common_cnts.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Search'
        );
    }
    $conn = get_cdat_pdo();

        $number = (string) ($_POST['PHONE_NO'] ?? '');
    $phones = cdat_sum_split_phones($number);

    // Address queries
    $address1 = "CREATE TEMP TABLE temp_a1 (phone varchar(20))";
    $address3 = "CREATE TEMP TABLE temp_a2 AS SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL, MAX(STARTTIME) AS LAST_CALL, 
                MAX(A.ASONDATE) AS LAST_UPDATED, NICKNAME || '_' || ROLE || ' MO:' || MO AS NICKNAME  FROM CDATPCSUSPECT A 
                LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE 
                WHERE A.PHONE IN (SELECT phone FROM temp_a1)
                GROUP BY A.PHONE, NICKNAME, MO, ROLE";
    $address4 = "CREATE TEMP TABLE temp_a3 AS SELECT DISTINCT A.PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME FROM temp_a1 A
                LEFT JOIN temp_a2 B ON A.PHONE = B.PHONE";
    $address5 = "CREATE TEMP TABLE temp_a4 AS SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATADDRESS 
                WHERE PHONE IN (SELECT phone FROM temp_a1) AND EFF_TO_DATE IS NULL";
    $address6 = "INSERT INTO temp_a4
                SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM ADDRESS_OTHER_STATE
                WHERE PHONE IN (SELECT phone FROM temp_a1) AND EFF_TO_DATE IS NULL";
    $address7 = "CREATE TEMP TABLE temp_a5 AS SELECT DISTINCT A.PHONE, COALESCE(TO_CHAR((FIRST_CALL)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), 'NIL') AS FIRST_CALL,
                COALESCE(TO_CHAR((A.LAST_CALL)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), 'NIL') AS LAST_CALL,
                COALESCE(TO_CHAR((A.LAST_UPDATED)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), 'NIL') AS LAST_UPDATED, COALESCE(NICKNAME, 'NIL') AS NICKNAME,
                CASE WHEN A.PHONE IN (SELECT phone FROM temp_a4) THEN FULLNAME || ', ' || B.FULLADDRESS || ', DOA: ' || TO_CHAR(DOA::timestamp, 'DD Mon YYYY') || ', LAST UPDATE: ' || TO_CHAR(EFF_FROM_DATE::timestamp, 'DD Mon YYYY')
                ELSE AREADESCRIPTION END AS ADDRESS  FROM temp_a3 A
                LEFT JOIN temp_a4 B ON A.PHONE = B.PHONE
                LEFT JOIN CDATPHONEAREA E ON CASE WHEN LENGTH(A.PHONE) = 10 
                THEN A.PHONE ELSE CASE WHEN LENGTH(A.PHONE) > 10 THEN '00' || A.PHONE ELSE 'CODE NOT AVAILABLE' END END
                LIKE phoneprefix || '%' ORDER BY A.PHONE";
    $address8 = "SELECT PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME,
                CASE WHEN ADDRESS IS NULL AND LENGTH(PHONE) <> 10 THEN 'JUNK OR VOIP CALL' 
                WHEN ADDRESS IS NULL AND SUBSTRING(PHONE, 1, 1) IN ('6','7','8','9') AND LENGTH(ADDRESS) >= 10 THEN 'CODE NOT AVAILABLE' 
                ELSE ADDRESS END AS ADDRESS FROM temp_a5";

    // Common contacts queries
    $sql1 = "CREATE TEMP TABLE temp_t AS SELECT * FROM CDATPCSUSPECT WHERE PHONE IN (SELECT phone FROM temp_a1)";
    $sql2 = "CREATE TEMP TABLE temp_common_numbertable1 AS SELECT PHONE, OTHER, COUNT(OTHER) AS COUNT1 FROM temp_t
            GROUP BY OTHER, PHONE HAVING (COUNT(OTHER)) > 1 ORDER BY OTHER, PHONE";
    $sql3 = "CREATE TEMP TABLE temp_common_numbertable2 AS SELECT OTHER, PHONE, COUNT(OTHER) COUNT1 FROM temp_common_numbertable1
            GROUP BY OTHER, PHONE ORDER BY OTHER";
    $sql4 = "CREATE TEMP TABLE temp_common_numbertable3 AS SELECT DISTINCT OTHER, 
            (SELECT string_agg(phone || ', ', '' ORDER BY phone) FROM temp_common_numbertable2 us WHERE us.other = ss.other) AS phones,
            (SELECT SUM(count1) FROM temp_common_numbertable2 xx WHERE xx.other = ss.other) AS totalnumberofphones FROM temp_common_numbertable2 ss
            GROUP BY ss.other ORDER BY 1";
    $sql5 = "DELETE FROM temp_common_numbertable3 WHERE totalnumberofphones = 1";
    $sql6 = "DROP TABLE temp_common_numbertable1";
    $sql7 = "DROP TABLE temp_common_numbertable2";
    $sql8 = "UPDATE temp_common_numbertable3 SET phones = TRIM(TRAILING ', ' FROM phones)";
    $sql9 = "SELECT DISTINCT A.OTHER, A.PHONES, A.TOTALNUMBEROFPHONES PHONE_COUNT, E.NICKNAME || '_' || E.ROLE AS others_nickname, E.MO AS others_mo,
            CASE WHEN A.OTHER = C.PHONE THEN COALESCE(C.FULLNAME, '') || ', ' || COALESCE(C.FULLADDRESS, '') || ', DOA: ' || COALESCE(TO_CHAR((C.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), '') || ', LAST_UPDATED: ' ||
            COALESCE(TO_CHAR((C.EFF_FROM_DATE)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), '') || ', ' ||
            (CASE WHEN C.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE C.OPERATOR END)
            WHEN A.OTHER = D.PHONE THEN COALESCE(D.FULLNAME, '') || ', ' || COALESCE(D.FULLADDRESS, '') || ', ' || COALESCE(TO_CHAR((D.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), '') || ', ' ||
            (CASE WHEN D.OPERATOR IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE D.OPERATOR END) 
            ELSE COALESCE(AREADESCRIPTION, '') END AS OTHER_ADDRESS 
            FROM temp_common_numbertable3 A
            LEFT JOIN CDATADDRESS C ON A.OTHER = C.PHONE AND C.EFF_TO_DATE IS NULL AND LENGTH(A.OTHER) >= 10
            LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATPHONEAREA ON CASE WHEN LENGTH(A.OTHER) = 10 THEN A.OTHER 
            ELSE CASE WHEN LENGTH(A.OTHER) > 10 THEN '00' || A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
            LIKE phoneprefix || '%'
            LEFT JOIN CDATSUSPECT E ON A.OTHER = E.PHONE
            WHERE LENGTH(A.OTHER) = 10 AND A.OTHER ~ '^[0-9]+$' AND (A.OTHER LIKE '6%' OR A.OTHER LIKE '7%' OR A.OTHER LIKE '8%' OR A.OTHER LIKE '9%')
            ORDER BY PHONE_COUNT DESC, OTHER DESC";

    // Execute address queries
    $at1 = $conn->query($address1);
    cdat_sum_insert_phones($conn, 'temp_a1', $phones);
    $at3 = $conn->query($address3);
    $at4 = $conn->query($address4);
    $at5 = $conn->query($address5);
    $at6 = $conn->query($address6);
    $at7 = $conn->query($address7);
    $at8 = $conn->query($address8);

    // Execute common contacts queries
    $st1 = $conn->query($sql1);
    $st2 = $conn->query($sql2);
    $st3 = $conn->query($sql3);
    $st4 = $conn->query($sql4);
    $conn->query($sql5);
    if (count($phones) >= 2) {
        $op = $stringOp === '=' ? '=' : '>';
        $n = max(1, (int) $noVal);
        $conn->query("DELETE FROM temp_common_numbertable3 WHERE NOT (totalnumberofphones $op $n)");
    }
    $st6 = $conn->query($sql6);
    $st7 = $conn->query($sql7);
    $st8 = $conn->query($sql8);
    $st9 = $conn->query($sql9);

    $addressRows = cdat_sum_fetch_all($at8);
    $contactRows = cdat_sum_fetch_all($st9);

    cdat_sum_results_open();
    cdat_sum_report_banner('ADDRESSES OF MOBILE NOS');
    if (empty($addressRows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_generic_table_open(
            'Addresses',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'LAST_UPDATED', 'NICKNAME', 'ADDRESS'],
            'addresses_table',
            'common_addresses.csv',
            count($addressRows)
        );
        foreach ($addressRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    cdat_sum_report_banner('COMMON CONTACTS');
    if (empty($contactRows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_generic_table_open(
            'Common Contacts',
            ['COMMON CONTACT', 'PHONES', 'PHONE_COUNT', 'OTHERS_NICKNAME', 'OTHERS_MO', 'OTHER_ADDRESS'],
            'contacts_table',
            'common_contacts.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['OTHER_ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['PHONES'] ?? ''),
                ['text' => (string) ($row['PHONE_COUNT'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['OTHERS_NICKNAME'] ?? ''),
                (string) ($row['OTHERS_MO'] ?? ''),
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();

    if ($at8) {
        $at8 = null;
    }
    if ($st9) {
        $st9 = null;
    }
    $conn = null;

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Common Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'Common Contacts of Mobile Numbers',
    'Find shared contacts across multiple mobile numbers.',
    'common_cnts.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Mobile Numbers',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ) . $stringHtml . $noHtml,
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
