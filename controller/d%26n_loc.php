<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';
require_once __DIR__ . '/cdr_enrichment_sql.php';
require_once __DIR__ . '/sql_safe.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: day%26nightloc.php');
    exit;
}

if (!$isAjax) {
    layout_begin('D & N Loc');
    cdat_sum_page_open();
    cdat_sum_back_link('day%26nightloc.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array("Database" => "CDATDUPL");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
if ($number === '') {
    cdat_sum_empty_state('Invalid mobile number.');
    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}
$numberSql = cdr_escape_sql_literal($number);

function dn_enc_top_towers($conn, string $numberSql, string $timePredicate): array
{
    $sql = "SELECT TOP 10
                PHONE,
                CELLTOWERID,
                COUNT(CELLTOWERID) AS CALLS
            FROM CDATDUPL.DBO.CDATPCSUSPECT
            WHERE PHONE = '{$numberSql}'
              AND ({$timePredicate})
            GROUP BY PHONE, CELLTOWERID
            ORDER BY CALLS DESC";

    $st = sqlsrv_query($conn, $sql);
    if ($st === false) {
        return [];
    }

    $rows = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function dn_enc_render_loc_table(array $rows, array $towerMap, string $title, string $tableId, string $exportName): void
{
    if (empty($rows)) {
        cdat_sum_report_banner($title);
        cdat_sum_empty_state('No location records found');
        return;
    }

    cdat_sum_report_banner($title);
    cdat_sum_generic_table_open(
        $title,
        ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
        $tableId,
        $exportName,
        count($rows)
    );
    foreach ($rows as $row) {
        $cid = $row['CELLTOWERID'] ?? '';
        $tower = $towerMap[$cid] ?? [
            'areadescription' => '',
            'lat' => '',
            'long' => '',
            'azimuth' => '',
        ];
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['text' => (string) $cid, 'class' => 'sum-cell-num'],
            ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
            ['html' => cdat_sum_address_lines((string) $tower['areadescription']), 'class' => 'sum-address-cell'],
            (string) $tower['lat'],
            (string) $tower['long'],
            (string) $tower['azimuth'],
        ]);
    }
    cdat_sum_generic_table_close();
}

$dayPred = "CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00'";
$nightPred = "CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00'";

$dayRows = dn_enc_top_towers($conn, $numberSql, $dayPred);
$nightRows = dn_enc_top_towers($conn, $numberSql, $nightPred);

$towerIds = array_merge(
    array_column($dayRows, 'CELLTOWERID'),
    array_column($nightRows, 'CELLTOWERID')
);
$towerMap = cdat_fetch_tower_map($conn, $towerIds);

cdat_sum_results_open();
dn_enc_render_loc_table($dayRows, $towerMap, 'DAY LOCATION OF MOBILE NO: ' . $number, 'day_loc_table', 'day_location.csv');
dn_enc_render_loc_table($nightRows, $towerMap, 'NIGHT LOCATION OF MOBILE NO: ' . $number, 'night_loc_table', 'night_location.csv');
cdat_sum_results_close();

sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
