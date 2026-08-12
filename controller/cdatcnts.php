<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';

if ($hasSearch) {
    if (!$isAjax) {
        layout_begin('CDAT Contacts');
        cdat_sum_page_open();
        cdat_sum_search_card(
            'CDAT Contacts of Mobile No',
            'Find CDAT contacts linked to a mobile number.',
            'cdatcnts.php',
            cdat_sum_field_phone($number),
            'BTN_CDAT',
            'Search'
        );
    }

    set_time_limit(0);
    require_once __DIR__ . '/activity_logger.php';
    require_once __DIR__ . '/cdr_enrichment_sql.php';

    audit_log('CDAT Contacts', 'Search', ['phone_number' => $_POST['PHONE_NO'] ?? '']);

    $serverName = "CPHYDERABAD1\DAU_HYD_2023";
    $connectionInfo = array( "Database"=>"CDATDUPL");
    $conn = sqlsrv_connect( $serverName, $connectionInfo );

    if( $conn === false ) {
        die( print_r( sqlsrv_errors(), true));
    }

    if ($number === '') {
        die('<center><font color="white">Phone number required</font></center>');
    }

    // Use parameterized queries to prevent SQL injection
    $sql10 = "SELECT DISTINCT A.PHONE,CONVERT(VARCHAR,MIN(STARTTIME),20) AS FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) AS LAST_CALL,B.NICKNAME+'_'+B.ROLE NICKNAME,B.MO,CATEGORY,CONVERT(VARCHAR,MAX(A.ASONDATE),20) AS LAST_UPDATED,
    INC_OFFICER 
    FROM CDATDUPL.DBO.CDATPCSUSPECT A LEFT JOIN CDATDUPL.DBO.CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE=? GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";
    $params10 = array($number);
    $st10 = sqlsrv_prepare($conn, $sql10, $params10);
    sqlsrv_execute($st10);

    $sql4 = "SELECT * INTO #XX FROM CDAT_DETAILS1 WHERE PHONE=? and other!=''";
    $params4 = array($number);
    $st4 = sqlsrv_prepare($conn, $sql4, $params4);
    sqlsrv_execute($st4);

    $sql5 = "select distinct a.PHONE,OTHER, NICKNAME+'_'+ROLE NICKNAME,
    SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS 'IN',
    SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS 'OUT', count(*) as CALLS,sum(cast(duration as numeric)) as dur,CONVERT(VARCHAR,MIN(STARTTIME),20) as FIRST_CALL,CONVERT(VARCHAR,MAX(STARTTIME),20) as LAST_CALL,
    MO, CATEGORY, INC_OFFICER INTO #TT from #XX a
    left join cdatdupl.dbo.cdatsuspect b on a.other=b.phone
    WHERE OTHER IN (SELECT PHONE FROM CDATDUPL.DBO.CDATSUSPECT)
    group by a.phone, A.other, nickname,ROLE, MO, CATEGORY, INC_OFFICER order by  calls desc, other";
    $st5 = sqlsrv_query($conn, $sql5);

    if ($st5 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql8 = "SELECT 'CDAT CONTACTS OF MOBILE NO: ' + ? as PHONE";
    $params8 = array($number);
    $st8 = sqlsrv_prepare($conn, $sql8, $params8);
    sqlsrv_execute($st8);

    $phoneAreaPrefixes = cdat_load_phonearea_prefixes($conn);
    $cdatAddressMap = cdat_fetch_cdataddress_map($conn, [$number]);
    $otherStateMap = cdat_fetch_other_state_address_map($conn, [$number]);
    $defaultImage = cdat_default_suspect_image($conn);
    $suspectProfile = cdat_fetch_suspect_profile_map($conn, [$number]);
    $searchedSuspect = $suspectProfile[$number] ?? null;

    $headerRow = [
        'PHONE' => $number,
        'FIRST_CALL' => '',
        'LAST_CALL' => '',
        'NICKNAME' => $searchedSuspect['nickname_label'] ?? '',
        'MO' => $searchedSuspect['mo'] ?? '',
        'CAT' => $searchedSuspect['category'] ?? '',
        'ADDRESS' => cdat_format_sum_header_address($number, $cdatAddressMap, $otherStateMap, cdat_phonearea_lookup($phoneAreaPrefixes, $number)),
        'INC_OFFICER' => $searchedSuspect['inc_officer'] ?? '',
        'IMAGE' => $defaultImage,
    ];

    if ($st10 && ($stats = sqlsrv_fetch_array($st10, SQLSRV_FETCH_ASSOC))) {
        $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
        $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
        if ($searchedSuspect === null) {
            $headerRow['NICKNAME'] = $stats['NICKNAME'] ?? '';
            $headerRow['MO'] = $stats['MO'] ?? '';
            $headerRow['CAT'] = $stats['CATEGORY'] ?? '';
            $headerRow['INC_OFFICER'] = $stats['INC_OFFICER'] ?? '';
        }
    }

    $headerImages = cdat_fetch_suspect_image_map($conn, [$number]);
    if (isset($headerImages[$number])) {
        $headerRow['IMAGE'] = $headerImages[$number];
    }

    $contactRows = [];
    $lookupPhones = [$number];
    $stContacts = sqlsrv_query($conn, 'SELECT * FROM #TT ORDER BY CALLS DESC, OTHER');
    if ($stContacts) {
        while ($row = sqlsrv_fetch_array($stContacts, SQLSRV_FETCH_ASSOC)) {
            $contactRows[] = $row;
            $lookupPhones[] = $row['OTHER'] ?? '';
        }
    }

    $contactAddressMap = cdat_fetch_cdataddress_map($conn, $lookupPhones);
    $contactOtherStateMap = cdat_fetch_other_state_address_map($conn, $lookupPhones);
    $contactSuspectMap = cdat_fetch_suspect_profile_map($conn, array_column($contactRows, 'OTHER'));
    $irFormsMap = cdat_fetch_ir_forms_map($conn, array_column($contactRows, 'OTHER'));
    $contactImageMap = cdat_fetch_suspect_image_map($conn, array_column($contactRows, 'OTHER'));

    $displayContacts = [];
    foreach ($contactRows as $row) {
        $other = trim((string) ($row['OTHER'] ?? ''));
        $address = cdat_format_cdatcnts_tt_address(
            $other,
            $row['CALLS'] ?? 0,
            $row['DUR'] ?? 0,
            $contactAddressMap,
            $phoneAreaPrefixes
        );
        if (isset($contactOtherStateMap[$other])) {
            $address = cdat_format_cdatcnts_other_state_address($contactOtherStateMap[$other]);
        }

        $suspect = $contactSuspectMap[$other] ?? null;

        $displayContacts[] = [
            'PHONE' => $row['PHONE'] ?? '',
            'OTHER' => $other,
            'NICKNAME' => $suspect['nickname_label'] ?? ($row['NICKNAME'] ?? ''),
            'MO' => $suspect['mo'] ?? ($row['MO'] ?? ''),
            'CAT' => $suspect['category'] ?? ($row['CATEGORY'] ?? ''),
            'IN' => $row['IN'] ?? '',
            'OUT' => $row['OUT'] ?? '',
            'CALLS' => $row['CALLS'] ?? '',
            'DUR' => $row['DUR'] ?? '',
            'FIRST_CALL' => $row['FIRST_CALL'] ?? '',
            'LAST_CALL' => $row['LAST_CALL'] ?? '',
            'ADDRESS' => $address,
            'INC_OFFICER' => $suspect['inc_officer'] ?? ($row['INC_OFFICER'] ?? ''),
            'IRFORMS' => $irFormsMap[$other] ?? '',
            'IMAGE' => $contactImageMap[$other] ?? $defaultImage,
        ];
    }

    $noContactsMsg = count($displayContacts) >= 1 ? '' : "*** NO CDAT CONTACTS TO $number ***";

    $bannerTitle = 'CDAT CONTACTS OF MOBILE NO: ' . $number;
    if ($st8 && ($bannerRow = sqlsrv_fetch_array($st8, SQLSRV_FETCH_ASSOC))) {
        $bannerTitle = (string) ($bannerRow['PHONE'] ?? $bannerTitle);
    }

    cdat_sum_results_open();
    cdat_sum_report_banner($bannerTitle);

    cdat_sum_generic_table_open(
        'Subject',
        ['PHONE', 'IMAGE', 'FIRST_CALL', 'LAST_CALL', 'NICKNAME', 'MO', 'CAT', 'ADDRESS', 'IO NAME'],
        'results_table',
        'cdat_contacts_subject.csv',
        1
    );
    $addrHtml = cdat_sum_address_lines((string) $headerRow['ADDRESS']);
    cdat_sum_table_row([
        ['text' => (string) $headerRow['PHONE'], 'class' => 'sum-cell-num'],
        ['html' => '<img height="100" width="100" src="' . cdat_base64_image_src($headerRow['IMAGE']) . '">', 'class' => 'sum-cell-img'],
        ['text' => (string) $headerRow['FIRST_CALL'], 'class' => 'sum-cell-date'],
        ['text' => (string) $headerRow['LAST_CALL'], 'class' => 'sum-cell-date'],
        (string) $headerRow['NICKNAME'],
        (string) $headerRow['MO'],
        (string) $headerRow['CAT'],
        ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
        (string) $headerRow['INC_OFFICER'],
    ]);
    cdat_sum_generic_table_close();

    if ($noContactsMsg !== '') {
        cdat_sum_empty_state($noContactsMsg);
    } else {
        cdat_sum_generic_table_open(
            'CDAT Contacts',
            ['PHONE', 'OTHER', 'IMAGE', 'NICK NAME', 'MO', 'CAT', 'IN', 'OUT', 'CALLS', 'DUR', 'FIRST_CALL', 'LAST_CALL', 'ADDRESS', 'IO NAME', 'IR'],
            'contact_results_table',
            'cdat_contacts.csv',
            count($displayContacts)
        );
        foreach ($displayContacts as $row) {
            $other = (string) $row['OTHER'];
            $addrHtml = cdat_sum_address_lines((string) $row['ADDRESS']);
            cdat_sum_table_row([
                ['text' => (string) $row['PHONE'], 'class' => 'sum-cell-num'],
                [
                    'html' => '<a href="CDATCNTS2.PHP?PHONE_NO=' . urlencode($other) . '">' . cdat_sum_h($other) . '</a>',
                    'class' => 'sum-cell-other',
                ],
                ['html' => '<img height="100" width="100" src="' . cdat_base64_image_src($row['IMAGE']) . '">', 'class' => 'sum-cell-img'],
                (string) $row['NICKNAME'],
                (string) $row['MO'],
                (string) $row['CAT'],
                ['text' => (string) $row['IN'], 'class' => 'sum-cell-num'],
                ['text' => (string) $row['OUT'], 'class' => 'sum-cell-num'],
                ['text' => (string) $row['CALLS'], 'class' => 'sum-cell-num sum-cell-calls'],
                ['text' => (string) $row['DUR'], 'class' => 'sum-cell-num'],
                ['text' => (string) $row['FIRST_CALL'], 'class' => 'sum-cell-date'],
                ['text' => (string) $row['LAST_CALL'], 'class' => 'sum-cell-date'],
                ['html' => $addrHtml !== '' ? $addrHtml : '—', 'class' => 'sum-address-cell'],
                (string) $row['INC_OFFICER'],
                [
                    'html' => '<a href="CDAT_IRFORM.PHP?OTHER_NO=' . urlencode($other) . '">' . cdat_sum_h((string) $row['IRFORMS']) . '</a>',
                ],
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

layout_begin('CDAT Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'CDAT Contacts of Mobile No',
    'Find CDAT contacts linked to a mobile number.',
    'cdatcnts.php',
    cdat_sum_field_phone(),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
