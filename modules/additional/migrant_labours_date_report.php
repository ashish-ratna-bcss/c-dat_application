<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$hh1 = (string) ($_POST['hh1'] ?? '00');
$mm1 = (string) ($_POST['mm1'] ?? '00');
$ss1 = (string) ($_POST['ss1'] ?? '00');
$hh2 = (string) ($_POST['hh2'] ?? '00');
$mm2 = (string) ($_POST['mm2'] ?? '00');
$ss2 = (string) ($_POST['ss2'] ?? '00');
$hasSearch = $fromDt !== '' && $toDt !== '';

$timeFromHtml = '<div class="sum-search-form__field">'
    . '<label>Time From (HH:MM:SS)</label>'
    . '<div style="display:flex;gap:.35rem;align-items:center;">'
    . '<input type="number" name="hh1" id="number1" min="0" max="23" value="' . cdat_sum_h($hh1) . '" required="required" />'
    . '<span>:</span>'
    . '<input type="number" name="mm1" id="number2" min="0" max="59" value="' . cdat_sum_h($mm1) . '" required="required" />'
    . '<span>:</span>'
    . '<input type="number" name="ss1" id="number3" min="0" max="59" value="' . cdat_sum_h($ss1) . '" required="required" />'
    . '</div></div>';
$timeToHtml = '<div class="sum-search-form__field">'
    . '<label>Time To (HH:MM:SS)</label>'
    . '<div style="display:flex;gap:.35rem;align-items:center;">'
    . '<input type="number" name="hh2" id="number4" min="0" max="23" value="' . cdat_sum_h($hh2) . '" required="required" />'
    . '<span>:</span>'
    . '<input type="number" name="mm2" id="number5" min="0" max="59" value="' . cdat_sum_h($mm2) . '" required="required" />'
    . '<span>:</span>'
    . '<input type="number" name="ss2" id="number6" min="0" max="59" value="' . cdat_sum_h($ss2) . '" required="required" />'
    . '</div></div>';

$fieldsHtml = cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID', $fromDt)
            . $timeFromHtml
            . cdat_sum_field_date('TO_DT', 'Date To', 'datepickerID1', $toDt)
            . $timeToHtml;

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Migrant Labours Date Report');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Trouble Monger Migrant Labours Between Entry Dates',
            'Search migrant labour records between entry date and time.',
            'migrant_labours_date_report.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "MIGRANT_LABOURS_FORM");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $f_date = $_POST['FROM_DT'];
    $t_date = $_POST['TO_DT'];
    $HH1 = $_POST['hh1'];
    $MM1 = $_POST['mm1'];
    $SS1 = $_POST['ss1'];
    $HH2 = $_POST['hh2'];
    $MM2 = $_POST['mm2'];
    $SS2 = $_POST['ss2'];

    $sql1 = "select distinct POLICE_STATION,
NAME,NATIVE_STATE,NATIVE_DISTRICT,PHONE,WORK_STATUS,
PART_OF_LABOUR_CAMP,URGENT,PROBLEM_CASES,REMARKS,ZONE,DIVISION   INTO #TEMP from migrant_labour_table
WHERE (CONVERT(DATETIME,ENTRY_DATE) BETWEEN '$f_date $HH1:$MM1:$SS1' AND '$t_date $HH2:$MM2:$SS2') ORDER BY POLICE_STATION";

    $sql2 = "select distinct *,ROW_NUMBER() OVER(ORDER BY POLICE_STATION) SLNO  from #TEMP
ORDER BY POLICE_STATION";


    $sql6 = "SELECT 'TROUBLE MONGERS MIGRANT DATA <br/>
 FROM: '+'$f_date $HH1:$MM1:$SS1'+' TO: '+'$t_date $HH2:$MM2:$SS2' AS PHONE";

    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st6 = sqlsrv_query($conn, $sql6);

    $banner = 'TROUBLE MONGERS MIGRANT DATA FROM: ' . $f_date . ' ' . $HH1 . ':' . $MM1 . ':' . $SS1
            . ' TO: ' . $t_date . ' ' . $HH2 . ':' . $MM2 . ':' . $SS2;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = strip_tags((string) ($b['PHONE'] ?? $banner));
    }
    $rows = cdat_sum_fetch_all($st2);

    if (empty($rows)) {
        cdat_sum_empty_state('No migrant labour records found for that date range.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Migrant Labours',
            ['NAME OF THE POLICE STATION', 'SLNO', 'NAME OF THE MIGRANT WORKER', 'NATIVE STATE', 'NATIVE DISTRICT', 'MOBILE NUMBER', 'WORKING STATUS', 'IS HE PART OF LABOUR CAMP', 'IS URGENT', 'PROBLEM CASES', 'REMARKS'],
            'results_table',
            'migrant_labours.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                (string) ($row['POLICE_STATION'] ?? ''),
                ['text' => (string) ($row['SLNO'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['NATIVE_STATE'] ?? ''),
                (string) ($row['NATIVE_DISTRICT'] ?? ''),
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['WORK_STATUS'] ?? ''),
                (string) ($row['PART_OF_LABOUR_CAMP'] ?? ''),
                (string) ($row['URGENT'] ?? ''),
                (string) ($row['PROBLEM_CASES'] ?? ''),
                (string) ($row['REMARKS'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Migrant Labours Date Report');
cdat_sum_page_open();
cdat_sum_search_card(
    'Trouble Monger Migrant Labours Between Entry Dates',
    'Search migrant labour records between entry date and time.',
    'migrant_labours_date_report.php',
    cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
        . '<div class="sum-search-form__field"><label>Time From (HH:MM:SS)</label>'
        . '<div style="display:flex;gap:.35rem;align-items:center;">'
        . '<input type="number" name="hh1" id="number1" min="0" max="23" value="00" required="required" />'
        . '<span>:</span>'
        . '<input type="number" name="mm1" id="number2" min="0" max="59" value="00" required="required" />'
        . '<span>:</span>'
        . '<input type="number" name="ss1" id="number3" min="0" max="59" value="00" required="required" />'
        . '</div></div>'
        . cdat_sum_field_date('TO_DT', 'Date To', 'datepickerID1')
        . '<div class="sum-search-form__field"><label>Time To (HH:MM:SS)</label>'
        . '<div style="display:flex;gap:.35rem;align-items:center;">'
        . '<input type="number" name="hh2" id="number4" min="0" max="23" value="00" required="required" />'
        . '<span>:</span>'
        . '<input type="number" name="mm2" id="number5" min="0" max="59" value="00" required="required" />'
        . '<span>:</span>'
        . '<input type="number" name="ss2" id="number6" min="0" max="59" value="00" required="required" />'
        . '</div></div>',
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
