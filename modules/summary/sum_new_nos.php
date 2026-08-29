<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    $date = trim((string) ($_POST['FROM_DT'] ?? ''));
    if ($number !== '' && $date !== '') {
        if (!$isAjax) {
            layout_begin('New Contacts');
            cdat_sum_page_open();
        }
        $conn = get_cdat_pdo();
                $sql3 = "CREATE TEMP TABLE temp_tt AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE='$number' AND STARTTIME>'$date' AND OTHER NOT IN
(SELECT DISTINCT OTHER FROM CDATPCSUSPECT WHERE PHONE='$number' AND 
STARTTIME < '$date')";

        $sql4 = "CREATE TEMP TABLE temp_result AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS \"OUT\",
COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRSTCALL,
TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LASTCALL  FROM temp_tt 
GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

        $sql5 = "CREATE TEMP TABLE temp_result1 AS SELECT * FROM temp_result WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
SELECT DISTINCT OTHER  FROM temp_result WHERE (CALLS=DUR OR CALLS>DUR)
AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

        $sql6 = "SELECT DISTINCT A.PHONE,
CASE WHEN OTHER IN (SELECT PHONE FROM CDATSUSPECT) THEN OTHER || ' - ' || NICKNAME  
ELSE OTHER END   AS  OTHER,\"IN\",\"OUT\",CALLS,DUR,
FIRSTCALL,LASTCALL,
CASE WHEN OTHER=C.PHONE 
THEN COALESCE(C.FULLNAME,'') || ', ' || COALESCE(C.FULLADDRESS,'') || ' ' || TO_CHAR((C.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS') || ' ' || COALESCE(C.CATEGORY_TYPE,'')
WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LENGTH(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN LENGTH(OTHER)<10 AND \"OUT\"=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL'
WHEN LENGTH(OTHER)<10 AND \"IN\"=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER'
WHEN OTHER IN(SELECT DISTINCT PHONE FROM ADDRESS_OTHER_STATE) 
THEN COALESCE(D.FULLNAME || ', ' || D.FULLADDRESS,'') || ' ' || COALESCE(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM temp_result1 A
LEFT JOIN CDATSUSPECT B ON OTHER=B.PHONE 
LEFT JOIN CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATPHONEAREA E ON  CASE WHEN LENGTH(OTHER)=10 THEN OTHER ELSE CASE WHEN LENGTH(OTHER)>10 THEN '00' || OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
 LIKE PHONEPREFIX || '%' ORDER BY CALLS DESC";

        $sql9 = "CREATE TEMP TABLE temp_t AS SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,'' AS LAST_UPDATED";

        $sql10 = "CREATE TEMP TABLE temp_s AS SELECT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

        $sql11 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN COALESCE(C.FULLNAME,'') || ', ' || COALESCE(C.FULLADDRESS,'') || ', ' || COALESCE(TO_CHAR((C.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'') || ', ' || (CASE WHEN C.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN COALESCE(D.FULLNAME,'') || ', ' || COALESCE(D.FULLADDRESS,'') || ', ' || COALESCE(TO_CHAR((D.DOA)::timestamp, 'YYYY-MM-DD HH24:MI:SS'),'') || ', ' || (CASE WHEN D.CATEGORY_TYPE IS NULL THEN COALESCE(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE COALESCE(AREADESCRIPTION,'') END AS ADDRESS FROM temp_t A
LEFT JOIN CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATPHONEAREA ON CASE WHEN LENGTH(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LENGTH(A.PHONE)>10 THEN '00' || A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
 LIKE PHONEPREFIX || '%'
LEFT JOIN temp_s B ON  A.PHONE=B.PHONE";

        $st3 = $conn->query($sql3);
        $st4 = $conn->query($sql4);
        $st5 = $conn->query($sql5);
        $stmt = $conn->query($sql6);
        $st9 = $conn->query($sql9);
        $st10 = $conn->query($sql10);
        $st11 = $conn->query($sql11);

        $contactRows = cdat_sum_fetch_all($stmt);
        $headerRow = cdat_sum_fetch_one($st11) ?? ['PHONE' => $number, 'FIRST_CALL' => '', 'LAST_CALL' => '', 'NICKNAME' => '', 'ADDRESS' => ''];

        cdat_sum_render_results($headerRow, $contactRows, 'new_contacts.csv', 'New Contacts Summary');

        if ($stmt) {
            $stmt = null;
        }

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

layout_begin('New Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'New Contacts Summary',
    'Find new contacts for a mobile number from a given date.',
    'sum_new_nos.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date('FROM_DT', 'New Contacts From', 'datepickerID')
);
cdat_sum_page_close();
layout_end();
