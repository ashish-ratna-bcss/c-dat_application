<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$isAjax = cdat_sum_is_ajax();
$ps = trim((string) ($_POST['PDACT_PS'] ?? ''));
$hasSearch = $ps !== '';
cdat_sum_ajax_need_search($hasSearch, 'Select a police station and try again.');

$db_handle = new DBController();
$query = "SELECT DISTINCT UPPER(LTRIM(RTRIM(PD_ACT_PS))) PD_ACT_PS FROM pdact_main_table";
$psRows = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select Police Station'];
foreach ($psRows as $r) {
    $v = (string) ($r['PD_ACT_PS'] ?? '');
    if ($v !== '') {
        $psOptions[$v] = $v;
    }
}
$fieldsHtml = cdat_sum_searchable_select(
    'PDACT_PS',
    'PDACT Police Station',
    $psOptions,
    $ps,
    'Select Police Station',
    true
);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('PDACT Search By PS');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'PDACT Search By Police Station',
            'Search PDACT records for a police station.',
            'pdact_ps_wise_search.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }
    $conn = get_cdat_pdo();
        $number = $ps;

    $sql0 = "CREATE TEMP TABLE temp_jrms_temp AS select distinct PDACT_KEY,REPLACE(IRKEY,' ','') AS IRKEY,NAME,FATHER_NAME,AGE,DISTRICT AS NATIVE_DISTRICT,STATE AS NATIVE_STATE,PD_ACT_PS,
(Date_Of_Arrest)::varchar AS DATE_OF_PDACT  from pdact_main_table WHERE PD_ACT_PS LIKE '%$number%'";

    $sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,NATIVE_DISTRICT,NATIVE_STATE,PD_ACT_PS,
(DATE_OF_PDACT)::varchar AS DATE_OF_PDACT,CASE WHEN (A.IRKEY)::varchar=(B.IRKEY)::varchar
THEN IMAGE ELSE (SELECT IMAGE FROM image_table WHERE IRKEY='113769')END  AS IMAGE
FROM temp_jrms_temp A LEFT JOIN image_table B ON (A.IRKEY)::varchar=(B.IRKEY)::varchar ";

    $conn->query($sql0);
    $st1 = $conn->query($sql1);
    $rows = cdat_sum_fetch_all($st1);

    if (empty($rows)) {
        cdat_sum_empty_state('No PDACT records found for PS: ' . $ps);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner('ACCUSED INFORMATION');
        cdat_sum_generic_table_open(
            'PDACT Search By PS',
            ['PDACT_KEY', 'IRKEY', 'NAME', 'IMAGE', 'FATHER_NAME', 'AGE', 'NATIVE_DISTRICT', 'NATIVE_STATE', 'PD_ACT_PS', 'DATE_OF_PDACT'],
            'results_table',
            'pdact_ps_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $irKey = (string) ($row['IRKEY'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactKey) . '</a>'],
                ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                (string) ($row['NAME'] ?? ''),
                ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 120, 120), 'class' => 'sum-cell-img'],
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['NATIVE_DISTRICT'] ?? ''),
                (string) ($row['NATIVE_STATE'] ?? ''),
                (string) ($row['PD_ACT_PS'] ?? ''),
                ['text' => (string) ($row['DATE_OF_PDACT'] ?? ''), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }
    $conn = null;

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('PDACT Search By PS');
cdat_sum_page_open();
cdat_sum_search_card(
    'PDACT Search By Police Station',
    'Search PDACT records for a police station.',
    'pdact_ps_wise_search.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
