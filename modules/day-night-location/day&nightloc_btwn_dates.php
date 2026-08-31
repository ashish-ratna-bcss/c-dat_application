<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CDAT_COMMON . '/sql_safe.php';

    $number = sql_safe_phone((string) ($_POST['PHONE_NO'] ?? ''));
    $f_date = sql_safe_date((string) ($_POST['FROM_DT'] ?? ''));
    $t_date = sql_safe_date((string) ($_POST['TO_DT'] ?? ''));
    if ($number !== '' && $f_date !== '' && $t_date !== '') {
        if (!$isAjax) {
            layout_begin('Day / Night Between Dates');
            cdat_sum_page_open();
        }
        $conn = get_cdat_pdo();
                $sql1 = "CREATE TEMP TABLE temp_temp AS SELECT * FROM CDATPCSUSPECT WHERE 
(TO_CHAR(STARTTIME, 'HH24:MI:SS')<'22:00:00' AND TO_CHAR(STARTTIME, 'HH24:MI:SS')>'05:00:00') 
AND PHONE = ? AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN ? AND ?";
        $conn->prepare($sql1)->execute([$number, $f_date, $t_date]);

        $sql2 = cdr_sql_enrich_location_temp_local('temp_temp', 'temp_tt1');

        $sql4 = "CREATE TEMP TABLE temp_t AS SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM  FROM temp_tt1
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

        $sql5 = 'SELECT  * FROM temp_t order by calls desc LIMIT 10';

        $sql6 = "SELECT 'DAY LOCATION OF MOBILE NO: ' || ? || ' BETWEEN ' || ? || ' AND ' || ? as PHONE1";
        $st6 = $conn->prepare($sql6);
        $st6->execute([$number, $f_date, $t_date]);

        $sql7 = "SELECT 'NIGHT LOCATION OF MOBILE NO: ' || ? || ' BETWEEN ' || ? || ' AND ' || ? as PHONE1";
        $st7 = $conn->prepare($sql7);
        $st7->execute([$number, $f_date, $t_date]);

        $sql8 = "CREATE TEMP TABLE temp_t1 AS SELECT * FROM CDATPCSUSPECT WHERE 
(TO_CHAR(STARTTIME, 'HH24:MI:SS')>'22:00:00' OR TO_CHAR(STARTTIME, 'HH24:MI:SS')<'07:00:00') 
AND PHONE = ? AND TO_CHAR(STARTTIME, 'YYYY-MM-DD') BETWEEN ? AND ?";
        $conn->prepare($sql8)->execute([$number, $f_date, $t_date]);

        $sql9 = cdr_sql_enrich_location_temp_local('temp_t1', 'temp_t3');

        $sql11 = "CREATE TEMP TABLE temp_t4 AS SELECT DISTINCT PHONE,CELLTOWERID,COUNT(CELLTOWERID) AS CALLS,
SITEADDRESS AS AREADESCRIPTION,LAT,LONG,AZM  FROM temp_t3
GROUP BY PHONE,CELLTOWERID,SITEADDRESS,LAT,LONG,AZM ORDER BY CALLS DESC";

        $sql12 = 'SELECT  * FROM temp_t4 order by calls desc LIMIT 10';

        $st2 = $conn->query($sql2);
        $st4 = $conn->query($sql4);
        $st5 = $conn->query($sql5);
        $st9 = $conn->query($sql9);
        $st11 = $conn->query($sql11);
        $st12 = $conn->query($sql12);

        $dayTitle = 'Day Location of Mobile No: ' . $number . ' Between ' . $f_date . ' And ' . $t_date;
        $nightTitle = 'Night Location of Mobile No: ' . $number . ' Between ' . $f_date . ' And ' . $t_date;
        if ($st6 && ($row = $st6->fetch(PDO::FETCH_ASSOC))) {
            $dayTitle = (string) ($row['PHONE1'] ?? $dayTitle);
        }
        if ($st7 && ($row = $st7->fetch(PDO::FETCH_ASSOC))) {
            $nightTitle = (string) ($row['PHONE1'] ?? $nightTitle);
        }

        $dayRows = cdat_sum_fetch_all($st5);
        $nightRows = cdat_sum_fetch_all($st12);

        echo '<div class="sum-results">';

        cdat_sum_report_banner($dayTitle);
        if (empty($dayRows)) {
            cdat_sum_empty_state('No day location records found');
        } else {
            cdat_sum_generic_table_open(
                'Day Locations',
                ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
                'day_loc_btwn_table',
                'day_location_between_dates.csv',
                count($dayRows)
            );
            foreach ($dayRows as $row) {
                cdat_sum_table_row([
                    ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                    ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                    ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                    ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                    (string) ($row['LAT'] ?? ''),
                    (string) ($row['LONG'] ?? ''),
                    (string) ($row['AZM'] ?? ''),
                ]);
            }
            cdat_sum_generic_table_close();
        }

        cdat_sum_report_banner($nightTitle);
        if (empty($nightRows)) {
            cdat_sum_empty_state('No night location records found');
        } else {
            cdat_sum_generic_table_open(
                'Night Locations',
                ['PHONE', 'CELLTOWERID', 'CALLS', 'AREADESCRIPTION', 'LAT', 'LONG', 'AZM'],
                'night_loc_btwn_table',
                'night_location_between_dates.csv',
                count($nightRows)
            );
            foreach ($nightRows as $row) {
                cdat_sum_table_row([
                    ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                    ['text' => (string) ($row['CELLTOWERID'] ?? ''), 'class' => 'sum-cell-num'],
                    ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                    ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')), 'class' => 'sum-address-cell'],
                    (string) ($row['LAT'] ?? ''),
                    (string) ($row['LONG'] ?? ''),
                    (string) ($row['AZM'] ?? ''),
                ]);
            }
            cdat_sum_generic_table_close();
        }

        echo '</div>';

        if ($st5) {
            $st5 = null;
        }

        if ($isAjax) {
            exit;
        }

        cdat_sum_page_close();
        layout_end();
        exit;
    }

    if ($isAjax) {
        cdat_sum_empty_state('Enter a mobile number and both dates.');
        exit;
    }
}

layout_begin('Day / Night Between Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Top 10 Day & Night Locations Between Dates',
    'Find top day and night locations for a mobile number within a date range.',
    'day&nightloc_btwn_dates.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
    . cdat_sum_field_date('TO_DT', 'Date To', 'datepickerID1')
);
cdat_sum_page_close();
layout_end();

function cdr_sql_enrich_location_temp_local(string $sourceTable, string $outputTable): string
{
    return "CREATE TEMP TABLE {$outputTable} AS SELECT DISTINCT A.PHONE,A.OTHER,A.STARTTIME,A.DURATION,
CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
A.IMEINUMBER,A.CELLTOWERID,
COALESCE(B.SITEADDRESS, B.AREADESCRIPTION, '') AS SITEADDRESS,
COALESCE(B.LAT, '') AS LAT,
COALESCE(B.LONG, '') AS LONG,
COALESCE(B.AZIMUTH, '') AS AZM
FROM {$sourceTable} A
LEFT JOIN cdatcelltowerareanew B ON A.CELLTOWERID=B.CELLTOWERID";
}
