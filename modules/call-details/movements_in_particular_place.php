<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax  = cdat_sum_is_ajax();
$number  = trim((string) ($_POST['PHONE']  ?? ''));
$lat     = trim((string) ($_POST['LAT']    ?? ''));
$long    = trim((string) ($_POST['LONG']   ?? ''));
$range   = trim((string) ($_POST['RANGE']  ?? ''));
$hasSearch = $number !== '' && $lat !== '' && $long !== '' && $range !== '';

cdat_sum_ajax_need_search($hasSearch, 'Enter all required fields and try again.');

/* ---- field helpers ---- */
$rangeOptions = '';
for ($m = 100; $m <= 5000; $m += 100) {
    $sel = ($range === (string) $m) ? ' selected' : '';
    $rangeOptions .= '<option value="' . $m . '"' . $sel . '>' . $m . ' m</option>';
}

$fieldsHtml =
    cdat_sum_field_phone($number) .
    cdat_sum_field_text('LAT',   'Latitude',       $lat,   'LAT',   'e.g. 17.385044', true, 'decimal') .
    cdat_sum_field_text('LONG',  'Longitude',      $long,  'LONG',  'e.g. 78.486671', true, 'decimal') .
    '<div class="sum-search-form__field col-12 col-sm-6 col-lg-3">'
        . '<label class="form-label fw-semibold" for="RANGE">Range (metres)</label>'
        . '<select name="RANGE" id="RANGE" class="form-select" required>'
        . '<option value="">-- Select --</option>'
        . $rangeOptions
        . '</select>'
    . '</div>';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Movements in Particular Place');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Movements in Particular Place',
            'Find movements of a mobile number within a given radius of a location.',
            'movements_in_particular_place.php',
            $fieldsHtml,
            'BTN_SUM',
            'Search'
        );
    }

    set_time_limit(0);
    require_once CDAT_COMMON . '/activity_logger.php';
        audit_log('Movements in Particular Place', 'Search', [
        'phone_number' => $number,
        'lat'   => $lat,
        'long'  => $long,
        'range' => $range,
    ]);
    $conn = get_cdat_pdo();
        /* Step 1 – pull raw records for the phone into a temp table */
    $sql2 = "CREATE TEMP TABLE temp_tt AS SELECT DISTINCT PHONE, OTHER,
                TO_CHAR((STARTTIME)::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME, DURATION,
                CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
                IMEINUMBER, CELLTOWERID, STATE_KEY, PROVIDER_KEY
              FROM CDATPCSUSPECT
             WHERE PHONE = ?";

    /* Step 2 – join with cell tower table */
    $sql3 = "CREATE TEMP TABLE temp_ttp AS SELECT DISTINCT
                A.PHONE, OTHER,
                CASE WHEN C.PHONE IS NOT NULL THEN C.NICKNAME ELSE '' END AS NICKNAME,
                STARTTIME, DURATION, TYPE, A.IMEINUMBER, A.CELLTOWERID, OPERATOR,
                (CASE WHEN A.CELLTOWERID = B.CELLTOWERID
                      THEN MAX(SITEADDRESS) ELSE '' END || ', LAST_UPDATE:' || TO_CHAR((LASTUPDATE)::timestamp, 'YYYY-MM-DD HH24:MI:SS')) AS AREADESCRIPTION,
                LAT, LONG, AZIMUTH AS AZM
              FROM temp_tt A
             INNER JOIN CDATCELLTOWERAREANEW B
                ON A.CELLTOWERID = B.CELLTOWERID
               AND A.STATE_KEY    = B.STATE_KEY
               AND A.PROVIDER_KEY = B.PROVIDER_KEY
             LEFT JOIN CDATSUSPECT C ON A.OTHER = C.PHONE
             WHERE B.LASTUPDATE = (
                 SELECT MAX(LASTUPDATE)
                 FROM CDATCELLTOWERAREANEW X
                 WHERE X.CELLTOWERID   = B.CELLTOWERID
                   AND X.PROVIDER_KEY  = B.PROVIDER_KEY
                   AND X.STATE_KEY     = B.STATE_KEY
             )
             GROUP BY A.PHONE, OTHER, C.NICKNAME, C.PHONE, STARTTIME, DURATION, TYPE,
                      A.IMEINUMBER, A.CELLTOWERID, B.CELLTOWERID, LASTUPDATE,
                      OPERATOR, A.STATE_KEY, B.STATE_KEY, A.PROVIDER_KEY,
                      B.PROVIDER_KEY, LAT, LONG, AZIMUTH";

    /* Step 3 – distance filter (haversine, metres) */
    $sql4 = "SELECT PHONE, OTHER, NICKNAME, STARTTIME, DURATION, TYPE, CELLTOWERID,
                    CAST(
                        6371000 * acos(LEAST(1.0, GREATEST(-1.0,
                            cos(radians(?)) * cos(radians(lat::double precision)) *
                            cos(radians(long::double precision) - radians(?)) +
                            sin(radians(?)) * sin(radians(lat::double precision))
                        )))
                    AS INT) AS DIST,
                    CAST(degrees(atan2(
                        sin(radians(long::double precision - ?)) * cos(radians(lat::double precision)),
                        cos(radians(?)) * sin(radians(lat::double precision)) -
                        sin(radians(?)) * cos(radians(lat::double precision)) * cos(radians(long::double precision - ?))
                    )) AS INT) AS BR,
                    AREADESCRIPTION, OPERATOR, LAT, LONG, AZM
             FROM temp_ttp
             WHERE lat ~ '^-?[0-9]+(\\.[0-9]+)?$' AND lat IS NOT NULL
               AND long ~ '^-?[0-9]+(\\.[0-9]+)?$' AND long IS NOT NULL
               AND lat::double precision BETWEEN ? - 1 AND ? + 1
               AND long::double precision BETWEEN ? - 1 AND ? + 1
               AND 6371000 * acos(LEAST(1.0, GREATEST(-1.0,
                    cos(radians(?)) * cos(radians(lat::double precision)) *
                    cos(radians(long::double precision) - radians(?)) +
                    sin(radians(?)) * sin(radians(lat::double precision))
               ))) < ?
             ORDER BY STARTTIME";

    $st2 = $conn->prepare($sql2);
    $st2->execute([$number]);
    if ($st2 === false) { die(print_r(error_get_last(), true)); }

    $st3 = $conn->query($sql3);
    if ($st3 === false) { die(print_r(error_get_last(), true)); }

    $st4 = $conn->prepare($sql4);
    $st4->execute([$lat, $long, $lat, $long, $lat, $lat, $long, $lat, $lat, $long, $long, $lat, $long, $lat, $range]);
    if ($st4 === false) { die(print_r(error_get_last(), true)); }

    $rows = [];
    while ($row = $st4->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }

    $st2 = null;
    $st3 = null;
    $st4 = null;
    $conn = null;

    if (empty($rows)) {
        cdat_sum_empty_state("No movements found for $number within {$range}m of $lat, $long");
    } else {
        echo '<div class="sum-results">';
        cdat_sum_report_banner(
            "Movements within {$range}m of Lat $lat / Long $long",
            'Phone: ' . $number . ' — ' . count($rows) . ' record(s) found'
        );
        cdat_sum_generic_table_open(
            'Movements in Particular Place',
            ['PHONE', 'OTHER', 'NICKNAME', 'STARTTIME', 'DURATION', 'TYPE',
             'CELLID', 'DIST (m)', 'BEARING', 'AREA DESCRIPTION', 'OPERATOR',
             'LAT', 'LONG', 'AZM'],
            'movements_place_table',
            'movements_in_particular_place.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE']    ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER']    ?? ''), 'class' => 'sum-cell-other'],
                (string) ($row['NICKNAME']            ?? ''),
                ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['DURATION']  ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['TYPE']                ?? ''),
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['DIST']      ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['BR']        ?? ''), 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')),
                 'class' => 'sum-address-cell'],
                (string) ($row['OPERATOR']            ?? ''),
                (string) ($row['LAT']                 ?? ''),
                (string) ($row['LONG']                ?? ''),
                (string) ($row['AZM']                 ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        echo '</div>';
    }

    if ($isAjax) { exit; }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Movements in Particular Place');
cdat_sum_page_open();
cdat_sum_search_card(
    'Movements in Particular Place',
    'Find movements of a mobile number within a given radius of a location.',
    'movements_in_particular_place.php',
    $fieldsHtml,
    'BTN_SUM',
    'Search'
);
cdat_sum_page_close();
layout_end();
