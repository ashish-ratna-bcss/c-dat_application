<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('JRMS Search For Uniquekey');
    cdat_sum_page_open();
    cdat_sum_back_link('jrms_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"CDATDUPL");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$UNIQUE_KEY = $_GET['UNIQUE_KEY'];



$sql1 ="SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  UNIQUE_KEY='$UNIQUE_KEY' ";

$sql2 ="SELECT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY JAILNAME, RELEASE_DATE DESC";

$sql6="SELECT 'ACCUSED RELEASED FROM JAIL' PHONE ";


$st1 = sqlsrv_query( $conn, $sql1 );
$st2 = sqlsrv_query( $conn, $sql2 );
$st6 = sqlsrv_query( $conn, $sql6 );

$banner = 'ACCUSED RELEASED FROM JAIL';
if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st2);
cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No JRMS records found for that unique key.');
} else {
    cdat_sum_generic_table_open(
        'JRMS Unique Key',
        ['PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
        'results_table',
        'jrms_uniquekey.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        $irForm = (string) ($row['IRFORM'] ?? '');
        $phone = (string) ($row['PHONE'] ?? '');
        cdat_sum_table_row([
            (string) ($row['PSARRESTED'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHERSNAME'] ?? ''),
            (string) ($row['CRIMENOS'] ?? ''),
            (string) ($row['HEADOFCRIME'] ?? ''),
            ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['IDPROOF'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['ADDR_DURING_RELEASE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['JAILNAME'] ?? ''),
            ['text' => (string) ($row['ADD_TO_JAIL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['RELEASE_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => cdat_sum_img_html($row['PHOTO'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
            ['html' => $irForm !== '' ? '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irForm) . '</a>' : ''],
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();
sqlsrv_close($conn);
if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
