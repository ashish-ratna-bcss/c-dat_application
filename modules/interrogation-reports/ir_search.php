<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$crimeHead = trim((string) ($_POST['CRIME_HEAD'] ?? ''));
$hasSearch = $name !== '' && $crimeHead !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a name and crime head and try again.');

$fieldsHtml = cdat_sum_field_text('NAME', 'Name of the Offender', $name, 'NAME', 'Enter NAME')
            . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', $crimeHead, 'CRIME_HEAD', 'Enter CRIME HEAD');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('IR Search By Name');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Offender IR Search By Name',
            'Search IR records by offender name and crime head.',
            'ir_search.php',
            $fieldsHtml,
            'BTN_CDAT',
            'Submit'
        );
    }
    $conn = get_cdat_pdo();
    require_once CDAT_COMMON . '/sql_safe.php';
    $namePattern = '%' . str_replace(' ', '%', sql_safe_like_value($name, 200)) . '%';
    $crimePattern = '%' . str_replace(' ', '%', sql_safe_like_value($crimeHead, 200)) . '%';
    $nameSafe = sql_safe_like_value($name, 200);

        // Use parameterized queries to prevent SQL injection
    $sql8 = "SELECT 'DETAILS OF : ' || ? as PHONE1";
    $params8 = array($nameSafe);
    $st8 = $conn->prepare($sql8);
    $st8->execute($params8);
    

    $sql9 = "SELECT DISTINCT A.IRKEY,
                    (CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM pdact_main_table
                    WHERE IRKEY ~ '^[0-9]+$') THEN 'PDACT IS IMPOSED CLICK HERE TO VIEW THE DETAILS' ELSE '' END) PDACT,
                    CASE WHEN A.IRKEY IN (SELECT DISTINCT REPLACE(IRKEY,' ','') FROM pdact_main_table
                    WHERE IRKEY ~ '^[0-9]+$') THEN (SELECT DISTINCT (MAX(PDACT_KEY)::varchar) FROM pdact_main_table 
                    WHERE REPLACE(IRKEY,' ','')=A.IRKEY AND IRKEY ~ '^[0-9]+$') 
                    ELSE '' END PDACT_KEY,
                    A.NAME,A.ALIAS_NAME,A.FATHER_NAME,A.AGE,A.PRESENT_ADDRESS,A.CRIME_HEAD,A.MO,A.CRIME_NO,A.YEAR,A.SEC_OF_LAW,A.POLICE_STATION,
                    (A.DATE_OF_ARREST)::varchar DATE_OF_ARREST 
                    FROM ir_particulars A
                    INNER JOIN OFFENCE_DETAILS B ON A.NAME LIKE ? 
                    AND (B.CRIME_HEAD LIKE ? OR 
                    B.MO LIKE ?) 
                    AND LTRIM(RTRIM(?)) != '' 
                    AND LENGTH(REPLACE(?, ' ', '')) > '4' 
                    AND A.IRKEY = B.IRKEY 
                    ORDER BY DATE_OF_ARREST DESC";

    $params9 = array($namePattern, $crimePattern, $crimePattern, $nameSafe, $nameSafe);
    $st9 = $conn->prepare($sql9);
    $st9->execute($params9);
    

    if ($st9 === false) {
        //  die(print_r(error_get_last(), true));
    }

    $bannerTitle = 'DETAILS OF : ' . $name;
    if ($st8 && ($bannerRow = $st8->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE1'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st9);

    if (empty($rows)) {
        cdat_sum_empty_state('No records found for the search criteria');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'IR Search Results',
            ['IRKEY', 'PDACT', 'ACCUSED NAME', 'ALIAS NAME', 'FATHER NAME', 'AGE', 'PRESENT ADDRESS', 'CRIME NO', 'YEAR', 'SEC_OF_LAW', 'POLICE STATION', 'CRIME HEAD', 'MO', 'DOA'],
            'results_table',
            'ir_search.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $irKey = (string) ($row['IRKEY'] ?? '');
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $pdactText = (string) ($row['PDACT'] ?? '');
            $addressHtml = cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? ''));

            $irKeyCell = [
                'html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>',
                'class' => 'sum-cell-num',
            ];
            $pdactCell = $pdactText !== ''
                ? [
                    'html' => '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactText) . '</a>',
                ]
                : '';

            cdat_sum_table_row([
                $irKeyCell,
                $pdactCell,
                (string) ($row['NAME'] ?? ''),
                (string) ($row['ALIAS_NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => $addressHtml !== '' ? $addressHtml : '—', 'class' => 'sum-address-cell'],
                (string) ($row['CRIME_NO'] ?? ''),
                (string) ($row['YEAR'] ?? ''),
                (string) ($row['SEC_OF_LAW'] ?? ''),
                (string) ($row['POLICE_STATION'] ?? ''),
                (string) ($row['CRIME_HEAD'] ?? ''),
                (string) ($row['MO'] ?? ''),
                (string) ($row['DATE_OF_ARREST'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st9) {
        $st9 = null;
    }
    if ($conn) {
        $conn = null;
    }

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('IR Search By Name');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender IR Search By Name',
    'Search IR records by offender name and crime head.',
    'ir_search.php',
    cdat_sum_field_text('NAME', 'Name of the Offender', '', 'NAME', 'Enter NAME')
        . cdat_sum_field_text('CRIME_HEAD', 'Crime Head', '', 'CRIME_HEAD', 'Enter CRIME HEAD'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
