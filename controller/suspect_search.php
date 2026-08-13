<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT * FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select PS'];
foreach ($results as $row) {
    $id = (string) ($row['id'] ?? '');
    $label = (string) ($row['Police_station'] ?? '');
    if ($id !== '' && $label !== '') {
        $psOptions[$id] = $label;
    }
}

$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$ps = trim((string) ($_POST['Police_station'] ?? ''));
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$hh1 = (string) ($_POST['hh1'] ?? '00');
$mm1 = (string) ($_POST['mm1'] ?? '00');
$ss1 = (string) ($_POST['ss1'] ?? '00');
$hh2 = (string) ($_POST['hh2'] ?? '00');
$mm2 = (string) ($_POST['mm2'] ?? '00');
$ss2 = (string) ($_POST['ss2'] ?? '00');

$fieldsHtml = cdat_sum_field_phone($phone)
            . cdat_sum_searchable_select('Police_station', 'Police Station', $psOptions, $ps, 'Select PS', false, '', 'police_station')
            . cdat_sum_field_date('FROM_DT', 'Date', 'datepickerID', $fromDt)
            . cdat_sum_field_hms('hh1', 'mm1', 'ss1', 'Between Time HH:MM:SS', $hh1, $mm1, $ss1)
            . cdat_sum_field_hms('hh2', 'mm2', 'ss2', 'and HH:MM:SS', $hh2, $mm2, $ss2);

layout_begin('Suspect Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Suspect Number Search In Tower Dump',
    'Search a mobile number in tower dump for a police station and date/time window.',
    'suspect_search.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
