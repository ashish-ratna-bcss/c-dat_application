<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('JRMS Search By Prisonerno Uniqueness Old');
    cdat_sum_page_open();
    cdat_sum_back_link('jrms_cin_search_uniqueness.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$f_PRI = $_POST['PRISONERNO'];

$sql1 = "SET DATEFORMAT DMY SELECT DISTINCT  CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,TYPEOFRELEASE,CRIMENOS,HEADOFCRIME,
MOBILENO,SEC_OF_LAW,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
IDPROOF_TYPE,IDPROOF_NO,RLDTORDER,
JAILREFID,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (PRISONERNO LIKE '%'+'$f_PRI'+'%' )";

$sql2 = "SELECT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,CRIMENOS,HEADOFCRIME,MOBILENO,IDPROOF,IDPROOF_TYPE,IDPROOF_NO,
JAILREFID,ADDR_DURING_RELEASE,TYPEOFRELEASE,RLDTORDER,SEC_OF_LAW,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM IRFORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM IRFORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM IRFORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY CIN,RELEASE_DATE DESC";

$sql6 = "SELECT 'JAIL DATA OF PRISONERNO : '+'$f_PRI' AS PHONE";

sqlsrv_query($conn, $sql1);
$st2 = sqlsrv_query($conn, $sql2);
$st6 = sqlsrv_query($conn, $sql6);

$banner = 'JAIL DATA OF PRISONERNO : ' . $f_PRI;
if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st2);

cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No JRMS records found for that prisoner number.');
} else {
    cdat_sum_generic_table_open(
        'JRMS Prisoner Search',
        ['CIN', 'UNIQUE_KEY', 'IRKEY', 'PRISONERNO', 'PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'Operation'],
        'results_table',
        'jrms_prisoner_old.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $mobile = (string) ($row['MOBILENO'] ?? '');
        $qs = http_build_query([
            'CIN' => (string) ($row['CIN'] ?? ''),
            'UNIQUE_KEY' => (string) ($row['UNIQUE_KEY'] ?? ''),
            'PRISONERNO' => (string) ($row['PRISONERNO'] ?? ''),
            'IRKEY' => (string) ($row['IRKEY'] ?? ''),
            'JAIL_REF_ID' => (string) ($row['JAILREFID'] ?? ''),
            'NAME' => (string) ($row['NAME'] ?? ''),
            'FATHERNAME' => (string) ($row['FATHERSNAME'] ?? ''),
            'GENDER' => (string) ($row['GENDER'] ?? ''),
            'DOB' => (string) ($row['DOB_AGE'] ?? ''),
            'IDENTIFICATIONMARK' => (string) ($row['IDENTIFICATIONMARK'] ?? ''),
            'PLACE_OF_MARK' => (string) ($row['PlaceofIdentificationMark'] ?? ''),
            'MOBILENO' => $mobile,
            'IDPROOF_TYPE' => (string) ($row['IDPROOF_TYPE'] ?? ''),
            'IDPROOF_NO' => (string) ($row['IDPROOF_NO'] ?? ''),
            'TYPEOFRELEASE' => (string) ($row['TYPEOFRELEASE'] ?? ''),
            'JAIL_NAME' => (string) ($row['JAILNAME'] ?? ''),
            'ADMISSION_DATE' => (string) ($row['ADD_TO_JAIL'] ?? ''),
            'RELEASE_DATE' => (string) ($row['RELEASE_DATE'] ?? ''),
            'ADD_DUR_RELEASE' => (string) ($row['ADDR_DURING_RELEASE'] ?? ''),
            'REL_DATE_ORDER' => (string) ($row['RLDTORDER'] ?? ''),
            'CRIME_NOS' => (string) ($row['CRIMENOS'] ?? ''),
            'SEC_OF_LAW' => (string) ($row['SEC_OF_LAW'] ?? ''),
            'HEAD_OF_CRIME' => (string) ($row['HEADOFCRIME'] ?? ''),
            'PSARRESTED' => (string) ($row['PSARRESTED'] ?? ''),
            'DISTRICT' => (string) ($row['DISTRICT'] ?? ''),
        ]);
        cdat_sum_table_row([
            (string) ($row['CIN'] ?? ''),
            (string) ($row['UNIQUE_KEY'] ?? ''),
            (string) ($row['IRKEY'] ?? ''),
            (string) ($row['PRISONERNO'] ?? ''),
            (string) ($row['PSARRESTED'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHERSNAME'] ?? ''),
            (string) ($row['CRIMENOS'] ?? ''),
            (string) ($row['HEADOFCRIME'] ?? ''),
            ['html' => '<a href="cdatcnts2.php?PHONE_NO=' . cdat_sum_h(urlencode($mobile)) . '">' . cdat_sum_h($mobile) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['IDPROOF'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['ADDR_DURING_RELEASE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['JAILNAME'] ?? ''),
            ['text' => (string) ($row['ADD_TO_JAIL'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['RELEASE_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            ['html' => '<a href="jrms_uniqueness_update.php?' . cdat_sum_h($qs) . '">EDIT/UPDATE</a>'],
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
