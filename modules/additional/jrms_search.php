<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$crimeHeadForm = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$crimeHead = trim((string) ($_POST['CRIMEHEAD'] ?? ''));
$hasDateSearch = $fromDt !== '' && $toDt !== '';

$fieldsHtml = cdat_sum_field_text('NAME', 'Name', $name, 'NAME', 'Enter NAME')
            . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHeadForm, 'CRIME_HEAD', 'Enter CRIME HEAD');

if ($hasDateSearch) {
    if (!$isAjax) {
        layout_begin('JRMS Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender IR Search By Name',
            'Search JRMS records. Date search from Search By Date uses this page.',
            'jrms_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }

    $serverName = "10.10.46.14\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $CRIMEHEAD = $crimeHead;
    $f_date = $fromDt;
    $t_date = $toDt;

    $sql1 = "SET DATEFORMAT DMY SELECT DISTINCT PRISONERNO,UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,MOBILENO PHONE,
CASE WHEN LEN(RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))))>1 THEN RIGHT(NAME,CHARINDEX('/',REVERSE(NAME))-1) ELSE '' END IDPROOF,
ADDR_DURINGRELEASE ADDR_DURING_RELEASE,GENDER,JAILNAME,
CONVERT(VARCHAR(20),CONVERT(DATE,ADMISSION_TO_JAIL)) ADD_TO_JAIL,CONVERT(VARCHAR(20),CONVERT(DATE,RELEASEDT)) RELEASE_DATE,PHOTO INTO #TEMP FROM
JRMS..JRMS_TOTAL_2012_TO_2017
WHERE  (CONVERT(DATE,RELEASEDT) BETWEEN '$f_date' AND '$t_date') AND HEADOFCRIME LIKE '%'+'$CRIMEHEAD'+'%' AND HEADOFCRIME!='' ";

    $sql11 = "select distinct UNIQUE_KEY,COUNT(UNIQUE_KEY) NO_OF_TIMES_RELEASED INTO #COUNT from JRMS..JRMS_TOTAL_2012_TO_2017
GROUP BY UNIQUE_KEY";

    $sql2 = "SELECT PRISONERNO,A.UNIQUE_KEY,PSARRESTED,NAME,FATHERSNAME,CRIMENOS,HEADOFCRIME,NO_OF_TIMES_RELEASED
NO_OF_TIMES_RELEASED,PHONE,IDPROOF,ADDR_DURING_RELEASE,
JAILNAME,ADD_TO_JAIL,RELEASE_DATE,CONVERT(IMAGE,PHOTO) PHOTO,CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN 'IR AVAILABLE' ELSE '' END IRFORM,
CASE WHEN IDPROOF!='' AND ISNUMERIC(IDPROOF)='1' AND
IDPROOF in (select distinct AADHAR_NO FROM FORMS..IR_PARTICULARS) THEN (SELECT DISTINCT CONVERT(VARCHAR(20),MAX(IRKEY)) IRKEY FROM FORMS..IR_PARTICULARS WHERE
AADHAR_NO !='' AND AADHAR_NO=CONVERT(VARCHAR(20),IDPROOF))  ELSE '' END IRKEY FROM #TEMP A
LEFT JOIN #COUNT B ON A.UNIQUE_KEY=B.UNIQUE_KEY ORDER BY JAILNAME, RELEASE_DATE DESC";

    $sql6 = "SELECT 'ACCUSED RELEASED FROM: '+'$f_date'+' TO: '+'$t_date' + ' UNDER CRIME HEAD ' + '$CRIMEHEAD' AS PHONE";

    sqlsrv_query($conn, $sql1);
    sqlsrv_query($conn, $sql11);
    $st2 = sqlsrv_query($conn, $sql2);
    $st6 = sqlsrv_query($conn, $sql6);

    $banner = 'ACCUSED RELEASED FROM: ' . $f_date . ' TO: ' . $t_date . ' UNDER CRIME HEAD ' . $CRIMEHEAD;
    if ($st6 && ($b = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st2);

    if (empty($rows)) {
        cdat_sum_empty_state('No JRMS records found.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'JRMS Search',
            ['PSARRESTED', 'NAME', 'FATHERSNAME', 'CRIMENOS', 'HEADOFCRIME', 'CRIMES INVOLVED', 'PHONE', 'IDPROOF', 'ADDR_DURING_RELEASE', 'JAILNAME', 'ADD_TO_JAIL', 'RELEASEDT', 'IMAGE', 'IRFORM'],
            'results_table',
            'jrms_search.csv',
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
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('JRMS Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender IR Search By Name',
    'Search JRMS records by name and crime head.',
    'jrms_search.php',
    $fieldsHtml,
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
