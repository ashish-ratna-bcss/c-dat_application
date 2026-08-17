<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phoneNo = (string) ($_POST['PHONE_NO'] ?? '');
$stringOp = (string) ($_POST['STRING'] ?? '>');
$noVal = (string) ($_POST['NO'] ?? '1');
$hasSearch = trim($phoneNo) !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter at least one mobile number and try again.');

$stringHtml = cdat_sum_searchable_select(
    'STRING',
    'Compare',
    ['>' => '>', '=' => '='],
    $stringOp === '=' ? '=' : '>',
    'Select compare',
    false
);

$noOptions = ['' => 'Select number'];
for ($i = 1; $i <= 20; $i++) {
    $noOptions[(string) $i] = (string) $i;
}
$noHtml = cdat_sum_searchable_select(
    'NO',
    'More Than No',
    $noOptions,
    $noVal !== '' ? $noVal : '1',
    'Select number',
    false
);

$fieldsHtml = cdat_sum_field_textarea(
    'PHONE_NO',
    'Mobile Numbers',
    $phoneNo,
    'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
) . $stringHtml . $noHtml;

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Common Contacts');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Common Contacts of Mobile Numbers',
            'Find shared contacts across multiple mobile numbers.',
            'common_cnts.php',
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

    // Address queries
    $address1 = "CREATE TABLE #A1 (PHONE NVARCHAR (20) NULL)";
    $address3 = "SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL, MAX(STARTTIME) AS LAST_CALL, 
                MAX(A.ASONDATE) AS LAST_UPDATED, NICKNAME + '_' + ROLE + ' MO:' + MO NICKNAME INTO #A2
                FROM CDATDUPL.DBO.CDATPCSUSPECT A 
                LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE = B.PHONE 
                WHERE A.PHONE IN ('$number2')
                GROUP BY A.PHONE, NICKNAME, MO, ROLE";
    $address4 = "SELECT DISTINCT A.PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME INTO #A3 FROM #A1 A
                LEFT JOIN #A2 B ON A.PHONE = B.PHONE";
    $address5 = "SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE INTO #A4 FROM CDATDUPL.DBO.CDATADDRESS 
                WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";
    $address6 = "INSERT INTO #A4
                SELECT PHONE, FULLNAME, FULLADDRESS, CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE
                WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";
    $address7 = "SELECT DISTINCT A.PHONE, ISNULL(CONVERT(VARCHAR, FIRST_CALL, 20), 'NIL') AS FIRST_CALL,
                ISNULL(CONVERT(VARCHAR, A.LAST_CALL, 20), 'NIL') AS LAST_CALL,
                ISNULL(CONVERT(VARCHAR, A.LAST_UPDATED, 20), 'NIL') AS LAST_UPDATED, ISNULL(NICKNAME, 'NIL') AS NICKNAME,
                CASE WHEN A.PHONE IN (SELECT PHONE FROM #A4) THEN FULLNAME + ', ' + B.FULLADDRESS + ', DOA: ' + CONVERT(VARCHAR, DOA, 106) + ', LAST UPDATE: ' + CONVERT(VARCHAR, EFF_FROM_DATE, 106)
                ELSE AREADESCRIPTION END AS ADDRESS INTO #A5 FROM #A3 A
                LEFT JOIN #A4 B ON A.PHONE = B.PHONE
                LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON CASE WHEN LEN(A.PHONE) = 10 
                THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE) > 10 THEN '00' + A.PHONE ELSE 'CODE NOT AVAILABLE' END END
                LIKE PHONEPREFIX + '%' ORDER BY A.PHONE";
    $address8 = "SELECT PHONE, FIRST_CALL, LAST_CALL, LAST_UPDATED, NICKNAME,
                CASE WHEN ADDRESS IS NULL AND LEN(PHONE) <> 10 THEN 'JUNK OR VOIP CALL' 
                WHEN ADDRESS IS NULL AND SUBSTRING(PHONE, 1, 1) IN ('6','7','8','9') AND LEN(ADDRESS) >= 10 THEN 'CODE NOT AVAILABLE' 
                ELSE ADDRESS END AS ADDRESS FROM #A5";

    // Common contacts queries
    $sql1 = "SELECT * INTO #T FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN ('$number2')";
    $sql2 = "SELECT PHONE, OTHER, COUNT(OTHER) AS COUNT1 INTO #common_numbertable1 FROM #T
            GROUP BY OTHER, PHONE HAVING (COUNT(OTHER)) > 1 ORDER BY OTHER, PHONE";
    $sql3 = "SELECT OTHER, PHONE, COUNT(OTHER) COUNT1 INTO #common_numbertable2 FROM #common_numbertable1
            GROUP BY OTHER, PHONE ORDER BY OTHER";
    $sql4 = "SELECT DISTINCT OTHER, 
            (SELECT PHONE + ', ' FROM #common_numbertable2 US
            WHERE US.OTHER = SS.OTHER FOR XML PATH('')) [PHONES],
            (SELECT SUM(COUNT1) FROM #common_numbertable2 XX WHERE XX.OTHER = SS.OTHER) TOTALNUMBEROFPHONES
            INTO #common_numbertable3 FROM #common_numbertable2 SS
            GROUP BY SS.OTHER ORDER BY 1";
    $sql5 = "DELETE FROM #common_numbertable3 WHERE TOTALNUMBEROFPHONES = 1";
    $sql6 = "DROP TABLE #common_numbertable1";
    $sql7 = "DROP TABLE #common_numbertable2";
    $sql8 = "UPDATE #common_numbertable3 SET PHONES = LEFT(PHONES, LEN(PHONES) - 1) + ''";
    $sql9 = "SELECT DISTINCT A.OTHER, A.PHONES, A.TOTALNUMBEROFPHONES PHONE_COUNT, E.NICKNAME + '_' + ROLE OTHERS_NICKNAME, E.MO OTHERS_MO,
            CASE WHEN A.OTHER = C.PHONE THEN ISNULL(C.FULLNAME, '') + ', ' + ISNULL(C.FULLADDRESS, '') +
            ', DOA: ' + ISNULL(CONVERT(VARCHAR, C.DOA, 20), '') + ', LAST_UPDATED: ' +
            ISNULL(CONVERT(VARCHAR, C.EFF_FROM_DATE, 20), '') + ', ' +
            (CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE C.OPERATOR END)
            WHEN A.OTHER = D.PHONE THEN ISNULL(D.FULLNAME, '') + ', ' + ISNULL(D.FULLADDRESS, '') +
            ', ' + ISNULL(CONVERT(VARCHAR, D.DOA, 20), '') + ', ' +
            (CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION, '') ELSE D.OPERATOR END) 
            ELSE ISNULL(AREADESCRIPTION, '') END AS OTHER_ADDRESS 
            FROM #common_numbertable3 A
            LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER = C.PHONE AND C.EFF_TO_DATE IS NULL AND LEN(A.OTHER) >= '10'
            LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER = D.PHONE AND D.EFF_TO_DATE IS NULL
            LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER) = 10 THEN A.OTHER 
            ELSE CASE WHEN LEN(A.OTHER) > 10 THEN '00' + A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
            LIKE PHONEPREFIX + '%'
            LEFT JOIN CDATDUPL.DBO.CDATSUSPECT E ON A.OTHER = E.PHONE
            WHERE LEN(A.OTHER) = '10' AND ISNUMERIC(A.OTHER) = '1' AND A.OTHER LIKE '[6-9]%'
            ORDER BY PHONE_COUNT DESC, OTHER DESC";

    // Execute address queries
    $at1 = sqlsrv_query($conn, $address1);
    cdat_sum_insert_phones($conn, '#A1', $phones);
    $at3 = sqlsrv_query($conn, $address3);
    $at4 = sqlsrv_query($conn, $address4);
    $at5 = sqlsrv_query($conn, $address5);
    $at6 = sqlsrv_query($conn, $address6);
    $at7 = sqlsrv_query($conn, $address7);
    $at8 = sqlsrv_query($conn, $address8);

    // Execute common contacts queries
    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st3 = sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    if (count($phones) >= 2) {
        $op = $stringOp === '=' ? '=' : '>';
        $n = max(1, (int) $noVal);
        sqlsrv_query($conn, "DELETE FROM #common_numbertable3 WHERE NOT (TOTALNUMBEROFPHONES $op $n)");
    }
    $st6 = sqlsrv_query($conn, $sql6);
    $st7 = sqlsrv_query($conn, $sql7);
    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);

    $addressRows = cdat_sum_fetch_all($at8);
    $contactRows = cdat_sum_fetch_all($st9);

    cdat_sum_results_open();
    cdat_sum_report_banner('ADDRESSES OF MOBILE NOS');
    if (empty($addressRows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_generic_table_open(
            'Addresses',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'LAST_UPDATED', 'NICKNAME', 'ADDRESS'],
            'addresses_table',
            'common_addresses.csv',
            count($addressRows)
        );
        foreach ($addressRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    cdat_sum_report_banner('COMMON CONTACTS');
    if (empty($contactRows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_generic_table_open(
            'Common Contacts',
            ['COMMON CONTACT', 'PHONES', 'PHONE_COUNT', 'OTHERS_NICKNAME', 'OTHERS_MO', 'OTHER_ADDRESS'],
            'contacts_table',
            'common_contacts.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            $addrHtml = cdat_sum_address_lines((string) ($row['OTHER_ADDRESS'] ?? ''));
            cdat_sum_table_row([
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['PHONES'] ?? ''),
                ['text' => (string) ($row['PHONE_COUNT'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['OTHERS_NICKNAME'] ?? ''),
                (string) ($row['OTHERS_MO'] ?? ''),
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();

    if ($at8) {
        sqlsrv_free_stmt($at8);
    }
    if ($st9) {
        sqlsrv_free_stmt($st9);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Common Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'Common Contacts of Mobile Numbers',
    'Find shared contacts across multiple mobile numbers.',
    'common_cnts.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Mobile Numbers',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ) . $stringHtml . $noHtml,
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
