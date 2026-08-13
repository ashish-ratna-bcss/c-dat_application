<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$year = trim((string) ($_POST['Y'] ?? ''));

$psStations = [
    'Abids', 'Begum Bazar', 'Narayanaguda', 'Chikkadpally', 'Gandhi Nagar', 'Musheerabad',
    'Nampally', 'Ramgopalpet', 'Saifabad', 'Kachiguda', 'Nallakunta', 'O.U.Sity', 'Amberpet',
    'Malakpet', 'Saidabad', 'Afzalgunj', 'Chaderghat', 'Sultanbazar', 'Begumpet', 'Bollaram',
    'Bowenpally', 'Tirumalgherry', 'WPS Begumpet', 'Chilkalguda', 'Gopalpuram', 'Lalaguda',
    'Tukkaramgate', 'Karkhana', 'Mahankali', 'Market', 'Marredpally', 'Bahadurpura', 'Charminar',
    'Hussainialam', 'Kalapattar', 'Kamatipura', 'WPS South Zone', 'Chandrayangutta', 'Chatrinaka',
    'Falaknuma', 'Shalialibanda', 'Dabeerpura', 'Mirchowk', 'Moghalpura', 'Reinbazar',
    'BhavaniNagar', 'Kanchanbagh', 'Madannapet', 'SantoshNagar', 'Asif Nagar', 'Golconda',
    'Humayunnagar', 'Langerhouz', 'Tappachabutra', 'BanjaraHills', 'Jubille Hills', 'Habeebnagar',
    'Kulsumpura', 'Mangalhat', 'Shahinayathgunj', 'Panjagutta', 'S.R.Nagar', 'CCS',
];
$psOptions = ['' => ''];
foreach ($psStations as $ps) {
    $psOptions[$ps] = $ps;
}
$psOptions['CYBER CRIME PS'] = 'CYBER CRIMES';
$psOptions['TASK FORCE EAST ZONE'] = 'TASK FORCE EAST ZONE';
$psOptions['TASK FORCE WEST ZONE'] = 'TASK FORCE WEST ZONE';
$psOptions['TASK FORCE NORTH ZONE'] = 'TASK FORCE NORHT ZONE';
$psOptions['TASK FORCE SOUTH ZONE'] = 'TASK FORCE SOUTH ZONE';

$crimeOptions = ['' => ''];
$serverName = "CPHYDERABAD1";
$connectionInfo = array("Database" => "TWRMDB");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$sql = "select distinct crime_no from offence_details";
$st1 = sqlsrv_query($conn, $sql);
if ($st1) {
    while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
        $c = (string) ($row['crime_no'] ?? '');
        if ($c !== '') {
            $crimeOptions[$c] = $c;
        }
    }
    sqlsrv_free_stmt($st1);
}
sqlsrv_close($conn);

$psHtml = '<div class="sum-search-form__field">'
    . '<label for="POLICE_STATION">Police Station</label>'
    . '<select id="POLICE_STATION" class="sum-select" data-searchable-select="1" data-placeholder="Select Police Station">'
    . '<option value="" data-placeholder="1"></option>';
foreach ($psOptions as $value => $label) {
    if ((string) $value === '') {
        continue;
    }
    $psHtml .= '<option value="' . cdat_sum_h((string) $value) . '">' . cdat_sum_h((string) $label) . '</option>';
}
$psHtml .= '</select></div>';

$crimeHtml = '<div class="sum-search-form__field">'
    . '<label for="CRIME_NO">Crime No</label>'
    . '<select id="CRIME_NO" class="sum-select" data-searchable-select="1" data-placeholder="Select Crime No">'
    . '<option value="" data-placeholder="1"></option>';
foreach ($crimeOptions as $value => $label) {
    if ((string) $value === '') {
        continue;
    }
    $crimeHtml .= '<option value="' . cdat_sum_h((string) $value) . '">' . cdat_sum_h((string) $label) . '</option>';
}
$crimeHtml .= '</select></div>';

$fieldsHtml = $psHtml
            . $crimeHtml
            . cdat_sum_field_text('Y', 'Year', $year, 'SUM', 'Enter Year');

layout_begin('Dump Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Suspect Number Search in Tower Dump',
    'Search tower dump records by police station, crime number, and year.',
    'dump_search.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
