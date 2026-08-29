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
        
        audit_log('Summary Total', 'Search', ['phone_number' => $number]);
        $conn = get_cdat_pdo();
                $sql3 = "CREATE TEMP TABLE temp_tt AS SELECT * FROM CDAT_DETAILS  WHERE PHONE='$number' and other ~ '^[0-9]+$'";

        $sql4 = "CREATE TEMP TABLE temp_result AS SELECT LTRIM(RTRIM(PHONE)) AS PHONE, LTRIM(RTRIM(OTHER)) AS OTHER, 
        SUM(CASE WHEN INCOMING='1' THEN 1 ELSE 0 END) AS \"IN\",
        SUM(CASE WHEN INCOMING ='0'THEN 1 ELSE 0 END) AS \"OUT\",
        COUNT(PHONE) AS CALLS,SUM(CAST(DURATION AS NUMERIC)) AS DUR, 
        TO_CHAR((MIN(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS FIRSTCALL,
        TO_CHAR((MAX(STARTTIME))::timestamp, 'YYYY-MM-DD HH24:MI:SS') AS LASTCALL FROM temp_tt 
        GROUP BY PHONE, OTHER";

        $sql5 = "CREATE TEMP TABLE temp_result1 AS SELECT * FROM temp_result WHERE OTHER NOT LIKE '140%' AND OTHER NOT IN (
        SELECT DISTINCT OTHER  FROM temp_result WHERE (CALLS=DUR OR CALLS>DUR)
        AND LEFT(OTHER,1) NOT IN ('9','8','7','G','I'))";

        $sql6 = "SELECT PHONE, OTHER, \"IN\", \"OUT\", CALLS, DUR, FIRSTCALL, LASTCALL
        FROM temp_result1 ORDER BY CALLS DESC";

        $sql8 = "SELECT 'SUMMARY OF MOBILE NO: ' || '$number' as PHONE1";

        $sql10 = "SELECT A.PHONE,TO_CHAR(MIN(STARTTIME), 'YYYY-MM-DD HH24:MI:SS') AS FIRST_CALL,TO_CHAR(MAX(STARTTIME), 'YYYY-MM-DD HH24:MI:SS') AS LAST_CALL,B.NICKNAME,TO_CHAR(MAX(A.ASONDATE), 'YYYY-MM-DD HH24:MI:SS') AS LAST_UPDATED 
        FROM CDATPCSUSPECT A  LEFT JOIN CDATSUSPECT B  ON A.PHONE=B.PHONE WHERE A.PHONE='$number' GROUP BY A.PHONE,B.NICKNAME";

        $sql12 = "SELECT case when count(PHONE)>=1 THEN '' ELSE 'Records not found' end as PHONE FROM temp_result";

        $st3 = $conn->query($sql3);
        $st4 = $conn->query($sql4);
        $st5 = $conn->query($sql5);
        $stmt = $conn->query($sql6);
        $st8 = $conn->query($sql8);
        $st10 = $conn->query($sql10);
        $st12 = $conn->query($sql12);

        if ($stmt === false) {
            die(print_r(error_get_last(), true));
        }

        $contactRows = [];
        $lookupPhones = [$number];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $contactRows[] = $row;
            $lookupPhones[] = $row['OTHER'] ?? '';
        }

        $phoneAreaPrefixes = cdat_load_phonearea_prefixes_local($conn);
        $suspectMap = cdat_fetch_suspect_nickname_map_local($conn, $lookupPhones);
        $cdatAddressMap = cdat_fetch_cdataddress_map_local($conn, $lookupPhones);
        $otherStateMap = cdat_fetch_other_state_address_map_local($conn, $lookupPhones);
        $headerPhoneArea = cdat_phonearea_lookup_local($phoneAreaPrefixes, $number);

        $headerRow = [
            'PHONE' => $number,
            'FIRST_CALL' => '',
            'LAST_CALL' => '',
            'NICKNAME' => '',
            'ADDRESS' => cdat_format_sum_header_address_local($number, $cdatAddressMap, $otherStateMap, $headerPhoneArea),
        ];
        if ($st10 && ($stats = $st10->fetch(PDO::FETCH_ASSOC))) {
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
                $row['ADDRESS'] = cdat_format_sum_contact_address_local(
                    $other,
                    (int) ($row['IN'] ?? 0),
                    (int) ($row['OUT'] ?? 0),
                    $row['DUR'] ?? 0,
                    $cdatAddressMap,
                    $otherStateMap,
                    cdat_phonearea_lookup_local($phoneAreaPrefixes, $other)
                );
                cdat_sum_contact_row($row);
            }

            cdat_sum_table_panel_close();
            cdat_sum_results_close();
        }

        $stmt = null;

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

function cdat_fetch_suspect_nickname_map_local($conn, array $phones): array
{
    $profiles = cdat_fetch_suspect_profile_map_local($conn, $phones);
    $map = [];
    foreach ($profiles as $phone => $profile) {
        $map[$phone] = $profile['nickname_label'];
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

function cdat_format_sum_contact_address_local(
    string $other,
    int $in,
    int $out,
    $dur,
    array $cdatAddressMap,
    array $otherStateMap,
    string $phoneAreaDescription
): string {
    $other = trim($other);
    $len = strlen($other);

    if (isset($cdatAddressMap[$other])) {
        return cdat_format_address_from_row_local($cdatAddressMap[$other]);
    }
    if (str_starts_with($other, '140')) {
        return 'TELE-MARKETING NUMBER';
    }
    if (str_starts_with($other, '1800') && $len === 11) {
        return 'TOLL-FREE NUMBER';
    }
    if (in_array($other, ['121', '111', '198', '123', '139', '122', '199', '12345'], true)) {
        return 'CUSTOMER CARE / ENQUIRY NUMBER';
    }
    if ($len < 10 && $out === 0 && (int) $dur > 0) {
        return 'POSSIBLE OF VOIP CALL OR SKYPE OR WIFI CALL';
    }
    if ($len < 10 && $in === 0 && (int) $dur > 0) {
        return 'POSSIBLE OF VOIP CALL OR CUSTOMER CARE / ENQUIRY NUMBER';
    }
    if (isset($otherStateMap[$other])) {
        $row = $otherStateMap[$other];
        return trim(($row['fullname'] ?? '') . ', ' . ($row['fulladdress'] ?? '') . ' ' . ($row['category_type'] ?? ''));
    }

    return $phoneAreaDescription;
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
