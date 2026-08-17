<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Single Address');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Address of Mobile No',
            'Look up the registered address for a mobile number.',
            'address.php',
            cdat_sum_field_phone($phone, 'ADDRESS'),
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

    $sql8="SELECT 'ADDRESS OF MOBILE NO: '+'$number' as PHONE1";

    $sql9="SELECT  '$number' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,''LAST_UPDATED,''INC_OFFICER INTO #T";

    $sql10="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,MO,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
    INC_OFFICER 
    INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,B.ROLE, INC_OFFICER";

    $sql11="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
    CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
    CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
    CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
    CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
    CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
    (CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
    WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
    (CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS,
    CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM #T A
    LEFT JOIN CDATDUPL.DBO.CDATADDRESS C WITH (NOLOCK) ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
    LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D WITH (NOLOCK) ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
    LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
    LIKE PHONEPREFIX+'%'
    LEFT JOIN #S B ON  A.PHONE=B.PHONE";

    $st8 = sqlsrv_query( $conn, $sql8 );
    $st9 = sqlsrv_query( $conn, $sql9 );
    $st10 = sqlsrv_query( $conn, $sql10 );
    $st11 = sqlsrv_query( $conn, $sql11 );

    $bannerTitle = 'ADDRESS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st11);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Address',
            ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'MO', 'LAST_UPDATED', 'ADDRESS', 'IO NAME', 'QRCODE'],
            'results_table',
            'address.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $address = (string) ($row['ADDRESS'] ?? '');
            $addrHtml = cdat_sum_address_lines($address);
            $qrSrc = CDAT_BASE . '/qrcode/php/qr_img.php?d=' . urlencode(
                'PHONE NO:' . $number . '  ' . 'ADDRESS: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $address)
            );
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
                (string) ($row['MO'] ?? ''),
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['INC_OFFICER'] ?? ''),
                ['html' => '<img height="100" width="100" src="' . cdat_sum_h($qrSrc) . '">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st11) {
        sqlsrv_free_stmt($st11);
    }

    if ($isAjax) {
        exit;
    }

    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Single Address');
cdat_sum_page_open();
cdat_sum_search_card(
    'Address of Mobile No',
    'Look up the registered address for a mobile number.',
    'address.php',
    cdat_sum_field_phone('', 'ADDRESS'),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
