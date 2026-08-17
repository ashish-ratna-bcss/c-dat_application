<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Demo.php');
cdat_sum_page_open();

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array('Database' => 'TRAINING_DB');
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql8 = "SELECT DISTINCT * FROM ADDMORE";
$st8 = sqlsrv_query($conn, $sql8);
$rows = cdat_sum_fetch_all($st8);

if (empty($rows)) {
    cdat_sum_empty_state('No demo records found.');
} else {
    cdat_sum_results_open();
    cdat_sum_generic_table_open(
        'Demo',
        ['ID', 'Name', 'Gender', 'Address'],
        'mytable',
        'demo.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['user_id'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['user_name'] ?? ''),
            (string) ($row['user_gender'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['user_address'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
        ]);
    }
    cdat_sum_generic_table_close();
    cdat_sum_results_close();
}
sqlsrv_close($conn);

cdat_sum_page_close();
layout_end();
