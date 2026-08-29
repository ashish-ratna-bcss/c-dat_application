<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and try again.');

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
    require_once CDAT_COMMON . '/activity_logger.php';
    audit_log('Movements / Call Details', 'Search', ['phone_number' => $number]);

    $conn = get_cdat_pdo();

    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    if ($page <= 0) {
        $page = 1;
    }
    $limit = 100000;
    $offset = ($page - 1) * $limit;

    $sql = "
SELECT DISTINCT
    A.PHONE, A.OTHER, COALESCE(C.NICKNAME,'') AS NICKNAME,
    TO_CHAR(A.STARTTIME, 'YYYY-MM-DD') AS DATE1,
    TO_CHAR(A.STARTTIME, 'HH24:MI:SS') AS TIME1,
    TO_CHAR(A.STARTTIME, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME,
    A.DURATION,
    CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
    A.IMEINUMBER, A.CELLTOWERID
FROM CDATPCSUSPECT A 
LEFT JOIN CDATSUSPECT C  ON A.OTHER = C.PHONE
WHERE A.PHONE = ?
ORDER BY STARTTIME ASC
LIMIT ? OFFSET ?";

    $st = $conn->prepare($sql);
    $st->execute([$number, $offset, $limit]);
    if ($st === false) {
        die(print_r(error_get_last(), true));
    }

    $count_stmt = $conn->prepare('SELECT COUNT(*) AS TOTAL FROM CDATPCSUSPECT  WHERE PHONE = ?');
    $count_stmt->execute([$number]);
    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $total_records = (int) ($count_row['TOTAL'] ?? 0);

    $rows = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    $towerMap = cdat_fetch_tower_map_local($conn, array_column($rows, 'CELLTOWERID'));

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

    $st = null;
    $conn = null;

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
