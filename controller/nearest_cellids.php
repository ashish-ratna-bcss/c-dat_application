<?php
// Must run before any output: audit_require_* redirects with
// header(), which is lost once the layout has started printing.
require_once __DIR__ . '/activity_logger.php';
audit_require_session();

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$lat = trim((string) ($_POST['LAT'] ?? ''));
$long = trim((string) ($_POST['LONG'] ?? ''));
$hasSearch = $lat !== '' && $long !== '';

$fieldsHtml = cdat_sum_field_text('LAT', 'LAT', $lat, 'LAT', 'Enter LAT')
            . cdat_sum_field_text('LONG', 'LONG', $long, 'LONG', 'Enter LONG');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Nearest Cell IDs');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Near By Cell ID Search',
            'Find the nearest cell towers for a latitude and longitude.',
            'nearest_cellids.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once __DIR__ . '/activity_logger.php';
    require_once __DIR__ . '/sql_safe.php';
    audit_require_session();
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $LAT = sql_safe_float($_POST['LAT'] ?? '0');
    $LONG = sql_safe_float($_POST['LONG'] ?? '0');

    $sql1 = "SELECT 'NEAR BY CELLID SEARCH: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";


    $sql2 = "declare @lat decimal(14,10),@long decimal (14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='10000'
SELECT CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,SITEADDRESS,OPERATOR,LAT,LONG,AZIMUTH,OTYPE,STATE,
DENSE_RANK()
over (PARTITION by operator order by CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)) as RANK,
CONVERT(VARCHAR,LASTUPDATE,20) LASTUPDATE
INTO #T FROM dbo.celltowerfiltered WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY OPERATOR,DIST,OTYPE";

    $sql3 = "select distinct *,CASE WHEN RANK=1 THEN 'A' WHEN RANK='2' THEN 'B' END AS CATEGORY from #T
where rank<3  and otype not like '%cdma%'
order by otype,operator,CATEGORY";

    $st1 = sqlsrv_query($conn, $sql1);
    sqlsrv_render_query_error($st1, 'Title query');
    $st2 = sqlsrv_query($conn, $sql2);
    sqlsrv_render_query_error($st2, 'Nearest towers');
    $st3 = sqlsrv_query($conn, $sql3);
    sqlsrv_render_query_error($st3, 'Tower ranking');

    $banner = 'NEAR BY CELLID SEARCH: ' . $LAT . ' AND ' . $LONG;
    if ($st1 && ($b = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st3);

    if (empty($rows)) {
        cdat_sum_empty_state('No nearest cell towers found.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Nearest Cell IDs',
            ['CELLTOWERID', 'CATEGORY', 'DIST', 'BR', 'OPERATOR', 'OTYPE'],
            'results_table',
            'nearest_cellids.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['CATEGORY'] ?? ''),
                ['text' => (string) ($row['DIST'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['BR'] ?? ''),
                (string) ($row['OPERATOR'] ?? ''),
                (string) ($row['OTYPE'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st3) {
        sqlsrv_free_stmt($st3);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Nearest Cell IDs');
cdat_sum_page_open();
cdat_sum_search_card(
    'Near By Cell ID Search',
    'Find the nearest cell towers for a latitude and longitude.',
    'nearest_cellids.php',
    cdat_sum_field_text('LAT', 'LAT', '', 'LAT', 'Enter LAT')
        . cdat_sum_field_text('LONG', 'LONG', '', 'LONG', 'Enter LONG'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
