<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Undetected Cases List');
cdat_sum_page_open();
cdat_sum_begin_heavy_search();
$conn = get_cdat_pdo();
$sql8 = "select 'UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINT LIST' PHONE1";
$sql9 = "select SNO, POLICE_STATION, ZONE, CRIME_NO, SECTION, TIN_NO, DATE_OF_IDENTITY,
LOSS_OF_PROPERTY, NAME_AND_PARTICULARS, IRKEY, CCNO, DOA, REMARKS FROM fingerprint_matched_undetected_cases_withimage
ORDER BY ZONE,IRKEY LIMIT 2000";

$st8 = $conn->query($sql8);
$st9 = $conn->query($sql9);

$banner = 'UNDETECTED CASES MATCHED WITH OLD OFFENDERS FINGER PRINT LIST';
if ($st8 && ($b = $st8->fetch(PDO::FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE1'] ?? $banner);
}

$rows = cdat_sum_fetch_all($st9);
if (empty($rows)) {
    cdat_sum_empty_state('No fingerprint-matched undetected cases found.');
} else {
    cdat_sum_results_open();
    cdat_sum_report_banner($banner);
    cdat_sum_generic_table_open(
        'Undetected Cases',
        ['POLICE_STATION', 'ZONE', 'CRIME_NO', 'SECTION', 'TIN_NO', 'DATE_OF_IDENTITY', 'LOSS_OF_PROPERTY', 'NAME_AND_PARTICULARS', 'IMAGE', 'IRKEY', 'CCNO', 'DOA', 'REMARKS'],
        'results_table',
        'fp_list.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        cdat_sum_table_row([
            (string) ($row['POLICE_STATION'] ?? ''),
            (string) ($row['ZONE'] ?? ''),
            (string) ($row['CRIME_NO'] ?? ''),
            (string) ($row['SECTION'] ?? ''),
            (string) ($row['TIN_NO'] ?? ''),
            ['text' => (string) ($row['DATE_OF_IDENTITY'] ?? ''), 'class' => 'sum-cell-date'],
            (string) ($row['LOSS_OF_PROPERTY'] ?? ''),
            (string) ($row['NAME_AND_PARTICULARS'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 120, 120), 'class' => 'sum-cell-img'],
            ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['CCNO'] ?? ''),
            (string) ($row['DOA'] ?? ''),
            (string) ($row['REMARKS'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
    cdat_sum_results_close();
}

if ($st9) {
    $st9 = null;
}
$conn = null;

cdat_sum_page_close();
layout_end();
