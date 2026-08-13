<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('JRMS Main Page');
cdat_sum_page_open();

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"JRMS");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}




$sql9="SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,PSARRESTED,SUBSTRING(NAME,1,CHARINDEX('/',NAME)-1) NAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM JRMS..JRMS_TOTAL_2012_TO_2017
WHERE CONVERT(DATE,RELEASEDT) =(SELECT DISTINCT MAX(CONVERT(DATE,RELEASEDT)) FROM JRMS..JRMS_TOTAL_2012_TO_2017) AND HEADOFCRIME!='' AND JAILNAME IN ('CHERLAPALLI','CHANCHALGUDA')";

$sql10="SELECT PRISONERNO,PSARRESTED,NAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,
ADDR_DURING_RELEASE,JAILNAME,ADD_TO_JAIL,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND isnumeric(IDPROOF)=1 AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,CASE WHEN IDPROOF!='' AND isnumeric(IDPROOF)=1 AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY RELEASE_DATE DESC";

$sql8="SELECT DISTINCT 'RECENTLY RELEASED ACCUSED FROM JAIL (CHERLAPALLI AND CHANCHALGUDA)'  +' ON '+RELEASE_DATE as PHONE1 FROM #TEMP";


$st9 = sqlsrv_query( $conn, $sql9 );
$st10 = sqlsrv_query( $conn, $sql10 );
$st8 = sqlsrv_query( $conn, $sql8 );

$banner = 'RECENTLY RELEASED ACCUSED FROM JAIL (CHERLAPALLI AND CHANCHALGUDA)';
if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE1'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st10);
if (empty($rows)) {
    cdat_sum_empty_state('No recently released JRMS records found.');
} else {
    cdat_sum_results_open();
    cdat_sum_report_banner($banner);
    cdat_sum_generic_table_open(
        'JRMS Recently Released',
        ['PSARRESTED', 'NAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'IMAGE', 'IRFORM'],
        'results_table',
        'jrms_main.csv',
        count($rows)
    );

    foreach ($rows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        $irForm = (string) ($row['IRFORM'] ?? '');
        $phone = (string) ($row['PHONE'] ?? '');
        cdat_sum_table_row([
            (string) ($row['PSARRESTED'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            (string) ($row['CRIMENOS'] ?? ''),
            (string) ($row['HEADOFCRIME'] ?? ''),
            ['html' => '<a href="cdatcnts.php?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['IDPROOF'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['ADDR_DURING_RELEASE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['JAILNAME'] ?? ''),
            ['text' => (string) ($row['ADD_TO_JAIL'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => cdat_sum_img_html($row['PHOTO'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
            ['html' => $irForm !== '' ? '<a href="ir.php?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irForm) . '</a>' : ''],
        ]);
    }

    cdat_sum_generic_table_close();
    cdat_sum_results_close();
}
sqlsrv_close($conn);
cdat_sum_page_close();
layout_end();
