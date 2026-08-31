<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

$isAjax = cdat_sum_is_ajax();
$number = trim((string) ($_POST['PHONE_NO'] ?? ''));
$hasSearch = $number !== '';
cdat_sum_ajax_need_search($hasSearch, 'Enter a mobile number and try again.');

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
    require_once CDAT_COMMON . '/activity_logger.php';
    require_once CDAT_COMMON . '/sql_safe.php';
    $number = sql_safe_phone($number);
    if ($number === '') {
        cdat_sum_empty_state('Enter a valid mobile number and try again.');
        if ($isAjax) {
            exit;
        }
        cdat_sum_page_close();
        layout_end();
        exit;
    }

    audit_log('CDAT Contacts', 'Search', ['phone_number' => $number]);
    $conn = get_cdat_pdo();

    // Use parameterized queries to prevent SQL injection
    $sql10 = "SELECT DISTINCT A.PHONE,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME || '_' || B.ROLE NICKNAME,B.MO,CATEGORY,TO_CHAR((MAX(A.ASONDATE))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED,
    INC_OFFICER 
    FROM CDATPCSUSPECT A LEFT JOIN CDATSUSPECT B ON A.PHONE=B.PHONE WHERE A.PHONE=? GROUP BY A.PHONE,B.NICKNAME,MO,CATEGORY, INC_OFFICER,B.ROLE";
    $params10 = array($number);
    $st10 = $conn->prepare($sql10);
    $st10->execute($params10);
    

    $sql4 = "CREATE TEMP TABLE temp_xx AS SELECT * FROM CDAT_DETAILS1 WHERE PHONE=? and other!=''";
    $params4 = array($number);
    $st4 = $conn->prepare($sql4);
    $st4->execute($params4);
    

    $sql5 = "CREATE TEMP TABLE temp_tt AS select distinct a.PHONE,OTHER, NICKNAME || '_' || ROLE NICKNAME,
    SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
    SUM(CASE WHEN INCOMING='0' THEN 1 ELSE 0 END) AS \"OUT\", count(*) as CALLS,sum(cast(duration as numeric)) as dur,TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') as FIRST_CALL,TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') as LAST_CALL,
    MO, CATEGORY, INC_OFFICER  from temp_xx a
    left join cdatsuspect b on a.other=b.phone
    WHERE OTHER IN (SELECT PHONE FROM CDATSUSPECT)
    group by a.phone, A.other, nickname,ROLE, MO, CATEGORY, INC_OFFICER order by  calls desc, other";
    $st5 = $conn->query($sql5);

    if ($st5 === false) {
        die(print_r(error_get_last(), true));
    }

    $sql8 = "SELECT 'CDAT CONTACTS OF MOBILE NO: ' || ? as PHONE";
    $params8 = array($number);
    $st8 = $conn->prepare($sql8);
    $st8->execute($params8);
    

    $phoneAreaPrefixes = cdat_load_phonearea_prefixes_local($conn);
    $cdatAddressMap = cdat_fetch_cdataddress_map_local($conn, [$number]);
    $otherStateMap = cdat_fetch_other_state_address_map_local($conn, [$number]);
    $defaultImage = cdat_default_suspect_image_local($conn);
    $suspectProfile = cdat_fetch_suspect_profile_map_local($conn, [$number]);
    $searchedSuspect = $suspectProfile[$number] ?? null;

    $headerRow = [
        'PHONE' => $number,
        'FIRST_CALL' => '',
        'LAST_CALL' => '',
        'NICKNAME' => $searchedSuspect['nickname_label'] ?? '',
        'MO' => $searchedSuspect['mo'] ?? '',
        'CAT' => $searchedSuspect['category'] ?? '',
        'ADDRESS' => cdat_format_sum_header_address_local($number, $cdatAddressMap, $otherStateMap, cdat_phonearea_lookup_local($phoneAreaPrefixes, $number)),
        'INC_OFFICER' => $searchedSuspect['inc_officer'] ?? '',
        'IMAGE' => $defaultImage,
    ];

    if ($st10 && ($stats = $st10->fetch(PDO::FETCH_ASSOC))) {
        $headerRow['FIRST_CALL'] = $stats['FIRST_CALL'] ?? '';
        $headerRow['LAST_CALL'] = $stats['LAST_CALL'] ?? '';
        if ($searchedSuspect === null) {
            $headerRow['NICKNAME'] = $stats['NICKNAME'] ?? '';
            $headerRow['MO'] = $stats['MO'] ?? '';
            $headerRow['CAT'] = $stats['CATEGORY'] ?? '';
            $headerRow['INC_OFFICER'] = $stats['INC_OFFICER'] ?? '';
        }
    }

    $headerImages = cdat_fetch_suspect_image_map_local($conn, [$number]);
    if (isset($headerImages[$number])) {
        $headerRow['IMAGE'] = $headerImages[$number];
    }

    $contactRows = [];
    $lookupPhones = [$number];
    $stContacts = $conn->query('SELECT * FROM temp_tt ORDER BY CALLS DESC, OTHER');
    if ($stContacts) {
        while ($row = $stContacts->fetch(PDO::FETCH_ASSOC)) {
            $contactRows[] = $row;
            $lookupPhones[] = $row['OTHER'] ?? '';
        }
    }

    $contactAddressMap = cdat_fetch_cdataddress_map_local($conn, $lookupPhones);
    $contactOtherStateMap = cdat_fetch_other_state_address_map_local($conn, $lookupPhones);
    $contactSuspectMap = cdat_fetch_suspect_profile_map_local($conn, array_column($contactRows, 'OTHER'));
    $irFormsMap = cdat_fetch_ir_forms_map_local($conn, array_column($contactRows, 'OTHER'));
    $contactImageMap = cdat_fetch_suspect_image_map_local($conn, array_column($contactRows, 'OTHER'));

    $displayContacts = [];
    foreach ($contactRows as $row) {
        $other = trim((string) ($row['OTHER'] ?? ''));
        $address = cdat_format_cdatcnts_tt_address_local(
            $other,
            $row['CALLS'] ?? 0,
            $row['DUR'] ?? 0,
            $contactAddressMap,
            $phoneAreaPrefixes
        );
        if (isset($contactOtherStateMap[$other])) {
            $address = cdat_format_cdatcnts_other_state_address_local($contactOtherStateMap[$other]);
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
    if ($st8 && ($bannerRow = $st8->fetch(PDO::FETCH_ASSOC))) {
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
    $conn = null;

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

function cdat_phone_prefix_key_local(?string $phone): string
{
    $phone = trim((string) $phone);
    if ($phone === '') {
        return '';
    }
    $len = strlen($phone);
    if ($len === 10) {
        return $phone;
    }
    if ($len > 10) {
        return '00' . $phone;
    }

    return $phone;
}

function cdat_load_phonearea_prefixes_local($conn): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    $st = $conn->query('SELECT phoneprefix, areadescription FROM cdatphonearea ORDER BY length(phoneprefix) DESC');
    if ($st === false) {
        return $cache;
    }

    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $prefix = trim((string) ($row['PHONEPREFIX'] ?? ''));
        if ($prefix === '') {
            continue;
        }
        $cache[] = [
            'prefix' => $prefix,
            'area' => trim((string) ($row['AREADESCRIPTION'] ?? '')),
        ];
    }

    return $cache;
}

function cdat_phonearea_lookup_local(array $prefixes, ?string $phone): string
{
    $key = cdat_phone_prefix_key_local($phone);
    if ($key === '') {
        return '';
    }

    foreach ($prefixes as $row) {
        if (strncmp($key, $row['prefix'], strlen($row['prefix'])) === 0) {
            return $row['area'];
        }
    }

    return '';
}

function cdat_fetch_suspect_profile_map_local($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = $conn->prepare("SELECT phone,
                COALESCE(nickname, '') AS nickname,
                COALESCE(mo, '') AS mo,
                COALESCE(inc_officer, '') AS inc_officer,
                COALESCE(category, '') AS category,
                COALESCE(role, '') AS role
         FROM cdatsuspect
         WHERE phone IN ($placeholders)"
    );
    $st->execute($phones);
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $nickname = $row['NICKNAME'] ?? '';
        $role = $row['ROLE'] ?? '';
        $label = $nickname;
        if ($role !== '') {
            $label = $nickname !== '' ? $nickname . '_' . $role : $role;
        }

        $map[$row['PHONE']] = [
            'nickname_label' => $label,
            'nickname' => $nickname,
            'mo' => $row['MO'] ?? '',
            'inc_officer' => $row['INC_OFFICER'] ?? '',
            'category' => $row['CATEGORY'] ?? '',
            'role' => $role,
        ];
    }

    return $map;
}

function cdat_fetch_cdataddress_map_local($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = $conn->prepare("SELECT phone,
                COALESCE(fullname, '') AS fullname,
                COALESCE(fulladdress, '') AS fulladdress,
                COALESCE(category_type, '') AS category_type,
                doa
         FROM cdataddress
         WHERE phone IN ($placeholders) AND eff_to_date IS NULL");
    $st->execute($phones);
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['PHONE']] = [
            'fullname' => $row['FULLNAME'] ?? '',
            'fulladdress' => $row['FULLADDRESS'] ?? '',
            'category_type' => $row['CATEGORY_TYPE'] ?? '',
            'doa' => $row['DOA'] ?? '',
        ];
    }

    return $map;
}

function cdat_fetch_other_state_address_map_local($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = $conn->prepare("SELECT phone,
                COALESCE(fullname, '') AS fullname,
                COALESCE(fulladdress, '') AS fulladdress,
                COALESCE(category_type, '') AS category_type,
                doa
         FROM address_other_state
         WHERE phone IN ($placeholders) AND eff_to_date IS NULL");
    $st->execute($phones);
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['PHONE']] = [
            'fullname' => $row['FULLNAME'] ?? '',
            'fulladdress' => $row['FULLADDRESS'] ?? '',
            'category_type' => $row['CATEGORY_TYPE'] ?? '',
            'doa' => $row['DOA'] ?? '',
        ];
    }

    return $map;
}

function cdat_format_address_from_row_local(array $row): string
{
    $parts = array_filter([
        trim(($row['fullname'] ?? '') . ', ' . ($row['fulladdress'] ?? '')),
        isset($row['doa']) && $row['doa'] !== '' ? 'DOA:' . $row['doa'] : '',
        $row['category_type'] ?? '',
    ]);

    return trim(implode(' ', $parts));
}

function cdat_format_sum_header_address_local(
    string $phone,
    array $cdatAddressMap,
    array $otherStateMap,
    string $phoneAreaDescription
): string {
    if (isset($cdatAddressMap[$phone])) {
        $row = $cdatAddressMap[$phone];
        $category = $row['category_type'] !== '' ? $row['category_type'] : $phoneAreaDescription;
        return cdat_format_address_from_row_local([
            'fullname' => $row['fullname'],
            'fulladdress' => $row['fulladdress'],
            'doa' => $row['doa'],
            'category_type' => $category,
        ]);
    }
    if (isset($otherStateMap[$phone])) {
        $row = $otherStateMap[$phone];
        $category = $row['category_type'] !== '' ? $row['category_type'] : $phoneAreaDescription;
        return trim(($row['fullname'] ?? '') . ', ' . ($row['fulladdress'] ?? '') . ', ' . ($row['doa'] ?? '') . ', ' . $category);
    }

    return $phoneAreaDescription;
}

function cdat_format_cdatcnts_tt_address_local(
    string $other,
    $calls,
    $dur,
    array $cdatAddressMap,
    array $phoneAreaPrefixes
): string {
    $other = trim($other);
    $addr = $cdatAddressMap[$other] ?? null;
    $prefix = ($addr && ($addr['fullname'] ?? '') !== '') ? $addr['fullname'] . ' ' : '';

    if ($addr && ($addr['fulladdress'] ?? '') !== '') {
        return trim($prefix . $addr['fulladdress'] . ',' . ($addr['category_type'] ?? ''));
    }

    $calls = (int) $calls;
    $dur = (int) $dur;
    $len = strlen($other);
    $isNumeric = preg_match('/^[0-9]+$/', $other) === 1;
    $junk = ($calls === $dur && $len !== 10)
        || (!in_array(substr($other, 0, 1), ['9', '8'], true) && $len > 14)
        || $len < 10
        || ($len >= 14 && str_contains(substr($other, 4, 10), '0000'))
        || !$isNumeric;

    if ($junk) {
        return trim($prefix . 'JUNK-COULD BE bulk SMS or VOIP calls');
    }

    $area = cdat_phonearea_lookup_local($phoneAreaPrefixes, $other);

    return trim($prefix . ($area !== '' ? $area : 'code n/a'));
}

function cdat_format_cdatcnts_other_state_address_local(array $row): string
{
    $doa = $row['doa'] ?? '';
    if ($doa !== '') {
        $ts = strtotime((string) $doa);
        $doa = $ts ? date('d-m-Y', $ts) : (string) $doa;
    }

    return trim(
        ($row['fullname'] ?? '') . ',' .
        ($row['fulladdress'] ?? '') . ',' .
        ($row['category_type'] ?? '') . ',' .
        $doa
    );
}

function cdat_fetch_ir_forms_map_local($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $map = [];
    $chunkSize = 50;
    for ($offset = 0; $offset < count($phones); $offset += $chunkSize) {
        $chunk = array_slice($phones, $offset, $chunkSize);
        $conditions = [];
        $params = [];
        foreach ($chunk as $phone) {
            $conditions[] = 'mobile LIKE ?';
            $params[] = '%' . $phone . '%';
        }
        $st = $conn->prepare('SELECT mobile FROM ir_particulars WHERE ' . implode(' OR ', $conditions));
        $st->execute($params);
        if ($st === false) {
            continue;
        }

        $mobiles = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $mobiles[] = (string) ($row['MOBILE'] ?? '');
        }

        foreach ($chunk as $phone) {
            foreach ($mobiles as $mobile) {
                if ($mobile !== '' && str_contains($mobile, $phone)) {
                    $map[$phone] = 'IR AVAILABLE CLICK HERE TO VIEW IR';
                    break;
                }
            }
        }
    }

    return $map;
}

function cdat_fetch_suspect_image_map_local($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = $conn->prepare("SELECT mobile, image FROM suspect_image_table WHERE mobile IN ($placeholders)");
    $st->execute($phones);
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['MOBILE']] = cdat_pg_binary_to_string($row['IMAGE'] ?? null);
    }

    return $map;
}
