<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$uniqueKey = trim((string) ($_GET['UNIQUE_KEY'] ?? ''));
if ($uniqueKey === '') {
    if ($isAjax) {
        cdat_sum_empty_state('Unique key is required.');
        exit;
    }
    layout_begin('JRMS Search For Uniquekey');
    cdat_sum_page_open();
    cdat_sum_empty_state('Unique key is required.');
    cdat_sum_page_close();
    layout_end();
    exit;
}

if (!$isAjax) {
    layout_begin('JRMS Search For Uniquekey');
    cdat_sum_page_open();
    cdat_sum_back_link('jrms_search.php');
}
cdat_sum_begin_heavy_search();
require_once CDAT_COMMON . '/sql_safe.php';
$uniqueKey = sql_safe_alnum($uniqueKey);
if ($uniqueKey === '') {
    cdat_sum_empty_state('Enter a valid unique key and try again.');
    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}
$conn = get_cdat_pdo();

$sql1 = "CREATE TEMP TABLE temp_jrms_temp AS SELECT DISTINCT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LENGTH(RIGHT(NAME,POSITION('/' IN REVERSE(NAME))))>1 THEN RIGHT(NAME,POSITION('/' IN REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
TO_CHAR(NULLIF(TRIM(admission_to_jail), '')::date, 'YYYY-MM-DD') AS add_to_jail,
TO_CHAR(NULLIF(TRIM(releasedt), '')::date, 'YYYY-MM-DD') AS release_date, photo  FROM 
jrms_total_2012_to_2017
WHERE UNIQUE_KEY = :uk";

$sql2 = "SELECT PRISONERNO,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE, photo,CASE WHEN IDPROOF!='' AND IDPROOF ~ '^[0-9]+$' AND IDPROOF in (select distinct AADHAR_NO FROM ir_particulars) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND IDPROOF ~ '^[0-9]+$' AND 
IDPROOF in (select distinct AADHAR_NO FROM ir_particulars) THEN (SELECT DISTINCT (MAX(IRKEY)::varchar) IRKEY FROM ir_particulars WHERE 
AADHAR_NO !='' AND AADHAR_NO=(IDPROOF)::varchar)  ELSE '' END IRKEY FROM temp_jrms_temp ORDER BY JAILNAME, RELEASE_DATE DESC LIMIT 501";

$sql6 = "SELECT 'ACCUSED RELEASED FROM JAIL' PHONE ";

$st1 = $conn->prepare($sql1);
$st1->execute([':uk' => $uniqueKey]);
$st2 = $conn->query($sql2);
$st6 = $conn->query($sql6);

$banner = 'ACCUSED RELEASED FROM JAIL';
if ($st6 && ($b = $st6->fetch(PDO::FETCH_ASSOC))) {
    $banner = (string) ($b['PHONE'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st2);
$truncated = count($rows) > 500;
if ($truncated) {
    $rows = array_slice($rows, 0, 500);
}
cdat_sum_results_open();
cdat_sum_report_banner($banner . ($truncated ? ' (first 500 matches)' : ''));
if (empty($rows)) {
    cdat_sum_empty_state('No JRMS records found for that unique key.');
} else {
    cdat_sum_generic_table_open(
        'JRMS Unique Key',
        ['PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
        'results_table',
        'jrms_uniquekey.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        $irKey = (string) ($row['IRKEY'] ?? '');
        $irForm = (string) ($row['IRFORM'] ?? '');
        $phone = (string) ($row['PHONE'] ?? '');
        cdat_sum_table_row([
            (string) ($row['PSARRESTED'] ?? ''),
            (string) ($row['NAME'] ?? ''),
            (string) ($row['FATHERSNAME'] ?? ''),
            (string) ($row['CRIMENOS'] ?? ''),
            (string) ($row['HEADOFCRIME'] ?? ''),
            ['html' => '<a href="' . htmlspecialchars(cdat_page('cdatcnts.php')) . '?PHONE_NO=' . cdat_sum_h(urlencode($phone)) . '">' . cdat_sum_h($phone) . '</a>', 'class' => 'sum-cell-num'],
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
}
cdat_sum_results_close();
$conn = null;
if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
