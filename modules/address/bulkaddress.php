<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('Bulk Address');
            cdat_sum_page_open();
        }

        $serverName = "CPHYDERABAD1\DAU_HYD_2023";
        $connectionInfo = array( "Database"=>"CDATDUPL");
        $conn = sqlsrv_connect( $serverName, $connectionInfo );
        if( $conn === false ) {
            die( print_r( sqlsrv_errors(), true));
        }

        $phones = cdat_sum_split_phones($number);
        $number2 = cdat_sum_sql_phone_in($phones);

        $sql1= "CREATE TABLE #T1 (PHONE NVARCHAR (20) NULL)";

        $sql3= "SELECT DISTINCT A.PHONE, MIN(STARTTIME) AS FIRST_CALL,MAX(STARTTIME) AS LAST_CALL, MAX(A.ASONDATE) AS LAST_UPDATED,NICKNAME INTO #T2
FROM CDATDUPL.DBO.CDATPCSUSPECT A 
LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN ('$number2')
GROUP BY A.PHONE,NICKNAME";

        $sql4 = "SELECT DISTINCT A.PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME INTO #T3 FROM #T1 A
LEFT JOIN #T2 B ON A.PHONE=B.PHONE";

        $sql5= "SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE,DOA, EFF_FROM_DATE INTO #T4   FROM CDATDUPL.DBO.CDATADDRESS 
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

        $sql6 = "INSERT INTO #T4
SELECT PHONE,FULLNAME,FULLADDRESS,CATEGORY_TYPE, DOA, EFF_FROM_DATE FROM CDATDUPL.DBO.ADDRESS_OTHER_STATE
WHERE PHONE IN ('$number2') AND EFF_TO_DATE IS NULL";

        $sql7 = "select DISTINCT A.PHONE,ISNULL(CONVERT(VARCHAR,FIRST_CALL,20),'NIL')  AS FIRST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_CALL,20),'NIL') AS LAST_CALL,
ISNULL(CONVERT(VARCHAR,A.LAST_UPDATED,20),'NIL') AS LAST_UPDATED,ISNULL(NICKNAME,'NIL') AS NICKNAME,
CASE WHEN A.PHONE IN (SELECT PHONE FROM #T4) THEN FULLNAME+', '+B.FULLADDRESS+', DOA: '+CONVERT(VARCHAR,DOA,106)+', LAST UPDATE: '+CONVERT(VARCHAR,EFF_FROM_DATE,106)
ELSE AREADESCRIPTION END AS ADDRESS INTO #T5 FROM #T3 A
LEFT JOIN #T4 B ON A.PHONE=B.PHONE
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA E ON  CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'CODE NOT AVAILABLE' END END
 LIKE PHONEPREFIX+'%' ORDER BY A.PHONE";

        $sql8 = "SELECT PHONE, FIRST_CALL,LAST_CALL,LAST_UPDATED,NICKNAME,
 CASE WHEN ADDRESS IS NULL AND LEN(PHONE)<>10 THEN 'JUNK OR VOIP CALL' 
 WHEN ADDRESS IS NULL AND SUBSTRING(PHONE,1,1) IN ('7','8','9') AND LEN(ADDRESS)>=10 THEN 'CODE NOT AVAILABLE' ELSE ADDRESS 
 END AS ADDRESS FROM #T5";

        $st1 = sqlsrv_query( $conn, $sql1 );
        cdat_sum_insert_phones($conn, '#T1', $phones);
        $st3 = sqlsrv_query( $conn, $sql3 );
        $st4 = sqlsrv_query( $conn, $sql4 );
        $st5 = sqlsrv_query( $conn, $sql5 );
        $st6 = sqlsrv_query( $conn, $sql6 );
        $st7 = sqlsrv_query( $conn, $sql7 );
        $st8 = sqlsrv_query( $conn, $sql8 );

        $rows = cdat_sum_fetch_all($st8);

        cdat_sum_results_open();
        cdat_sum_report_banner('ADDRESSES OF MOBILE NOS');

        if (empty($rows)) {
            cdat_sum_empty_state();
        } else {
            cdat_sum_generic_table_open(
                'Bulk Addresses',
                ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'LAST_UPDATED', 'NICKNAME', 'ADDRESS'],
                'results_table',
                'bulk_address.csv',
                count($rows)
            );
            foreach ($rows as $row) {
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

        cdat_sum_results_close();

        if ($st8) {
            sqlsrv_free_stmt($st8);
        }

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }

    if ($isAjax) {
        cdat_sum_empty_state('Enter mobile numbers and try again.');
        exit;
    }
}

layout_begin('Bulk Addresses');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk Addresses',
    'Enter comma-separated mobile numbers to look up addresses.',
    'bulkaddress.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Addresses of Mobile Numbers',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
