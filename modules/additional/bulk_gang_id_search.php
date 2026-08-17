<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Bulk Gang Id Search');
    cdat_sum_page_open();
    cdat_sum_back_link('bulk_gang_id.php');
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

    $sql10 = "SELECT PHOTO_ID_1,BASE64_IMAGE FROM GANG_FILES_MASTER_TABLE.DBO.IMAGES_BASE64_FORMAT  WHERE PHOTO_ID_1 IN ('$number2')";

    $st10 = sqlsrv_query($conn, $sql10);
    $rows = cdat_sum_fetch_all($st10);

    cdat_sum_results_open();
    cdat_sum_report_banner('BULK IR SEARCH');
    if (empty($rows)) {
        cdat_sum_empty_state('No gang ID images found for the given keys.');
    } else {
        cdat_sum_generic_table_open(
            'Bulk Gang ID Search',
            ['IRKEY', 'IMAGE'],
            'results_table',
            'bulk_gang_id.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $photoId = (string) ($row['PHOTO_ID_1'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($photoId)) . '">' . cdat_sum_h($photoId) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_img_html($row['BASE64_IMAGE'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
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
