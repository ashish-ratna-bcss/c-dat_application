<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

function offender_kv_table(string $title, array $pairs, string $tableId): void
{
    cdat_sum_generic_table_open($title, ['Field', 'Value'], $tableId, $tableId . '.csv', count($pairs));
    foreach ($pairs as $label => $value) {
        $text = (string) $value;
        $isAddr = stripos((string) $label, 'ADDRESS') !== false || stripos((string) $label, 'PLACE') !== false;
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
    layout_begin('Offender Fd');
    cdat_sum_page_open();
    cdat_sum_back_link('offender_search_by_mo.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$number = $_GET['MO_KEY'];

$sql0 = "SELECT ACC_NAME,IMAGE FROM COMPLETE_MO_CLASSIFICATION A LEFT JOIN MO_IMAGE_TABLE B ON A.MO_KEY=B.MO_KEY WHERE A.MO_KEY='$number'";

$sql1 = "SELECT DISTINCT MO_KEY, PHONE, ROLE, CATEGORY, ACC_NAME, FATHER_NAME, DATE_OF_BIRTH, AGE, FULLADDRESS, CITY_OR_DISTRICT, STATE, 
ID_PROOF, CRIME_HEAD, MO1, MO2, CRIME_NO, Year, SEC_OF_LAW, DATE_OF_ARREST, 
PLACE_OF_OFF, off_lat, off_long, POLICE_STATION, PS_DIVISION, PS_ZONE, 
INC_OFFICER, OFFICIAL_MAILID FROM CDATDUPL..COMPLETE_MO_CLASSIFICATION WHERE MO_KEY='$number'";

$st0 = sqlsrv_query($conn, $sql0);
$st1 = sqlsrv_query($conn, $sql1);
$heroRows = cdat_sum_fetch_all($st0);
$detailRows = cdat_sum_fetch_all($st1);

cdat_sum_results_open();
cdat_sum_report_banner('OFFENDERS LIST');

if (empty($heroRows)) {
    cdat_sum_empty_state('No offender image record found.');
} else {
    cdat_sum_generic_table_open(
        'Accused',
        ['ACCUSED NAME', 'IMAGE'],
        'offender_hero_table',
        'offender_hero.csv',
        count($heroRows)
    );
    foreach ($heroRows as $row) {
        cdat_sum_table_row([
            (string) ($row['ACC_NAME'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 220, 200), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($detailRows)) {
    cdat_sum_report_banner('OFFENDER PARTICULARS');
    cdat_sum_empty_state('No offender particulars found.');
} else {
    $p = $detailRows[0];
    offender_kv_table('Offender Particulars', [
        'MO_KEY' => $p['MO_KEY'] ?? '',
        'ACCUSED NAME' => $p['ACC_NAME'] ?? '',
        'FATHER_NAME' => $p['FATHER_NAME'] ?? '',
        'DATE_OF_BIRTH' => $p['DATE_OF_BIRTH'] ?? '',
        'AGE' => $p['AGE'] ?? '',
        'FULLADDRESS' => $p['FULLADDRESS'] ?? '',
        'CITY_OR_DISTRICT' => $p['CITY_OR_DISTRICT'] ?? '',
        'STATE' => $p['STATE'] ?? '',
        'IDPROOF' => $p['ID_PROOF'] ?? '',
        'CRIME_HEAD' => $p['CRIME_HEAD'] ?? '',
        'MO SUB CLASSIFICATION1' => $p['MO1'] ?? '',
        'MO SUB CLASSIFICATION2' => $p['MO2'] ?? '',
        'CRIME NO' => $p['CRIME_NO'] ?? '',
        'YEAR' => $p['Year'] ?? '',
        'SEC_OF_LAW' => $p['SEC_OF_LAW'] ?? '',
        'DATE_OF_ARREST' => $p['DATE_OF_ARREST'] ?? '',
        'PLACE_OF_OFF' => $p['PLACE_OF_OFF'] ?? '',
        'OFFENCE LAT' => $p['off_lat'] ?? '',
        'OFFENCE LONG' => $p['off_long'] ?? '',
        'POLICE_STATION' => $p['POLICE_STATION'] ?? '',
        'PS_DIVISION' => $p['PS_DIVISION'] ?? '',
        'PS_ZONE' => $p['PS_ZONE'] ?? '',
        'INC_OFFICER' => $p['INC_OFFICER'] ?? '',
        'OFFICIAL_MAILID' => $p['OFFICIAL_MAILID'] ?? '',
    ], 'offender_detail_table');
}
cdat_sum_results_close();

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
