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
    require_once CDAT_COMMON . '/cdr_enrichment_sql.php';
    audit_log('Movements in Particular Place', 'Search', [
        'phone_number' => $number,
        'lat'   => $lat,
        'long'  => $long,
        'range' => $range,
    ]);

    $serverName     = 'CPHYDERABAD1\\DAU_HYD_2023';
    $connectionInfo = ['Database' => 'CDATDUPL'];
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    /* Step 1 – pull raw records for the phone into a temp table */
    $sql2 = "SELECT DISTINCT PHONE, OTHER,
                CONVERT(VARCHAR, STARTTIME, 20) AS STARTTIME, DURATION,
                CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
                IMEINUMBER, CELLTOWERID, STATE_KEY, PROVIDER_KEY
             INTO #TT
             FROM CDATDUPL.DBO.CDATPCSUSPECT
             WHERE PHONE = ?";

    /* Step 2 – join with cell tower table */
    $sql3 = "SELECT DISTINCT
                A.PHONE, OTHER,
                CASE WHEN OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
                     THEN NICKNAME ELSE '' END AS NICKNAME,
                STARTTIME, DURATION, TYPE, A.IMEINUMBER, A.CELLTOWERID, OPERATOR,
                (CASE WHEN A.CELLTOWERID = B.CELLTOWERID
                      THEN MAX(SITEADDRESS) ELSE '' END
                 + ', LAST_UPDATE:' + CONVERT(VARCHAR, LASTUPDATE, 20)) AS AREADESCRIPTION,
                LAT, LONG, AZIMUTH AS AZM
             INTO #TTP
             FROM #TT A
             INNER JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B
                ON A.CELLTOWERID = B.CELLTOWERID
               AND A.STATE_KEY    = B.STATE_KEY
               AND A.PROVIDER_KEY = B.PROVIDER_KEY
             LEFT JOIN CDATDUPL.DBO.CDATSUSPECT C ON A.OTHER = C.PHONE
             WHERE B.LASTUPDATE = (
                 SELECT DISTINCT MAX(LASTUPDATE)
                 FROM CDATDUPL.DBO.CDATCELLTOWERAREANEW X
                 WHERE X.CELLTOWERID   = B.CELLTOWERID
                   AND X.PROVIDER_KEY  = B.PROVIDER_KEY
                   AND X.STATE_KEY     = B.STATE_KEY
             )
             GROUP BY A.PHONE, OTHER, NICKNAME, STARTTIME, DURATION, TYPE,
                      A.IMEINUMBER, A.CELLTOWERID, B.CELLTOWERID, LASTUPDATE,
                      OPERATOR, A.STATE_KEY, B.STATE_KEY, A.PROVIDER_KEY,
                      B.PROVIDER_KEY, LAT, LONG, AZIMUTH";

    /* Step 3 – distance filter using stored function */
    $sql4 = "DECLARE @lat    DECIMAL(14,10) = ?,
                     @long   DECIMAL(14,10) = ?,
                     @radius DECIMAL(15,10) = ?
             SELECT PHONE, OTHER, NICKNAME, STARTTIME, DURATION, TYPE, CELLTOWERID,
                    CAST(DBO.CALCULATEDISTANCE(@long, @lat, LONG, LAT) * 1000 AS INT) AS DIST,
                    DBO.GETBEARING(LAT, LONG, @lat, @long) AS BR,
                    AREADESCRIPTION, OPERATOR, LAT, LONG, AZM
             FROM #TTP
             WHERE LAT  BETWEEN @lat  - 1 AND @lat  + 1
               AND LONG BETWEEN @long - 1 AND @long + 1
               AND ISNUMERIC(LAT)  = 1 AND LAT  IS NOT NULL
               AND ISNUMERIC(LONG) = 1 AND LONG IS NOT NULL
               AND DBO.CALCULATEDISTANCE(@long, @lat, LONG, LAT) * 1000 < @radius
             ORDER BY STARTTIME";

    $st2 = sqlsrv_query($conn, $sql2, [$number]);
    if ($st2 === false) { die(print_r(sqlsrv_errors(), true)); }

    $st3 = sqlsrv_query($conn, $sql3);
    if ($st3 === false) { die(print_r(sqlsrv_errors(), true)); }

    $st4 = sqlsrv_query($conn, $sql4, [$lat, $long, $range]);
    if ($st4 === false) { die(print_r(sqlsrv_errors(), true)); }

    $rows = [];
    while ($row = sqlsrv_fetch_array($st4, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }

    sqlsrv_free_stmt($st2);
    sqlsrv_free_stmt($st3);
    sqlsrv_free_stmt($st4);
    sqlsrv_close($conn);

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
