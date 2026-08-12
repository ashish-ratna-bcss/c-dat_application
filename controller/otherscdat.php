<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Others Cdat');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Others CDAT Contacts',
            'Find CDAT contacts of others linked to a mobile number.',
            'otherscdat.php',
            cdat_sum_field_phone($number),
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

    $number = $_POST['PHONE_NO'];

    // Use parameterized queries to prevent SQL injection
    $sql1 = "SELECT ? AS PHONE, '' AS FIRST_CALL, '' AS LAST_CALL, '' AS NICKNAME, '' AS LAST_UPDATED INTO #T";
    $params1 = array($number);
    $st1 = sqlsrv_prepare($conn, $sql1, $params1);
    sqlsrv_execute($st1);

    $sql2 = "SELECT A.PHONE, CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FIRST_CALL, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LAST_CALL, B.NICKNAME, CONVERT(VARCHAR, MAX(A.ASONDATE), 20) AS LAST_UPDATED 
    INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE, B.NICKNAME";
    $params2 = array($number);
    $st2 = sqlsrv_prepare($conn, $sql2, $params2);
    sqlsrv_execute($st2);

    $sql3 = "SELECT DISTINCT A.PHONE,
            CASE WHEN A.PHONE = B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
            CASE WHEN A.PHONE = B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
            CASE WHEN A.PHONE = B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
            CASE WHEN A.PHONE = B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
            CASE WHEN A.PHONE = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') + ', ' + ISNULL(C.CATEGORY_TYPE, '') 
            WHEN A.PHONE = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') + ', ' + ISNULL(D.CATEGORY_TYPE, '') 
            ELSE ISNULL(AREADESCRIPTION, '') END AS ADDRESS 
            FROM #T A
            LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE = C.PHONE AND C.EFF_TO_DATE IS NULL
            LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE) = 10 THEN A.PHONE 
            ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
            LIKE PHONEPREFIX + '%'
            LEFT JOIN #S B ON A.PHONE = B.PHONE";
    $st3 = sqlsrv_query($conn, $sql3);

    $sql4 = "SELECT DISTINCT OTHER INTO #TEMP FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE = ?
            AND LEN(OTHER) >= 10 AND ISNUMERIC(OTHER) = 1 AND SUBSTRING(OTHER,1,1) IN ('7','8','9')
            AND OTHER NOT IN (SELECT DISTINCT OTHER FROM CDAT_IMPORT.dbo.CALLCENTER_NOS)";
    $params4 = array($number);
    $st4 = sqlsrv_prepare($conn, $sql4, $params4);
    sqlsrv_execute($st4);

    $sql5 = "SELECT DISTINCT PHONE, OTHER,
            SUM(CASE WHEN INCOMING = '1' THEN 1 ELSE 0 END) AS 'IN',
            SUM(CASE WHEN INCOMING = '0' THEN 1 ELSE 0 END) AS 'OUT',
            COUNT(PHONE) AS CALLS, SUM(DURATION) AS DUR,
            CONVERT(VARCHAR, MIN(STARTTIME), 20) AS FC, CONVERT(VARCHAR, MAX(STARTTIME), 20) AS LC 
            INTO #TEMP1 FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE OTHER IN
            (SELECT DISTINCT OTHER FROM #TEMP) AND PHONE != ?
            GROUP BY PHONE, OTHER ORDER BY OTHER";
    $params5 = array($number);
    $st5 = sqlsrv_prepare($conn, $sql5, $params5);
    sqlsrv_execute($st5);

    $sql6 = "SELECT OTHER AS PHONE, A.PHONE AS OTHER, C.NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FC AS FIRST_CALL, LC AS LAST_CALL, INC_OFFICER 
            INTO #TEMP2 FROM #TEMP1 A
            LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.PHONE = C.PHONE";
    $st6 = sqlsrv_query($conn, $sql6);

    $sql7 = "SELECT DISTINCT A.PHONE, OTHER, NICKNAME, CATEGORY, [IN], [OUT], CALLS, DUR, FIRST_CALL, LAST_CALL, INC_OFFICER 
            FROM #TEMP2 A ORDER BY PHONE, CALLS DESC";
    $st7 = sqlsrv_query($conn, $sql7);

    $sql8 = "SELECT 'OTHERS CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";
    $params8 = array($number);
    $st8 = sqlsrv_prepare($conn, $sql8, $params8);
    sqlsrv_execute($st8);

    $sql9 = "SELECT CASE WHEN COUNT(PHONE) >= 1 THEN '' ELSE '*** NO CDAT CONTACTS TO OTHERS OF $number ***' END as CNTS FROM #TEMP2";
    $st9 = sqlsrv_query($conn, $sql9);

    $bannerTitle = 'OTHERS CDAT CONTACTS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $headerRows = cdat_sum_fetch_all($st3);
    $contactRows = cdat_sum_fetch_all($st7);

    $noContactsMsg = '';
    if ($st9 && ($cntRow = sqlsrv_fetch_array($st9, SQLSRV_FETCH_ASSOC))) {
        $noContactsMsg = (string) ($cntRow['CNTS'] ?? '');
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($bannerTitle);

    if (!empty($headerRows)) {
        cdat_sum_generic_table_open(
            'Subject',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'LAST_UPDATED', 'ADDRESS'],
            'results_table',
            'others_cdat_subject.csv',
            count($headerRows)
        );
        foreach ($headerRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($noContactsMsg !== '') {
        cdat_sum_empty_state($noContactsMsg);
    } elseif (!empty($contactRows)) {
        cdat_sum_generic_table_open(
            'CDAT Contacts',
            ['OTHER', 'CDAT PHONE', 'NICK NAME', 'CAT', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'IO NAME'],
            'contact_results_table',
            'others_cdat_contacts.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            $other = (string) ($row['OTHER'] ?? '');
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                [
                    'html' => '<a href="cdatcnts2.php?PHONE_NO=' . urlencode($other) . '">' . cdat_sum_h($other) . '</a>',
                    'class' => 'sum-cell-other',
                ],
                (string) ($row['NICKNAME'] ?? ''),
                (string) ($row['CATEGORY'] ?? ''),
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['INC_OFFICER'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    cdat_sum_results_close();

    if ($st2) {
        sqlsrv_free_stmt($st2);
    }
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

layout_begin('Others Cdat');
cdat_sum_page_open();
cdat_sum_search_card(
    'Others CDAT Contacts',
    'Find CDAT contacts of others linked to a mobile number.',
    'otherscdat.php',
    cdat_sum_field_phone(),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
