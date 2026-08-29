<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$isAjax = cdat_sum_is_ajax();
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$crimeHead = trim((string) ($_POST['CRIMEHEAD'] ?? ''));
$hasSearch = $fromDt !== '' && $toDt !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter both dates and try again.');

$db_handle = new DBController();
$query = "SELECT distinct HEADOFCRIME FROM jrms_total_2012_to_2017
WHERE HEADOFCRIME!='' ORDER BY HEADOFCRIME";
$results = $db_handle->runQuery($query) ?: [];
$crimeOptions = ['' => 'Select CrimeHead'];
foreach ($results as $r) {
    $v = (string) ($r['HEADOFCRIME'] ?? '');
    if ($v !== '') {
        $crimeOptions[$v] = $v;
    }
}

$fieldsHtml = cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID', $fromDt)
            . cdat_sum_field_date('TO_DT', 'To', 'datepickerID1', $toDt)
            . cdat_sum_searchable_select('CRIMEHEAD', 'CrimeHead', $crimeOptions, $crimeHead, 'Select CrimeHead', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('JRMS Search By Dates');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Jail Release Between Dates',
            'Search JRMS records by release date and crime head.',
            'jrms_search_by_dates.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }
    $conn = get_cdat_pdo();
        $CRIMEHEAD = $crimeHead;
    $f_date = $fromDt;
    $t_date = $toDt;

    $sql1 = "CREATE TEMP TABLE temp_jrms_temp AS SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LENGTH(RIGHT(NAME,POSITION('/' IN REVERSE(NAME))))>1 THEN RIGHT(NAME,POSITION('/' IN REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
TO_CHAR(NULLIF(TRIM(admission_to_jail), '')::date, 'YYYY-MM-DD') AS add_to_jail,
TO_CHAR(NULLIF(TRIM(releasedt), '')::date, 'YYYY-MM-DD') AS release_date, photo  FROM
jrms_total_2012_to_2017
WHERE  ((RELEASEDT)::date BETWEEN '$f_date' AND '$t_date') AND HEADOFCRIME LIKE '%' || '$CRIMEHEAD' || '%' AND HEADOFCRIME!='' ";

    $sql11 = "CREATE TEMP TABLE temp_jrms_count AS SELECT distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED from jrms_total_2012_to_2017
GROUP BY UNIQUE_KEY";

    $sql2 = "SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,B.no_of_times_released,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE, photo,CASE WHEN IDPROOF!='' AND IDPROOF ~ '^[0-9]+$' AND IDPROOF in (select distinct AADHAR_NO FROM ir_particulars) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND IDPROOF ~ '^[0-9]+$' AND
IDPROOF in (select distinct AADHAR_NO FROM ir_particulars) THEN (SELECT DISTINCT (MAX(IRKEY)::varchar) IRKEY FROM ir_particulars WHERE
AADHAR_NO !='' AND AADHAR_NO=(IDPROOF)::varchar)  ELSE '' END IRKEY FROM temp_jrms_temp A
LEFT JOIN temp_jrms_count B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

    $sql6 = "SELECT 'ACCUSED RELEASED FROM: ' || '$f_date' || ' TO: ' || '$t_date' || ' UNDER CRIME HEAD ' || '$CRIMEHEAD' AS PHONE";

    $conn->query($sql1);
    $conn->query($sql11);
    $st2 = $conn->query($sql2);
    $st6 = $conn->query($sql6);

    $banner = 'ACCUSED RELEASED FROM: ' . $f_date . ' TO: ' . $t_date . ' UNDER CRIME HEAD ' . $CRIMEHEAD;
    if ($st6 && ($b = $st6->fetch(PDO::FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st2);

    if (empty($rows)) {
        cdat_sum_empty_state('No JRMS records found for that date range.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'JRMS Search By Dates',
            ['PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'CRIMES INVOLVED', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
            'results_table',
            'jrms_dates.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $phone = (string) ($row['PHONE'] ?? '');
            $uniqueKey = (string) ($row['UNIQUE_KEY'] ?? '');
            $irKey = (string) ($row['IRKEY'] ?? '');
            $irForm = (string) ($row['IRFORM'] ?? '');
            cdat_sum_table_row([
                (string) ($row['PSARRESTED'] ?? ''),
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHERSNAME'] ?? ''),
                (string) ($row['CRIMENOS'] ?? ''),
                (string) ($row['HEADOFCRIME'] ?? ''),
                ['html' => '<a href="' . htmlspecialchars(cdat_page('jrms_search_for_uniquekey.php')) . '?UNIQUE_KEY=' . cdat_sum_h(urlencode($uniqueKey)) . '">' . cdat_sum_h((string) ($row['NO_OF_TIMES_RELEASED'] ?? '')) . '</a>'],
                ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts2.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
                (string) ($row['IDPROOF'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['ADDR_DURING_RELEASE'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['JAILNAME'] ?? ''),
                ['text' => (string) ($row['ADD_TO_JAIL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['RELEASE_DATE'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_img_html($row['PHOTO'] ?? '', 100, 100), 'class' => 'sum-cell-img'],
                ['html' => $irForm !== '' ? '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irForm) . '</a>' : ''],
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

layout_begin('JRMS Search By Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Jail Release Between Dates',
    'Search JRMS records by release date and crime head.',
    'jrms_search_by_dates.php',
    cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
        . cdat_sum_field_date('TO_DT', 'To', 'datepickerID1')
        . cdat_sum_searchable_select('CRIMEHEAD', 'CrimeHead', $crimeOptions, '', 'Select CrimeHead', false),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
