<?php
require_once __DIR__ . '/../common/bootstrap.php';
// Must run before any output: audit_require_* redirects with
// header(), which is lost once the layout has started printing.
require_once CDAT_COMMON . '/activity_logger.php';
audit_require_session();

require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$lat = trim((string) ($_POST['LAT'] ?? ''));
$long = trim((string) ($_POST['LONG'] ?? ''));
$range = trim((string) ($_POST['RANGE'] ?? ''));
$hasSearch = $lat !== '' && $long !== '';

$rangeOptions = ['' => 'Select range'];
for ($i = 100; $i <= 5000; $i += 100) {
    $rangeOptions[(string) $i] = (string) $i;
}

$fieldsHtml = cdat_sum_field_text('LAT', 'LAT', $lat, 'LAT', 'Enter LAT')
            . cdat_sum_field_text('LONG', 'LONG', $long, 'LONG', 'Enter LONG')
            . cdat_sum_searchable_select('RANGE', 'Range in MTS', $rangeOptions, $range, 'Select range', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Near By Celltowerids');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Near By Cell ID Search',
            'Find cell towers near a latitude and longitude within a range.',
            'near_by_celltowerids.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once CDAT_COMMON . '/activity_logger.php';
    require_once CDAT_COMMON . '/sql_safe.php';
    audit_require_session();
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $LAT = sql_safe_float($_POST['LAT'] ?? '0');
    $LONG = sql_safe_float($_POST['LONG'] ?? '0');
    $RANGE = sql_safe_float($_POST['RANGE'] ?? '10000');

    $sql1 = "SELECT 'NEAR BY CELLID SEARCH: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";


    $sql2 = "declare @lat decimal(14,10),@long decimal (14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='$RANGE'
SELECT CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,SITEADDRESS,OPERATOR,LAT,LONG,AZIMUTH,OTYPE,STATE,CONVERT(VARCHAR,LASTUPDATE,20) LASTUPDATE
FROM dbo.CELLTOWERfiltered WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY OPERATOR,DIST,OTYPE";

    $st1 = sqlsrv_query($conn, $sql1);
    sqlsrv_render_query_error($st1, 'Title query');
    $st2 = sqlsrv_query($conn, $sql2);
    sqlsrv_render_query_error($st2, 'Nearby towers');

    $banner = 'NEAR BY CELLID SEARCH: ' . $LAT . ' AND ' . $LONG;
    if ($st1 && ($b = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st2);

    if (empty($rows)) {
        cdat_sum_empty_state('No nearby cell towers found.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Nearby Cell Towers',
            ['CELLTOWERID', 'DIST', 'BR', 'AREADESCRIPTION', 'SITEADDRESS', 'OPERATOR', 'LAT', 'LONG', 'AZIMUTH', 'OTYPE', 'STATE', 'LASTUPDATE'],
            'results_table',
            'near_by_celltowerids.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['DIST'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['BR'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                ['html' => cdat_sum_address_lines((string) ($row['SITEADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['OPERATOR'] ?? ''),
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZIMUTH'] ?? ''),
                (string) ($row['OTYPE'] ?? ''),
                (string) ($row['STATE'] ?? ''),
                ['text' => (string) ($row['LASTUPDATE'] ?? ''), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st2) {
        sqlsrv_free_stmt($st2);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Near By Celltowerids');
cdat_sum_page_open();
cdat_sum_search_card(
    'Near By Cell ID Search',
    'Find cell towers near a latitude and longitude within a range.',
    'near_by_celltowerids.php',
    cdat_sum_field_text('LAT', 'LAT', '', 'LAT', 'Enter LAT')
        . cdat_sum_field_text('LONG', 'LONG', '', 'LONG', 'Enter LONG')
        . cdat_sum_searchable_select('RANGE', 'Range in MTS', $rangeOptions, '', 'Select range', false),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
