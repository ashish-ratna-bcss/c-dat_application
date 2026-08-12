<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Movements');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Movements of Mobile Number',
            'View detailed call movements for a mobile number.',
            'movements.php',
            cdat_sum_field_phone($number)
        );
    }

    set_time_limit(0);
    require_once __DIR__ . '/activity_logger.php';
    require_once __DIR__ . '/cdr_enrichment_sql.php';
    audit_log('Movements / Call Details', 'Search', ['phone_number' => $number]);

    $serverName = 'CPHYDERABAD1\\DAU_HYD_2023';
    $connectionInfo = ['Database' => 'CDATDUPL'];
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page <= 0) {
        $page = 1;
    }
    $limit = 100000;
    $offset = ($page - 1) * $limit;

    $sql = "
SELECT DISTINCT
    A.PHONE, A.OTHER, ISNULL(C.NICKNAME,'') AS NICKNAME,
    CONVERT(VARCHAR(10),A.STARTTIME,120) AS DATE1,
    CONVERT(VARCHAR(8),A.STARTTIME,108) AS TIME1,
    CONVERT(VARCHAR,A.STARTTIME,120) AS STARTTIME,
    A.DURATION,
    CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
    A.IMEINUMBER, A.CELLTOWERID
FROM CDATDUPL.dbo.CDATPCSUSPECT A WITH (NOLOCK)
LEFT JOIN CDATDUPL.dbo.CDATSUSPECT C WITH (NOLOCK) ON A.OTHER = C.PHONE
WHERE A.PHONE = ?
ORDER BY STARTTIME ASC
OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

    $st = sqlsrv_query($conn, $sql, [$number, $offset, $limit], ['Scrollable' => SQLSRV_CURSOR_KEYSET]);
    if ($st === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $count_stmt = sqlsrv_query($conn, 'SELECT COUNT(*) AS TOTAL FROM CDATDUPL.dbo.CDATPCSUSPECT WITH (NOLOCK) WHERE PHONE = ?', [$number]);
    $count_row = sqlsrv_fetch_array($count_stmt, SQLSRV_FETCH_ASSOC);
    $total_records = (int) ($count_row['TOTAL'] ?? 0);

    $rows = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    $towerMap = cdat_fetch_tower_map($conn, array_column($rows, 'CELLTOWERID'));

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        echo '<div class="sum-results">';
        cdat_sum_report_banner(
            'Call Details of Mobile No: ' . $number,
            'Total Records: ' . number_format($total_records)
        );
        cdat_sum_generic_table_open(
            'Movements',
            ['PHONE', 'OTHER', 'NICKNAME', 'DATE', 'TIME', 'STARTTIME', 'DURATION', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'STATE', 'AREA DESCRIPTION', 'LAT', 'LONG', 'AZM'],
            'contact_results_table',
            'movements.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            $tower = $towerMap[$row['CELLTOWERID']] ?? [
                'operator' => '', 'state' => '', 'areadescription' => '', 'lat' => '', 'long' => '', 'azimuth' => '',
            ];
            cdat_sum_table_row([
                ['text' => $row['PHONE'], 'class' => 'sum-cell-num'],
                ['text' => $row['OTHER'], 'class' => 'sum-cell-other'],
                $row['NICKNAME'],
                ['text' => $row['DATE1'], 'class' => 'sum-cell-date'],
                ['text' => $row['TIME1'], 'class' => 'sum-cell-date'],
                ['text' => $row['STARTTIME'], 'class' => 'sum-cell-date'],
                ['text' => (string) $row['DURATION'], 'class' => 'sum-cell-num'],
                $row['TYPE'],
                ['text' => (string) $row['IMEINUMBER'], 'class' => 'sum-cell-num'],
                ['text' => (string) $row['CELLTOWERID'], 'class' => 'sum-cell-num'],
                $tower['operator'],
                $tower['state'],
                ['html' => cdat_sum_address_lines((string) $tower['areadescription']), 'class' => 'sum-address-cell'],
                $tower['lat'],
                $tower['long'],
                $tower['azimuth'],
            ]);
        }
        cdat_sum_generic_table_close();
        echo '</div>';
    }

    sqlsrv_free_stmt($st);
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Movements');
cdat_sum_page_open();
cdat_sum_search_card(
    'Movements of Mobile Number',
    'View detailed call movements for a mobile number.',
    'movements.php',
    cdat_sum_field_phone()
);
cdat_sum_page_close();
layout_end();
