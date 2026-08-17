<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$crimeHead = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$gender = trim((string) ($_POST['GENDER'] ?? ''));
$hasSearch = $crimeHead !== '' && $gender !== '';
cdat_sum_ajax_need_search($hasSearch, 'Select crime head and gender and try again.');

$genderOptions = [
    '' => 'PLZ Select Gender',
    'FEMALE' => 'FEMALE',
    'MALE' => 'MALE',
    'TRANSGENDER' => 'TRANSGENDER',
];
$fieldsHtml = cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHead, 'CRIME_HEAD', 'Enter CRIME HEAD')
            . cdat_sum_searchable_select('GENDER', 'Gender of the Offender', $genderOptions, $gender, 'PLZ Select Gender', true);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IR Search By Head Gender');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender IR Search By MO/Crime Head and Gender',
            'Search IR records by crime head / MO and gender.',
            'ir_search_by_head_gender.php',
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

    $number = $gender;
    $number1 = $crimeHead;

    $sql8 = "SELECT 'DETAILS OF : '+'$number' as PHONE1";

    $sql9 = "SELECT DISTINCT A.IRKEY,(CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM PDACT..PDACT_MAIN_TABLE
WHERE ISNUMERIC(IRKEY)=1) THEN (SELECT DISTINCT CONVERT(VARCHAR(20), MAX(PDACT_KEY)) FROM PDACT..PDACT_MAIN_TABLE
WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND ISNUMERIC(IRKEY)='1')
ELSE '' END PDACT_KEY,NAME,ALIAS_NAME,FATHER_NAME,AGE,SEX,PRESENT_ADDRESS,CRIME_HEAD,MO,CRIME_NO,YEAR,SEC_OF_LAW,POLICE_STATION,CONVERT(VARCHAR(20),DATE_OF_ARREST) DATE_OF_ARREST FROM FORMS..IR_PARTICULARS A
INNER JOIN FORMS..OFFENCE_DETAILS B ON A.SEX ='$number' AND (B.CRIME_HEAD LIKE '%'+REPLACE('$number1',' ','%')+'%' OR
B.MO LIKE '%'+REPLACE('$number1',' ','%')+'%') AND
ltrim(rtrim('$number'))!=''  AND A.IRKEY=B.IRKEY ORDER BY DATE_OF_ARREST DESC";

    $st8 = sqlsrv_query($conn, $sql8);
    $st9 = sqlsrv_query($conn, $sql9);

    $banner = 'DETAILS OF : ' . $number;
    if ($st8 && ($b = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No IR records found.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'IR Search By Head and Gender',
            ['IRKEY', 'PDACT', 'ACCUSED NAME', 'ALIAS NAME', 'FATHER NAME', 'AGE', 'SEX', 'PRESENT ADDRESS', 'CRIME NO', 'YEAR', 'SEC_OF_LAW', 'POLICE STATION', 'CRIME HEAD', 'MO', 'DOA'],
            'results_table',
            'ir_head_gender.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $pdact = (string) ($row['PDACT'] ?? '');
            cdat_sum_table_row([
                ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => $pdact !== '' ? '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdact) . '</a>' : ''],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['ALIAS_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['SEX'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRIME_NO'] ?? ''),
                (string) ($row['YEAR'] ?? ''),
                (string) ($row['SEC_OF_LAW'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
                ['text' => (string) ($row['DATE_OF_ARREST'] ?? ''), 'class' => 'sum-cell-date'],
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

layout_begin('IR Search By Head Gender');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender IR Search By MO/Crime Head and Gender',
    'Search IR records by crime head / MO and gender.',
    'ir_search_by_head_gender.php',
    cdat_sum_field_text('CRIME_HEAD', 'Crime Head', '', 'CRIME_HEAD', 'Enter CRIME HEAD')
        . cdat_sum_searchable_select('GENDER', 'Gender of the Offender', $genderOptions, '', 'PLZ Select Gender', true),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
