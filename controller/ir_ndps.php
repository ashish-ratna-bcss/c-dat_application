<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

function ir_ndps_kv_table(string $title, array $pairs, string $tableId): void
{
    cdat_sum_generic_table_open($title, ['Field', 'Value'], $tableId, $tableId . '.csv', count($pairs));
    foreach ($pairs as $label => $value) {
        $text = (string) $value;
        $isAddr = stripos((string) $label, 'ADDRESS') !== false;
        cdat_sum_table_row([
            (string) $label,
            $isAddr
                ? ['html' => cdat_sum_address_lines($text) ?: '—', 'class' => 'sum-address-cell']
                : $text,
        ]);
    }
    cdat_sum_generic_table_close();
}

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('IR Ndps');
    cdat_sum_page_open();
    cdat_sum_back_link('bulk_irkey_ndps.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "IRFORMS");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$number = $_GET['IRKEY'];

$sql0 = "SELECT NAME,FATHER_NAME,IMAGE,B.CCNO FROM IR_PARTICULARS A LEFT JOIN IMAGE_TABLE B ON A.IRKEY=B.IRKEY WHERE A.IRKEY='$number'";

$sql1 = "SELECT DISTINCT 
IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, CONVERT(VARCHAR,DATE_OF_BIRTH,20) DATE_OF_BIRTH, NATIONALITY, 
RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, 
EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, PASSPORT, 
PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, DRIVING_LICENSE, 
OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, COLOUR, TEETH, NOSE, 
BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, LANGUAGE_DIALECT, 
BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, EDUCATION_DETAILS, 
OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY FROM IRFORMS..IR_PARTICULARS
WHERE IRKEY='$number'";

$sql2 = "SELECT DISTINCT RELATIONSHIP RELATION,NAME+' FATHER_OR_SPOUSE: '+FATHER_OR_SPOUSE+' OCCUPATION: '+OCCUPATION
+' PHONE_NO: '+PHONE+' AGE: '+AGE NAME,PRESENT_ADDRESS ADDRESS,CRIMINAL_BACKGROUND,STATUS FROM FAMILY_HISTORY WHERE IRKEY='$number' ORDER BY RELATION";

$sql3 = "SELECT DISTINCT PERIOD_OF_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY='$number'";

$sql4 = "SELECT DISTINCT TOWN_CITY_OR_VILLAGE,POLICE_STATION_LIMITS,NAME+' S/O '+FATHER_NAME+' AGE: '+AGE+' OCCUPATION: '+OCCUPATION NAME 
,PHONE,ADDRESS_OF_CONTACT_PERSON ADDRESS FROM LOCAL_CONTACTS_FACILITATORS
WHERE IRKEY='$number'";

$sql5 = "SELECT DISTINCT REGULAR_HABITS FROM IR_PARTICULARS WHERE IRKEY='$number'";

$sql6 = "SELECT DISTINCT INDULGANCE_BEFORE_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql7 = "SELECT DISTINCT CRIME_HEAD,SUB_TYPE SUB_HEAD,MO FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql8 = "SELECT DISTINCT REGULAR_RESIDENCE,PREPARATION_OF_OFFENCE,AFTER_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql9 = "SELECT DISTINCT PROPERTY_STOLEN,PROPERTY_RECOVERED,RECEIVER_NAME,RECEIVER_ADDRESS,REMARKS FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY='$number'";

$sql10 = "SELECT DISTINCT HOW_SHARE_IS_SPENT FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY='$number'";

$sql11 = "SELECT DISTINCT DISTRICT,CONFESSED_POLICE_STATION,CONFESSED_CRIME_NO,CONFESSED_YEAR,CONFESSED_SEC_OF_LAW,ASSOCIATES,PROPERTY_STOLEN,PROPERTY_RECOVERED,
REMARKS FROM PREVIOUS_OFFENCE_DETAILS WHERE IRKEY='$number'";

$sql12 = "SELECT DISTINCT CONVERT(VARCHAR,DATE_OF_ARREST) DATE_OF_ARREST,PLACE_OF_ARREST,'CRIME_NO: '+CONVERT(VARCHAR,CRIME_NO)+'/'+CONVERT(VARCHAR,YEAR)+' SEC_OF_LAW:'+SEC_OF_LAW
[CRIME_NO_SEC_OF_LAW],POLICE_STATION,SUB_DIVISION,DISTRICT_OR_UNIT,
ARRESTED_BY,INTERROGATED_BY,OTHERS_WHO_CAN_IDENTIFY FROM OFFENCE_DETAILS
WHERE IRKEY='$number'";

$sql13 = "SELECT DISTINCT BRIEF_FACTS1+'
'+BRIEF_FACTS2+'
'+BRIEF_FACTS3 BRIEF_FACTS FROM BRIEF_FACTS
WHERE IRKEY='$number'";

$sql20 = "select DISTINCT IRKEY,COUNT(*) TOTAL_NBWS_PENDING,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC from nbws_verify_data_important
WHERE CASE_STATUS LIKE '%PENDING%' AND IRKEY='$number'
GROUP BY IRKEY,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC";

$st0 = sqlsrv_query($conn, $sql0);
$st1 = sqlsrv_query($conn, $sql1);
$st2 = sqlsrv_query($conn, $sql1);
$st3 = sqlsrv_query($conn, $sql1);
$st4 = sqlsrv_query($conn, $sql1);
$st5 = sqlsrv_query($conn, $sql2);
$st6 = sqlsrv_query($conn, $sql3);
$st7 = sqlsrv_query($conn, $sql4);
$st8 = sqlsrv_query($conn, $sql5);
$st9 = sqlsrv_query($conn, $sql6);
$st10 = sqlsrv_query($conn, $sql7);
$st11 = sqlsrv_query($conn, $sql8);
$st12 = sqlsrv_query($conn, $sql9);
$st13 = sqlsrv_query($conn, $sql10);
$st14 = sqlsrv_query($conn, $sql11);
$st15 = sqlsrv_query($conn, $sql12);
$st20 = sqlsrv_query($conn, $sql20);
$st16 = sqlsrv_query($conn, $sql13);

$heroRows = cdat_sum_fetch_all($st0);
$part1 = cdat_sum_fetch_all($st1);
$part2 = cdat_sum_fetch_all($st2);
$part4 = cdat_sum_fetch_all($st4);
$moRows = cdat_sum_fetch_all($st10);
$dispRows = cdat_sum_fetch_all($st12);
$prevRows = cdat_sum_fetch_all($st14);
$arrestRows = cdat_sum_fetch_all($st15);
cdat_sum_fetch_all($st3);
cdat_sum_fetch_all($st5);
cdat_sum_fetch_all($st6);
cdat_sum_fetch_all($st7);
cdat_sum_fetch_all($st8);
cdat_sum_fetch_all($st9);
cdat_sum_fetch_all($st11);
cdat_sum_fetch_all($st13);
cdat_sum_fetch_all($st20);
cdat_sum_fetch_all($st16);

cdat_sum_results_open();
cdat_sum_report_banner('NDPS ACT SUSPECT PROFILE');

if (empty($heroRows)) {
    cdat_sum_empty_state('No NDPS header record found.');
} else {
    cdat_sum_generic_table_open(
        'NDPS Suspect Profile',
        ['NAME', 'FATHER NAME', 'IMAGE'],
        'ndps_hero_table',
        'ndps_header.csv',
        count($heroRows)
    );
    foreach ($heroRows as $row) {
        cdat_sum_table_row([
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHER_NAME'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 220, 200), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}

$p = $part1[0] ?? null;
if ($p) {
    ir_ndps_kv_table('Individual Particulars', [
        'IRKEY' => $p['IRKEY'] ?? '',
        'NAME' => $p['NAME'] ?? '',
        'ALIAS_NAME' => $p['ALIAS_NAME'] ?? '',
        'FATHER_NAME' => $p['FATHER_NAME'] ?? '',
        'AGE' => $p['AGE'] ?? '',
        'DATE_OF_BIRTH' => $p['DATE_OF_BIRTH'] ?? '',
        'NATIONALITY' => $p['NATIONALITY'] ?? '',
        'RELIGION' => $p['RELIGION'] ?? '',
        'CASTE' => $p['CASTE'] ?? '',
        'COMMUNITY' => $p['COMMUNITY'] ?? '',
        'PRESENT ADDRESS' => $p['PRESENT_ADDRESS'] ?? '',
        'PERMANENT ADDRESS' => $p['PERMANENT_ADDRESS'] ?? '',
    ], 'ndps_particulars_table');
} else {
    cdat_sum_report_banner('INDIVIDUAL PARTICULARS');
    cdat_sum_empty_state('No individual particulars found.');
}

$p2 = $part2[0] ?? null;
if ($p2) {
    $idText = 'MOBILE NO :' . ($p2['MOBILE'] ?? '') . ' && EMAIL ID: ' . ($p2['EMAIL_ID'] ?? '')
        . ' && AADHAR NO: ' . ($p2['AADHAR_NO'] ?? '') . ' && RATION CARD NO: ' . ($p2['RATION_CARD_NO'] ?? '')
        . ' && VOTER ID: ' . ($p2['VOTERID'] ?? '') . ' && PASSPORT: ' . ($p2['PASSPORT'] ?? '')
        . ' && PANCAR: ' . ($p2['PANCARD'] ?? '') . ' && GAS CONNECTION: ' . ($p2['GAS_CONNECTION'] ?? '')
        . '   && DRIVING LICENSE: ' . ($p2['DRIVING_LICENSE'] ?? '');
    ir_ndps_kv_table('Unique Identifications / ID Proofs', [
        'IDPROOFS' => $idText,
    ], 'ndps_ids_table');
} else {
    cdat_sum_report_banner('UNIQUE IDENTIFICATIONS / IDPROOFS');
    cdat_sum_empty_state('No identification documents found.');
}

$p4 = $part4[0] ?? null;
if ($p4) {
    ir_ndps_kv_table('Socio/Economic Profile', [
        'LIVING_STATUS' => $p4['LIVING_STATUS'] ?? '',
        'MARITAL_STATUS' => $p4['MARITAL_STATUS'] ?? '',
        'EDUCATION_DETAILS' => $p4['EDUCATION_DETAILS'] ?? '',
        'OCCUPATION' => $p4['OCCUPATION'] ?? '',
        'INCOME_GROUP' => $p4['INCOME_GROUP'] ?? '',
        'REGULAR_HABITS' => $p4['REGULAR_HABITS'] ?? '',
        'CATEGORY' => $p4['CATEGORY'] ?? '',
    ], 'ndps_socio_table');
} else {
    cdat_sum_report_banner('SOCIO/ECONOMIC PROFILE');
    cdat_sum_empty_state('No socio/economic profile found.');
}

if (empty($moRows)) {
    cdat_sum_report_banner('MODUS OPERANDI');
    cdat_sum_empty_state('No modus operandi found.');
} else {
    cdat_sum_generic_table_open(
        'Modus Operandi',
        ['CRIME_HEAD', 'SUB_HEAD', 'MO'],
        'ndps_mo_table',
        'ndps_mo.csv',
        count($moRows)
    );
    foreach ($moRows as $row) {
        cdat_sum_table_row([
            (string) ($row['CRIME_HEAD'] ?? ''),
            (string) ($row['SUB_HEAD'] ?? ''),
            (string) ($row['MO'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($dispRows)) {
    cdat_sum_report_banner('DISPOSAL OF PROPERTY');
    cdat_sum_empty_state('No property disposal records found.');
} else {
    cdat_sum_generic_table_open(
        'Disposal of Property',
        ['PROPERTY STOLEN', 'PROPERTY RECOVERED', 'RECEIVER NAME', 'RECEIVER ADDRESS', 'REMARKS'],
        'ndps_disposal_table',
        'ndps_disposal.csv',
        count($dispRows)
    );
    foreach ($dispRows as $row) {
        cdat_sum_table_row([
            (string) ($row['PROPERTY_STOLEN'] ?? ''),
            (string) ($row['PROPERTY_RECOVERED'] ?? ''),
            (string) ($row['RECEIVER_NAME'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['RECEIVER_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['REMARKS'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($prevRows)) {
    cdat_sum_report_banner('CASES CONFESSED / PREVIOUS OFFENCE DETAILS');
    cdat_sum_empty_state('No previous offence details found.');
} else {
    cdat_sum_generic_table_open(
        'Cases Confessed / Previous Offence Details',
        ['DIST', 'CONFESSED POLICE STATION', 'CONFESSED CRIME NO', 'CONFESSED YEAR', 'CONFESSED SEC OF LAW', 'ASSOCIATES', 'PROPERTY RECOVERED'],
        'ndps_prev_table',
        'ndps_previous.csv',
        count($prevRows)
    );
    foreach ($prevRows as $row) {
        cdat_sum_table_row([
            (string) ($row['DISTRICT'] ?? ''),
            (string) ($row['CONFESSED_POLICE_STATION'] ?? ''),
            (string) ($row['CONFESSED_CRIME_NO'] ?? ''),
            (string) ($row['CONFESSED_YEAR'] ?? ''),
            (string) ($row['CONFESSED_SEC_OF_LAW'] ?? ''),
            (string) ($row['ASSOCIATES'] ?? ''),
            (string) ($row['PROPERTY_RECOVERED'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($arrestRows)) {
    cdat_sum_report_banner('ARREST PARTICULARS');
    cdat_sum_empty_state('No arrest particulars found.');
} else {
    cdat_sum_generic_table_open(
        'Arrest Particulars',
        ['DATE OF ARREST', 'PLACE OF ARREST', 'CRIME NO & SEC OF LAW', 'POLICE STATION', 'SUB DIVISION', 'ARRESTED BY', 'OTHERS WHO CAN IDENTIFY'],
        'ndps_arrest_table',
        'ndps_arrest.csv',
        count($arrestRows)
    );
    foreach ($arrestRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['DATE_OF_ARREST'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['PLACE_OF_ARREST'] ?? ''),
            (string) ($row['CRIME_NO_SEC_OF_LAW'] ?? ''),
            (string) ($row['POLICE_STATION'] ?? ''),
            (string) ($row['SUB_DIVISION'] ?? ''),
            (string) ($row['ARRESTED_BY'] ?? ''),
            (string) ($row['OTHERS_WHO_CAN_IDENTIFY'] ?? ''),
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
