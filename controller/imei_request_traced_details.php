<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$hasSearch = $fromDt !== '' && $toDt !== '';

$fieldsHtml = cdat_sum_field_date('FROM_DT', 'Request From Date', 'datepickerID', $fromDt)
            . cdat_sum_field_date('TO_DT', 'Request To Date', 'datepickerID1', $toDt);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IMEI Request Traced Details');
        cdat_sum_page_open();
        cdat_sum_search_card(
            "IMEI'S Traced Between Request Dates",
            'Find IMEIs traced between two request dates.',
            'imei_request_traced_details.php',
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
    $f_date = $fromDt;
    $t_date = $toDt;

    $sql3 = "SET DATEFORMAT DMY SELECT DISTINCT LEFT(A.IMEINUMBER,14) IMEINUMBER, A.PHONE,MIN(STARTTIME) FC,
MAX(STARTTIME) LC,MAX(B.MOBILE_LOST_DATE) MOBILE_LOST_DATE
INTO #TR FROM LOST_REPORT_CDR_DATA  A
INNER JOIN COMPLAINANT_DETAILS B ON LEFT(A.IMEINUMBER,14)=LEFT(B.IMEI1,14)
WHERE LEFT(A.IMEINUMBER,14) IN (
SELECT DISTINCT LEFT(IMEI_NO,14) IMEINUMBER FROM IMEI_REQUESTED_DETAILS
WHERE CONVERT(DATE,REQUESTED_DATE) BETWEEN '$f_date' AND '$t_date')
GROUP BY LEFT(IMEINUMBER,14),PHONE";

    $sql4 = "SELECT DISTINCT ROW_NUMBER() OVER(ORDER BY IMEINUMBER DESC) SLNO,A.IMEINUMBER,A.PHONE,
CASE WHEN A.PHONE=C.PHONE
THEN REPLACE(ISNULL(C.FULLNAME,''),'	','')+', '+REPLACE
(ISNULL(C.FULLADDRESS,''),'	','')+' DOA:'+CONVERT
(VARCHAR,C.DOA,20)+' '+ISNULL(C.CATEGORY_TYPE,'')
WHEN A.PHONE LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN A.PHONE LIKE '1800%' AND LEN(A.PHONE)=11 THEN 'TOLL-FREE
NUMBER'
WHEN A.PHONE IN
('121','111','198','123','139','122','199','12345') THEN
'CUSTOMER CARE / ENQUIRY NUMBER'
WHEN A.PHONE IN(SELECT DISTINCT PHONE FROM
CDATDUPL.DBO.ADDRESS_OTHER_STATE)
THEN REPLACE(ISNULL(D.FULLNAME+', '+D.FULLADDRESS,''),'	','')+'
'+ISNULL(D.CATEGORY_TYPE,'')
ELSE AREADESCRIPTION END AS ADDRESS,
CONVERT(VARCHAR(20),A.FC) FIRST_CALL,CONVERT(VARCHAR(20),A.LC)  LAST_CALL,LC,
A.MOBILE_LOST_DATE,B.COMPLAINANT_NAME,
B.APPLICATION,B.LRNO ID,B.BRAND+' '+B.Model MODEL,'TRACED' TRACED FROM #TR A
INNER JOIN COMPLAINANT_DETAILS B ON  LEFT(A.IMEINUMBER,14)=LEFT(B.IMEI1,14)
AND A.MOBILE_LOST_DATE=B.Mobile_Lost_Date
AND CONVERT(DATE,A.FC)>CONVERT(DATE,A.MOBILE_LOST_DATE)
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
ORDER BY SLNO,LC DESC";

    $sql5 = "SELECT 'LR/HAWKEYE IMEI TRACED REPORT FROM: '+'$f_date' +' TO '+'$t_date' as PHONE1";

    sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    $st5 = sqlsrv_query($conn, $sql5);

    $banner = 'LR/HAWKEYE IMEI TRACED REPORT FROM: ' . $f_date . ' TO ' . $t_date;
    if ($st5 && ($b = sqlsrv_fetch_array($st5, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st4);

    if (empty($rows)) {
        cdat_sum_empty_state('No traced IMEI details found for that date range.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'IMEI Traced Details',
            ['SLNO', 'IMEINUMBER', 'PHONE NO USED', 'SUMMARY OF PHONE NO USED', 'MOVEMENTS OF PHONE NO USED', 'PHONE ADDRESS', 'FIRST CALL', 'LAST CALL', 'MOBILE LOST DATE', 'COMPLAINANT NAME', 'APPLICATION', 'LR/HAWKEYE ID', 'MODEL / BRAND', 'TRACED STATUS'],
            'results_table',
            'imei_traced.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $phone = (string) ($row['PHONE'] ?? '');
            cdat_sum_table_row([
                ['text' => (string) ($row['SLNO'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => $phone, 'class' => 'sum-cell-num'],
                ['html' => '<a href="imei_request_sum.php?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => '<a href="imei_request_movements.php?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['MOBILE_LOST_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['COMPLAINANT_NAME'] ?? ''),
                (string) ($row['APPLICATION'] ?? ''),
                (string) ($row['ID'] ?? ''),
                (string) ($row['MODEL'] ?? ''),
                (string) ($row['TRACED'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IMEI Request Traced Details');
cdat_sum_page_open();
cdat_sum_search_card(
    "IMEI'S Traced Between Request Dates",
    'Find IMEIs traced between two request dates.',
    'imei_request_traced_details.php',
    cdat_sum_field_date('FROM_DT', 'Request From Date', 'datepickerID')
        . cdat_sum_field_date('TO_DT', 'Request To Date', 'datepickerID1'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
