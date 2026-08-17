<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT distinct POLICE_STATION FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select PS'];
foreach ($results as $row) {
    $v = (string) ($row['POLICE_STATION'] ?? '');
    if ($v !== '') {
        $psOptions[$v] = $v;
    }
}

$ps = trim((string) ($_POST['Police_station'] ?? ''));
$crimeNo = trim((string) ($_POST['CRIME_NO'] ?? ''));
$year = trim((string) ($_POST['YEAR'] ?? ''));
$offDate = trim((string) ($_POST['OFF_DATE'] ?? ''));
$hh1 = (string) ($_POST['hh1'] ?? '00');
$mm1 = (string) ($_POST['mm1'] ?? '00');
$ss1 = (string) ($_POST['ss1'] ?? '00');
$hh2 = (string) ($_POST['hh2'] ?? '00');
$mm2 = (string) ($_POST['mm2'] ?? '00');
$ss2 = (string) ($_POST['ss2'] ?? '00');

$psSelect = cdat_sum_searchable_select('Police_station', 'Police Station', $psOptions, $ps, 'Select PS', false, '', 'Police_station');
$psSelect = str_replace(
    'class="sum-select" data-searchable-select="1"',
    'class="sum-select" data-searchable-select="1" onChange="getps(this.value);"',
    $psSelect
);

$fieldsHtml = $psSelect
            . '<div class="sum-search-form__field"><label for="Crime-list">Crime No</label>'
            . '<select name="CRIME_NO" id="Crime-list" class="sum-select" onChange="getyear(this.value);">'
            . '<option value="">Select Crime No</option>'
            . ($crimeNo !== '' ? '<option value="' . cdat_sum_h($crimeNo) . '" selected="selected">' . cdat_sum_h($crimeNo) . '</option>' : '')
            . '</select></div>'
            . '<div class="sum-search-form__field"><label for="YEAR">Year</label>'
            . '<select name="YEAR" id="YEAR" class="sum-select">'
            . '<option value="">Select Year</option>'
            . ($year !== '' ? '<option value="' . cdat_sum_h($year) . '" selected="selected">' . cdat_sum_h($year) . '</option>' : '')
            . '</select></div>'
            . cdat_sum_field_date('OFF_DATE', 'Date', 'datepickerID', $offDate)
            . cdat_sum_field_hms('hh1', 'mm1', 'ss1', 'Between Time HH:MM:SS', $hh1, $mm1, $ss1)
            . cdat_sum_field_hms('hh2', 'mm2', 'ss2', 'and HH:MM:SS', $hh2, $mm2, $ss2);

layout_begin('Pre Off Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Previous Offenders In Tower Dump',
    'Search previous offenders in tower dump for a crime and time window.',
    'pre_off_search_twr.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_tower_cascade_script();
cdat_sum_page_close();
layout_end();
