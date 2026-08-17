<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT distinct DISTRICT FROM CIS_DATA_BASE.DBO.CIS_COMPLETE_DATA";
$results = $db_handle->runQuery($query) ?: [];

$name = trim((string) ($_POST['NAME'] ?? ''));
$district = trim((string) ($_POST['DISTRICT'] ?? ''));
$ps = trim((string) ($_POST['POLICE_STATION'] ?? ''));

$districtHtml = '<div class="sum-search-form__field">'
    . '<label for="DISTRICT">District</label>'
    . '<select name="DISTRICT" id="DISTRICT" class="sum-select" data-searchable-select="1"'
    . ' data-placeholder="Select DISTRICT" onChange="GETPS(this.value);">'
    . '<option value="" data-placeholder="1">Select DISTRICT</option>';
foreach ($results as $row) {
    $v = (string) ($row['DISTRICT'] ?? '');
    if ($v === '') {
        continue;
    }
    $sel = ($district === $v) ? ' selected="selected"' : '';
    $districtHtml .= '<option value="' . cdat_sum_h($v) . '"' . $sel . '>' . cdat_sum_h($v) . '</option>';
}
$districtHtml .= '</select></div>';

$psHtml = '<div class="sum-search-form__field">'
    . '<label for="POLICE_STATION">Police Station</label>'
    . '<select name="POLICE_STATION" id="POLICE_STATION" class="sum-select"'
    . ' data-placeholder="Select POLICE_STATION">'
    . '<option value="">Select POLICE_STATION</option>'
    . '</select></div>';

$fieldsHtml = cdat_sum_field_text('NAME', 'Name', $name, 'NAME', 'Enter Name')
            . $districtHtml
            . $psHtml;

$getPsScript = '<script src="' . htmlspecialchars(CDAT_ASSETS . '/js/jquerydynamic.js', ENT_QUOTES) . '" type="text/javascript"></script>'
    . '<script>'
    . 'function GETPS(val) {'
    . ' $.ajax({ type: "POST", url: ' . json_encode((function_exists('cdat_href') ? cdat_href('/api/police-stations') : '/api/police-stations')) . ', data: "DISTRICT="+val,'
    . ' success: function(data){ $("#POLICE_STATION").html(data); } });'
    . '}'
    . '</script>';

layout_begin('Cis Data Name Search', '', $getPsScript);
cdat_sum_page_open();
cdat_sum_search_card(
    'Accused Search in CIS Data',
    'Search CIS accused records by name, district, and police station.',
    'cis_data_name_search_php.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
