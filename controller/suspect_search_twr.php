<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Suspect Search Twr');
    cdat_sum_page_open();
    cdat_sum_back_link('suspect_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

$PHONE_NO 	= $_POST['PHONE_NO'];
$POLICE_STATION	= $_POST['Police_station'];
$CRIME_NO	= $_POST['CRIME_NO'];
$YEAR		= $_POST['YEAR'];
$OFF_DATE       = $_POST['OFF_DATE'];
$HH1		= $_POST['hh1'];
$MM1		= $_POST['mm1'];
$SS1		= $_POST['ss1'];
$HH2		= $_POST['hh2'];
$MM2		= $_POST['mm2'];
$SS2		= $_POST['ss2'];

$sql0="SELECT 'MOBILE NO: '+'$PHONE_NO'+' SEARCH IN TOWER DUMP OF PS: '+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO:'+'/'+'$YEAR' as SEARCH";

$ADD0="SELECT 'ADDRESS OF MOBILE NO: '+'$PHONE_NO' as PHONE1";

$ADD1="SELECT  '$PHONE_NO' AS PHONE,'' AS FIRST_CALL,'' AS LAST_CALL,'' AS NICKNAME,''AS MO,''LAST_UPDATED,''INC_OFFICER INTO #T";

$ADD2="SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,MO,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$PHONE_NO' GROUP BY A.PHONE,B.NICKNAME,MO, INC_OFFICER";

$ADD3="SELECT DISTINCT A.PHONE,CASE WHEN A.PHONE=B.PHONE THEN B.FIRST_CALL ELSE A.FIRST_CALL END AS FIRST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_CALL ELSE A.LAST_CALL END AS LAST_CALL,
CASE WHEN A.PHONE=B.PHONE THEN B.NICKNAME ELSE A.NICKNAME END AS NICKNAME,
CASE WHEN A.PHONE=B.PHONE THEN B.MO ELSE A.MO END AS MO,
CASE WHEN A.PHONE=B.PHONE THEN B.LAST_UPDATED ELSE A.LAST_UPDATED END AS LAST_UPDATED,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS,
CASE WHEN A.PHONE=B.PHONE THEN B.INC_OFFICER ELSE A.INC_OFFICER END AS INC_OFFICER FROM #T A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
LEFT JOIN #S B ON  A.PHONE=B.PHONE";



$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";
$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";


$sql1 ="select distinct A.phone,other,CONVERT(VARCHAR,starttime,20) starttime,duration,imeinumber,call_type,
CASE WHEN A.OTHER=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.OTHER=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS
from TWRMDB_MASTER_CDAT A 
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.OTHER=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.OTHER=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.OTHER)=10 THEN A.OTHER ELSE CASE WHEN LEN(A.OTHER)>10 THEN '00'+A.OTHER ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL' END END
LIKE PHONEPREFIX+'%'
where A.phone='$PHONE_NO' and crkey=(SELECT DISTINCT 
CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE') AND convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and (select distinct max(Timing) from #time))
ORDER BY STARTTIME DESC";



$st0 = sqlsrv_query( $conn, $sql0);
$AD0 = sqlsrv_query( $conn, $ADD0);
$AD1 = sqlsrv_query( $conn, $ADD1);
$AD2 = sqlsrv_query( $conn, $ADD2);
$AD3 = sqlsrv_query( $conn, $ADD3);
$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st3 = sqlsrv_query( $conn, $sql1 );
$bannerAddr = 'ADDRESS OF MOBILE NO: ' . $PHONE_NO;
if ($AD0 && ($b = sqlsrv_fetch_array($AD0, SQLSRV_FETCH_ASSOC))) {
    $bannerAddr = (string) ($b['PHONE1'] ?? $bannerAddr);
}
$addrRows = cdat_sum_fetch_all($AD3);

$bannerSearch = 'MOBILE NO SEARCH IN TOWER DUMP';
if ($st0 && ($b0 = sqlsrv_fetch_array($st0, SQLSRV_FETCH_ASSOC))) {
    $bannerSearch = (string) ($b0['SEARCH'] ?? $bannerSearch);
}
$callRows = cdat_sum_fetch_all($st3);

cdat_sum_results_open();
cdat_sum_report_banner($bannerAddr);
if (empty($addrRows)) {
    cdat_sum_empty_state('No address details found.');
} else {
    cdat_sum_generic_table_open(
        'Mobile Address',
        ['PHONE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'MO', 'LAST_UPDATED', 'PHONE ADDRESS', 'IO NAME', 'QRCODE'],
        'suspect_twr_addr_table',
        'suspect_search_twr_address.csv',
        count($addrRows)
    );
    foreach ($addrRows as $row) {
        $addr = (string) ($row['ADDRESS'] ?? '');
        $qrSrc = '../qrcode/php/qr_img.php?d=' . 'PHONE NO:' . $PHONE_NO . '  ' . 'ADDRESS: ' . preg_replace('/[^A-Za-z0-9\-:]/', ' ', $addr);
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['NICKNAME'] ?? ''),
            (string) ($row['MO'] ?? ''),
            ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => cdat_sum_address_lines($addr) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['INC_OFFICER'] ?? ''),
            ['html' => '<img height="100" width="100" src="' . cdat_sum_h($qrSrc) . '" alt="">', 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}

cdat_sum_report_banner($bannerSearch);
if (empty($callRows)) {
    cdat_sum_empty_state('No tower dump call records found.');
} else {
    cdat_sum_generic_table_open(
        'Tower Dump Calls',
        ['PHONE', 'OTHER', 'STARTTIME', 'DURATION', 'IMEINUMBER', 'CALLTYPE', 'PHONE ADDRESS'],
        'suspect_twr_calls_table',
        'suspect_search_twr.csv',
        count($callRows)
    );
    foreach ($callRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['phone'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['other'] ?? ''), 'class' => 'sum-cell-other'],
            ['text' => (string) ($row['starttime'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['duration'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['imeinumber'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['call_type'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();

if ($st1) {
    sqlsrv_free_stmt($st1);
}
sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
