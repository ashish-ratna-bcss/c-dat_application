<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
if (!$isAjax) {
    layout_begin('Other State Number Twr');
    cdat_sum_page_open();
    cdat_sum_back_link('suspect_search.php');
}

$serverName = "CPHYDERABAD1\\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TWRMDB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}
$POLICE_STATION	= $_POST['Police_station'];
$CRIME_NO	= $_POST['CRIME_NO'];
$YEAR		= $_POST['YEAR'];
$OFF_DATE       = $_POST['OFF_DATE'];
$HH1		= $_POST['hh1'];
$MM1		= $_POST['mm1'];
$SS1		= $_POST['ss1'];
$HH2		= $_POST['hh2'];
$MM2		= $_POST['mm2'];
$SS2		= $_POST['ss2'];

$sql0="SELECT 'OTHER STATE NUMBERS SEARCH IN TOWER DUMP OF PS: '+'$POLICE_STATION'+' UNDER CRIME NO '+'$CRIME_NO:'+'/'+'$YEAR' as SEARCH";

$time1="select '$HH1'+':'+'$MM1'+':'+'$SS1' as Timing into #time";

$time2="insert into #time select '$HH2'+':'+'$MM2'+':'+'$SS2' as Timing";

$sql1 ="select distinct a.PHONE,AREADESCRIPTION,STATE from twrmdb..TWRMDB_MASTER_CDAT a inner join cdatdupl..cdatphonearea b
on phone like '[7-9]%' and len(phone)='10' and left(phone,5) = phoneprefix and b.STATE_KEY!='1' AND crkey=(SELECT DISTINCT CRKEY FROM OFFENCE_DETAILS WHERE POLICE_STATION='$POLICE_STATION' AND CRIME_NO='$CRIME_NO' AND YEAR='$YEAR' AND PLACE_DESCRIPTION='PLACE_OF_OFFENCE') AND convert(date,starttime)='$OFF_DATE' AND (convert(time,starttime) between (select distinct min(Timing) from #time) and (select distinct max(Timing) from #time))";

$st1 = sqlsrv_query( $conn, $time1 );
$st2 = sqlsrv_query( $conn, $time2 );
$st0 = sqlsrv_query( $conn, $sql0);
$st3 = sqlsrv_query( $conn, $sql1 );
$banner = 'OTHER STATE NUMBERS SEARCH IN TOWER DUMP';
if ($st0 && ($b = sqlsrv_fetch_array($st0, SQLSRV_FETCH_ASSOC))) {
    $banner = (string) ($b['SEARCH'] ?? $banner);
}
$rows = cdat_sum_fetch_all($st3);

cdat_sum_results_open();
cdat_sum_report_banner($banner);
if (empty($rows)) {
    cdat_sum_empty_state('No other-state numbers found.');
} else {
    cdat_sum_generic_table_open(
        'Other State Numbers',
        ['PHONE', 'AREADESCRIPTION', 'STATE'],
        'other_state_twr_table',
        'other_state_number_twr.csv',
        count($rows)
    );
    foreach ($rows as $row) {
        cdat_sum_table_row([
            ['text' => (string) ($row['PHONE'] ?? ''), 'class' => 'sum-cell-num'],
            ['html' => cdat_sum_address_lines((string) ($row['AREADESCRIPTION'] ?? '')) ?: '—', 'class' => 'sum-address-cell'],
            (string) ($row['STATE'] ?? ''),
        ]);
    }
    cdat_sum_generic_table_close();
}
cdat_sum_results_close();

if ($st1) {
    sqlsrv_free_stmt($st1);
}
sqlsrv_close($conn);

if ($isAjax) {
    exit;
}
cdat_sum_page_close();
layout_end();
