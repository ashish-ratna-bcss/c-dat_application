<?php
require_once __DIR__ . '/../common/bootstrap.php';
// One page for both halves of this screen: the form, and the results.
// Was view/bulk_cdat_contacts.htm (form) + controller/bulk_cdat_contacts.php (handler).
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

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );
    if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
    }
    $number = (string) ($_POST['PHONE_NO'] ?? '');
    $phones = cdat_sum_split_phones($number);
    $number2 = cdat_sum_sql_phone_in($phones);

    $sqlB1= "CREATE TABLE #T1 (PHONE NVARCHAR (20) NULL)";

    $sql1="SELECT  DISTINCT PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,'' AS CATEGORY,''LAST_UPDATED,''INC_OFFICER INTO #T FROM #T1";

    $sql10="SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,INC_OFFICER INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE 
WHERE A.PHONE IN ('$number2') GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";

    $sqlA="select distinct * INTO #CDATADDRESS from cdatdupl..cdataddress where phone in ('$number2')";

    $sqlB="select distinct * INTO #ADDRESS_OTHER_STATE from cdatdupl..ADDRESS_OTHER_STATE where phone in ('$number2')";




    $sql3="SELECT DISTINCT PHONE, IMEINUMBER, CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL, CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,
CONVERT(VARCHAR,MAX(ASONDATE),20) AS LAST_UPDATED FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number2') GROUP BY PHONE,IMEINUMBER ORDER BY LAST_UPDATED";

    $sql4="SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE IN ('$number2') and other!=''";

    $sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL INTO #TT from #XX a
left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
 group by a.phone, A.other, nickname,ROLE order by  calls desc, other";

    $sql6 = "SELECT A.PHONE,A.OTHER,A.NICKNAME,MO,CATEGORY,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN FULLNAME IS NULL THEN '' ELSE FULLNAME END+' '+
CASE WHEN b.FULLADDRESS IS NULL THEN  
CASE WHEN (CALLS=DUR AND LEN(OTHER)<>10) 
OR (LEFT(OTHER,1)NOT IN ('9','8') AND LEN(OTHER)>14) 
OR LEN(OTHER)<10  OR SUBSTRING(OTHER,5,10) LIKE '%0000%' or isnumeric(other)=0
--or (len(other)>11 and '00'+other not in (select phoneprefix+'%' from cdatphonearea))
THEN 'JUNK-COULD BE bulk SMS or VOIP calls' else
case when min(areadescription) is null then 'code n/a' else min(areadescription) end
END  ELSE b.FULLADDRESS+','+ISNULL(CATEGORY_type,'') 
END AS ADDRESS,INC_OFFICER INTO #WITHADDRESS FROM #TT  A 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS B ON OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.OTHER=C.PHONE
left join cdatdupl.dbo.cdatphonearea d on case when len(other)=10 then other else case when len(other)>10 then '00'+other else null end end
like phoneprefix+'%'
group by a.PHONE, other,[IN],[OUT],calls,dur, FIRST_CALL,
LAST_CALL,FULLNAME,b.FULLADDRESS, A.nickname,CATEGORY_type,MO,CATEGORY, INC_OFFICER";

    $sql7 = "SELECT A.PHONE,OTHER,NICKNAME,MO,CATEGORY AS CAT,[IN],[OUT],CALLS,DUR,FIRST_CALL,LAST_CALL,
CASE WHEN A.OTHER=B.PHONE THEN ISNULL(B.FULLNAME,'')+','+ISNULL(B.FULLADDRESS,'')+','+
ISNULL(CATEGORY_TYPE,'')+','+CONVERT(CHAR(10),CAST(DOA AS DATETIME),105)  ELSE A.ADDRESS END AS ADDRESS, 
INC_OFFICER INTO #WITHADDRESS1 FROM #WITHADDRESS A
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE AND B.EFF_TO_DATE IS NULL";

    $sql71="Select A.*,CASE WHEN B.MOBILE=A.OTHER THEN B.IMAGE ELSE (SELECT IMAGE FROM SUSPECT_IMAGE_TABLE WHERE IRKEY='113769') END AS IMAGE FROM #WITHADDRESS1 A LEFT JOIN 
SUSPECT_IMAGE_TABLE B ON B.MOBILE=A.OTHER ORDER BY PHONE,CALLS DESC,OTHER";

    $sql8 ="SELECT 'CDAT CONTACTS OF MOBILE NO: '+'$number' as PHONE";

    $sql9="SELECT case when count(PHONE)>=1 THEN '' ELSE '*** NO CDAT CONTACTS TO $number ***' end as CNTS FROM #WITHADDRESS";

    $stA = sqlsrv_query($conn, $sqlA);
    $stB = sqlsrv_query($conn, $sqlB);
    $stB1 = sqlsrv_query($conn, $sqlB1);
    cdat_sum_insert_phones($conn, '#T1', $phones);
    $st3 = sqlsrv_query( $conn, $sql3 );
    $st4 = sqlsrv_query( $conn, $sql4 );
    $st5 = sqlsrv_query( $conn, $sql5 );
    $st6 = sqlsrv_query( $conn, $sql6 );
    $st7 = sqlsrv_query( $conn, $sql7 );
    $st71= sqlsrv_query( $conn, $sql71);
    $st8 = sqlsrv_query( $conn, $sql8 );
    $st9 = sqlsrv_query( $conn, $sql9 );

    $bannerTitle = 'CDAT CONTACTS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st71);

    $noContactsMsg = '';
    if ($st9 && ($cntRow = sqlsrv_fetch_array($st9, SQLSRV_FETCH_ASSOC))) {
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
