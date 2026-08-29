<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('ISD Contacts');
            cdat_sum_page_open();
        }

        set_time_limit(0);
        $conn = get_cdat_pdo();

                $sql1 = "CREATE TEMP TABLE temp_xx AS SELECT DISTINCT * FROM CDATPCSUSPECT WHERE phone = ?";
        $params1 = array($number);
        $st1 = $conn->prepare($sql1);
    $st1->execute($params1);
        

        $sql3 = "CREATE TEMP TABLE temp_jrms_temp AS SELECT * FROM CDAT_DETAILS1 WHERE LENGTH(OTHER) > 10 AND DURATION > '0' AND PHONE = ?";
        $params3 = array($number);
        $st3 = $conn->prepare($sql3);
    $st3->execute($params3);
        

        $sql4 = "CREATE TEMP TABLE temp_tt AS SELECT DISTINCT * FROM temp_jrms_temp";
        $st4 = $conn->query($sql4);

        $sql5 = "CREATE TEMP TABLE temp_result AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
                SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
                SUM(CASE WHEN INCOMING ='0' THEN 1 ELSE 0 END) AS \"OUT\",
                COUNT(PHONE) AS CALLS, SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
                TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRSTCALL,
                TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LASTCALL 
                 FROM temp_tt 
                GROUP BY PHONE, OTHER ORDER BY CALLS DESC";
        $st5 = $conn->query($sql5);

        $sql6 = "CREATE TEMP TABLE temp_WITHADDRESS AS SELECT A.PHONE, 
                CASE WHEN A.OTHER = B.PHONE THEN OTHER || ', - ' || NICKNAME ELSE OTHER END AS OTHER,
                \"IN\",\"OUT\", CALLS, DUR, FIRSTCALL, LASTCALL,
                COALESCE(AREADESCRIPTION, 'CODE N/A') AS ADDRESS 
                 FROM temp_result A 
                LEFT JOIN cdatsuspect B ON a.other = B.phone 
                LEFT JOIN cdatphonearea C ON '00' || other LIKE phoneprefix || '%' 
                WHERE A.OTHER NOT LIKE '1800%'
                GROUP BY a.PHONE, B.PHONE, other, \"IN\",\"OUT\", calls, dur, FIRSTCALL, LASTCALL, nickname, AREADESCRIPTION";
        $st6 = $conn->query($sql6);

        $sql7 = "SELECT * FROM temp_WITHADDRESS WHERE ADDRESS != ' JUNK-COULD BE bulk SMS or VOIP calls' ORDER BY calls DESC";
        $st7 = $conn->query($sql7);

        $sql8 = "SELECT 'ISD CONTACTS OF MOBILE NO: ' || ? AS PHONE1";
        $params8 = array($number);
        $st8 = $conn->prepare($sql8);
    $st8->execute($params8);
        

        $sql9 = "CREATE TEMP TABLE temp_t AS SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED";
        $params9 = array($number);
        $st9 = $conn->prepare($sql9);
    $st9->execute($params9);
        

        $sql10 = "CREATE TEMP TABLE temp_s AS SELECT A.PHONE, TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL, 
                  TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL, B.NICKNAME, 
                  TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED 
                   FROM CDATPCSUSPECT A 
                  LEFT JOIN CDATSUSPECT B ON A.PHONE = B.PHONE 
                  WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
        $params10 = array($number);
        $st10 = $conn->prepare($sql10);
    $st10->execute($params10);
        

        $sql11 = "SELECT DISTINCT A.PHONE,
                  CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
                  CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
                  CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
                  CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
                  CASE WHEN A.PHONE = C.PHONE THEN COALESCE(C.FULLNAME, '') || ', ' || COALESCE(C.FULLADDRESS, '') || ', ' || COALESCE(TO_CHAR((C.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), '') || ', ' || (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE C.CATEGORY_TYPE END)
                  WHEN A.PHONE = D.PHONE THEN COALESCE(D.FULLNAME, '') || ', ' || COALESCE(D.FULLADDRESS, '') || ', ' || COALESCE(TO_CHAR((D.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'), '') || ', ' || (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION, '') ELSE D.CATEGORY_TYPE END) 
                  ELSE COALESCE(AREADESCRIPTION, '') END AS ADDRESS 
                  FROM temp_t A
                  LEFT JOIN CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
                  LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
                  LEFT JOIN CDATPHONEAREA ON CASE WHEN LENGTH(A.PHONE) = 10 THEN A.PHONE 
                  ELSE CASE WHEN LENGTH(A.PHONE) > 10 THEN '00' || A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
                  LIKE PHONEPREFIX || '%'
                  LEFT JOIN temp_s B ON A.PHONE = B.PHONE";
        $st11 = $conn->query($sql11);

        $contactRows = cdat_sum_fetch_all($st7);
        $headerRow = cdat_sum_fetch_one($st11) ?? [
            'PHONE' => $number,
            'FIRST_CALL' => '',
            'LAST_CALL' => '',
            'NICKNAME' => '',
            'ADDRESS' => '',
        ];

        cdat_sum_render_results($headerRow, $contactRows, 'isd_contacts.csv', 'ISD Contacts Summary');

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

    if ($isAjax) {
        cdat_sum_empty_state('Fill in the required fields and try again.');
        exit;
    }
}

layout_begin('ISD Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'ISD Contacts Summary',
    'Search ISD contact summary for a mobile number.',
    'sum_isd_cnts.php',
    cdat_sum_field_phone(trim((string) ($_POST['PHONE_NO'] ?? '')))
);
cdat_sum_page_close();
layout_end();
