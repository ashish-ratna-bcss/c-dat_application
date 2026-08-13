<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE'] ?? ''));
$lat = trim((string) ($_POST['LAT'] ?? ''));
$long = trim((string) ($_POST['LONG'] ?? ''));
$range = trim((string) ($_POST['RANGE'] ?? ''));
$hasSearch = $phone !== '' && $lat !== '' && $long !== '';

$rangeOptions = ['' => 'Select range'];
for ($i = 100; $i <= 5000; $i += 100) {
    $rangeOptions[(string) $i] = (string) $i;
}

$fieldsHtml = cdat_sum_field_text('PHONE', 'Phone', $phone, 'PHONE', 'Enter PHONE NO', true, 'tel')
            . cdat_sum_field_text('LAT', 'LAT', $lat, 'LAT', 'Enter LAT')
            . cdat_sum_field_text('LONG', 'LONG', $long, 'LONG', 'Enter LONG')
            . cdat_sum_searchable_select('RANGE', 'Range in MTS', $rangeOptions, $range, 'Select range', false);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Movements In Particular Place');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Movements in Particular Lat Long',
            'Find movements of a mobile number near a latitude and longitude.',
            'movements_in_particular_place.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    require_once __DIR__ . '/cdr_enrichment_sql.php';
    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $number = $_POST['PHONE'];
    $LAT = $_POST['LAT'];
    $LONG = $_POST['LONG'];
    $RANGE = $_POST['RANGE'];

    $sql1 = "SELECT 'MOVEMENTS IN: '+'$LAT' + ' AND ' + '$LONG' as PHONE1";

    $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
INC_OFFICER 
INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";


    $sql2 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' ";

    $sql3 = cdr_sql_enrich_tt('', '', ['with_last_update' => true, 'with_lat_long' => true, 'output_table' => '#TTP']);

    $sql4 = "declare @lat decimal(14,10),@long decimal(14,10),@radius decimal(15,10)
set @lat='$LAT'
set @long='$LONG'
set @radius='$RANGE'
SELECT PHONE,OTHER,STARTTIME,DURATION,TYPE,CELLTOWERID, CAST(DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000 AS INT)  DIST,
DBO.GETBEARING(LAT,LONG,@lat,@long) BR,
AREADESCRIPTION,OPERATOR,LAT,LONG,AZM
FROM #TTP WHERE 
LAT BETWEEN @lat-1 AND @lat+1  AND LONG BETWEEN @long-1 AND @long+1  AND
ISNUMERIC(LAT)=1 AND LAT IS NOT NULL AND ISNUMERIC(LONG)=1 AND LONG IS NOT NULL AND
DBO.CALCULATEDISTANCE(@long,@lat,LONG,LAT)*1000<@radius
ORDER BY STARTTIME";

    $st1 = sqlsrv_query($conn, $sql1);
    $st2 = sqlsrv_query($conn, $sql10);
    $st3 = sqlsrv_query($conn, $sql2);
    $st4 = sqlsrv_query($conn, $sql3);
    $st5 = sqlsrv_query($conn, $sql4);

    $banner = 'MOVEMENTS IN: ' . $LAT . ' AND ' . $LONG;
    if ($st1 && ($b = sqlsrv_fetch_array($st1, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }
    $rows = cdat_sum_fetch_all($st5);

    if (empty($rows)) {
        cdat_sum_empty_state('No movements found for that location.');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($banner);
        cdat_sum_generic_table_open(
            'Movements',
            ['PHONE', 'OTHER', 'STARTTIME', 'DURATION', 'TYPE', 'CELLTOWERID', 'DIST', 'BR', 'AREADESCRIPTION', 'OPERATOR', 'LAT', 'LONG', 'AZIMUTH'],
            'results_table',
            'movements_in_particular_place.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['TYPE'] ?? ''),
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['DIST'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['BR'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
                (string) ($row['OPERATOR'] ?? ''),
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZM'] ?? ''),
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

layout_begin('Movements In Particular Place');
cdat_sum_page_open();
cdat_sum_search_card(
    'Movements in Particular Lat Long',
    'Find movements of a mobile number near a latitude and longitude.',
    'movements_in_particular_place.php',
    cdat_sum_field_text('PHONE', 'Phone', '', 'PHONE', 'Enter PHONE NO', true, 'tel')
        . cdat_sum_field_text('LAT', 'LAT', '', 'LAT', 'Enter LAT')
        . cdat_sum_field_text('LONG', 'LONG', '', 'LONG', 'Enter LONG')
        . cdat_sum_searchable_select('RANGE', 'Range in MTS', $rangeOptions, '', 'Select range', false),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
