<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

$phoneNo = trim((string) ($_POST['PHONE_NO'] ?? ''));
$fromDt = trim((string) ($_POST['FROM_DT'] ?? ''));
$toDt = trim((string) ($_POST['TO_DT'] ?? ''));
$operatorIn = trim((string) ($_POST['OPERATOR'] ?? ''));
$stateIn = trim((string) ($_POST['STATE'] ?? ''));
$hasSearch = $phoneNo !== '' && $fromDt !== '' && $toDt !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and both dates and try again.');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Call Details Between Dates');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Call Details Between Dates',
            'Search call details for a mobile number within a date range.',
            'calls_btwn_dates.php',
            cdat_sum_field_phone($phoneNo)
            . cdat_sum_field_date_native('FROM_DT', 'From Date', $fromDt)
            . cdat_sum_field_date_native('TO_DT', 'To Date', $toDt)
            . cdat_sum_field_text('OPERATOR', 'Operator', $operatorIn, 'OPERATOR', 'Operator', false)
            . cdat_sum_field_text('STATE', 'State', $stateIn, 'STATE', 'State', false)
        );
    }

    require_once CDAT_COMMON . '/activity_logger.php';
    require_once CDAT_COMMON . '/sql_safe.php';
    
    audit_require_session();
    $conn = get_cdat_pdo();

        // Sanitize input
    $number = sql_safe_phone($_POST['PHONE_NO'] ?? '');
    $operator = sql_safe_alnum($_POST['OPERATOR'] ?? '', 50);
    $state = sql_safe_alnum($_POST['STATE'] ?? '', 50);
    $f_date = sql_safe_alnum($_POST['FROM_DT'] ?? '', 10);
    $t_date = sql_safe_alnum($_POST['TO_DT'] ?? '', 10);

    // Audit log
    audit_log('Call Details Between Dates', 'Search', [
        'phone_number' => $number,
        'from_date' => $f_date,
        'to_date' => $t_date,
        'state' => $state,
        'operator' => $operator
    ]);

    // Use parameterized queries to prevent SQL injection
    $sql1 = "CREATE TEMP TABLE temp_TT AS SELECT DISTINCT PHONE,OTHER,TO_CHAR((STARTTIME)::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS STARTTIME,DURATION,
            CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
            IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  
             FROM CDATPCSUSPECT 
            WHERE PHONE = ? AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN ? AND ?";
    $params1 = array($number, $f_date, $t_date);
    $st1 = $conn->prepare($sql1);
    $st1->execute($params1);
    
    

    $sql2 = cdr_sql_enrich_tt_local($operator, $state);
    $st2 = $conn->query($sql2);
    

    $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION 
             FROM temp_temp_cdrs ORDER BY STARTTIME";
    $st5 = $conn->query($sql5);
    

    $sql6 = "SELECT 'CALL DETAILS OF MOBILE NO: ' || ? || ' FROM: ' || ? || ' TO: ' || ? AS PHONE";
    $params6 = array($number, $f_date, $t_date);
    $st6 = $conn->prepare($sql6);
    $st6->execute($params6);
    
    

    $bannerTitle = '';
    while ($row = $st6->fetch(PDO::FETCH_ASSOC)) {
        $bannerTitle = (string) ($row['PHONE'] ?? '');
    }

    $resultRows = cdat_sum_fetch_all($st5);

    if (empty($resultRows)) {
        cdat_sum_empty_state();
    } else {
        echo '<div class="sum-results">';
        cdat_sum_report_banner($bannerTitle);
        cdat_sum_generic_table_open(
            'Call Details',
            ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION'],
            'contact_results_table',
            'calls_between_dates.csv',
            count($resultRows)
        );
        foreach ($resultRows as $row) {
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
            ]);
        }
        cdat_sum_generic_table_close();
        echo '</div>';
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

layout_begin('Call Details Between Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details Between Dates',
    'Search call details for a mobile number within a date range.',
    'calls_btwn_dates.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date_native('FROM_DT', 'From Date')
    . cdat_sum_field_date_native('TO_DT', 'To Date')
    . cdat_sum_field_text('OPERATOR', 'Operator', '', 'OPERATOR', 'Operator', false)
    . cdat_sum_field_text('STATE', 'State', '', 'STATE', 'State', false)
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
