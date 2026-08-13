<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$phone = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $phone !== '';
$fieldsHtml = cdat_sum_field_phone($phone);

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('VBR Search');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'VBR Search',
            'Search VBR / ISD call summary for a mobile number.',
            'vbr_search.php',
            $fieldsHtml,
            'BTN_SUM',
            'Submit'
        );
    }

    $serverName = "CPHYDERABAD1\\DAU_HYD_2023";
    $connectionInfo = array("Database" => "ALL_ILD_DATA_2012");
    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $number = $_POST['PHONE_NO'];

    $sql1 = "select distinct PHONE,OTHER,STARTTIME,DURATION,INCOMING,' 'INFO,' 'PROV,IMEInumber,first_CELLID into #MM
from dbo.ISD_DATA_TOT_2012 where phone='$number' or other='$number'";

    $sql2 = "INSERT INTO #MM
select distinct PHONE,OTHER,STARTTIME,DURATION,INCOMING,' 'INFO,' 'PROV,IMEInumber,' 'first_CELLID 
from ALL_ILD_DATA_2011.DBO.isd_data_tot where phone='$number' or other='$number'";

    $sql3 = "INSERT INTO #MM 
select distinct PHONE,OTHER,STARTTIME,DURATION,INCOMING,' 'INFO,' ' PROV,IMEInumber,' 'first_CELLID
from ALL_ILD_DATA_2013.dbo.ISD_DATA_TOT_2013  where phone='$number' or other='$number'";

    $sql4 = "SELECT '$number' as PHONE, 
CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL
from #MM";

    $sql5 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,
CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,B.NICKNAME  FROM CDAT.DBO.CDATPCSUSPECT A
LEFT JOIN CDAT.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE 
WHERE A.PHONE='$number' GROUP BY A.PHONE,NICKNAME";

    $sql6 = "select DISTINCT *  INTO #TEMP from #MM";

    $sql7 = "update #temp set PHONE=substring(PHONE,3,20) where substring(PHONE,1,2) in ('95') AND LEN(PHONE)>10";
    $sql8 = "update #temp set PHONE=substring(PHONE,3,20) where substring(PHONE,1,2) in ('00')";
    $sql9 = "update #temp set PHONE=substring(PHONE,3,20) where substring(PHONE,1,2) in ('91') AND LEN(PHONE)>10";
    $sql10 = "update #temp set PHONE=substring(PHONE,2,20) where substring(PHONE,1,1) in ('0')";
    $sql11 = "update #temp set OTHER=substring(OTHER,3,20) where substring(OTHER,1,2) in ('00','95') AND LEN(OTHER)>10";
    $sql12 = "update #temp set OTHER=substring(OTHER,3,20) where substring(OTHER,1,2) in ('00')";
    $sql13 = "update #temp set OTHER=substring(OTHER,3,20) where substring(OTHER,1,2) in ('91') AND LEN(OTHER)>10";
    $sql14 = "update #temp set OTHER=substring(OTHER,2,20) where substring(OTHER,1,1) in ('0')";

    $sql15 = "SELECT DISTINCT * INTO:#TT FROM #TEMP";

    $sql16 = "SELECT CASE WHEN PHONE='$number' THEN PHONE ELSE OTHER END AS PHONE,
CASE WHEN PHONE!='$number' THEN PHONE ELSE OTHER END AS OTHER,STARTTIME,DURATION,INCOMING,
 IMEINUMBER,FIRST_CELLID AS CELLID INTO #TT1 FROM #TT";

    $sql17 = "UPDATE #TT1 SET OTHER=RIGHT(OTHER,10) WHERE LEN(OTHER)=14";

    $sql18 = "SELECT  PHONE, OTHER, 
sum(case when INCOMING='1' then 1 else 0 end) as 'IN',
sum(case when INCOMING ='0'Then 1 else 0 end) as 'OUT',
count(phone) as CALLS, 
sum(CAST(duration AS numeric)) as DUR, 
min(CAST(starttime AS DATETIME)) as FIRSTCALL,
max(CAST(starttime AS DATETIME)) as LASTCALL INTO:#RESULT FROM #TT1 
GROUP BY PHONE, OTHER ORDER BY calls DESC";

    $sql19 = "SELECT  DISTINCT a.PHONE, OTHER,
[IN],[OUT],CALLS,DUR, CAST(FIRSTCALL AS DATETIME) AS FIRST_CALL,
CAST(LASTCALL AS DATETIME) AS LAST_CALL,CASE WHEN NAME IS NULL THEN '' ELSE NAME END+' '+
CASE WHEN b.ADDRESS IS NULL THEN  
CASE WHEN (CALLS=DUR AND LEN(OTHER)<>10) 
OR (LEFT(OTHER,1)NOT IN ('9','8') AND LEN(OTHER)>14) 
OR LEN(OTHER)<10  OR SUBSTRING(OTHER,5,10) LIKE '%0000' or isnumeric(other)=0
--or (len(other)>11 and '00'+other not in (select phoneprefix+'%' from cdatphonearea))
THEN 'JUNK-COULD BE bulk SMS or VOIP calls' else
case when min(areadescription) is null then 'code n/a' else min(areadescription) end
END  ELSE b.ADDRESS+','+ISNULL(B.type,'') 
END AS ADDRESS INTO:#WITHADDRESS FROM 
#RESULT  A LEFT JOIN CDAT.DBO.CDATADDRESS B ON OTHER=B.PHONE
left join cdat.dbo.cdatphonearea   d on case when len(other)=10 then other else case when len(other)>10 then '00'+other else null end end
 like phoneprefix+'%'
group by a.PHONE, other,[IN],[OUT],calls,dur, FIRSTCALL,
LASTCALL,NAME,b.ADDRESS, type";

    $sql20 = "SELECT DISTINCT A.PHONE,CASE WHEN A.OTHER=C.PHONE THEN 
A.OTHER+'-'+NICKNAME ELSE A.OTHER END AS OTHER,[IN],[OUT],CALLS,DUR,
FIRST_CALL,LAST_CALL,CASE WHEN A.OTHER=B.PHONE THEN ISNULL(B.NAME,'')+','+ISNULL(B.ADDRESS,'')+','+
ISNULL(TYPE,'')+','+CONVERT(CHAR(10),CAST(ACTDATE AS DATETIME),105)  ELSE A.ADDRESS END AS ADDRESS INTO #TOT_WITHADDRESS FROM #WITHADDRESS A
LEFT JOIN CDAT.DBO.ADDRESS_OTHER_STATE B ON A.OTHER=B.PHONE
LEFT JOIN CDAT.DBO.CDATSUSPECT C ON A.OTHER=C.PHONE";

    $sql21 = "SELECT DISTINCT PHONE, OTHER,[IN],[OUT],CALLS,DUR,CONVERT(VARCHAR,(FIRST_CALL),20) AS FIRST_CALL,
CONVERT(VARCHAR,(LAST_CALL),20) AS LAST_CALL,ADDRESS FROM #TOT_WITHADDRESS ORDER BY ADDRESS";

    $sql22 = "SELECT 'VBR SUMMARY OF MOBILE NO: '+'$number' as PHONE1";

    sqlsrv_query($conn, $sql1);
    sqlsrv_query($conn, $sql2);
    sqlsrv_query($conn, $sql3);
    $st4 = sqlsrv_query($conn, $sql4);
    $st5 = sqlsrv_query($conn, $sql5);
    sqlsrv_query($conn, $sql6);
    sqlsrv_query($conn, $sql7);
    sqlsrv_query($conn, $sql8);
    sqlsrv_query($conn, $sql9);
    sqlsrv_query($conn, $sql10);
    sqlsrv_query($conn, $sql11);
    sqlsrv_query($conn, $sql12);
    sqlsrv_query($conn, $sql13);
    sqlsrv_query($conn, $sql14);
    sqlsrv_query($conn, $sql15);
    sqlsrv_query($conn, $sql16);
    sqlsrv_query($conn, $sql17);
    sqlsrv_query($conn, $sql18);
    sqlsrv_query($conn, $sql19);
    sqlsrv_query($conn, $sql20);
    $st21 = sqlsrv_query($conn, $sql21);
    $st22 = sqlsrv_query($conn, $sql22);

    $banner = 'VBR SUMMARY OF MOBILE NO: ' . $number;
    if ($st22 && ($b = sqlsrv_fetch_array($st22, SQLSRV_FETCH_ASSOC))) {
        $banner = (string) ($b['PHONE1'] ?? $banner);
    }

    $headerRows = cdat_sum_fetch_all($st4);
    $suspectRows = cdat_sum_fetch_all($st5);
    $contactRows = cdat_sum_fetch_all($st21);

    cdat_sum_results_open();
    cdat_sum_report_banner($banner);

    if (empty($headerRows)) {
        cdat_sum_empty_state('No VBR header record found.');
    } else {
        cdat_sum_generic_table_open(
            'VBR Phone',
            ['VBR_PHONE', 'FIRST_CALL', 'LAST_CALL'],
            'vbr_header_table',
            'vbr_header.csv',
            count($headerRows)
        );
        foreach ($headerRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if (empty($suspectRows)) {
        cdat_sum_empty_state('No CDAT suspect record found.');
    } else {
        cdat_sum_generic_table_open(
            'CDAT Phone',
            ['CDAT_PHONE', 'FIRST_CALL', 'LAST_CALL', 'LAST_UPDATED', 'NICKNAME'],
            'vbr_cdat_table',
            'vbr_cdat.csv',
            count($suspectRows)
        );
        foreach ($suspectRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_UPDATED'] ?? ''), 'class' => 'sum-cell-date'],
                (string) ($row['NICKNAME'] ?? ''),
            ]);
        }
        cdat_sum_generic_table_close();
    }

    if (empty($contactRows)) {
        cdat_sum_empty_state('No VBR contact records found.');
    } else {
        cdat_sum_generic_table_open(
            'VBR Contact Analysis',
            ['PHONE', 'OTHER', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS'],
            'vbr_contact_table',
            'vbr_contacts.csv',
            count($contactRows)
        );
        foreach ($contactRows as $row) {
            cdat_sum_table_row([
                ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OTHER'] ?? ''), 'class' => 'sum-cell-other'],
                ['text' => (string) ($row['IN'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['OUT'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['CALLS'] ?? ''), 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) ($row['DUR'] ?? ''), 'class' => 'sum-cell-num'],
                ['text' => (string) ($row['FIRST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['text' => (string) ($row['LAST_CALL'] ?? ''), 'class' => 'sum-cell-date'],
                ['html' => cdat_sum_address_lines((string) ($row['ADDRESS'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            ]);
        }
        cdat_sum_generic_table_close();
    }
    cdat_sum_results_close();

    sqlsrv_close($conn);

    if ($isAjax) {
        exit;
    }
    cdat_sum_page_close();
    layout_end();
    exit;
}

layout_begin('VBR Search');
cdat_sum_page_open();
cdat_sum_search_card(
    'VBR Search',
    'Search VBR / ISD call summary for a mobile number.',
    'vbr_search.php',
    cdat_sum_field_phone(),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
