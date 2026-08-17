<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$db_handle = new DBController();
$query = "SELECT * FROM country";
$results = $db_handle->runQuery($query) ?: [];
$countryOptions = ['' => 'Select Country'];
foreach ($results as $row) {
    $id = (string) ($row['id'] ?? '');
    $label = (string) ($row['country_name'] ?? '');
    if ($id !== '' && $label !== '') {
        $countryOptions[$id] = $label;
    }
}

$countrySelect = cdat_sum_searchable_select('country', 'Country', $countryOptions, '', 'Select Country', false, '', 'country-list');
$countrySelect = str_replace(
    'class="sum-select" data-searchable-select="1"',
    'class="sum-select" data-searchable-select="1" onChange="getState(this.value);"',
    $countrySelect
);

$fieldsHtml = $countrySelect
            . '<div class="sum-search-form__field"><label for="state-list">State</label>'
            . '<select name="state" id="state-list" class="sum-select">'
            . '<option value="">Select State</option></select></div>';

layout_begin('Myindex');
cdat_sum_page_open();
cdat_sum_search_card(
    'Country / State demo',
    'Test cascading country and state dropdown.',
    'myindex.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
echo '<script>function getState(val){if(window.jQuery){jQuery.ajax({type:"POST",url:"get_state.php",data:"country_id="+val,success:function(data){jQuery("#state-list").html(data);}});}}</script>';
cdat_sum_page_close();
layout_end();
