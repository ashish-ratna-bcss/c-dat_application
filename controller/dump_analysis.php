<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT DISTINCT POLICE_STATION FROM OFFENCE_DETAILS";
$results = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select PS'];
foreach ($results as $row) {
    $v = (string) ($row['POLICE_STATION'] ?? '');
    if ($v !== '') {
        $psOptions[$v] = $v;
    }
}

$psSelect = cdat_sum_searchable_select('POLICE_STATION', 'Police Station', $psOptions, '', 'Select PS', false, '', 'POLICE_STATION');
$psSelect = str_replace(
    'class="sum-select" data-searchable-select="1"',
    'class="sum-select" data-searchable-select="1" onChange="getState(this.value);"',
    $psSelect
);

$fieldsHtml = $psSelect
            . '<div class="sum-search-form__field"><label for="Crime-list">Crime No</label>'
            . '<select name="CRIME_NO" id="Crime-list" class="sum-select">'
            . '<option value="">Select crime</option></select></div>'
            . '<div class="sum-search-form__field"><label for="year-list">Year</label>'
            . '<select name="year" id="year-list" class="sum-select">'
            . '<option value="">Select year</option></select></div>';

layout_begin('Dump Analysis');
cdat_sum_page_open();
cdat_sum_search_card(
    'Dump Analysis',
    'Select police station, crime number, and year.',
    'dump_analysis.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
echo '<script>function getState(val){if(window.jQuery){jQuery.ajax({type:"POST",url:"get_crno.php",data:"POLICE_STATION="+val,success:function(data){jQuery("#Crime-list").html(data);}});}}</script>';
cdat_sum_page_close();
layout_end();
