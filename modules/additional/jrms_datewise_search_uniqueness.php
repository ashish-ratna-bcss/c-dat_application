<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$hasSearch = $fromDt !== '' && $toDt !== '';

$fieldsHtml = cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID', $fromDt)
            . cdat_sum_field_date('TO_DT', 'To', 'datepickerID1', $toDt);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('JRMS Datewise Search Uniqueness');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Jail Release Between Dates',
            'Search JRMS records released between two dates.',
            'jrms_datewise_search_uniqueness.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "10.10.46.14\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $f_date = $fromDt;
    $t_date = $toDt;

    $sql1 = "SET DATEFORMAT DMY SELECT DISTINCT  CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,
MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date')";

    $sql2 = "SELECT CIN,UNIQUE_KEY,IRKEY,PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRFORM FROM #TEMP ORDER BY RELEASE_DATE DESC";

    $sql6 = "SELECT 'ACCUSED RELEASED FROM: '+'$f_date'+' TO: '+'$t_date' AS PHONE";

    sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql2);
    $st6 = sqlsrv_query($conn, $sql6);

    $banner = 'ACCUSED RELEASED FROM: ' . $f_date . ' TO: ' . $t_date;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st2);

    if (empty($rows)) {
        cdat_sum_empty_state('No JRMS records found for that date range.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'JRMS Datewise Search',
            ['CIN', 'UNIQUE_KEY', 'IRKEY', 'PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
            'results_table',
            'jrms_datewise.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $phone = (string) ($row['PHONE'] ?? '');
            $irForm = (string) ($row['IRFORM'] ?? '');
            cdat_sum_table_row([
                (string) ($row['CIN'] ?? ''),
                (string) ($row['UNIQUE_KEY'] ?? ''),
                (string) ($row['IRKEY'] ?? ''),
                (string) ($row['PSARRESTED'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHERSNAME'] ?? ''),
                (string) ($row['CRIMENOS'] ?? ''),
                (string) ($row['HEADOFCRIME'] ?? ''),
                ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts2.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
                (string) ($row['IDPROOF'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['ADDR_DURING_RELEASE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['JAILNAME'] ?? ''),
                ['text' => (string) ($row['ADD_TO_JAIL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['RELEASE_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_img_html($row['PHOTO'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
                ['html' => $irForm !== '' ? '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irForm)) . '">' . cdat_sum_h($irForm) . '</a>' : ''],
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

layout_begin('JRMS Datewise Search Uniqueness');
cdat_sum_page_open();
cdat_sum_search_card(
    'Jail Release Between Dates',
    'Search JRMS records released between two dates.',
    'jrms_datewise_search_uniqueness.php',
    cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
        . cdat_sum_field_date('TO_DT', 'To', 'datepickerID1'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
