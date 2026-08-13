<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/dbcontroller.php';

$db_handle = new DBController();
$query = "select DISTINCT POLICE_STATION  from IRFORMS..VERIFY_REPORT_IR A
where stage_of_case in ('ISSUE BW/NBW','PROCLAMATION US 82-83 CRPC','NBW','N.B.W._Ready','Issue NBW','Awaiting Warrant',
'Awaiting Warrant ( DORMANT CASE )','NBW PENDING','PROCLAMATION','Re issue NBW')
ORDER BY POLICE_STATION";
$results = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select POLICE_STATION'];
foreach ($results as $row) {
    $v = (string) ($row['POLICE_STATION'] ?? '');
    if ($v !== '') {
        $psOptions[$v] = $v;
    }
}

$ps = trim((string) ($_POST['POLICE_STATION'] ?? ''));
$fieldsHtml = cdat_sum_searchable_select(
    'POLICE_STATION',
    'Police Station',
    $psOptions,
    $ps,
    'Select POLICE_STATION',
    true
);

layout_begin('Nbws');
cdat_sum_page_open();
cdat_sum_search_card(
    'NBWS / Warrant Search',
    'Search NBW / warrant records by police station.',
    'nbws.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
