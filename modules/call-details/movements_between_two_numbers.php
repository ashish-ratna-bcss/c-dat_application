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
        layout_begin('Movements Between Two Nos');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Call Details Between Numbers',
            'View call movements between two mobile numbers.',
            'movements_between_two_numbers.php',
            cdat_sum_field_phone($number) . cdat_sum_field_other_phone($number1)
        );
    }

    set_time_limit(0);
    require_once CDAT_COMMON . '/sql_safe.php';
    $number = sql_safe_phone($number);
    $number1 = sql_safe_phone($number1);
    if ($number === '' || $number1 === '') {
        cdat_sum_empty_state('Enter valid mobile numbers and try again.');
        exit;
    }
    $conn = get_cdat_pdo();
    $sql10 = "CREATE TEMP TABLE temp_S AS SELECT DISTINCT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME,B.MO,CATEGORY,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED,
INC_OFFICER 
 FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE = ? GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER";
    $conn->prepare($sql10)->execute([$number]);

    $sql1 = "CREATE TEMP TABLE temp_TT AS SELECT DISTINCT PHONE,OTHER,TO_CHAR((STARTTIME)::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY   FROM CDATPCSUSPECT WHERE PHONE = ? AND OTHER = ?";
    $st1 = $conn->prepare($sql1);
    $st1->execute([$number, $number1]);

    $sql2 = cdr_sql_enrich_tt_local('', '', ['with_last_update' => true, 'with_lat_long' => true]);

    $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION,LAT,LONG,AZM from temp_temp_cdrs  ORDER BY STARTTIME";

    $sql6 = "select 'CALL DETAILS OF MOBILE NO. ' || ? || ' AND OTHER NO. ' || ? as PHONE";
    $st6 = $conn->prepare($sql6);
    $st6->execute([$number, $number1]);

    $st2 = $conn->query($sql2);
    $st5 = $conn->query($sql5);

    $bannerTitle = "CALL DETAILS OF MOBILE NO. {$number} AND OTHER NO. {$number1}";
    if ($st6 && ($bannerRow = $st6->fetch(PDO::FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    $rows = cdat_sum_fetch_all($st5);

    if (empty($rows)) {
        cdat_sum_empty_state();
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Call Details Between Numbers',
            ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION', 'LAT', 'LONG', 'AZM'],
            'results_table',
            'movements_between_two.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
                (string) ($row['NICKNAME'] ?? ''),
                ['text' => (string) ($row['STARTTIME'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['DURATION'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['TYPE'] ?? ''),
                ['text' => (string) ($row['IMEINUMBER'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['OPERATOR'] ?? ''),
                ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                (string) ($row['LAT'] ?? ''),
                (string) ($row['LONG'] ?? ''),
                (string) ($row['AZM'] ?? ''),
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

layout_begin('Movements Between Two Nos');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details Between Numbers',
    'View call movements between two mobile numbers.',
    'movements_between_two_numbers.php',
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
