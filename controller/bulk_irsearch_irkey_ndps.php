<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Bulk Irsearch Irkey Ndps');
    cdat_sum_page_open();
    cdat_sum_back_link('bulk_irkey_ndps.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['IRKEY'])) {
    $number = $_POST['IRKEY'];
    $number2 = str_replace(",", "','", "$number");

    $sql9 = "SELECT DISTINCT IRKEY INTO #TEMP FROM FORMS.DBO.OFFENCE_DETAILS WHERE IRKEY IN ('$number2')";

    $sql10 = "SELECT A.IRKEY,C.NAME,B.IMAGE FROM #TEMP A LEFT JOIN FORMS.DBO.IMAGE_TABLE B ON A.IRKEY=B.IRKEY
LEFT JOIN FORMS.DBO.IR_PARTICULARS C ON A.IRKEY=C.IRKEY";

    sqlsrv_query($conn, $sql9);
    $st10 = sqlsrv_query($conn, $sql10);
    $rows = cdat_sum_fetch_all($st10);

    cdat_sum_results_open();
    cdat_sum_report_banner('BULK IR SEARCH');
    if (empty($rows)) {
        cdat_sum_empty_state('No NDPS IR records found for the given keys.');
    } else {
        cdat_sum_generic_table_open(
            'Bulk IR Search NDPS',
            ['IRKEY', 'NAME', 'IMAGE'],
            'results_table',
            'bulk_irsearch_ndps.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $name = (string) ($row['NAME'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="ir_ndps.php?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => '<a href="ir_ndps.php?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($name) . '</a>'],
                ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();
} else {
    cdat_sum_empty_state('Enter IR keys to search.');
}

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
