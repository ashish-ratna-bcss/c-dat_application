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

        $serverName = "CPHYDERABAD1\DAU_HYD_2023";
        $connectionInfo = array( "Database"=>"CDATDUPL");
        $conn = sqlsrv_connect( $serverName, $connectionInfo );

        if( $conn === false ) {
            die( print_r( sqlsrv_errors(), true));
        }

        $sql1 = "SELECT DISTINCT * INTO #XX FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE phone = ?";
        $params1 = array($number);
        $st1 = sqlsrv_prepare($conn, $sql1, $params1);
        sqlsrv_execute($st1);

        $sql3 = "SELECT * INTO #TEMP FROM CDAT_DETAILS1 WHERE LEN(OTHER) > 10 AND DURATION > '0' AND PHONE = ?";
        $params3 = array($number);
        $st3 = sqlsrv_prepare($conn, $sql3, $params3);
        sqlsrv_execute($st3);

        $sql4 = "SELECT DISTINCT * INTO #TT FROM #TEMP";
        $st4 = sqlsrv_query($conn, $sql4);

        $sql5 = "SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
                SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
                SUM(CASE WHEN INCOMING ='0' THEN 1 ELSE 0 END) AS 'OUT',
                COUNT(PHONE) AS CALLS, SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
                CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRSTCALL,
                CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LASTCALL 
                INTO #RESULT FROM #TT 
                GROUP BY PHONE, OTHER ORDER BY CALLS DESC";
        $st5 = sqlsrv_query($conn, $sql5);

        $sql6 = "SELECT A.PHONE, 
                CASE WHEN A.OTHER = B.PHONE THEN OTHER + ', - ' + NICKNAME ELSE OTHER END AS OTHER,
                [IN],[OUT], CALLS, DUR, FIRSTCALL, LASTCALL,
                ISNULL(AREADESCRIPTION, 'CODE N/A') AS ADDRESS 
                INTO #WITHADDRESS FROM #RESULT A 
                LEFT JOIN CDATDUPL.DBO.cdatsuspect B ON a.other = B.phone 
                LEFT JOIN CDATDUPL.DBO.cdatphonearea C ON '00' + other LIKE phoneprefix + '%' 
                WHERE A.OTHER NOT LIKE '1800%'
                GROUP BY a.PHONE, B.PHONE, other, [IN],[OUT], calls, dur, FIRSTCALL, LASTCALL, nickname, AREADESCRIPTION";
        $st6 = sqlsrv_query($conn, $sql6);

        $sql7 = "SELECT * FROM #WITHADDRESS WHERE ADDRESS != ' JUNK-COULD BE bulk SMS or VOIP calls' ORDER BY calls DESC";
        $st7 = sqlsrv_query($conn, $sql7);

        $sql8 = "SELECT 'ISD CONTACTS OF MOBILE NO: ' + ? AS PHONE1";
        $params8 = array($number);
        $st8 = sqlsrv_prepare($conn, $sql8, $params8);
        sqlsrv_execute($st8);

        $sql9 = "SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED INTO #T";
        $params9 = array($number);
        $st9 = sqlsrv_prepare($conn, $sql9, $params9);
        sqlsrv_execute($st9);

        $sql10 = "SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, 
                  CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, 
                  CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED 
                  INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A 
                  LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE 
                  WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
        $params10 = array($number);
        $st10 = sqlsrv_prepare($conn, $sql10, $params10);
        sqlsrv_execute($st10);

        $sql11 = "SELECT DISTINCT A.PHONE,
                  CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
                  CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
                  CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
                  CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
                  CASE WHEN A.PHONE = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') + ', ' + ISNULL(CONVERT(VARCHAR, C.DOA, 20), '') + ', ' +
                  (CASE WHEN C.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE C.CATEGORY_TYPE END)
                  WHEN A.PHONE = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') + ', ' + ISNULL(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' +
                  (CASE WHEN D.CATEGORY_TYPE IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE D.CATEGORY_TYPE END) 
                  ELSE ISNULL(AREADESCRIPTION, '') END AS ADDRESS 
                  FROM #T A
                  LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
                  LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
                  LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE 
                  ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
                  LIKE PHONEPREFIX + '%'
                  LEFT JOIN #S B ON A.PHONE = B.PHONE";
        $st11 = sqlsrv_query($conn, $sql11);

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
            sqlsrv_free_stmt($st7);
        }
        sqlsrv_close($conn);

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
