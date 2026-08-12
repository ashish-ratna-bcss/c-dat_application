<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$name = trim((string) ($_POST['NAME'] ?? ''));
$fatherName = trim((string) ($_POST['FATHER_NAME'] ?? ''));
$hasSearch = $name !== '' || $fatherName !== '';

$fieldsHtml = cdat_sum_field_text('NAME', 'Name', $name, 'NAME', 'Enter name')
            . cdat_sum_field_text('FATHER_NAME', 'Father Name', $fatherName, 'FATHER_NAME', 'Enter father name');

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('Retrieve');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'Retrieve',
            'Search IR records by name and father name to view associated images.',
            'retrieve.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array("Database" => "FORMS");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $NAME = $_POST['NAME'];
    $FATHER_NAME = $_POST['FATHER_NAME'];
    $sql = "SELECT A.IRKEY,NAME,FATHER_NAME,B.[IMAGE] FROM IR_PARTICULARS A INNER JOIN IMAGE_TABLE  B
ON A.IRKEY=B.IRKEY AND A.CATEGORY=B.CATEGORY
WHERE A.NAME LIKE '%'+'$NAME'+'%' AND A.FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%'";
    $sql1 = "SELECT case when count(NAME)>=1 THEN '' ELSE '*** NO RECORD FOUND TO NAME:$NAME AND FATHER NAME:$FATHER_NAME ***' end as DETAILS
FROM [FORMS].[dbo].[IR_PARTICULARS] WHERE NAME LIKE '%'+'$NAME'+'%' AND FATHER_NAME LIKE '%'+'$FATHER_NAME'+'%'";
    $st1 = sqlsrv_query($conn, $sql);
    $st2 = sqlsrv_query($conn, $sql1);

    $rows = cdat_sum_fetch_all($st1);
    $detailsRow = cdat_sum_fetch_one($st2);
    $detailsMsg = trim((string) ($detailsRow['DETAILS'] ?? ''));

    if (empty($rows)) {
        cdat_sum_empty_state($detailsMsg !== '' ? $detailsMsg : 'No records found');
    } else {
        cdat_sum_results_open();
        cdat_sum_report_banner('Retrieve: ' . $name . ($fatherName !== '' ? ' / ' . $fatherName : ''));
        cdat_sum_generic_table_open(
            'Retrieve Results',
            ['IRKEY', 'NAME', 'FATHER_NAME', 'IMAGE'],
            'results_table',
            'retrieve.csv',
            count($rows)
        );
        foreach ($rows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['IRKEY'] ?? ''), 'class' => 'sum-cell-num'],
                (string) ($row['NAME'] ?? ''),
                (string) ($row['FATHER_NAME'] ?? ''),
                ['html' => '<img height="300" width="300" src="' . cdat_base64_image_src($row['IMAGE']) . '">', 'class' => 'sum-cell-img'],
            ]);
        }
        cdat_sum_generic_table_close();
        cdat_sum_results_close();
    }

    if ($st1) {
        sqlsrv_free_stmt($st1);
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

layout_begin('Retrieve');
cdat_sum_page_open();
cdat_sum_search_card(
    'Retrieve',
    'Search IR records by name and father name to view associated images.',
    'retrieve.php',
    cdat_sum_field_text('NAME', 'Name', '', 'NAME', 'Enter name')
        . cdat_sum_field_text('FATHER_NAME', 'Father Name', '', 'FATHER_NAME', 'Enter father name'),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
