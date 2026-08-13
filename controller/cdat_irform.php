<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

function cdat_irform_kv_table(string $title, array $pairs, string $tableId): void
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
    layout_begin('CDAT Irform');
    cdat_sum_page_open();
    cdat_sum_back_link('cdatcnts.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "IRFORMS");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$number = $_GET['OTHER_NO'];

$sql0000 = "SELECT DISTINCT *  FROM IRFORMS..IR_PARTICULARS WHERE MOBILE LIKE '%$number%'";

$st0000 = sqlsrv_query($conn, $sql0000);

$st1234 = sqlsrv_num_rows($st0000);

if (!$st1234 = 1) {
    $sql000000 = "SELECT  '*** $number IS NOT LINKED WITH ANY IR ***' as CNTS ";
    $st000000 = sqlsrv_query($conn, $sql000000);
    $msg = '*** ' . $number . ' IS NOT LINKED WITH ANY IR ***';
    if ($st000000 && ($row = sqlsrv_fetch_array($st000000, SQLSRV_FETCH_ASSOC))) {
        $msg = (string) ($row['CNTS'] ?? $msg);
    }
    cdat_sum_results_open();
    cdat_sum_empty_state($msg);
    cdat_sum_results_close();
} else {
    $sql00 = "SELECT DISTINCT max(IRKEY) IRKEY INTO #TEMP FROM IR_PARTICULARS WHERE MOBILE LIKE '%$number%'";

    $sql0 = "SELECT NAME,FATHER_NAME,IMAGE,B.CCNO FROM IR_PARTICULARS A LEFT JOIN IMAGE_TABLE B ON 
A.IRKEY=B.IRKEY WHERE A.IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql1 = "SELECT DISTINCT 
IRKEY, NAME, ALIAS_NAME, FATHER_NAME, AGE, CONVERT(VARCHAR,DATE_OF_BIRTH,20) DATE_OF_BIRTH, NATIONALITY, 
RELIGION, CASTE, COMMUNITY, PRESENT_ADDRESS, PERMANENT_ADDRESS, MOBILE, 
EMAIL_ID, SOCIAL_MEDIA_ACCOUNTS, AADHAR_NO, RATION_CARD_NO, VOTERID, PASSPORT, 
PANCARD, ELECTRICITY_CONNECTION, GAS_CONNECTION, VEHICLES, DRIVING_LICENSE, 
OTHER_ID_PROOFS, SEX, BUILT, HEIGHT, EYES, HAIR, FACE, COLOUR, TEETH, NOSE, 
BEARD, MUSTACHES, EAR, IDENTIFICATION_MARKS, DEFORMITIES_PECULIARITIES, LANGUAGE_DIALECT, 
BURN_MARKS, LEUCODEMA, MOLE, SCAR, TATTOO, LIVING_STATUS, MARITAL_STATUS, EDUCATION_DETAILS, 
OCCUPATION, INCOME_GROUP, REGULAR_HABITS, CATEGORY FROM IRFORMS..IR_PARTICULARS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql2 = "SELECT DISTINCT RELATIONSHIP RELATION,NAME+' FATHER_OR_SPOUSE: '+FATHER_OR_SPOUSE+' OCCUPATION: '+OCCUPATION
+' PHONE_NO: '+PHONE+' AGE: '+AGE NAME,PRESENT_ADDRESS ADDRESS,CRIMINAL_BACKGROUND,STATUS FROM FAMILY_HISTORY WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP) ORDER BY RELATION";

    $sql3 = "SELECT DISTINCT PERIOD_OF_OFFENCE FROM OFFENCE_DETAILS WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql4 = "SELECT DISTINCT TOWN_CITY_OR_VILLAGE,POLICE_STATION_LIMITS,NAME+' S/O '+FATHER_NAME+' AGE: '+AGE+' OCCUPATION: '+OCCUPATION NAME 
,PHONE,ADDRESS_OF_CONTACT_PERSON ADDRESS FROM LOCAL_CONTACTS_FACILITATORS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql5 = "SELECT DISTINCT REGULAR_HABITS FROM IR_PARTICULARS WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql6 = "SELECT DISTINCT INDULGANCE_BEFORE_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql7 = "SELECT DISTINCT CRIME_HEAD,SUB_TYPE SUB_HEAD,MO FROM OFFENCE_DETAILS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql8 = "SELECT DISTINCT REGULAR_RESIDENCE,PREPARATION_OF_OFFENCE,AFTER_OFFENCE FROM OFFENCE_DETAILS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql9 = "SELECT DISTINCT PROPERTY_STOLEN,PROPERTY_RECOVERED,RECEIVER_NAME,RECEIVER_ADDRESS,REMARKS FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql10 = "SELECT DISTINCT HOW_SHARE_IS_SPENT FROM DISPOSAL_OF_PROPERTY
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql11 = "SELECT DISTINCT DISTRICT,CONFESSED_POLICE_STATION,CONFESSED_CRIME_NO,CONFESSED_SEC_OF_LAW,ASSOCIATES,PROPERTY_STOLEN,PROPERTY_RECOVERED,
REMARKS FROM PREVIOUS_OFFENCE_DETAILS WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql12 = "SELECT DISTINCT CONVERT(VARCHAR,DATE_OF_ARREST) DATE_OF_ARREST,PLACE_OF_ARREST,'CRIME_NO: '+CONVERT(VARCHAR,CRIME_NO)+'/'+CONVERT(VARCHAR,YEAR)+' SEC_OF_LAW:'+SEC_OF_LAW
[CRIME_NO_SEC_OF_LAW],POLICE_STATION,SUB_DIVISION,DISTRICT_OR_UNIT,
ARRESTED_BY,INTERROGATED_BY,OTHERS_WHO_CAN_IDENTIFY FROM OFFENCE_DETAILS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    $sql13 = "SELECT DISTINCT BRIEF_FACTS1+'
'+BRIEF_FACTS2+'
'+BRIEF_FACTS3 BRIEF_FACTS FROM BRIEF_FACTS
WHERE IRKEY=(SELECT DISTINCT IRKEY FROM #TEMP)";

    sqlsrv_query($conn, $sql00);
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
    $st16 = sqlsrv_query($conn, $sql13);

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
    $briefRows = cdat_sum_fetch_all($st16);

    cdat_sum_results_open();
    cdat_sum_report_banner('INTERROGATION REPORT');

    if (empty($heroRows)) {
        cdat_sum_empty_state('No IR header record found.');
    } else {
        cdat_sum_generic_table_open(
            'Interrogation Report',
            ['NAME', 'FATHER NAME', 'EXCC/CCNO', 'IMAGE'],
            'irform_hero_table',
            'irform_header.csv',
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
        cdat_irform_kv_table('Individual Particulars', [
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
        ], 'irform_particulars_table');
    } else {
        cdat_sum_report_banner('INDIVIDUAL PARTICULARS');
        cdat_sum_empty_state('No individual particulars found.');
    }

    $p2 = $part2[0] ?? null;
    if ($p2) {
        cdat_irform_kv_table('Unique Identifications (Documents)', [
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
        ], 'irform_ids_table');
    } else {
        cdat_sum_report_banner('UNIQUE IDENTIFICATIONS (DOCUMENTS)');
        cdat_sum_empty_state('No identification documents found.');
    }

    $p3 = $part3[0] ?? null;
    if ($p3) {
        cdat_irform_kv_table('Physical Features', [
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
        ], 'irform_features_table');
    } else {
        cdat_sum_report_banner('PHYSICAL FEATURES');
        cdat_sum_empty_state('No physical features found.');
    }

    $p4 = $part4[0] ?? null;
    if ($p4) {
        cdat_irform_kv_table('Socio/Economic Profile', [
            'LIVING_STATUS' => $p4['LIVING_STATUS'] ?? '',
            'MARITAL_STATUS' => $p4['MARITAL_STATUS'] ?? '',
            'EDUCATION_DETAILS' => $p4['EDUCATION_DETAILS'] ?? '',
            'OCCUPATION' => $p4['OCCUPATION'] ?? '',
            'INCOME_GROUP' => $p4['INCOME_GROUP'] ?? '',
            'REGULAR_HABITS' => $p4['REGULAR_HABITS'] ?? '',
            'CATEGORY' => $p4['CATEGORY'] ?? '',
        ], 'irform_socio_table');
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
            'irform_family_table',
            'irform_family.csv',
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
            $label = count($periodRows) > 1 ? ('PERIOD OF OFFENCE ' . ($i + 1)) : 'PERIOD OF OFFENCE';
            $pairs[$label] = $row['PERIOD_OF_OFFENCE'] ?? '';
        }
        cdat_irform_kv_table('Period of Offence', $pairs, 'irform_period_table');
    }

    if (empty($localRows)) {
        cdat_sum_report_banner('LOCAL CONTACTS/FACILITATORS');
        cdat_sum_empty_state('No local contacts found.');
    } else {
        cdat_sum_generic_table_open(
            'Local Contacts / Facilitators',
            ['TOWN_CITY_OR_VILLAGE', 'POLICE_STATION_LIMITS', 'NAME', 'PHONE', 'ADDRESS'],
            'irform_local_table',
            'irform_local.csv',
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
            $label = count($habitRows) > 1 ? ('REGULAR HABITS ' . ($i + 1)) : 'REGULAR HABITS';
            $pairs[$label] = $row['REGULAR_HABITS'] ?? '';
        }
        cdat_irform_kv_table('Regular Habits', $pairs, 'irform_habits_table');
    }

    if (empty($indulRows)) {
        cdat_sum_report_banner('INDULGANCE BEFORE OFFENCE');
        cdat_sum_empty_state('No indulgence details found.');
    } else {
        $pairs = [];
        foreach ($indulRows as $i => $row) {
            $label = count($indulRows) > 1 ? ('INDULGANCE BEFORE OFFENCE ' . ($i + 1)) : 'INDULGANCE BEFORE OFFENCE';
            $pairs[$label] = $row['INDULGANCE_BEFORE_OFFENCE'] ?? '';
        }
        cdat_irform_kv_table('Indulgence Before Offence', $pairs, 'irform_indul_table');
    }

    if (empty($moRows)) {
        cdat_sum_report_banner('MODUS OPERANDI');
        cdat_sum_empty_state('No modus operandi found.');
    } else {
        cdat_sum_generic_table_open(
            'Modus Operandi',
            ['CRIME_HEAD', 'SUB_HEAD', 'MO'],
            'irform_mo_table',
            'irform_mo.csv',
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
            'irform_shelter_table',
            'irform_shelter.csv',
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
            'irform_disposal_table',
            'irform_disposal.csv',
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
            $label = count($shareRows) > 1 ? ('HOW SHARE OF AMOUNT SPENT ' . ($i + 1)) : 'HOW SHARE OF AMOUNT SPENT';
            $pairs[$label] = $row['HOW_SHARE_IS_SPENT'] ?? '';
        }
        cdat_irform_kv_table('How Share of Amount Spent', $pairs, 'irform_share_table');
    }

    if (empty($prevRows)) {
        cdat_sum_report_banner('CASES CONFESSED / PREVIOUS OFFENCE DETAILS');
        cdat_sum_empty_state('No previous offence details found.');
    } else {
        cdat_sum_generic_table_open(
            'Cases Confessed / Previous Offence Details',
            ['DISTRICT', 'CONFESSED POLICE STATION', 'CONFESSED CRIME NO', 'CONFESSED SEC OF LAW', 'ASSOCIATES', 'PROPERTY STOLEN', 'PROPERTY RECOVERED', 'REMARKS'],
            'irform_prev_table',
            'irform_previous.csv',
            count($prevRows)
        );
        foreach ($prevRows as $row) {
            cdat_sum_table_row([
                (string) ($row['DISTRICT'] ?? ''),
                (string) ($row['CONFESSED_POLICE_STATION'] ?? ''),
                (string) ($row['CONFESSED_CRIME_NO'] ?? ''),
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
            'irform_arrest_table',
            'irform_arrest.csv',
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

    if (empty($briefRows)) {
        cdat_sum_report_banner('BRIEF FACTS');
        cdat_sum_empty_state('No brief facts found.');
    } else {
        $pairs = [];
        foreach ($briefRows as $i => $row) {
            $label = count($briefRows) > 1 ? ('BRIEF FACTS ' . ($i + 1)) : 'BRIEF FACTS';
            $pairs[$label] = $row['BRIEF_FACTS'] ?? '';
        }
        cdat_irform_kv_table('Brief Facts', $pairs, 'irform_brief_table');
    }

    cdat_sum_results_close();
}

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
