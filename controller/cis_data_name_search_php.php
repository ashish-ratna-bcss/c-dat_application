<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Cis Data Name Search Php');
    cdat_sum_page_open();
    cdat_sum_back_link('cis_data_name_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
$NAME = $_POST['NAME'];
$POLICE_STATION = $_POST['POLICE_STATION'];
$DISTRICT = $_POST['DISTRICT'];

$sql1 = "SELECT DISTINCT  Fir_No, POLICE_STATION, District, Name, FatherName, Age, Caste, Present_Add, 
Premenant_Add, folder_name, picture_name, PATH, image FROM CIS_DATA_BASE..CIS_COMPLETE_DATA
WHERE POLICE_STATION LIKE '%'+'$POLICE_STATION'+'%' AND DISTRICT LIKE '%'+'$DISTRICT'+'%' AND NAME LIKE '%'+'$NAME'+'%' ";

$sql6 = "SELECT 'ACCUSED ARRESTED FROM ' + '$POLICE_STATION' +' OF '+ '$DISTRICT' +' DISTRICT '+' BY NAME '+'$NAME' AS PHONE";

$st1 = sqlsrv_query($conn, $sql1);
$st6 = sqlsrv_query($conn, $sql6);

$banner = 'ACCUSED ARRESTED FROM ' . $POLICE_STATION . ' OF ' . $DISTRICT . ' DISTRICT BY NAME ' . $NAME;
if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st1);

cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No CIS records found.');
} else {
    cdat_sum_generic_table_open(
        'CIS Data Name Search',
        ['FIR_NO', 'POLICE_STATION', 'DISTRICT', 'NAME', 'FATHERNAME', 'AGE', 'CASTE', 'PRESENT_ADD', 'PERMANANT_ADD', 'IMAGE'],
        'results_table',
        'cis_name_search.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            (string) ($row['Fir_No'] ?? ''),
            (string) ($row['POLICE_STATION'] ?? ''),
            (string) ($row['District'] ?? ''),
            (string) ($row['Name'] ?? ''),
            (string) ($row['FatherName'] ?? ''),
            ['text' => (string) ($row['Age'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['Caste'] ?? ''),
            ['html' => cdat_sum_address_lines((string) ($row['Present_Add'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_address_lines((string) ($row['Premenant_Add'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ['html' => cdat_sum_img_html($row['image'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
