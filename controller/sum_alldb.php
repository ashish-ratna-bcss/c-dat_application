<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_phone($phone);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Summary Alldb');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Summary of Mobile Number',
            'Search IMEI, IMSI, contacts, and day/night locations for a mobile number.',
            'sum_alldb.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once __DIR__ . '/cdr_enrichment_sql.php';
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $_POST['PHONE_NO'];

    $sql3 = "SELECT * INTO #TT FROM CDAT_DETAILS1 WHERE PHONE='$number'";

    $sqlimei = "SELECT DISTINCT IMEINUMBER,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,COUNT(*) TOTAL_CALLS  FROM #TT
GROUP BY IMEINUMBER ORDER BY TOTAL_CALLS DESC";


    $sqlimsi = "SELECT DISTINCT IMSINUMBER,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,COUNT(*) TOTAL_CALLS FROM #TT
GROUP BY IMSINUMBER ORDER BY TOTAL_CALLS DESC";


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

    $sql6 = "SELECT DISTINCT A.PHONE PHONE,CASE WHEN OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT) THEN OTHER+' - '+J.NICKNAME  
ELSE OTHER END   AS  OTHER,[IN],
[OUT],CALLS, DUR,
FIRSTCALL,LASTCALL,
ISNULL((CASE WHEN OTHER=C.PHONE
THEN ISNULL(C.FULLNAME,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
when isnumeric(other)=0 then 'customer care'
WHEN OTHER=D.PHONE
THEN ISNULL(D.FULLNAME,'')
ELSE AREADESCRIPTION END)+','+ (CASE WHEN OTHER=C.PHONE
THEN ISNULL(C.FULLADDRESS,'') WHEN OTHER LIKE '140%' THEN 'TELE-MARKETING NUMBER'
WHEN OTHER LIKE '1800%' AND LEN(OTHER)=11 THEN 'TOLL-FREE NUMBER'
WHEN OTHER IN('121','111','198','123','139','122','199','12345') THEN 'CUSTOMER CARE / ENQUIRY NUMBER'
when isnumeric(other)=0 then 'customer care'
WHEN OTHER =D.PHONE
THEN ISNULL(D.FULLADDRESS,'')
ELSE AREADESCRIPTION END )+' DOA:'+
CASE WHEN OTHER=C.PHONE THEN CONVERT(VARCHAR,C.DOA)
WHEN OTHER=D.PHONE THEN CONVERT(VARCHAR,D.DOA)
END,'') SDR_DATA ,
ISNULL((CASE WHEN A.OTHER=F.PHONE AND F.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(F.FULLNAME,'')+' '+ISNULL(F.FULLADDRESS,'') END),'')
RTA_DATA,
ISNULL((CASE WHEN A.OTHER=G.PHONE AND G.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(G.FULLNAME,'')+' '+ISNULL(G.FULLADDRESS,'') END),'')
CIVIL_SUPPLY_DATA,
ISNULL((CASE WHEN A.OTHER=H.PHONE AND H.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(H.FULLNAME,'')+' '+ISNULL(H.FULLADDRESS,'') END),'')
LICENCE_DATA,
ISNULL((CASE WHEN A.OTHER=I.PHONE AND I.PHONE NOT IN ('121','111','198','123','139','122','199','12345') THEN ISNULL(I.NAME,'')+' '+ISNULL(I.ADDRESS,'') END),'') 
GAS_DATA_ADDRESS FROM #RESULT1 A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(OTHER)=10 THEN OTHER ELSE CASE WHEN LEN(OTHER)>10 THEN '00'+OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI 
CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN CDATDUPL..CDAT_RTA F ON A.OTHER=F.PHONE
LEFT JOIN CDATDUPL..CDAT_CIVILSUPPLY G ON A.OTHER=G.PHONE
LEFT JOIN CDATDUPL..CDAT_LICENCE H ON A.OTHER=H.PHONE
LEFT JOIN CDATDUPL..CDAT_GAS_DETAILS I ON A.OTHER=I.PHONE
LEFT JOIN CDATDUPL..CDATSUSPECT J ON A.OTHER=J.PHONE
ORDER BY DUR DESC";

    $sql8 = "SELECT 'SUMMARY OF MOBILE NO: '+'$number' as PHONE1";

    $sql9 = "SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''LAST_UPDATED INTO #T";

    $sql10 = "SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

    $sql11 = "SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA:'+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+
(CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.CATEGORY_TYPE END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+', '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+
(CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.CATEGORY_TYPE END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";

    $sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE '*** CDRs NOT AVAILABLE ***' end as PHONE FROM #RESULT";


    $sqlD1 = "SELECT * INTO #DTEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00') 
AND PHONE='$number'";

    $sqlD2 = cdr_sql_enrich_location_temp('#DTEMP', '#DTT1');

    $sqlD4 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #DT FROM #DTT1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";


    $sqlD5 = "SELECT TOP 10 * FROM #DT";

    $sqlD6 = "SELECT 'DAY LOCATION OF MOBILE NO: '+'$number' as PHONE1";

    $sqlN7 = "SELECT 'NIGHT LOCATION OF MOBILE NO: '+'$number' as PHONE1";

    $sqlN8 = "SELECT * INTO #DT1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE 
(CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00') 
AND PHONE='$number'";

    $sqlN9 = cdr_sql_enrich_location_temp('#DT1', '#DT3');

    $sqlN11 = "SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM INTO #DT4 FROM #DT3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

    $sqlN12 = "SELECT TOP 10 * FROM #DT4";

    $stimei = sqlsrv_query($conn, $sqlimei);
    $stimsi = sqlsrv_query($conn, $sqlimsi);


    $st3 = sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);


    $stimei = sqlsrv_query($conn, $sqlimei);
    $stimsi = sqlsrv_query($conn, $sqlimsi);


    $st5 = sqlsrv_query($conn, $sql5);
    $stmt = sqlsrv_query($conn, $sql6);
    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);
    $st12 = sqlsrv_query($conn, $sql12);

    $stD1 = sqlsrv_query($conn, $sqlD1);
    $stD2 = sqlsrv_query($conn, $sqlD2);
    $stD4 = sqlsrv_query($conn, $sqlD4);
    $stD5 = sqlsrv_query($conn, $sqlD5);
    $stD6 = sqlsrv_query($conn, $sqlD6);
    $stD7 = sqlsrv_query($conn, $sqlN7);
    $stD8 = sqlsrv_query($conn, $sqlN8);
    $stD9 = sqlsrv_query($conn, $sqlN9);
    $stD11 = sqlsrv_query($conn, $sqlN11);
    $stD12 = sqlsrv_query($conn, $sqlN12);

    $banner = 'SUMMARY OF MOBILE NO: ' . $number;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $imeiRows = cdat_sum_fetch_all($stimei);
    $imsiRows = cdat_sum_fetch_all($stimsi);
    $headerRows = cdat_sum_fetch_all($st11);
    $contactRows = cdat_sum_fetch_all($stmt);
    $cdrMsg = '';
    if ($st12 && ($m = sqlsrv_fetch_array($st12, SQLSRV_FETCH_ASSOC))) {
        $cdrMsg = (string) ($m['PHONE'] ?? '');
    }
    $dayBanner = 'DAY LOCATION OF MOBILE NO: ' . $number;
    if ($stD6 && ($b = sqlsrv_fetch_array($stD6, SQLSRV_FETCH_ASSOC))) {
        $dayBanner = (string) ($b['PHONE1'] ?? $dayBanner);
    }
    $dayRows = cdat_sum_fetch_all($stD5);
    $nightBanner = 'NIGHT LOCATION OF MOBILE NO: ' . $number;
    if ($stD7 && ($b = sqlsrv_fetch_array($stD7, SQLSRV_FETCH_ASSOC))) {
        $nightBanner = (string) ($b['PHONE1'] ?? $nightBanner);
    }
    $nightRows = cdat_sum_fetch_all($stD12);

    cdat_sum_results_open();
    cdat_sum_report_banner($banner);

    cdat_sum_generic_table_open(
        'IMEI Summary',
        ['IMEINUMBER', 'FIRST_CALL', 'LAST_CALL', 'TOTAL_CALLS'],
        'imei_results_table',
        'sum_alldb_imei.csv',
        count($imeiRows)
    );
    foreach ($imeiRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['TOTAL_CALLS'] ?? ''), 'class' => 'sum-cell-num'],
        ]);
    }
    cdat_sum_generic_table_close();

    cdat_sum_generic_table_open(
        'IMSI Summary',
        ['IMSINUMBER', 'FIRST_CALL', 'LAST_CALL', 'TOTAL_CALLS'],
        'imsi_results_table',
        'sum_alldb_imsi.csv',
        count($imsiRows)
    );
    foreach ($imsiRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['IMSINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['TOTAL_CALLS'] ?? ''), 'class' => 'sum-cell-num'],
        ]);
    }
    cdat_sum_generic_table_close();

    cdat_sum_generic_table_open(
        'Subject',
        ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'LAST_UPDATED', 'ADDRESS'],
        'header_results_table',
        'sum_alldb_subject.csv',
        count($headerRows)
    );
    foreach ($headerRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['NICKNAME'] ?? ''),
            ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();

    cdat_sum_generic_table_open(
        'Contact Analysis',
        ['PHONE', 'OTHER', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'SDR_DATA', 'RTA_DATA', 'CIVIL_SUPPLY_DATA', 'LICENCE_DATA', 'GAS_DATA'],
        'contact_results_table',
        'sum_alldb_contacts.csv',
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
            ['html' => cdat_sum_address_lines((string) ($row['SDR_DATA'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_address_lines((string) ($row['RTA_DATA'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_address_lines((string) ($row['CIVIL_SUPPLY_DATA'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_address_lines((string) ($row['LICENCE_DATA'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_address_lines((string) ($row['GAS_DATA_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();

    if ($cdrMsg !== '') {
        cdat_sum_status_message($cdrMsg, false);
    }

    cdat_sum_report_banner($dayBanner);
    cdat_sum_generic_table_open(
        'Day Location',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'day_results_table',
        'sum_alldb_day.csv',
        count($dayRows)
    );
    foreach ($dayRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();

    cdat_sum_report_banner($nightBanner);
    cdat_sum_generic_table_open(
        'Night Location',
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        'night_results_table',
        'sum_alldb_night.csv',
        count($nightRows)
    );
    foreach ($nightRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['LAT'] ?? ''),
            (string) ($row['LONG'] ?? ''),
            (string) ($row['AZM'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
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

layout_begin('Summary Alldb');
cdat_sum_page_open();
cdat_sum_search_card(
    'Summary of Mobile Number',
    'Search IMEI, IMSI, contacts, and day/night locations for a mobile number.',
    'sum_alldb.php',
    cdat_sum_field_phone(),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
