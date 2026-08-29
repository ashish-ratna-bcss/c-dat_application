<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once CDAT_COMMON . '/sql_safe.php';

    $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('Day / Night Location');
            cdat_sum_page_open();
        }
        $conn = get_cdat_pdo();
                $numberSql = cdr_escape_sql_literal_local($number);

        $dn_top_towers = static function ($conn, string $numberSql, string $timePredicate): array {
            $sql = "SELECT 
                        PHONE,
                        CELLTOWERID,
                        COUNT(CELLTOWERID) AS CALLS
                    FROM CDATPCSUSPECT
                    WHERE PHONE = '{$numberSql}'
                      AND ({$timePredicate})
                    GROUP BY PHONE, CELLTOWERID
                    ORDER BY CALLS DESC
                    LIMIT 10";

            $st = $conn->query($sql);
            if ($st === false) {
                return [];
            }

            $rows = [];
            while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
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

        $dayPred = "TO_CHAR(STARTTIME, 'HH24:MI:SS')<'22:00:00' AND TO_CHAR(STARTTIME, 'HH24:MI:SS')>'05:00:00'";
        $nightPred = "TO_CHAR(STARTTIME, 'HH24:MI:SS')>'22:00:00' OR TO_CHAR(STARTTIME, 'HH24:MI:SS')<'07:00:00'";

        $dayRows = $dn_top_towers($conn, $numberSql, $dayPred);
        $nightRows = $dn_top_towers($conn, $numberSql, $nightPred);

        $towerIds = array_merge(
            array_column($dayRows, 'CELLTOWERID'),
            array_column($nightRows, 'CELLTOWERID')
        );
        $towerMap = cdat_fetch_tower_map_local($conn, $towerIds);

        echo '<div class="sum-results">';
        $dn_render_loc_table($dayRows, $towerMap, 'Day Location of Mobile No: ' . $number, 'day_loc_table', 'day_location.csv');
        $dn_render_loc_table($nightRows, $towerMap, 'Night Location of Mobile No: ' . $number, 'night_loc_table', 'night_location.csv');
        echo '</div>';

        $conn = null;

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

function cdr_escape_sql_literal_local(string $value): string
{
    return str_replace("'", "''", $value);
}

function cdat_celltower_short_id_local(?string $cellTowerId): string
{
    $cellTowerId = trim((string) $cellTowerId);
    if ($cellTowerId === '') {
        return '';
    }

    return (string) preg_replace('/^[^-]+-[^-]+-/', '', $cellTowerId);
}

function cdat_fetch_tower_map_local($conn, array $rawCellTowerIds): array
{
    $shortByRaw = [];
    foreach ($rawCellTowerIds as $rawId) {
        $rawId = trim((string) $rawId);
        if ($rawId === '') {
            continue;
        }
        $shortByRaw[$rawId] = cdat_celltower_short_id_local($rawId);
    }

    $shortIds = array_values(array_unique(array_filter($shortByRaw)));
    if ($shortIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($shortIds), '?'));
    $sql = "SELECT DISTINCT ON (celltowerid)
                celltowerid,
                COALESCE(operator, '') AS operator,
                COALESCE(state, '') AS state,
                COALESCE(siteaddress, areadescription, '') AS areadescription,
                COALESCE(lat, '') AS lat,
                COALESCE(long, '') AS long,
                COALESCE(azimuth, '') AS azimuth
            FROM cdatcelltowerareanew
            WHERE celltowerid IN ($placeholders)
            ORDER BY celltowerid, lastupdate DESC NULLS LAST";

    $st = $conn->prepare($sql);
    $st->execute($shortIds);
    if ($st === false) {
        return [];
    }

    $byShort = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $byShort[$row['CELLTOWERID']] = [
            'operator' => $row['OPERATOR'] ?? '',
            'state' => $row['STATE'] ?? '',
            'areadescription' => $row['AREADESCRIPTION'] ?? '',
            'lat' => $row['LAT'] ?? '',
            'long' => $row['LONG'] ?? '',
            'azimuth' => $row['AZIMUTH'] ?? '',
        ];
    }

    $byRaw = [];
    foreach ($shortByRaw as $rawId => $shortId) {
        if ($shortId !== '' && isset($byShort[$shortId])) {
            $byRaw[$rawId] = $byShort[$shortId];
        }
    }

    return $byRaw;
}
