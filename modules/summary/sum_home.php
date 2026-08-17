<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = trim((string) ($_POST['PHONE_NO'] ?? ''));
    if ($number !== '') {
        if (!$isAjax) {
            layout_begin('Summary');
            cdat_sum_page_open();
        }

        set_time_limit(0);
        require_once CDAT_COMMON . '/activity_logger.php';
        require_once CDAT_COMMON . '/cdr_enrichment_sql.php';

        audit_log('Summary Total', 'Search', ['phone_number' => $number]);
        $serverName = "CPHYDERABAD1\DAU_HYD_2023";
        $connectionInfo = array("Database" => "CDATDUPL");
        $conn = sqlsrv_connect($serverName, $connectionInfo);
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $sql3 = "SELECT * INTO #TT FROM CDAT_DETAILS WITH (NOLOCK) WHERE PHONE='$number' and isnumeric(other)=1";

        $sql4 = "SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
        SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
        SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS 'OUT',
        COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
        CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRSTCALL,
        CONVERT(VARCHAR,MAX(STARTTIME),20) AS LASTCALL INTO #RESULT FROM #TT 
        GROUP BY PHONE, OTHER ORDER BY CALLS DESC";

        $sql5 = "SELECT * INTO #RESULT1 FROM #RESULT WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
        SELECT DISTINCT OTHER  FROM #RESULT WHERE (CALLS=DUR OR CALLS>DUR)
        AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

        $sql6 = "SELECT PHONE, OTHER, [IN], [OUT], CALLS, DUR, FIRSTCALL, LASTCALL
        FROM #RESULT1 ORDER BY CALLS DESC";

        $sql8 = "SELECT 'SUMMARY OF MOBILE NO: '+'$number' as PHONE1";

        $sql10 = "SELECT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED 
        FROM CDATDUPL.DBO.CDATPCSUSPECT A WITH (NOLOCK) LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B WITH (NOLOCK) ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

        $sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE 'Records not found' end as PHONE FROM #RESULT";

        $st3 = sqlsrv_query($conn, $sql3);
        $st4 = sqlsrv_query($conn, $sql4);
        $st5 = sqlsrv_query($conn, $sql5);
        $stmt = sqlsrv_query($conn, $sql6);
        $st8 = sqlsrv_query($conn, $sql8);
        $st10 = sqlsrv_query($conn, $sql10);
        $st12 = sqlsrv_query($conn, $sql12);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $contactRows = [];
        $lookupPhones = [$number];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $contactRows[] = $row;
            $lookupPhones[] = $row['OTHER'] ?? '';
        }

        $phoneAreaPrefixes = cdat_load_phonearea_prefixes($conn);
        $suspectMap = cdat_fetch_suspect_nickname_map($conn, $lookupPhones);
        $cdatAddressMap = cdat_fetch_cdataddress_map($conn, $lookupPhones);
        $otherStateMap = cdat_fetch_other_state_address_map($conn, $lookupPhones);
        $headerPhoneArea = cdat_phonearea_lookup($phoneAreaPrefixes, $number);

        $headerRow = [
            'PHONE' => $number,
            'FIRST_CALL' => '',
            'LAST_CALL' => '',
            'NICKNAME' => '',
            'ADDRESS' => cdat_format_sum_header_address($number, $cdatAddressMap, $otherStateMap, $headerPhoneArea),
        ];
        if ($st10 && ($stats = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
            $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
            $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
            if (!empty($stats['NICKNAME'])) {
                $headerRow['NICKNAME'] = $stats['NICKNAME'];
            }
        }

        if (empty($contactRows)) {
            cdat_sum_empty_state();
        } else {
            cdat_sum_results_open();
            cdat_sum_subject_card($headerRow, count($contactRows), 'Call Summary Report');
            cdat_sum_table_panel_open('Contact Analysis', count($contactRows), 'contact_results_table', 'contact_analysis.csv');

            foreach ($contactRows as $row) {
                $other = trim((string) ($row['OTHER'] ?? ''));
                $otherLabel = $other;
                if (isset($suspectMap[$other]) && $suspectMap[$other] !== '') {
                    $otherLabel = $other . ' - ' . $suspectMap[$other];
                }
                $row['OTHER'] = $otherLabel;
                $row['ADDRESS'] = cdat_format_sum_contact_address(
                    $other,
                    (int) ($row['IN'] ?? 0),
                    (int) ($row['OUT'] ?? 0),
                    $row['DUR'] ?? 0,
                    $cdatAddressMap,
                    $otherStateMap,
                    cdat_phonearea_lookup($phoneAreaPrefixes, $other)
                );
                cdat_sum_contact_row($row);
            }

            cdat_sum_table_panel_close();
            cdat_sum_results_close();
        }

        sqlsrv_free_stmt($stmt);

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }

    if ($isAjax) {
        cdat_sum_empty_state('Enter a mobile number and try again.');
        exit;
    }
}

layout_begin('Summary Total');
cdat_sum_page_open();
cdat_sum_search_card(
    'Summary of Mobile Number',
    'Search call records and contact analysis for a mobile number.',
    'sum_home.php',
    cdat_sum_field_phone()
);
cdat_sum_page_close();
layout_end();
