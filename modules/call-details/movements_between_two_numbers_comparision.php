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
    require_once CDAT_COMMON . '/sql_safe.php';
    $number = sql_safe_phone($number);
    $number1 = sql_safe_phone($number1);
    if ($number === '' || $number1 === '') {
        cdat_sum_empty_state('Enter valid mobile numbers and try again.');
        if ($isAjax) {
            exit;
        }
        cdat_sum_page_close();
        layout_end();
        exit;
    }
        $conn = get_cdat_pdo();
        $sql10 = "CREATE TEMP TABLE temp_S AS SELECT DISTINCT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED,
            INC_OFFICER 
             FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE IN (?,?)  GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";
    $params10 = array($number, $number1);
    $st10 = $conn->prepare($sql10);
    $st10->execute($params10);
    

    $sql1 = "CREATE TEMP TABLE temp_TT AS SELECT DISTINCT PHONE,OTHER,TO_CHAR((STARTTIME)::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME,DURATION,
            CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
            IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY   FROM CDATPCSUSPECT WHERE PHONE IN (?,?)";
    $params1 = array($number, $number1);
    $st1 = $conn->prepare($sql1);
    $st1->execute($params1);
    

    $sql2 = cdr_sql_enrich_tt_local('', '', [
        'with_last_update' => true,
        'with_lat_long' => true,
        'output_table' => 'temp_ttppp',
    ]);
    $st2 = $conn->query($sql2);

    $sql5 = "CREATE TEMP TABLE temp_ttpppp AS select distinct A.PHONE,A.STARTTIME STARTTIME,A.DURATION ,''''||A.CELLTOWERID PHONE_CELLTOWERID,
            A.AREADESCRIPTION PHONE_AREADESCRIPTION,A.LAT PHONE_LAT,A.LONG PHONE_LONG,A.AZM PHONE_AZM,
            A.OTHER,''''||B.CELLTOWERID OTHER_CELLTOWERID,
            B.AREADESCRIPTION OTHER_AREADESCRIPTION,B.LAT OTHER_LAT,B.LONG OTHER_LONG,B.AZM OTHER_AZM
             from temp_ttppp A INNER JOIN
            temp_TTPPP B ON A.OTHER=B.PHONE AND A.PHONE =B.OTHER AND A.STARTTIME::date=B.STARTTIME::date 
            and date_part('hour', A.STARTTIME::timestamp)=date_part('hour', b.STARTTIME::timestamp) and 
            date_part('minute', A.STARTTIME::timestamp)=date_part('minute', b.STARTTIME::timestamp) 
            AND EXTRACT(EPOCH FROM A.STARTTIME::timestamp - b.STARTTIME::timestamp)<4
            WHERE A.PHONE=?";
    $params5 = array($number);
    $st5 = $conn->prepare($sql5);
    $st5->execute($params5);
    

    $sql7 = "select distinct *,case when 
            phone_lat like '%.%' and other_lat like '%.%' and phone_long like '%.%' and other_long like '%.%'
            then CAST(
                6371 * acos(LEAST(1.0, GREATEST(-1.0,
                    cos(radians(left(phone_lat,8)::double precision)) * cos(radians(left(other_lat,8)::double precision)) *
                    cos(radians(left(other_long,8)::double precision) - radians(left(phone_long,8)::double precision)) +
                    sin(radians(left(phone_lat,8)::double precision)) * sin(radians(left(other_lat,8)::double precision))
                )))
            AS INT) else '' end 
            DIST FROM temp_ttpppp
            ORDER BY STARTTIME";
    $st7 = $conn->query($sql7);

    $sql6 = "select 'MOVEMENTS COMPARISION  OF MOBILE NO. ' || ? || ' AND OTHER NO. ' || ? as PHONE";
    $params6 = array($number, $number1);
    $st6 = $conn->prepare($sql6);
    $st6->execute($params6);
    

    $bannerTitle = "MOVEMENTS COMPARISION  OF MOBILE NO. {$number} AND OTHER NO. {$number1}";
    if ($st6 && ($bannerRow = $st6->fetch(PDO::FETCH_ASSOC))) {
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
        $st5 = null;
    }
    $conn = null;

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

function cdr_escape_sql_literal_local(string $value): string
{
    return str_replace("'", "''", $value);
}

function cdr_sql_enrich_tt_local(string $operator = '', string $state = '', array $opts = []): string
{
    $operator = cdr_escape_sql_literal_local($operator);
    $state = cdr_escape_sql_literal_local($state);
    $useKeys = !empty($opts['use_keys']);
    $withLastUpdate = !empty($opts['with_last_update']);
    $withLatLong = !empty($opts['with_lat_long']);
    $withStateCol = !empty($opts['with_state_col']);
    $withDateTimeCols = !empty($opts['with_date_time_cols']);
    $outputTable = $opts['output_table'] ?? 'temp_temp_cdrs';

    $joinOn = $useKeys
        ? 'A.CELLTOWERID=B.CELLTOWERID AND A.STATE_KEY=B.STATE_KEY AND A.PROVIDER_KEY=B.PROVIDER_KEY'
        : 'A.CELLTOWERID=B.CELLTOWERID';

    if ($withLastUpdate) {
        $areaExpr = "(CASE WHEN A.CELLTOWERID=B.CELLTOWERID THEN MAX(COALESCE(SITEADDRESS, AREADESCRIPTION, '')) ELSE '' END ||', LAST_UPDATE:'||TO_CHAR((LASTUPDATE)::timestamp, 'YYYY-MM-DD HH24:MI:SS'))";
    } else {
        $areaExpr = "CASE WHEN A.CELLTOWERID=B.CELLTOWERID THEN MAX(COALESCE(SITEADDRESS, AREADESCRIPTION, '')) ELSE '' END";
    }

    $dateTimeSelect = $withDateTimeCols ? 'DATE,TIME,' : '';
    $stateSelect = $withStateCol ? ',B.STATE' : '';
    $latSelect = $withLatLong ? ',LAT,LONG,AZIMUTH AS AZM' : '';

    $groupCols = ['A.PHONE', 'OTHER', 'NICKNAME'];
    if ($withDateTimeCols) {
        $groupCols[] = 'DATE';
        $groupCols[] = 'TIME';
    }
    $groupCols = array_merge($groupCols, [
        'STARTTIME', 'DURATION', 'TYPE', 'A.IMEINUMBER', 'A.CELLTOWERID',
        'B.CELLTOWERID', 'LASTUPDATE', 'B.OPERATOR',
    ]);
    if ($withStateCol) {
        $groupCols[] = 'B.STATE';
    }
    if ($withLatLong) {
        $groupCols[] = 'LAT';
        $groupCols[] = 'LONG';
        $groupCols[] = 'AZIMUTH';
    }
    $groupBy = implode(', ', $groupCols);

    $filters = [];
    if ($operator !== '') {
        $filters[] = "B.OPERATOR='{$operator}'";
    }
    if ($state !== '') {
        $filters[] = "B.STATE='{$state}'";
    }
    $where = $filters ? 'WHERE ' . implode(' AND ', $filters) : '';

    return "CREATE TEMP TABLE {$outputTable} AS SELECT DISTINCT A.PHONE,OTHER,CASE WHEN other in (select phone from cdatsuspect) THEN nickname ELSE ' ' END as NICKNAME,
{$dateTimeSelect}STARTTIME,DURATION,TYPE,A.IMEINUMBER,A.CELLTOWERID,COALESCE(B.OPERATOR, '') AS OPERATOR{$stateSelect},
{$areaExpr} AS AREADESCRIPTION{$latSelect} FROM temp_TT A
LEFT JOIN cdatcelltowerareanew B ON {$joinOn}
left join cdatsuspect c on a.other=c.phone
{$where}
GROUP BY {$groupBy}";
}
