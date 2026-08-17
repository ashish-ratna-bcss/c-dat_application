<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';
require_once CDAT_COMMON . '/dbcontroller.php';

$isAjax = cdat_sum_is_ajax();
$ps = trim((string) ($_POST['POLICE_STATION'] ?? ''));
$hasSearch = $ps !== '';
cdat_sum_ajax_need_search($hasSearch, 'Select a police station and try again.');

$db_handle = new DBController();
$query = "SELECT DISTINCT UPPER(LTRIM(RTRIM(POLICE_STATION))) POLICE_STATION FROM CDATDUPL..ROWDY_SHEETER_DATA1";
$psRows = $db_handle->runQuery($query) ?: [];
$psOptions = ['' => 'Select Police Station'];
foreach ($psRows as $r) {
    $v = (string) ($r['POLICE_STATION'] ?? '');
    if ($v !== '') {
        $psOptions[$v] = $v;
    }
}

$fieldsHtml = cdat_sum_searchable_select(
    'POLICE_STATION',
    'Police Station',
    $psOptions,
    $ps,
    'Select Police Station',
    true
);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Rowdy Sheeter By PS');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Rowdy Sheet Search By Police Station',
            'Search rowdy sheeter records for a police station.',
            'rowdysheeter_ps_wise_search.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array('Database' => 'CDATDUPL');
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $ps;

    $sql0 = "SELECT DISTINCT IRKEY,PDACT_KEY,NAME,AGE,FATHER_NAME,PHONE,PRESENT_ADDRESS,LAT_P PRESENT_ADDRESS_LAT,
LONG_P PRESENT_ADDRESS_LONG,PERMANENT_ADDRESS,LAT PERMANENT_ADD_LAT,LONG PERMANENT_ADD_LONG,ID_PROOF_TYPE+' '+ID_NO IDPROOF,
COMMUNAL_NONCOMMUNAL COMMUNAL_STATUS,LATEST_BIND_OVER_DATE BIND_OVER_DATE,POLICE_STATION,PRESENT_ACTIVITY,DATE_OF_OPENING_RWD INTO #TEMP FROM ROWDY_SHEETER_DATA1
WHERE POLICE_STATION LIKE '%$number%'";

    $sql1 = "select PDACT_KEY,A.IRKEY,NAME,FATHER_NAME,AGE,PHONE,PRESENT_ADDRESS,PERMANENT_ADDRESS,PRESENT_ACTIVITY,IDPROOF,COMMUNAL_STATUS,
CONVERT(VARCHAR(20),DATE_OF_OPENING_RWD) AS DATE_OF_OPENING_RWD,POLICE_STATION,CASE WHEN CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY)
THEN IMAGE ELSE (SELECT IMAGE FROM FORMS..IMAGE_TABLE WHERE IRKEY='113769')END  AS IMAGE
FROM #TEMP A LEFT JOIN FORMS..IMAGE_TABLE B ON CONVERT(VARCHAR(20),A.IRKEY)=CONVERT(VARCHAR(20),B.IRKEY) ";

    sqlsrv_query($conn, $sql0);
    $st1 = sqlsrv_query($conn, $sql1);
    $rows = cdat_sum_fetch_all($st1);

    if (empty($rows)) {
        cdat_sum_empty_state('No rowdy sheeter records found for: ' . $ps);
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner('ROWDY SHEET INFORMATION');
        cdat_sum_generic_table_open(
            'Rowdy Sheeter',
            ['NAME', 'FATHER_NAME', 'AGE', 'PHONE', 'PRESENT ADDRESS', 'PERMANENT_ADDRESS', 'PRESENT ACTIVITY', 'IDPROOF', 'DATE OF OPENING RWD', 'POLICE STATION', 'PDACT KEY', 'IRKEY', 'IMAGE'],
            'results_table',
            'rowdysheeter.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $pdactKey = (string) ($row['PDACT_KEY'] ?? '');
            $irKey = (string) ($row['IRKEY'] ?? '');
            cdat_sum_table_row([
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['text' => (string) ($row['AGE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['PRESENT_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                ['html' => cdat_sum_address_lines((string) ($row['PERMANENT_ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['PRESENT_ACTIVITY'] ?? ''),
                (string) ($row['IDPROOF'] ?? ''),
                ['text' => (string) ($row['DATE_OF_OPENING_RWD'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['POLICE_STATION'] ?? ''),
                ['html' => '<a href="' . htmlspecialchars(cdat_page('pdact_main.php')) . '?PDACT_KEY=' . cdat_sum_h(urlencode($pdactKey)) . '">' . cdat_sum_h($pdactKey) . '</a>'],
                ['html' => '<a href="' . htmlspecialchars(cdat_page('ir.php')) . '?IRKEY=' . cdat_sum_h(urlencode($irKey)) . '">' . cdat_sum_h($irKey) . '</a>', 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_img_html($row['IMAGE'] ?? '', 120, 120), 'class' => 'sum-cell-img'],
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

layout_begin('Rowdy Sheeter By PS');
cdat_sum_page_open();
cdat_sum_search_card(
    'Rowdy Sheet Search By Police Station',
    'Search rowdy sheeter records for a police station.',
    'rowdysheeter_ps_wise_search.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
