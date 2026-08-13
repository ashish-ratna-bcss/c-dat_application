<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$imei = trim((string) ($_POST['IMEI_NO'] ?? ''));
$hasSearch = $imei !== '';
$fieldsHtml = cdat_sum_field_imei($imei);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IMEI Request Status');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'IMEI Request Status',
            'Check lost-report IMEI request, complainant, and CDR phone details.',
            'imei_request_status.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'LOSTREPORT_HAWKEYE');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $imei;

    $sql3 = "SELECT DISTINCT IMEI1,MOBILE_LOST_DATE,COMPLAINT_RECEIVED_DATE,APPLICATION,LRNo ID,MODEL+' '+BRAND MODEL_BRAND,
COMPLAINANT_NAME,COMPLAINANT_PHONE FROM LOSTREPORT_HAWKEYE.DBO.COMPLAINANT_DETAILS
WHERE IMEI1 LIKE '%'+LEFT('$number',14)+'%'";

    $sql4 = "SELECT DISTINCT IMEI_NO, [FROM] FROM_DATE, [TO] TO_DATE, REQUESTED_DATE
FROM LOSTREPORT_HAWKEYE.DBO.IMEI_REQUESTED_DETAILS WHERE IMEI_NO LIKE '%'+LEFT('$number',14)+'%'";

    $sql5 = "SELECT DISTINCT LEFT(IMEINUMBER,14) IMEINUMBER,PHONE,MIN(STARTTIME) FIRST_CALL,
MAX(STARTTIME) LAST_CALL INTO #TT FROM LOSTREPORT_HAWKEYE.DBO.LOST_REPORT_CDR_DATA
WHERE IMEINUMBER LIKE '%'+LEFT('$number',14)+'%'
GROUP BY LEFT(IMEINUMBER,14),PHONE";

    $sql6 = "SELECT DISTINCT LEFT(IMEINUMBER,14)+'0' IMEINUMBER,A.PHONE,
CONVERT(VARCHAR(20),FIRST_CALL) FIRST_CALL,CONVERT(VARCHAR(20),LAST_CALL) LAST_CALL,LAST_CALL LC,
CASE WHEN A.PHONE=C.PHONE
THEN REPLACE(ISNULL(C.FULLNAME,''),'	','')+', '+REPLACE(ISNULL(C.FULLADDRESS,''),'	','')+' DOA:'+CONVERT(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN A.PHONE LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN A.PHONE LIKE '1800%' AND LEN(A.PHONE)=11 THEN 'TOLL-FREE NUMBER'
WHEN A.PHONE IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE)
THEN REPLACE(ISNULL(D.FULLNAME+', '+D.FULLADDRESS,''),'	','')+' '+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS FROM #TT A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%' ORDER BY LC DESC";

    $sql8 = "SELECT 'IMEI REQUEST STATUS: '+'$number' as PHONE1";
    $sql9 = "SELECT 'IMEI COMPLAINANT DETAILS OF: '+'$number' as PHONE1";
    $sql10 = "SELECT 'CDR REQUESTED DETAILS OF IMEI NO: '+'$number' as PHONE1";
    $sql11 = "SELECT 'IMEI CDR PHONE NUMBER DETAILS: '+'$number' as PHONE1";
    $sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE '*** DATA NOT AVAILABLE ***' end as PHONE FROM #TT";

    $st3 = sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    sqlsrv_query($conn, $sql5);
    $st6 = sqlsrv_query($conn, $sql6);
    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);
    $st12 = sqlsrv_query($conn, $sql12);

    $banner = 'IMEI REQUEST STATUS: ' . $number;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($banner);

    $compBanner = 'IMEI COMPLAINANT DETAILS OF: ' . $number;
    if ($st9 && ($b9 = sqlsrv_fetch_array($st9, SQLSRV_FETCH_ASSOC))) {
        $compBanner = (string) ($b9['PHONE1'] ?? $compBanner);
    }
    $compRows = cdat_sum_fetch_all($st3);
    cdat_sum_report_banner($compBanner);
    if (empty($compRows)) {
        cdat_sum_empty_state('No complainant details found.');
    } else {
        cdat_sum_generic_table_open(
            'Complainant Details',
            ['IMEI_NO', 'MOBILE LOST DATE', 'COMPLAINT DATE', 'APPLICATION', 'LR/HAWKEYE ID', 'MODEL / BRAND', 'COMPLAINANT NAME', 'PHONE'],
            'imei_comp_table',
            'imei_complainant.csv',
            count($compRows)
        );
        foreach ($compRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['IMEI1'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['MOBILE_LOST_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['COMPLAINT_RECEIVED_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['APPLICATION'] ?? ''),
                (string) ($row['ID'] ?? ''),
                (string) ($row['MODEL_BRAND'] ?? ''),
                (string) ($row['COMPLAINANT_NAME'] ?? ''),
                ['text' => (string) ($row['COMPLAINANT_PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $reqBanner = 'CDR REQUESTED DETAILS OF IMEI NO: ' . $number;
    if ($st10 && ($b10 = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
        $reqBanner = (string) ($b10['PHONE1'] ?? $reqBanner);
    }
    $reqRows = cdat_sum_fetch_all($st4);
    cdat_sum_report_banner($reqBanner);
    if (empty($reqRows)) {
        cdat_sum_empty_state('No CDR request details found.');
    } else {
        cdat_sum_generic_table_open(
            'CDR Requested Details',
            ['IMEI_NO', 'FROM_DATE', 'TO_DATE', 'REQUESTED_DATE'],
            'imei_req_table',
            'imei_request.csv',
            count($reqRows)
        );
        foreach ($reqRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['IMEI_NO'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FROM_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['TO_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['REQUESTED_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    $cdrBanner = 'IMEI CDR PHONE NUMBER DETAILS: ' . $number;
    if ($st11 && ($b11 = sqlsrv_fetch_array($st11, SQLSRV_FETCH_ASSOC))) {
        $cdrBanner = (string) ($b11['PHONE1'] ?? $cdrBanner);
    }
    $cdrRows = cdat_sum_fetch_all($st6);
    cdat_sum_report_banner($cdrBanner);
    if (empty($cdrRows)) {
        cdat_sum_empty_state('No IMEI CDR phone details found.');
    } else {
        cdat_sum_generic_table_open(
            'IMEI CDR Phone Details',
            ['IMEINUMBER', 'PHONE', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS'],
            'imei_cdr_table',
            'imei_cdr.csv',
            count($cdrRows)
        );
        foreach ($cdrRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
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

    if ($st3) {
        sqlsrv_free_stmt($st3);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IMEI Request Status');
cdat_sum_page_open();
cdat_sum_search_card(
    'IMEI Request Status',
    'Check lost-report IMEI request, complainant, and CDR phone details.',
    'imei_request_status.php',
    cdat_sum_field_imei(),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
