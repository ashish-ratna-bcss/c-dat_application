<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$prisonerNo = trim((string) ($_POST['PRISONERNO'] ?? ''));
$jailName = trim((string) ($_POST['JAILNAME'] ?? ''));
$hasSearch = $prisonerNo !== '' && $jailName !== '';

$jailOptions = [
    '' => 'Please Enter the Jail Name',
    'CHANCHALGUDA' => 'CHANCHALGUDA',
    'CHERLAPALLI' => 'CHERLAPALLI',
];
$fieldsHtml = cdat_sum_field_text('PRISONERNO', 'Prisoner Number', $prisonerNo, 'NAME', 'Please Enter Prisonerno')
            . cdat_sum_searchable_select('JAILNAME', 'Jail Name', $jailOptions, $jailName, 'Please Enter the Jail Name', true);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('JRMS Search By Prisonerno Uniqueness');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Jail Release Data By Prisoner Number',
            'Search JRMS records by prisoner number and jail name.',
            'jrms_search_by_prisonerno_uniqueness.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $f_PRI = $prisonerNo;
    $f_JAIL = $jailName;

    $sql1 = "SET DATEFORMAT DMY SELECT DISTINCT  AUTO_KEY,CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,TYPEOFRELEASE,CRIMENOS,HEADOFCRIME,
MOBILENO,SEC_OF_LAW,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
IDPROOF_TYPE,IDPROOF_NO,RLDTORDER,
JAILREFID,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (PRISONERNO LIKE '%'+'$f_PRI'+'%' AND JAILNAME LIKE '%'+'$f_JAIL'+'%')";

    $sql2 = "SELECT AUTO_KEY,CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,DISTRICT,NAME,FATHERSNAME,GENDER,DOB_AGE,IDENTIFICATIONMARK,
PlaceofIdentificationMark,CRIMENOS,HEADOFCRIME,MOBILENO,IDPROOF,IDPROOF_TYPE,IDPROOF_NO,
JAILREFID,ADDR_DURING_RELEASE,TYPEOFRELEASE,RLDTORDER,SEC_OF_LAW,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE
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

    if (empty($rows)) {
        cdat_sum_empty_state('No JRMS records found for that prisoner number.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'JRMS Prisoner Search',
            ['CIN', 'UNIQUE_KEY', 'IRKEY', 'PRISONERNO', 'PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'Operation'],
            'results_table',
            'jrms_prisoner.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $mobile = (string) ($row['MOBILENO'] ?? '');
            $autoKey = (string) ($row['AUTO_KEY'] ?? '');
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
                ['html' => '<a href="jrms_uniqueness_update.php?AUTO_KEY=' . cdat_sum_h(urlencode($autoKey)) . '">EDIT/UPDATE</a>'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('JRMS Search By Prisonerno Uniqueness');
cdat_sum_page_open();
cdat_sum_search_card(
    'Jail Release Data By Prisoner Number',
    'Search JRMS records by prisoner number and jail name.',
    'jrms_search_by_prisonerno_uniqueness.php',
    cdat_sum_field_text('PRISONERNO', 'Prisoner Number', '', 'NAME', 'Please Enter Prisonerno')
        . cdat_sum_searchable_select('JAILNAME', 'Jail Name', $jailOptions, '', 'Please Enter the Jail Name', true),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
