<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

function pdact_kv_table(string $title, array $pairs, string $tableId): void
{
    cdat_sum_generic_table_open($title, ['Field', 'Value'], $tableId, $tableId . '.csv', count($pairs));
    foreach ($pairs as $label => $value) {
        $text = (string) $value;
        $isAddr = stripos((string) $label, 'ADDRESS') !== false || stripos((string) $label, 'FACTS') !== false;
        cdat_sum_table_row([
            (string) $label,
            $isAddr
                ? ['html' => cdat_sum_address_lines($text) ?: '—', 'class' => 'sum-address-cell']
                : $text,
        ]);
    }
    cdat_sum_generic_table_close();
}

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('PDACT Main');
    cdat_sum_page_open();
    cdat_sum_back_link('pdact_search.php');
}
$conn = get_cdat_pdo();
$pdactKey = trim((string) ($_GET['PDACT_KEY'] ?? ''));
if ($pdactKey === '') {
    cdat_sum_results_open();
    cdat_sum_empty_state('PDACT key is required.');
    cdat_sum_results_close();
    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

$sql0 = "CREATE TEMP TABLE temp_jrms_temp AS SELECT distinct PDACT_KEY,IRKEY,NAME,FATHER_NAME,AGE,DISTRICT NATIVE_DISTRICT,STATE NATIVE_STATE from pdact_main_table
WHERE PDACT_KEY = :pdact_key";

$sql2 = "select A.PDACT_KEY,A.IRKEY,A.NAME,A.FATHER_NAME,A.AGE,NATIVE_DISTRICT,NATIVE_STATE,CASE WHEN (A.IRKEY)::varchar=(B.IRKEY)::varchar
THEN IMAGE ELSE (SELECT IMAGE FROM image_table WHERE IRKEY='113769')END  AS IMAGE FROM temp_jrms_temp A LEFT JOIN 
image_table B ON (A.IRKEY)::varchar=(B.IRKEY)::varchar";

$sql1 = "SELECT distinct  PD_ACT_PS,ZONE,FILE_NO,DETENU_NO,(ORDER_ISSUED_ON)::varchar ORDER_ISSUED_ON,APPROVAL_ORDERS_NO,CONFIRMATION_REVOCATION_ORDERS,CRIME_HEAD,MINOR_HEAD
MODUSOPERENDI,POLICE_STATION,WHETHER_INVOLVED_IN_OTHER_UNIT_CASES,NAME_OF_UNITS,NO_OF_CASES,
(DATE_OF_ARREST)::varchar PDACT_DATE,(DATE_OF_RELEASE)::varchar DATE_OF_RELEASE,BRIEF_FACTS FROM pdact_main_table
WHERE PDACT_KEY = :pdact_key";

$st0 = $conn->prepare($sql0);
$st0->execute([':pdact_key' => $pdactKey]);
$st2 = $conn->query($sql2);
$st1 = $conn->prepare($sql1);
$st1->execute([':pdact_key' => $pdactKey]);
$heroRows = cdat_sum_fetch_all($st2);
$detailRows = cdat_sum_fetch_all($st1);

cdat_sum_results_open();
cdat_sum_report_banner('ACCUSED INFORMATION');

if (empty($heroRows)) {
    cdat_sum_empty_state('No PDACT accused record found.');
} else {
    cdat_sum_generic_table_open(
        'Accused Information',
        ['PDACT_KEY', 'IRKEY', 'NAME', 'FATHER_NAME', 'AGE', 'NATIVE_DISTRICT', 'NATIVE_STATE', 'IMAGE'],
        'pdact_hero_table',
        'pdact_hero.csv',
        count($heroRows)
    );
    foreach ($heroRows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        cdat_sum_table_row([
            (string) ($row['PDACT_KEY'] ?? ''),
            ['html' => $irKey !== '' ? '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>' : '', 'class' => 'sum-cell-num'],
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHER_NAME'] ?? ''),
            ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
            (string) ($row['NATIVE_DISTRICT'] ?? ''),
            (string) ($row['NATIVE_STATE'] ?? ''),
            ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 220, 200), 'class' => 'sum-cell-img'],
        ]);
    }
    cdat_sum_generic_table_close();
}

if (empty($detailRows)) {
    cdat_sum_report_banner('PDACT DETAILS');
    cdat_sum_empty_state('No PDACT details found.');
} else {
    $p = $detailRows[0];
    pdact_kv_table('PDACT Details', [
        'PD_ACT_PS' => $p['PD_ACT_PS'] ?? '',
        'ZONE' => $p['ZONE'] ?? '',
        'FILE_NO' => $p['FILE_NO'] ?? '',
        'DETENU_NO' => $p['DETENU_NO'] ?? '',
        'ORDER_ISSUED_ON' => $p['ORDER_ISSUED_ON'] ?? '',
        'APPROVAL_ORDERS_NO' => $p['APPROVAL_ORDERS_NO'] ?? '',
        'CONFIRMATION_REVOCATION_ORDERS' => $p['CONFIRMATION_REVOCATION_ORDERS'] ?? '',
        'CRIME_HEAD' => $p['CRIME_HEAD'] ?? '',
        'MODUSOPERENDI' => $p['MODUSOPERENDI'] ?? '',
        'POLICE_STATION' => $p['POLICE_STATION'] ?? '',
        'WHETHER_INVOLVED_IN_OTHER_UNIT_CASES' => $p['WHETHER_INVOLVED_IN_OTHER_UNIT_CASES'] ?? '',
        'NO_OF_CASES' => $p['NO_OF_CASES'] ?? '',
        'PDACT_DATE' => $p['PDACT_DATE'] ?? '',
        'DATE_OF_RELEASE' => $p['DATE_OF_RELEASE'] ?? '',
        'BRIEF_FACTS' => $p['BRIEF_FACTS'] ?? '',
    ], 'pdact_detail_table');
}
cdat_sum_results_close();

$conn = null;

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
