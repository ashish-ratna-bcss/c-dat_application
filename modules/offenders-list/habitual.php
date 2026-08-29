<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Habitual Offenders');
cdat_sum_page_open();
cdat_sum_begin_heavy_search();
$conn = get_cdat_pdo();
$sql8 = "select 'HABITUAL OFFENDERS' PHONE1";
$sql9 = "SELECT irkey, name, alias_name, father_name, age, present_address, arrested_in_crimehead, mo, crime_no, year, sec_of_law, police_station, count1 FROM habitual_offenders ORDER BY count1 DESC";

$st8 = $conn->query($sql8);
$st9 = $conn->query($sql9);

$banner = 'HABITUAL OFFENDERS';
if ($st8 && ($b = $st8->fetch(PDO::FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE1'] ?? $banner);
}

$rows = cdat_sum_fetch_all($st9);
if (empty($rows)) {
    cdat_sum_empty_state('No habitual offender records found.');
} else {
    cdat_sum_results_open();
    cdat_sum_report_banner($banner);
    cdat_sum_generic_table_open(
        'Habitual Offenders',
        ['IRKEY', 'ACCUSED NAME', 'ALIAS NAME', 'IMAGE', 'FATHER NAME', 'AGE', 'PRESENT ADDRESS', 'ARRESTED CRIME NO', 'ARRESTED YEAR', 'ARRESTED SEC_OF_LAW', 'POLICE STATION', 'ARRESTED CRIME HEAD', 'MO', 'TOTAL NUMBER OF CRIMES INVOLVED'],
        'results_table',
        'habitual_offenders.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        $addr = cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? ''));
        cdat_sum_table_row([
            ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
            (string) ($row['NAME'] ?? ''),
            (string) ($row['ALIAS_NAME'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 120, 120), 'class' => 'sum-cell-img'],
            (string) ($row['FATHER_NAME'] ?? ''),
            ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
            ['html' => $addr !== '' ? $addr : '—', 'class' => 'sum-address-cell'],
            (string) ($row['CRIME_NO'] ?? ''),
            (string) ($row['YEAR'] ?? ''),
            (string) ($row['SEC_OF_LAW'] ?? ''),
            (string) ($row['POLICE_STATION'] ?? ''),
            (string) ($row['ARRESTED_IN_CRIMEHEAD'] ?? ''),
            (string) ($row['MO'] ?? ''),
            ['text' => (string) ($row['count1'] ?? ''), 'class' => 'sum-cell-num'],
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
