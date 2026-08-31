<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the results.
// Was view/bulk_cdat_contacts.htm (form) || controller/bulk_cdat_contacts.php (handler).
// GET shows the form; a submit renders the form and the results below it.
// !empty($_GET) covers links that pass parameters in the query string.
$__submitted = ($_SERVER['REQUEST_METHOD'] === 'POST') || !empty($_GET);

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phoneValue = (string) ($_POST['PHONE_NO'] ?? '');

$fieldsHtml = cdat_sum_field_textarea(
    'PHONE_NO',
    'Bulk CDAT Contacts',
    $phoneValue,
    'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
);

if ($__submitted && $phoneValue !== '') {
    if (!$isAjax) {
        layout_begin('Bulk Cdat Contacts');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Bulk CDAT Contacts',
            'Enter comma-separated mobile numbers to look up CDAT contacts.',
            'bulk_cdat_contacts.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Search'
        );
    }
    $conn = get_cdat_pdo();
    cdat_sum_begin_heavy_search();
        $number = (string) ($_POST['PHONE_NO'] ?? '');
    $phones = cdat_sum_split_phones($number);

    $sqlB1= "CREATE TEMP TABLE temp_t1 (phone varchar(20))";

    $sql1="CREATE TEMP TABLE temp_t AS SELECT DISTINCT PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,'' AS CATEGORY,''LAST_UPDATED,''INC_OFFICER FROM temp_t1";

    $sql10="CREATE TEMP TABLE temp_s AS SELECT DISTINCT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME || '_' || B.ROLE NICKNAME,B.MO,CATEGORY,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED,INC_OFFICER FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE 
WHERE A.PHONE IN (SELECT phone FROM temp_t1) GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

    $sqlA="CREATE TEMP TABLE CDATADDRESS AS SELECT distinct * from cdataddress where phone in (SELECT phone FROM temp_t1)";

    $sqlB="CREATE TEMP TABLE ADDRESS_OTHER_STATE AS SELECT distinct * from ADDRESS_OTHER_STATE where phone in (SELECT phone FROM temp_t1)";




    $sql3="SELECT DISTINCT PHONE, IMEINUMBER, TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL, TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,
TO_CHAR((MAX(ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED FROM CDATPCSUSPECT WHERE PHONE IN (SELECT phone FROM temp_t1) GROUP BY PHONE,IMEINUMBER ORDER BY LAST_UPDATED";

    $sql4="CREATE TEMP TABLE temp_xx AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE IN (SELECT phone FROM temp_t1) and other!=''";

    $sql5 = "CREATE TEMP TABLE temp_tt AS select distinct a.PHONE,OTHER, NICKNAME || '_' || ROLE NICKNAME,
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS \"OUT\", count(*) as CALLS,sum(cast(duration as numeric)) as dur,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') as FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') as LAST_CALL  from temp_xx a
left join cdatsuspect b on a.other=b.phone
WHERE OTHER IN (SELECT PHONE FROM CDATSUSPECT)
 group by a.phone, A.other, nickname,ROLE order by  calls desc, other";

    $sql6 = "CREATE TEMP TABLE temp_WITHADDRESS AS SELECT A.PHONE,A.OTHER,A.NICKNAME,MO,CATEGORY,\"IN\",\"OUT\",CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN FULLNAME IS NULL THEN '' ELSE FULLNAME END || ' ' || CASE WHEN b.FULLADDRESS IS NULL THEN  
CASE WHEN (CALLS=DUR AND LENGTH(OTHER)<>10) 
OR (LEFT(OTHER,1)NOT IN ('9','8') AND LENGTH(OTHER)>14) 
OR LENGTH(OTHER)<10  OR SUBSTRING(OTHER,5,10) LIKE '%0000%' or NOT (other ~ '^[0-9]+$')
--or (LENGTH(other)>11 and '00' || other not in (CREATE TEMP TABLE temp_WITHADDRESS AS select phoneprefix || '%' from cdatphonearea))
THEN 'JUNK-COULD BE bulk SMS or VOIP calls' else
case when min(areadescription) is null then 'code n/a' else min(areadescription) end
END  ELSE b.FULLADDRESS || ',' || COALESCE(CATEGORY_type,'') 
END AS ADDRESS,INC_OFFICER  FROM temp_tt  A 
LEFT JOIN CDATADDRESS B ON OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL
LEFT JOIN CDATSUSPECT C ON A.OTHER=C.PHONE
left join cdatphonearea d on case when LENGTH(other)=10 then other else case when LENGTH(other)>10 then '00' || other else null end end
like phoneprefix || '%'
group by a.PHONE, other,\"IN\",\"OUT\",calls,dur, FIRST_CALL,
LAST_CALL,FULLNAME,b.FULLADDRESS, A.nickname,CATEGORY_type,MO,CATEGORY, INC_OFFICER";

    $sql7 = "CREATE TEMP TABLE temp_WITHADDRESS1 AS SELECT A.PHONE,OTHER,NICKNAME,MO,CATEGORY AS CAT,\"IN\",\"OUT\",CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN A.OTHER=B.PHONE THEN COALESCE(B.FULLNAME,'') || ',' || COALESCE(B.FULLADDRESS,'') || ',' || COALESCE(CATEGORY_TYPE,'') || ',' || TO_CHAR(DOA::timestamp, 'DD-MM-YYYY')  ELSE A.ADDRESS END AS ADDRESS, 
INC_OFFICER  FROM temp_WITHADDRESS A
LEFT JOIN ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL";

    $sql71 = "SELECT A.*, B.IMAGE FROM temp_WITHADDRESS1 A
LEFT JOIN suspect_image_table B ON B.MOBILE = A.OTHER
ORDER BY PHONE, CALLS DESC, OTHER";

    $sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: ' || ? as PHONE";
    $st8 = $conn->prepare($sql8);
    $st8->execute([$number]);

    $sql9 = "SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO ' || ? || ' ***' end as CNTS FROM temp_WITHADDRESS";
    $st9 = $conn->prepare($sql9);
    $st9->execute([$number]);

    $stA = $conn->query($sqlA);
    $stB = $conn->query($sqlB);
    $stB1 = $conn->query($sqlB1);
        cdat_sum_insert_phones($conn, 'temp_t1', $phones);
    $st3 = $conn->query($sql3);
    $st4 = $conn->query($sql4);
    $st5 = $conn->query($sql5);
    $st6 = $conn->query($sql6);
    $st7 = $conn->query($sql7);
    $st71= $conn->query($sql71);

    $bannerTitle = 'CDAT CONTACTS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = $st8->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $defaultImage = cdat_default_suspect_image_local($conn);
    $rows = cdat_sum_fetch_all($st71);
    foreach ($rows as &$row) {
        $img = cdat_pg_binary_to_string($row['IMAGE'] ?? null);
        $row['IMAGE'] = $img !== null && $img !== '' ? $img : $defaultImage;
    }
    unset($row);

    $noContactsMsg = '';
    if ($st9 && ($cntRow = $st9->fetch(PDO::FETCH_ASSOC))) {
        $noContactsMsg = (string) ($cntRow['CNTS'] ?? '');
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($bannerTitle, 'ADDRESSES OF MOBILE NOS');

    if ($noContactsMsg !== '') {
        cdat_sum_empty_state($noContactsMsg);
    } elseif (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_generic_table_open(
            'Bulk CDAT Contacts',
            ['PHONE', 'OTHER', 'IMAGE', 'NICK NAME', 'MO', 'CAT', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS', 'IO NAME'],
            'contact_results_table',
            'bulk_cdat_contacts.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $other = (string) ($row['OTHER'] ?? '');
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                [
                    'html' => '<a href="' . htmlspecialchars(cdat_page('bulk_cdat_contacts1.php')) . '?PHONE_NO=' . urlencode($other) . '">' . cdat_sum_h($other) . '</a>',
                    'class' => 'sum-cell-other',
                ],
                ['html' => '<img height="100" width="100" src="' . cdat_base64_image_src($row['IMAGE']) . '">', 'class' => 'sum-cell-img'],
                (string) ($row['NICKNAME'] ?? ''),
                (string) ($row['MO'] ?? ''),
                (string) ($row['CAT'] ?? ''),
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['INC_OFFICER'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    cdat_sum_results_close();

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'POST') {
    cdat_sum_empty_state('Enter mobile numbers and try again.');
    exit;
}

layout_begin('Bulk Cdat Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk CDAT Contacts',
    'Enter comma-separated mobile numbers to look up CDAT contacts.',
    'bulk_cdat_contacts.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Bulk CDAT Contacts',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
