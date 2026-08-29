<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('Bulk Address');
            cdat_sum_page_open();
        }
        $conn = get_cdat_pdo();
                $phones = cdat_sum_split_phones($number);
        $number2 = cdat_sum_sql_phone_in($phones);

        $sql1= "CREATE TEMP TABLE temp_t1 (phone varchar(20))";

        $sql3= "CREATE TEMP TABLE temp_t2 AS SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL,MAX(STARTTIME) AS LAST_CALL, MAX(A.ASONDATE) AS LAST_UPDATED,NICKNAME
FROM CDATPCSUSPECT A 
LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2')
GROUP BY A.PHONE,NICKNAME";

        $sql4 = "CREATE TEMP TABLE temp_t3 AS SELECT DISTINCT A.PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME FROM temp_t1 A
LEFT JOIN temp_t2 B ON A.PHONE=B.PHONE";

        $sql5= "CREATE TEMP TABLE temp_t4 AS SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE,DOA, EFF_FROM_DATE   FROM CDATADDRESS 
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

        $sql6 = "INSERT INTO temp_t4
SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM ADDRESS_OTHER_STATE
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

        $sql7 = "CREATE TEMP TABLE temp_t5 AS SELECT DISTINCT A.PHONE,COALESCE(TO_CHAR((FIRST_CALL)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'NIL')  AS FIRST_CALL,
COALESCE(TO_CHAR((A.LAST_CALL)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'NIL') AS LAST_CALL,
COALESCE(TO_CHAR((A.LAST_UPDATED)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'NIL') AS LAST_UPDATED,COALESCE(NICKNAME,'NIL') AS NICKNAME,
CASE WHEN A.PHONE IN (SELECT phone FROM temp_t4) THEN FULLNAME || ', ' || B.FULLADDRESS || ', DOA: ' || TO_CHAR(DOA::timestamp, 'DD Mon YYYY') || ', LAST UPDATE: ' || TO_CHAR(EFF_FROM_DATE::timestamp, 'DD Mon YYYY')
ELSE AREADESCRIPTION END AS ADDRESS  FROM temp_t3 A
LEFT JOIN temp_t4 B ON A.PHONE=B.PHONE
LEFT JOIN CDATPHONEAREA E ON  CASE WHEN LENGTH(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LENGTH(A.PHONE)>10 THEN '00' || A.PHONE ELSE 'CODE NOT AVAILABLE' END END
 LIKE phoneprefix || '%' ORDER BY A.PHONE";

        $sql8 = "SELECT PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME,
 CASE WHEN ADDRESS IS NULL AND LENGTH(PHONE)<>10 THEN 'JUNK OR VOIP CALL' 
 WHEN ADDRESS IS NULL AND SUBSTRING(PHONE,1,1) IN ('7','8','9') AND LENGTH(ADDRESS)>=10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS 
 END AS ADDRESS FROM temp_t5";

        $st1 = $conn->query($sql1);
        cdat_sum_insert_phones($conn, 'temp_t1', $phones);
        $st3 = $conn->query($sql3);
        $st4 = $conn->query($sql4);
        $st5 = $conn->query($sql5);
        $st6 = $conn->query($sql6);
        $st7 = $conn->query($sql7);
        $st8 = $conn->query($sql8);

        $rows = cdat_sum_fetch_all($st8);

        cdat_sum_results_open();
        cdat_sum_report_banner('ADDRESSES OF MOBILE NOS');

        if (empty($rows)) {
            cdat_sum_empty_state();
        } else {
            cdat_sum_generic_table_open(
                'Bulk Addresses',
                ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'LAST_UPDATED', 'NICKNAME', 'ADDRESS'],
                'results_table',
                'bulk_address.csv',
                count($rows)
            );
            foreach ($rows as $row) {
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

        cdat_sum_results_close();

        if ($st8) {
            $st8 = null;
        }

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }

    if ($isAjax) {
        cdat_sum_empty_state('Enter mobile numbers and try again.');
        exit;
    }
}

layout_begin('Bulk Addresses');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk Addresses',
    'Enter comma-separated mobile numbers to look up addresses.',
    'bulkaddress.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Addresses of Mobile Numbers',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
