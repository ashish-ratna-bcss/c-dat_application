<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$cinNo = trim((string) ($_POST['CIN_NO'] ?? ''));
$uniqueKey = trim((string) ($_POST['UNIQUE_KEY'] ?? ''));
$irKey = trim((string) ($_POST['IRKEY'] ?? ''));
$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

layout_begin('JRMS Unique Key Update');
cdat_sum_page_open();
cdat_sum_entry_card_open(
    'JRMS Unique Key Updation',
    'Update unique key and IRKEY for one or more CIN numbers.',
    'jrms_unique_key_update.php'
);
echo cdat_sum_field_textarea('CIN_NO', "JRMS CIN NO'S", $cinNo, 'Enter Cin Number Seperated by comma without space Ex: 123xxx,124xxx,125xxx');
echo cdat_sum_field_text('UNIQUE_KEY', 'Unique Key', $uniqueKey, 'UNIQUE_KEY', 'Unique key');
echo cdat_sum_field_text('IRKEY', 'Irkey', $irKey, 'IRKEY', 'IRKEY', false);
cdat_sum_entry_card_close('Submit', 'BTN_CDAT');

if ($submitted) {
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'JRMS');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $NUMBER1 = $cinNo;
    $NUMBER2 = str_replace(',', "','", "$NUMBER1");
    $UNIQUE_KEY = $uniqueKey;
    $IRKEY = $irKey;

    $sql = "UPDATE JRMS_TOTAL_2012_TO_2017 SET UNIQUE_KEY='$UNIQUE_KEY', IRKEY='$IRKEY', ASONDATE=GETDATE(), APP_OR_MANUAL=  'APPLICATION_ENTRY'
WHERE CIN IN ('$NUMBER2')";
    if (!sqlsrv_query($conn, $sql)) {
        cdat_sum_status_message('Not Updated', false);
    } else {
        cdat_sum_status_message('Updated', true);
    }
    sqlsrv_close($conn);
}

cdat_sum_page_close();
layout_end();
