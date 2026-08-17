<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ($_GET['PHONE_NO'] ?? '')));
$hasSearch = $number !== '';
$fieldsHtml = cdat_sum_field_phone($number);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IMEI Request Summary');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'IMEI Request Summary',
            'Call summary for a lost-report mobile number.',
            'imei_request_sum.php',
            $fieldsHtml,
            'BTN_SUM',
            'Search'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'LOSTREPORT_HAWKEYE');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql3 = "SELECT * INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.CDAT_DETAILS1 WITH (NOLOCK) 
WHERE PHONE='$number' and isnumeric(other)=1";

    $sql4 = "SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT',
COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL,
CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL INTO #RESULT FROM #TT 
GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

    $sql5 = "SELECT * INTO #RESULT1 FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
SELECT DISTINCT OTHER  FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR)
AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

    $sql6 = "SELECT DISTINCT A.PHONE,
CASE WHEN OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT WITH (NOLOCK)) THEN OTHER+' - '+NICKNAME  
ELSE OTHER END   AS  OTHER,[IN],[OUT],CALLS,DUR,
FIRSTCALL,LASTCALL,
CASE WHEN OTHER=C.PHONE 
THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+' DOA:'+CONVERT(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN LEN(OTHER)<10 AND [OUT]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL'
WHEN LEN(OTHER)<10 AND [IN]=0 AND DUR>0 THEN 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER'
WHEN OTHER IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE WITH (NOLOCK)) 
THEN ISNULL(D.FULLNAME+', '+D.FULLADDRESS,'')+' '+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #RESULT1 A WITH (NOLOCK)
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON OTHER=B.PHONE 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C WITH (NOLOCK) ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D WITH (NOLOCK) ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E WITH (NOLOCK) ON  CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%' ORDER BY CALLS DESC";

    $sql8 = "SELECT 'SUMMARY OF LOST MOBILE NO: '+'$number' as PHONE1";

    $sql9 = "SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

    $sql10 = "SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.APPLICATION+' ID '+B.LRNO LR_NAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA A WITH (NOLOCK) LEFT JOIN LOSTREPORT_HAWKEYE.DBO.COMPLAINANT_DETAILS B WITH (NOLOCK) ON LEFT(A.IMEINUMBER,14)=LEFT(B.IMEI1,14) WHERE A.PHONE='$number' GROUP BY A.PHONE,B.APPLICATION,B.LRNO";

    $sql11 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LR_NAME ELSE A.NICKNAME END AS LR_NAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA:'+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C WITH (NOLOCK) ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D WITH (NOLOCK) ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA WITH (NOLOCK) ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

    $sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE '*** CDRs NOT AVAILABLE ***' end as PHONE FROM #RESULT";

    sqlsrv_query($conn, $sql3);
    sqlsrv_query($conn, $sql4);
    sqlsrv_query($conn, $sql5);
    $stmt = sqlsrv_query($conn, $sql6);
    $st8 = sqlsrv_query($conn, $sql8);
    sqlsrv_query($conn, $sql9);
    sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);
    $st12 = sqlsrv_query($conn, $sql12);

    $banner = 'SUMMARY OF LOST MOBILE NO: ' . $number;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($banner);

    $headerRows = cdat_sum_fetch_all($st11);
    if (empty($headerRows)) {
        cdat_sum_empty_state('No lost-report header details found.');
    } else {
        cdat_sum_generic_table_open(
            'Lost Mobile Summary',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'LR_NAME', 'ADDRESS'],
            'imei_sum_header_table',
            'imei_request_sum_header.csv',
            count($headerRows)
        );
        foreach ($headerRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['LR_NAME'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $contactRows = cdat_sum_fetch_all($stmt);
    if (empty($contactRows)) {
        cdat_sum_empty_state('No contact analysis records found.');
    } else {
        cdat_sum_generic_table_open(
            'Contact Analysis',
            ['PHONE', 'OTHER', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS'],
            'imei_sum_contacts_table',
            'imei_request_sum.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRSTCALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LASTCALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($st12 && ($miss = sqlsrv_fetch_array($st12, SQLSRV_FETCH_ASSOC))) {
        $msg = trim((string) ($miss['PHONE'] ?? ''));
        if ($msg !== '') {
            cdat_sum_empty_state($msg);
        }
    }
    cdat_sum_results_close();

    if ($stmt) {
        sqlsrv_free_stmt($stmt);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IMEI Request Summary');
cdat_sum_page_open();
cdat_sum_search_card(
    'IMEI Request Summary',
    'Call summary for a lost-report mobile number.',
    'imei_request_sum.php',
    cdat_sum_field_phone(),
    'BTN_SUM',
    'Search'
);
cdat_sum_page_close();
layout_end();
