<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array('Database' => 'TWRMDB');
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$sql = "select distinct crime_no from offence_details";
$st1 = sqlsrv_query($conn, $sql);
$crimeOptions = ['' => 'Select crime no'];
if ($st1) {
    while ($row = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC)) {
        $v = (string) ($row['crime_no'] ?? '');
        if ($v !== '') {
            $crimeOptions[$v] = $v;
        }
    }
}
sqlsrv_close($conn);

layout_begin('Dump');
cdat_sum_page_open();
cdat_sum_search_card(
    'Dump',
    'Select a crime number from offence details.',
    'dump.php',
    cdat_sum_searchable_select('crime_no', 'Crime No', $crimeOptions, '', 'Select crime no', false),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
