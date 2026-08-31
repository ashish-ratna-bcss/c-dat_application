<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

function ir_kv_table(string $title, array $pairs, string $tableId): void
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
$irKey = trim((string) ($_GET['IRKEY'] ?? ''));
$irNo = trim((string) ($_POST['IR_NO'] ?? ''));
$hasSearch = $irKey !== '' || $irNo !== '';
$fieldsHtml = cdat_sum_field_text('IR_NO', 'IR NO', $irNo !== '' ? $irNo : $irKey, 'CAF', 'ENTER IR NO');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IR');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'IR Search',
            'Look up an interrogation report by IR number or IRKEY.',
            'ir.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }
$conn = get_cdat_pdo();
$irKeyVal = trim((string) ($_GET['IRKEY'] ?? $_POST['IR_NO'] ?? ''));
$irParams = [':irkey' => $irKeyVal];

$irRun = static function (PDO $conn, string $sql, array $params): PDOStatement {
    $st = $conn->prepare($sql);
    $st->execute($params);
    return $st;
};


$sql0="SELECT NAME,FATHER_NAME,IMAGE,B.CCNO FROM ir_particulars A LEFT JOIN image_table B ON A.IRKEY=B.IRKEY WHERE A.IRKEY=:irkey";

$sql1="SELECT DISTINCT 
IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, TO_CHAR((DATE_OF_BIRTH)::timestamp, 'YYYY-MM-DD HH24:MI:SS') DATE_OF_BIRTH, NATIONALITY, 
RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, 
EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, PASSPORT, 
PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, DRIVING_LICENSE, 
OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, COLOUR, TEETH, NOSE, 
BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, LANGUAGE_DIALECT, 
BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, EDUCATION_DETAILS, 
OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY FROM ir_particulars
WHERE IRKEY=:irkey";


$sql2="SELECT DISTINCT RELATIONSHIP RELATION,NAME || ' FATHER_OR_SPOUSE: ' || FATHER_OR_SPOUSE || ' OCCUPATION: ' || OCCUPATION || ' PHONE_NO: ' || PHONE || ' AGE: ' || AGE NAME,PRESENT_ADDRESS ADDRESS,CRIMINAL_BACKGROUND,STATUS FROM FAMILY_HISTORY WHERE IRKEY=:irkey ORDER BY RELATION";

$sql3="SELECT DISTINCT PERIOD_OF_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY=:irkey";

$sql4="SELECT DISTINCT TOWN_CITY_OR_VILLAGE,POLICE_STATION_LIMITS,NAME || ' S/O ' || FATHER_NAME || ' AGE: ' || AGE || ' OCCUPATION: ' || OCCUPATION NAME 
,PHONE,ADDRESS_OF_CONTACT_PERSON ADDRESS FROM LOCAL_CONTACTS_FACILITATORS
WHERE IRKEY=:irkey";

$sql5="SELECT DISTINCT REGULAR_HABITS FROM ir_particulars WHERE IRKEY=:irkey";

$sql6="SELECT DISTINCT INDULGANCE_BEFORE_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY=:irkey";

$sql7="SELECT DISTINCT CRIME_HEAD,SUB_TYPE SUB_HEAD,MO FROM OFFENCE_DETAILS
WHERE IRKEY=:irkey";

$sql8="SELECT DISTINCT REGULAR_RESIDENCE,PREPARATION_OF_OFFENCE,AFTER_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY=:irkey";

$sql9="SELECT DISTINCT PROPERTY_STOLEN,PROPERTY_RECOVERED,RECEIVER_NAME,RECEIVER_ADDRESS,REMARKS FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY=:irkey";

$sql10="SELECT DISTINCT HOW_SHARE_IS_SPENT FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY=:irkey";

$sql11="SELECT DISTINCT DISTRICT,CONFESSED_POLICE_STATION,CONFESSED_CRIME_NO,CONFESSED_YEAR,CONFESSED_SEC_OF_LAW,ASSOCIATES,PROPERTY_STOLEN,PROPERTY_RECOVERED,
REMARKS FROM PREVIOUS_OFFENCE_DETAILS WHERE IRKEY=:irkey";

$sql12="SELECT DISTINCT TO_CHAR(DATE_OF_ARREST::timestamp, 'YYYY-MM-DD HH24:MI:SS') DATE_OF_ARREST,PLACE_OF_ARREST,'CRIME_NO: ' || TO_CHAR(CRIME_NO::timestamp, 'YYYY-MM-DD HH24:MI:SS') || '/' || TO_CHAR(YEAR::timestamp, 'YYYY-MM-DD HH24:MI:SS') || ' SEC_OF_LAW:' || SEC_OF_LAW
CRIME_NO_SEC_OF_LAW,POLICE_STATION,SUB_DIVISION,DISTRICT_OR_UNIT,
ARRESTED_BY,INTERROGATED_BY,OTHERS_WHO_CAN_IDENTIFY FROM OFFENCE_DETAILS
WHERE IRKEY=:irkey";

$sql13="SELECT DISTINCT BRIEF_FACTS1 || '
' || BRIEF_FACTS2 || '
' || BRIEF_FACTS3 BRIEF_FACTS FROM BRIEF_FACTS
WHERE IRKEY=:irkey";

$sql20="select DISTINCT IRKEY,COUNT(*) TOTAL_NBWS_PENDING,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC from nbws_verify_data_important
WHERE CASE_STATUS LIKE '%PENDING%' AND IRKEY=:irkey
GROUP BY IRKEY,FIRST_HEARING_DATE,DECISION_DATE,CASE_STATUS,NEXT_HEARING_DATE,NATURE_OF_DISPOSAL,COURT_NUMBER_AND_JUDGE,STAGE_OF_CASE,
PETITIONER_RESPONDENT,ACT_AND_SEC";

$st0 = $irRun($conn, $sql0, $irParams);
$st1 = $irRun($conn, $sql1, $irParams);
$st2 = $irRun($conn, $sql1, $irParams);
$st3 = $irRun($conn, $sql1, $irParams);
$st4 = $irRun($conn, $sql1, $irParams);
$st5 = $irRun($conn, $sql2, $irParams);
$st6 = $irRun($conn, $sql3, $irParams);
$st7 = $irRun($conn, $sql4, $irParams);
$st8 = $irRun($conn, $sql5, $irParams);
$st9 = $irRun($conn, $sql6, $irParams);
$st10 = $irRun($conn, $sql7, $irParams);
$st11 = $irRun($conn, $sql8, $irParams);
$st12 = $irRun($conn, $sql9, $irParams);
$st13 = $irRun($conn, $sql10, $irParams);
$st14 = $irRun($conn, $sql11, $irParams);
$st15 = $irRun($conn, $sql12, $irParams);
$st20 = $irRun($conn, $sql20, $irParams);
$st16 = $irRun($conn, $sql13, $irParams);

$heroRows = cdat_sum_fetch_all($st0);
$part1 = cdat_sum_fetch_all($st1);
$part2 = cdat_sum_fetch_all($st2);
$part3 = cdat_sum_fetch_all($st3);
$part4 = cdat_sum_fetch_all($st4);
$familyRows = cdat_sum_fetch_all($st5);
$periodRows = cdat_sum_fetch_all($st6);
$localRows = cdat_sum_fetch_all($st7);
$habitRows = cdat_sum_fetch_all($st8);
$indulRows = cdat_sum_fetch_all($st9);
$moRows = cdat_sum_fetch_all($st10);
$shelterRows = cdat_sum_fetch_all($st11);
$dispRows = cdat_sum_fetch_all($st12);
$shareRows = cdat_sum_fetch_all($st13);
$prevRows = cdat_sum_fetch_all($st14);
$arrestRows = cdat_sum_fetch_all($st15);
$nbwsRows = cdat_sum_fetch_all($st20);
$briefRows = cdat_sum_fetch_all($st16);

cdat_sum_results_open();
cdat_sum_report_banner('INTERROGATION REPORT');

if (empty($heroRows)) {
    cdat_sum_empty_state('No IR header record found.');
} else {
    cdat_sum_generic_table_open(
        'Interrogation Report',
        ['NAME', 'FATHER NAME', 'EXCC/CCNO', 'IMAGE'],
        'ir_hero_table',
        'ir_header.csv',
        count($heroRows)
    );
    foreach ($heroRows as $row) {
        cdat_sum_table_row([
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHER_NAME'] ?? ''),
            (string) ($row['CCNO'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 220, 200), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}

$p = $part1[0] ?? null;
if ($p) {
    ir_kv_table('Individual Particulars', [
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
    ], 'ir_particulars_table');
} else {
    cdat_sum_report_banner('INDIVIDUAL PARTICULARS');
    cdat_sum_empty_state('No individual particulars found.');
}

$p2 = $part2[0] ?? null;
if ($p2) {
    ir_kv_table('Unique Identifications (Documents)', [
        'MOBILE' => $p2['MOBILE'] ?? '',
        'EMAIL_ID' => $p2['EMAIL_ID'] ?? '',
        'SOCIAL MEDIA ACCOUNTS' => $p2['SOCIAL_MEDIA_ACCOUNTS'] ?? '',
        'AADHAR_NO' => $p2['AADHAR_NO'] ?? '',
        'RATION CARD NO' => $p2['RATION_CARD_NO'] ?? '',
        'VOTERID' => $p2['VOTERID'] ?? '',
        'PASSPORT' => $p2['PASSPORT'] ?? '',
        'PANCARD' => $p2['PANCARD'] ?? '',
        'ELECTRICITY CONNECTION' => $p2['ELECTRICITY_CONNECTION'] ?? '',
        'GAS_CONNECTION' => $p2['GAS_CONNECTION'] ?? '',
        'VEHICLES' => $p2['VEHICLES'] ?? '',
        'DRIVING LICENSE' => $p2['DRIVING_LICENSE'] ?? '',
        'OTHER ID PROOFS' => $p2['OTHER_ID_PROOFS'] ?? '',
    ], 'ir_ids_table');
} else {
    cdat_sum_report_banner('UNIQUE IDENTIFICATIONS (DOCUMENTS)');
    cdat_sum_empty_state('No identification documents found.');
}

$p3 = $part3[0] ?? null;
if ($p3) {
    ir_kv_table('Physical Features', [
        'SEX' => $p3['SEX'] ?? '',
        'BUILT' => $p3['BUILT'] ?? '',
        'HEIGHT' => $p3['HEIGHT'] ?? '',
        'EYES' => $p3['EYES'] ?? '',
        'HAIR' => $p3['HAIR'] ?? '',
        'FACE' => $p3['FACE'] ?? '',
        'COLOUR' => $p3['COLOUR'] ?? '',
        'TEETH' => $p3['TEETH'] ?? '',
        'NOSE' => $p3['NOSE'] ?? '',
        'BEARD' => $p3['BEARD'] ?? '',
        'MUSTACHES' => $p3['MUSTACHES'] ?? '',
        'EAR' => $p3['EAR'] ?? '',
        'IDENTIFICATION_MARKS' => $p3['IDENTIFICATION_MARKS'] ?? '',
        'DEFORMITIES_PECULIARITIES' => $p3['DEFORMITIES_PECULIARITIES'] ?? '',
        'LANGUAGE_DIALECT' => $p3['LANGUAGE_DIALECT'] ?? '',
        'BURN_MARKS' => $p3['BURN_MARKS'] ?? '',
        'LEUCODEMA' => $p3['LEUCODEMA'] ?? '',
        'MOLE' => $p3['MOLE'] ?? '',
        'SCAR' => $p3['SCAR'] ?? '',
        'TATTOO' => $p3['TATTOO'] ?? '',
    ], 'ir_features_table');
} else {
    cdat_sum_report_banner('PHYSICAL FEATURES');
    cdat_sum_empty_state('No physical features found.');
}

$p4 = $part4[0] ?? null;
if ($p4) {
    ir_kv_table('Socio/Economic Profile', [
        'LIVING_STATUS' => $p4['LIVING_STATUS'] ?? '',
        'MARITAL_STATUS' => $p4['MARITAL_STATUS'] ?? '',
        'EDUCATION_DETAILS' => $p4['EDUCATION_DETAILS'] ?? '',
        'OCCUPATION' => $p4['OCCUPATION'] ?? '',
        'INCOME_GROUP' => $p4['INCOME_GROUP'] ?? '',
        'REGULAR_HABITS' => $p4['REGULAR_HABITS'] ?? '',
        'CATEGORY' => $p4['CATEGORY'] ?? '',
    ], 'ir_socio_table');
} else {
    cdat_sum_report_banner('SOCIO/ECONOMIC PROFILE');
    cdat_sum_empty_state('No socio/economic profile found.');
}

if (empty($familyRows)) {
    cdat_sum_report_banner('FAMILY HISTORY');
    cdat_sum_empty_state('No family history found.');
} else {
    cdat_sum_generic_table_open(
        'Family History',
        ['RELATION', 'NAME', 'ADDRESS', 'CRIMINAL_BACKGROUND', 'STATUS'],
        'ir_family_table',
        'ir_family.csv',
        count($familyRows)
    );
    foreach ($familyRows as $row) {
        cdat_sum_table_row([
            (string) ($row['RELATION'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['CRIMINAL_BACKGROUND'] ?? ''),
            (string) ($row['STATUS'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($periodRows)) {
    cdat_sum_report_banner('PERIOD OF OFFENCE');
    cdat_sum_empty_state('No period of offence found.');
} else {
    $pairs = [];
    foreach ($periodRows as $i => $row) {
        $label = count($periodRows) > 1 ? ('PERIOD OF OFFENCE ' . ($i || 1)) : 'PERIOD OF OFFENCE';
        $pairs[$label] = $row['PERIOD_OF_OFFENCE'] ?? '';
    }
    ir_kv_table('Period of Offence', $pairs, 'ir_period_table');
}

if (empty($localRows)) {
    cdat_sum_report_banner('LOCAL CONTACTS/FACILITATORS');
    cdat_sum_empty_state('No local contacts found.');
} else {
    cdat_sum_generic_table_open(
        'Local Contacts / Facilitators',
        ['TOWN_CITY_OR_VILLAGE', 'POLICE_STATION_LIMITS', 'NAME', 'PHONE', 'ADDRESS'],
        'ir_local_table',
        'ir_local.csv',
        count($localRows)
    );
    foreach ($localRows as $row) {
        cdat_sum_table_row([
            (string) ($row['TOWN_CITY_OR_VILLAGE'] ?? ''),
            (string) ($row['POLICE_STATION_LIMITS'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($habitRows)) {
    cdat_sum_report_banner('REGULAR HABITS');
    cdat_sum_empty_state('No regular habits found.');
} else {
    $pairs = [];
    foreach ($habitRows as $i => $row) {
        $label = count($habitRows) > 1 ? ('REGULAR HABITS ' . ($i || 1)) : 'REGULAR HABITS';
        $pairs[$label] = $row['REGULAR_HABITS'] ?? '';
    }
    ir_kv_table('Regular Habits', $pairs, 'ir_habits_table');
}

if (empty($indulRows)) {
    cdat_sum_report_banner('INDULGANCE BEFORE OFFENCE');
    cdat_sum_empty_state('No indulgence details found.');
} else {
    $pairs = [];
    foreach ($indulRows as $i => $row) {
        $label = count($indulRows) > 1 ? ('INDULGANCE BEFORE OFFENCE ' . ($i || 1)) : 'INDULGANCE BEFORE OFFENCE';
        $pairs[$label] = $row['INDULGANCE_BEFORE_OFFENCE'] ?? '';
    }
    ir_kv_table('Indulgence Before Offence', $pairs, 'ir_indul_table');
}

if (empty($moRows)) {
    cdat_sum_report_banner('MODUS OPERANDI');
    cdat_sum_empty_state('No modus operandi found.');
} else {
    cdat_sum_generic_table_open(
        'Modus Operandi',
        ['CRIME_HEAD', 'SUB_HEAD', 'MO'],
        'ir_mo_table',
        'ir_mo.csv',
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

if (empty($shelterRows)) {
    cdat_sum_report_banner('SHELTER');
    cdat_sum_empty_state('No shelter details found.');
} else {
    cdat_sum_generic_table_open(
        'Shelter',
        ['REGULAR RESIDENCE', 'PREPARATION OF OFFENCE', 'AFTER OFFENCE'],
        'ir_shelter_table',
        'ir_shelter.csv',
        count($shelterRows)
    );
    foreach ($shelterRows as $row) {
        cdat_sum_table_row([
            ['html' => cdat_sum_address_lines((string) ($row['REGULAR_RESIDENCE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['PREPARATION_OF_OFFENCE'] ?? ''),
            (string) ($row['AFTER_OFFENCE'] ?? ''),
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
        'ir_disposal_table',
        'ir_disposal.csv',
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

if (empty($shareRows)) {
    cdat_sum_report_banner('HOW SHARE OF AMOUNT SPENT');
    cdat_sum_empty_state('No share-spent details found.');
} else {
    $pairs = [];
    foreach ($shareRows as $i => $row) {
        $label = count($shareRows) > 1 ? ('HOW SHARE OF AMOUNT SPENT ' . ($i || 1)) : 'HOW SHARE OF AMOUNT SPENT';
        $pairs[$label] = $row['HOW_SHARE_IS_SPENT'] ?? '';
    }
    ir_kv_table('How Share of Amount Spent', $pairs, 'ir_share_table');
}

if (empty($prevRows)) {
    cdat_sum_report_banner('CASES CONFESSED / PREVIOUS OFFENCE DETAILS');
    cdat_sum_empty_state('No previous offence details found.');
} else {
    cdat_sum_generic_table_open(
        'Cases Confessed / Previous Offence Details',
        ['DISTRICT', 'CONFESSED POLICE STATION', 'CONFESSED CRIME NO', 'CONFESSED YEAR', 'CONFESSED SEC OF LAW', 'ASSOCIATES', 'PROPERTY STOLEN', 'PROPERTY RECOVERED', 'REMARKS'],
        'ir_prev_table',
        'ir_previous.csv',
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
            (string) ($row['PROPERTY_STOLEN'] ?? ''),
            (string) ($row['PROPERTY_RECOVERED'] ?? ''),
            (string) ($row['REMARKS'] ?? ''),
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
        ['DATE OF ARREST', 'PLACE OF ARREST', 'CRIME NO AND SEC OF LAW', 'POLICE STATION', 'SUB DIVISION', 'DIST/UNIT', 'ARRESTED BY', 'INTERROGATED BY', 'OTHERS WHO CAN IDENTIFY'],
        'ir_arrest_table',
        'ir_arrest.csv',
        count($arrestRows)
    );
    foreach ($arrestRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['DATE_OF_ARREST'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['PLACE_OF_ARREST'] ?? ''),
            (string) ($row['CRIME_NO_SEC_OF_LAW'] ?? ''),
            (string) ($row['POLICE_STATION'] ?? ''),
            (string) ($row['SUB_DIVISION'] ?? ''),
            (string) ($row['DISTRICT_OR_UNIT'] ?? ''),
            (string) ($row['ARRESTED_BY'] ?? ''),
            (string) ($row['INTERROGATED_BY'] ?? ''),
            (string) ($row['OTHERS_WHO_CAN_IDENTIFY'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($nbwsRows)) {
    cdat_sum_report_banner('NBWS PENDING');
    cdat_sum_empty_state('No pending NBWs found.');
} else {
    cdat_sum_generic_table_open(
        'NBWs Pending',
        ['IRKEY', 'TOTAL_NBWS_PENDING', 'FIRST_HEARING_DATE', 'DECISION_DATE', 'CASE_STATUS', 'NEXT_HEARING_DATE', 'NATURE_OF_DISPOSAL', 'COURT_NUMBER_AND_JUDGE', 'STAGE_OF_CASE', 'PETITIONER_RESPONDENT', 'ACT_AND_SEC'],
        'ir_nbws_table',
        'ir_nbws.csv',
        count($nbwsRows)
    );
    foreach ($nbwsRows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['IRKEY'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['TOTAL_NBWS_PENDING'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['FIRST_HEARING_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            ['text' => (string) ($row['DECISION_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['CASE_STATUS'] ?? ''),
            ['text' => (string) ($row['NEXT_HEARING_DATE'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['NATURE_OF_DISPOSAL'] ?? ''),
            (string) ($row['COURT_NUMBER_AND_JUDGE'] ?? ''),
            (string) ($row['STAGE_OF_CASE'] ?? ''),
            (string) ($row['PETITIONER_RESPONDENT'] ?? ''),
            (string) ($row['ACT_AND_SEC'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($briefRows)) {
    cdat_sum_report_banner('BRIEF FACTS');
    cdat_sum_empty_state('No brief facts found.');
} else {
    $pairs = [];
    foreach ($briefRows as $i => $row) {
        $label = count($briefRows) > 1 ? ('BRIEF FACTS ' . ($i || 1)) : 'BRIEF FACTS';
        $pairs[$label] = $row['BRIEF_FACTS'] ?? '';
    }
    ir_kv_table('Brief Facts', $pairs, 'ir_brief_table');
}

cdat_sum_results_close();
$conn = null;

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IR');
cdat_sum_page_open();
cdat_sum_search_card(
    'IR Search',
    'Look up an interrogation report by IR number or IRKEY.',
    'ir.php',
    cdat_sum_field_text('IR_NO', 'IR NO', '', 'CAF', 'ENTER IR NO'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
