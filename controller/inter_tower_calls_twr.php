<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Inter Tower Calls Twr');
    cdat_sum_page_open();
    cdat_sum_back_link('suspect_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}

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

$sql0="SELECT 'INTER TOWER CALLS SEARCH IN TOWER DUMP OF PS:'+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO'+'/'+'$YEAR' as SEARCH";

$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";
$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";

$sql1="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,CALL_TYPE,IMEINUMBER INTO #TEMP1 FROM TWRMDB_MASTER_CDAT 
where crkey=(SELECT DISTINCT CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE')";

$sql11="SELECT DISTINCT PHONE,OTHER,STARTTIME,DURATION,CALL_TYPE,IMEINUMBER INTO #TEMP11 FROM #TEMP1 
where convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and 
(select distinct max(Timing) from #time))";

$sql2="select distinct PHONE,OTHER,STARTTIME INTO #TEMP2 from #TEMP11 INTERSECT select distinct OTHER,PHONE,STARTTIME from #TEMP11";

$sql3="SELECT DISTINCT A.PHONE,A.OTHER,A.STARTTIME,B.DURATION,B.CALL_TYPE,B.IMEINUMBER INTO #TEMP3 FROM #TEMP2 A
INNER JOIN TWRMDB..TWRMDB_MASTER_CDAT B ON A.PHONE=B.PHONE AND A.OTHER=B.OTHER AND A.STARTTIME=B.STARTTIME";

$sql4="SELECT DISTINCT A.PHONE PHONE,A.OTHER OTHER,CONVERT(VARCHAR,A.STARTTIME,20) STARTTIME,A.DURATION DURATION,A.CALL_TYPE CALL_TYPE,A.IMEINUMBER IMEINUMBER,
CASE WHEN A.PHONE=C.PHONE THEN ISNULL(C.FULLNAME,'')+', '+ISNULL(C.FULLADDRESS,'')+', DOA: '+ISNULL(CONVERT(VARCHAR,C.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,C.EFF_FROM_DATE,20),'')+', '+ISNULL(C.CATEGORY_TYPE,'')+', '+
(CASE WHEN C.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE C.OPERATOR END)
WHEN A.PHONE=D.PHONE THEN ISNULL(D.FULLNAME,'')+', '+ISNULL(D.FULLADDRESS,'')+',DOA: '+ISNULL(CONVERT(VARCHAR,D.DOA,20),'')+', '+', ASONDATE: '+ISNULL(CONVERT(VARCHAR,D.EFF_FROM_DATE,20),'')+', '+ISNULL(D.CATEGORY_TYPE,'')+', '+
(CASE WHEN D.OPERATOR IS NULL THEN ISNULL(AREADESCRIPTION,'') ELSE D.OPERATOR END) ELSE ISNULL(AREADESCRIPTION,'') END AS ADDRESS FROM #TEMP3 A
LEFT JOIN CDATDUPL.DBO.CDATADDRESS C ON A.PHONE=C.PHONE AND C.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.ADDRESS_OTHER_STATE D ON A.PHONE=D.PHONE AND D.EFF_TO_DATE IS NULL
LEFT JOIN CDATDUPL.DBO.CDATPHONEAREA ON CASE WHEN LEN(A.PHONE)=10 THEN A.PHONE ELSE CASE WHEN LEN(A.PHONE)>10 THEN '00'+A.PHONE ELSE 'POSSIBLE OF VOIP CALL OR SKYPE OR WWIFI CALL' END END LIKE PHONEPREFIX+'%' ORDER BY STARTTIME";

$st0 = sqlsrv_query( $conn, $sql0);
$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st3 = sqlsrv_query( $conn, $sql1 );
$st11 = sqlsrv_query( $conn,$sql11 );
$st4 = sqlsrv_query( $conn, $sql2 );
$st5 = sqlsrv_query( $conn, $sql3 );
$st6 = sqlsrv_query( $conn, $sql4 );
$banner = 'INTER TOWER CALLS SEARCH IN TOWER DUMP';
if ($st0 && ($b = sqlsrv_fetch_array($st0, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['SEARCH'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st6);

cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No inter-tower calls found.');
} else {
    cdat_sum_generic_table_open(
        'Inter Tower Calls',
        ['PHONE', 'OTHER', 'STARTTIME', 'DURATION', 'CALL_TYPE', 'IMEINUMBER', 'ADDRESS'],
        'inter_tower_twr_table',
        'inter_tower_calls_twr.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
            ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['CALL_TYPE'] ?? ''),
            ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
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
