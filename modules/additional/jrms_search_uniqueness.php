<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$father = trim((string) ($_POST['FATHER_NAME'] ?? ''));
$phone = trim((string) ($_POST['PHONE'] ?? ''));
$aadhaar = trim((string) ($_POST['AADHAAR_NO'] ?? ''));
$voter = trim((string) ($_POST['VOTER_ID'] ?? ''));
$hasSearch = $_SERVER['REQUEST_METHOD'] === 'POST';

$fieldsHtml = cdat_sum_field_text('NAME', 'Accused Name', $name, 'NAME', 'Accused name', false)
            . cdat_sum_field_text('FATHER_NAME', 'Father Name', $father, 'FATHER_NAME', 'Father name', false)
            . cdat_sum_field_text('PHONE', 'Phone', $phone, 'PHONE', 'Phone', false, 'tel')
            . cdat_sum_field_text('AADHAAR_NO', 'Aadhaar Number', $aadhaar, 'AADHAAR_NO', 'Aadhaar number', false)
            . cdat_sum_field_text('VOTER_ID', 'Voter ID', $voter, 'VOTER_ID', 'Voter ID', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('JRMS Search Uniqueness');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Jail Release Search',
            'Search by name, father name, phone, Aadhaar, or voter ID.',
            'jrms_search_uniqueness.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $NAME = $name;
    $FATHER_NAME = $father;
    $PHONE = $phone;
    $AADHAAR_NO = $aadhaar;
    $VOTER_ID = $voter;

    $sql1 = "SET DATEFORMAT DMY SELECT DISTINCT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,
MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM 
JRMS.dbo.JRMS_TOTAL_2012_TO_2017
WHERE  NAME LIKE '%'+'$NAME'+'%' AND FATHERSNAME LIKE '%'+'$FATHER_NAME'+'%' AND (MOBILENO like '%'+'$PHONE'+'%' OR MOBILENO IS NULL) and 
(NAME LIKE '%'+'$AADHAAR_NO'+'%' ) and (NAME LIKE '%'+'$VOTER_ID'+'%' )";

    $sql2 = "SET DATEFORMAT DMY SELECT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME, ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND 
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE 
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP ORDER BY CIN, RELEASE_DATE DESC";

    $sql6 = "SELECT 'ACCUSED RELEASED FROM JAIL'+' '+'$NAME'+' '+'$FATHER_NAME'+'$AADHAAR_NO' AS PHONE";
    $sql7 = "SELECT 'INTERROGATION REPORT MATCHED TO SEARCH CRITERIA' AS PHONE";
    $sql8 = "SELECT DISTINCT A.IRKEY,A.NAME,A.FATHER_NAME,A.AADHAR_NO,A.PRESENT_ADDRESS,
CONVERT(VARCHAR(20),B.CRIME_NO)+'/'+CONVERT(VARCHAR(20),B.YEAR) CRNO,
B.CRIME_HEAD,B.POLICE_STATION,A.MOBILE PHONE
INTO #TEMP1 FROM FORMS..IR_PARTICULARS A
left JOIN FORMS..OFFENCE_DETAILS B ON A.IRKEY=B.IRKEY
WHERE NAME LIKE '%'+'$NAME'+'%' AND FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%' AND (MOBILE like '%'+'$PHONE'+'%') and 
(AADHAR_NO LIKE '%'+'$AADHAAR_NO'+'%') and (VOTERID LIKE '%'+'$VOTER_ID'+'%')";
    $sql9 = "SELECT DISTINCT * FROM #TEMP1";
    $sql10 = "SELECT case when count(cin)>=1 THEN '' ELSE '*** JRMS RECORDS NOT AVAILABLE ***' end as PHONE FROM #TEMP";
    $sql11 = "SELECT case when count(IRKEY)>=1 THEN '' ELSE '*** IR RECORDS NOT AVAILABLE ***' end as PHONE FROM #TEMP1";

    sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st6 = sqlsrv_query($conn, $sql6);
    $st7 = sqlsrv_query($conn, $sql7);
    sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $st11 = sqlsrv_query($conn, $sql11);

    $banner = 'ACCUSED RELEASED FROM JAIL ' . $NAME . ' ' . $FATHER_NAME . $AADHAAR_NO;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE'] ?? $banner);
    }
    $irBanner = 'INTERROGATION REPORT MATCHED TO SEARCH CRITERIA';
    if ($st7 && ($b7 = sqlsrv_fetch_array($st7, SQLSRV_FETCH_ASSOC))) {
        $irBanner = (string) ($b7['PHONE'] ?? $irBanner);
    }
    $jrmsRows = cdat_sum_fetch_all($st2);
    $irRows = cdat_sum_fetch_all($st9);

    cdat_sum_results_open();
    cdat_sum_report_banner($banner);
    if (empty($jrmsRows)) {
        cdat_sum_empty_state('No JRMS records found.');
    } else {
        cdat_sum_generic_table_open(
            'JRMS Search',
            ['CIN', 'UNIQUE_KEY', 'PRISONERNO', 'IRKEY', 'NAME', 'FATHERSNAME', 'PSARRESTED', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
            'jrms_uniqueness_table',
            'jrms_uniqueness.csv',
            count($jrmsRows)
        );
        foreach ($jrmsRows as $row) {
            $rowPhone = (string) ($row['PHONE'] ?? '');
            $irKey = (string) ($row['IRKEY'] ?? '');
            $irForm = (string) ($row['IRFORM'] ?? '');
            cdat_sum_table_row([
                (string) ($row['CIN'] ?? ''),
                (string) ($row['UNIQUE_KEY'] ?? ''),
                (string) ($row['PRISONERNO'] ?? ''),
                (string) ($row['IRKEY'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHERSNAME'] ?? ''),
                (string) ($row['PSARRESTED'] ?? ''),
                (string) ($row['CRIMENOS'] ?? ''),
                (string) ($row['HEADOFCRIME'] ?? ''),
                ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts2.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($rowPhone)) . '">' . cdat_sum_h($rowPhone) . '</a>', 'class' => 'sum-cell-num'],
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

    if ($st10 && ($miss = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
        $msg = trim((string) ($miss['PHONE'] ?? ''));
        if ($msg !== '') {
            cdat_sum_empty_state($msg);
        }
    }

    cdat_sum_report_banner($irBanner);
    if (empty($irRows)) {
        cdat_sum_empty_state('No IR records found.');
    } else {
        cdat_sum_generic_table_open(
            'IR Match',
            ['IRKEY', 'NAME', 'FATHER_NAME', 'AADHAR_NO', 'PRESENT_ADDRESS', 'CRNO', 'CRIME HEAD', 'POLICE_STATION', 'PHONE'],
            'ir_match_table',
            'jrms_ir_match.csv',
            count($irRows)
        );
        foreach ($irRows as $row) {
            $rowPhone = (string) ($row['PHONE'] ?? '');
            cdat_sum_table_row([
                (string) ($row['IRKEY'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                (string) ($row['AADHAR_NO'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRNO'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts2.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($rowPhone)) . '">' . cdat_sum_h($rowPhone) . '</a>', 'class' => 'sum-cell-num'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if ($st11 && ($missIr = sqlsrv_fetch_array($st11, SQLSRV_FETCH_ASSOC))) {
        $msgIr = trim((string) ($missIr['PHONE'] ?? ''));
        if ($msgIr !== '') {
            cdat_sum_empty_state($msgIr);
        }
    }
    cdat_sum_results_close();
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('JRMS Search Uniqueness');
cdat_sum_page_open();
cdat_sum_search_card(
    'Jail Release Search',
    'Search by name, father name, phone, Aadhaar, or voter ID.',
    'jrms_search_uniqueness.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
