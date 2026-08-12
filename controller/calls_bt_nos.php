<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    $number1 = trim((string) ($_POST['OTHER_NO'] ?? ''));
    if ($number !== '' && $number1 !== '') {
        if (!$isAjax) {
            layout_begin('Calls Between Two Nos');
            cdat_sum_page_open();
        }

        require_once __DIR__ . '/cdr_enrichment_sql.php';
        $serverName = "CPHYDERABAD1\DAU_HYD_2023";
        $connectionInfo = array("Database" => "CDATDUPL");
        $conn = sqlsrv_connect($serverName, $connectionInfo);
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $operator = $_POST['OPERATOR'] ?? '';
        $state = $_POST['STATE'] ?? '';

        $sql1 = "SELECT DISTINCT PHONE,OTHER,CONVERT(VARCHAR,STARTTIME,20) AS STARTTIME,DURATION,
CASE WHEN INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
IMEINUMBER,CELLTOWERID,STATE_KEY,PROVIDER_KEY  INTO #TT FROM CDATDUPL.DBO.CDATPCSUSPECT WHERE PHONE='$number' AND OTHER='$number1' ";

        $sql2 = cdr_sql_enrich_tt($operator, $state);

        $sql5 = "SELECT PHONE,OTHER,NICKNAME,STARTTIME,DURATION,TYPE,IMEINUMBER,CELLTOWERID,OPERATOR,AREADESCRIPTION from #temp_cdrs  ORDER BY STARTTIME";

        $sql6 = "select 'CALLS BETWEEN MOBILE NO. '+'$number'+' AND '+'$number1'as PHONE";

        $st1 = sqlsrv_query($conn, $sql1);
        $st2 = sqlsrv_query($conn, $sql2);
        $st5 = sqlsrv_query($conn, $sql5);
        $st6 = sqlsrv_query($conn, $sql6);

        $bannerRow = cdat_sum_fetch_one($st6);
        $rows = cdat_sum_fetch_all($st5);

        if (empty($rows)) {
            cdat_sum_empty_state();
        } else {
            cdat_sum_results_open();
            cdat_sum_report_banner((string) ($bannerRow['PHONE'] ?? ('CALLS BETWEEN MOBILE NO. ' . $number . ' AND ' . $number1)));
            cdat_sum_generic_table_open(
                'Call Details',
                ['PHONE', 'OTHER', 'NICK NAME', 'STARTTIME', 'DUR', 'TYPE', 'IMEI', 'CELLID', 'OPERATOR', 'AREA DESCRIPTION'],
                'results_table',
                'calls_between_two.csv',
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
                ]);
            }
            cdat_sum_generic_table_close();
            cdat_sum_results_close();
        }

        if ($st5) {
            sqlsrv_free_stmt($st5);
        }

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }
}

layout_begin('Calls Between Two Nos');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details Between Two Mobile Numbers',
    'Search call records between two mobile numbers.',
    'calls_bt_nos.php',
    cdat_sum_field_phone()
    . cdat_sum_field_other_phone()
    . cdat_sum_field_operator()
    . cdat_sum_field_call_state()
);
cdat_sum_page_close();
layout_end();
