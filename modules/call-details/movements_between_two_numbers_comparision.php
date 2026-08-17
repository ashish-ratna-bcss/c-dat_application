<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$number1 = trim((string) ($_POST['OTHER_NO'] ?? ''));
$hasSearch = $number !== '' && $number1 !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter both mobile numbers and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Movements Between Two Numbers Comparision');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Comparison of Two Numbers Location',
            'Compare location movements between two mobile numbers.',
            'movements_between_two_numbers_comparision.php',
            cdat_sum_field_phone($number) . cdat_sum_field_other_phone($number1)
        );
    }

    set_time_limit(0);
    require_once CDAT_COMMON . '/cdr_enrichment_sql.php';

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array("Database" => "CDATDUPL");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
            INC_OFFICER 
            INTO #S FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN (?,?)  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";
    $params10 = array($number, $number1);
    $st10 = sqlsrv_prepare($conn, $sql10, $params10);
    sqlsrv_execute($st10);

    $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
            CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
            IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE IN (?,?)";
    $params1 = array($number, $number1);
    $st1 = sqlsrv_prepare($conn, $sql1, $params1);
    sqlsrv_execute($st1);

    $sql2 = cdr_sql_enrich_tt('', '', [
        'with_last_update' => true,
        'with_lat_long' => true,
        'output_table' => '#ttppp',
    ]);
    $st2 = sqlsrv_query($conn, $sql2);

    $sql5 = "select distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''+A.CELLTOWERID PHONE_CELLTOWERID,
            A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM,
            A.OTHER,''''+B.CELLTOWERID OTHER_CELLTOWERID,
            B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM
            into #ttpppp from #ttppp A INNER JOIN
            #TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND CONVERT(DATE,A.STARTTIME)=CONVERT(DATE,B.STARTTIME) 
            and datepart(hh,convert(datetime,A.STARTTIME))=datepart(hh,convert(datetime,b.STARTTIME)) and 
            datepart(mm,convert(datetime,A.STARTTIME))=datepart(mm,convert(datetime,b.STARTTIME)) 
            AND datediff(ss,convert(datetime,A.STARTTIME),convert(datetime,b.STARTTIME))<'4'
            WHERE A.PHONE=?";
    $params5 = array($number);
    $st5 = sqlsrv_prepare($conn, $sql5, $params5);
    sqlsrv_execute($st5);

    $sql7 = "select distinct *,case when 
            phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%'
            then CAST(import.DBO.CALCULATEDISTANCE(left(phone_long,8),left(phone_lat,8),left(other_LONG,8),left(other_LAT,8)) AS INT) else '' end 
            DIST FROM #ttpppp
            ORDER BY STARTTIME";
    $st7 = sqlsrv_query($conn, $sql7);

    $sql6 = "select 'MOVEMENTS COMPARISION  OF MOBILE NO. ' + ? + ' AND OTHER NO. ' + ? as PHONE";
    $params6 = array($number, $number1);
    $st6 = sqlsrv_prepare($conn, $sql6, $params6);
    sqlsrv_execute($st6);

    $bannerTitle = "MOVEMENTS COMPARISION  OF MOBILE NO. {$number} AND OTHER NO. {$number1}";
    if ($st6 && ($bannerRow = sqlsrv_fetch_array($st6, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st7);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Comparison of Two Numbers Location',
            [
                'PHONE',
                'OTHER',
                'STARTTIME',
                'DURATION',
                'PHONE AREADESCRIPTION',
                'OTHER AREADESCRIPTION',
                'PHONE CELLTOWERID',
                'PHONE LAT',
                'PHONE LONG',
                'PHONE AZM',
                'OTHER CELLTOWERID',
                'OTHER LAT',
                'OTHER LONG',
                'OTHER AZM',
                'DIST BETWEEN NUMBERS IN KM',
            ],
            'results_table',
            'movements_comparison.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
                ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
                ['html' => cdat_sum_address_lines((string) ($row['PHONE_AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                ['html' => cdat_sum_address_lines((string) ($row['OTHER_AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                ['text' => (string) ($row['PHONE_CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['PHONE_LAT'] ?? ''),
                (string) ($row['PHONE_LONG'] ?? ''),
                (string) ($row['PHONE_AZM'] ?? ''),
                ['text' => (string) ($row['OTHER_CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['OTHER_LAT'] ?? ''),
                (string) ($row['OTHER_LONG'] ?? ''),
                (string) ($row['OTHER_AZM'] ?? ''),
                ['text' => (string) ($row['DIST'] ?? ''), 'class' => 'sum-cell-num'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st5) {
        sqlsrv_free_stmt($st5);
    }
    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('Movements Between Two Numbers Comparision');
cdat_sum_page_open();
cdat_sum_search_card(
    'Comparison of Two Numbers Location',
    'Compare location movements between two mobile numbers.',
    'movements_between_two_numbers_comparision.php',
    cdat_sum_field_phone() . cdat_sum_field_other_phone()
);
cdat_sum_page_close();
layout_end();
