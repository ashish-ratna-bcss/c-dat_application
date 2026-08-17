<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CDAT_COMMON . '/cdr_enrichment_sql.php';
    require_once CDAT_COMMON . '/sql_safe.php';

    $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('Day / Night Location');
            cdat_sum_page_open();
        }

        $serverName = 'CPHYDERABAD1\\DAU_HYD_2023';
        $connectionInfo = ['Database' => 'CDATDUPL'];
        $conn = sqlsrv_connect($serverName, $connectionInfo);
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $numberSql = cdr_escape_sql_literal($number);

        $dn_top_towers = static function ($conn, string $numberSql, string $timePredicate): array {
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
        };

        $dn_render_loc_table = static function (array $rows, array $towerMap, string $title, string $tableId, string $exportName): void {
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
        };

        $dayPred = "CONVERT(CHAR(8),STARTTIME,108)<'22:00:00' AND CONVERT(CHAR(8),STARTTIME,108)>'05:00:00'";
        $nightPred = "CONVERT(CHAR(8),STARTTIME,108)>'22:00:00' OR CONVERT(CHAR(8),STARTTIME,108)<'07:00:00'";

        $dayRows = $dn_top_towers($conn, $numberSql, $dayPred);
        $nightRows = $dn_top_towers($conn, $numberSql, $nightPred);

        $towerIds = array_merge(
            array_column($dayRows, 'CELLTOWERID'),
            array_column($nightRows, 'CELLTOWERID')
        );
        $towerMap = cdat_fetch_tower_map($conn, $towerIds);

        echo '<div class="sum-results">';
        $dn_render_loc_table($dayRows, $towerMap, 'Day Location of Mobile No: ' . $number, 'day_loc_table', 'day_location.csv');
        $dn_render_loc_table($nightRows, $towerMap, 'Night Location of Mobile No: ' . $number, 'night_loc_table', 'night_location.csv');
        echo '</div>';

        sqlsrv_close($conn);

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }

    if ($isAjax) {
        cdat_sum_empty_state('Enter a mobile number and try again.');
        exit;
    }
}

layout_begin('Day / Night Location');
cdat_sum_page_open();
cdat_sum_search_card(
    'Top 10 Day and Night Locations',
    'Find the top day and night cell tower locations for a mobile number.',
    'day&nightloc.php',
    cdat_sum_field_phone()
);
cdat_sum_page_close();
layout_end();
