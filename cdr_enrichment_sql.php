<?php

function cdr_escape_sql_literal(string $value): string
{
    return str_replace("'", "''", $value);
}

function cdat_celltower_short_id(?string $cellTowerId): string
{
    $cellTowerId = trim((string) $cellTowerId);
    if ($cellTowerId === '') {
        return '';
    }

    return (string) preg_replace('/^[^-]+-[^-]+-/', '', $cellTowerId);
}

function cdat_fetch_tower_map($conn, array $rawCellTowerIds): array
{
    $shortByRaw = [];
    foreach ($rawCellTowerIds as $rawId) {
        $rawId = trim((string) $rawId);
        if ($rawId === '') {
            continue;
        }
        $shortByRaw[$rawId] = cdat_celltower_short_id($rawId);
    }

    $shortIds = array_values(array_unique(array_filter($shortByRaw)));
    if ($shortIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($shortIds), '?'));
    $sql = "SELECT DISTINCT ON (celltowerid)
                celltowerid,
                COALESCE(operator, '') AS operator,
                COALESCE(state, '') AS state,
                COALESCE(siteaddress, areadescription, '') AS areadescription,
                COALESCE(lat, '') AS lat,
                COALESCE(long, '') AS long,
                COALESCE(azimuth, '') AS azimuth
            FROM cdatcelltowerareanew
            WHERE celltowerid IN ($placeholders)
            ORDER BY celltowerid, lastupdate DESC NULLS LAST";

    $st = sqlsrv_query($conn, $sql, $shortIds);
    if ($st === false) {
        return [];
    }

    $byShort = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $byShort[$row['CELLTOWERID']] = [
            'operator' => $row['OPERATOR'] ?? '',
            'state' => $row['STATE'] ?? '',
            'areadescription' => $row['AREADESCRIPTION'] ?? '',
            'lat' => $row['LAT'] ?? '',
            'long' => $row['LONG'] ?? '',
            'azimuth' => $row['AZIMUTH'] ?? '',
        ];
    }

    $byRaw = [];
    foreach ($shortByRaw as $rawId => $shortId) {
        if ($shortId !== '' && isset($byShort[$shortId])) {
            $byRaw[$rawId] = $byShort[$shortId];
        }
    }

    return $byRaw;
}

function cdat_phone_prefix_key(?string $phone): string
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

function cdat_load_phonearea_prefixes($conn): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $cache = [];
    $st = sqlsrv_query($conn, 'SELECT phoneprefix, areadescription FROM cdatphonearea ORDER BY length(phoneprefix) DESC');
    if ($st === false) {
        return $cache;
    }

    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
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

function cdat_phonearea_lookup(array $prefixes, ?string $phone): string
{
    $key = cdat_phone_prefix_key($phone);
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

function cdat_fetch_suspect_profile_map($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = sqlsrv_query(
        $conn,
        "SELECT phone,
                COALESCE(nickname, '') AS nickname,
                COALESCE(mo, '') AS mo,
                COALESCE(inc_officer, '') AS inc_officer,
                COALESCE(category, '') AS category,
                COALESCE(role, '') AS role
         FROM cdatsuspect
         WHERE phone IN ($placeholders)",
        $phones
    );
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
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

function cdat_fetch_suspect_nickname_map($conn, array $phones): array
{
    $profiles = cdat_fetch_suspect_profile_map($conn, $phones);
    $map = [];
    foreach ($profiles as $phone => $profile) {
        $map[$phone] = $profile['nickname_label'];
    }

    return $map;
}

function cdat_fetch_cdataddress_map($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = sqlsrv_query(
        $conn,
        "SELECT phone,
                COALESCE(fullname, '') AS fullname,
                COALESCE(fulladdress, '') AS fulladdress,
                COALESCE(category_type, '') AS category_type,
                doa
         FROM cdataddress
         WHERE phone IN ($placeholders) AND eff_to_date IS NULL",
        $phones
    );
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $map[$row['PHONE']] = [
            'fullname' => $row['FULLNAME'] ?? '',
            'fulladdress' => $row['FULLADDRESS'] ?? '',
            'category_type' => $row['CATEGORY_TYPE'] ?? '',
            'doa' => $row['DOA'] ?? '',
        ];
    }

    return $map;
}

function cdat_fetch_other_state_address_map($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = sqlsrv_query(
        $conn,
        "SELECT phone,
                COALESCE(fullname, '') AS fullname,
                COALESCE(fulladdress, '') AS fulladdress,
                COALESCE(category_type, '') AS category_type,
                doa
         FROM address_other_state
         WHERE phone IN ($placeholders) AND eff_to_date IS NULL",
        $phones
    );
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $map[$row['PHONE']] = [
            'fullname' => $row['FULLNAME'] ?? '',
            'fulladdress' => $row['FULLADDRESS'] ?? '',
            'category_type' => $row['CATEGORY_TYPE'] ?? '',
            'doa' => $row['DOA'] ?? '',
        ];
    }

    return $map;
}

function cdat_format_address_from_row(array $row): string
{
    $parts = array_filter([
        trim(($row['fullname'] ?? '') . ', ' . ($row['fulladdress'] ?? '')),
        isset($row['doa']) && $row['doa'] !== '' ? 'DOA:' . $row['doa'] : '',
        $row['category_type'] ?? '',
    ]);

    return trim(implode(' ', $parts));
}

function cdat_format_sum_contact_address(
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
        return cdat_format_address_from_row($cdatAddressMap[$other]);
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

function cdat_format_sum_header_address(
    string $phone,
    array $cdatAddressMap,
    array $otherStateMap,
    string $phoneAreaDescription
): string {
    if (isset($cdatAddressMap[$phone])) {
        $row = $cdatAddressMap[$phone];
        $category = $row['category_type'] !== '' ? $row['category_type'] : $phoneAreaDescription;
        return cdat_format_address_from_row([
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

function cdat_format_cdatcnts_tt_address(
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

    $area = cdat_phonearea_lookup($phoneAreaPrefixes, $other);

    return trim($prefix . ($area !== '' ? $area : 'code n/a'));
}

function cdat_format_cdatcnts_other_state_address(array $row): string
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

function cdat_fetch_ir_forms_map($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $map = [];
    foreach ($phones as $phone) {
        $st = sqlsrv_query(
            $conn,
            "SELECT 1 FROM ir_particulars WHERE mobile LIKE ? LIMIT 1",
            ['%' . $phone . '%']
        );
        if ($st && sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
            $map[$phone] = 'IR AVAILABLE CLICK HERE TO VIEW IR';
        }
    }

    return $map;
}

function cdat_fetch_suspect_image_map($conn, array $phones): array
{
    $phones = array_values(array_unique(array_filter(array_map('strval', $phones))));
    if ($phones === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($phones), '?'));
    $st = sqlsrv_query(
        $conn,
        "SELECT mobile, image FROM suspect_image_table WHERE mobile IN ($placeholders)",
        $phones
    );
    if ($st === false) {
        return [];
    }

    $map = [];
    while ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC)) {
        $map[$row['MOBILE']] = $row['IMAGE'] ?? null;
    }

    return $map;
}

function cdat_default_suspect_image($conn): ?string
{
    static $image = false;
    if ($image !== false) {
        return $image;
    }

    $st = sqlsrv_query($conn, "SELECT image FROM suspect_image_table WHERE irkey = '113769' LIMIT 1");
    if ($st && ($row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC))) {
        $image = $row['IMAGE'] ?? null;
    } else {
        $image = null;
    }

    return $image;
}

function cdr_sql_enrich_tt(string $operator = '', string $state = '', array $opts = []): string
{
    $operator = cdr_escape_sql_literal($operator);
    $state = cdr_escape_sql_literal($state);
    $useKeys = !empty($opts['use_keys']);
    $withLastUpdate = !empty($opts['with_last_update']);
    $withLatLong = !empty($opts['with_lat_long']);
    $withStateCol = !empty($opts['with_state_col']);
    $withDateTimeCols = !empty($opts['with_date_time_cols']);
    $outputTable = $opts['output_table'] ?? '#temp_cdrs';

    $joinOn = $useKeys
        ? 'A.CELLTOWERID=B.CELLTOWERID AND A.STATE_KEY=B.STATE_KEY AND A.PROVIDER_KEY=B.PROVIDER_KEY'
        : 'A.CELLTOWERID=B.CELLTOWERID';

    if ($withLastUpdate) {
        $areaExpr = "(CASE WHEN A.CELLTOWERID=B.CELLTOWERID THEN MAX(COALESCE(SITEADDRESS, AREADESCRIPTION, '')) ELSE '' END +', LAST_UPDATE:'+CONVERT(VARCHAR,LASTUPDATE,20))";
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

    return "SELECT DISTINCT A.PHONE,OTHER,CASE WHEN other in (select phone from CDATDUPL.DBO.cdatsuspect) THEN nickname ELSE ' ' END as NICKNAME,
{$dateTimeSelect}STARTTIME,DURATION,TYPE,A.IMEINUMBER,A.CELLTOWERID,COALESCE(B.OPERATOR, '') AS OPERATOR{$stateSelect},
{$areaExpr} AS AREADESCRIPTION{$latSelect} INTO {$outputTable} FROM #TT A
LEFT JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B ON {$joinOn}
left join CDATDUPL.DBO.cdatsuspect c on a.other=c.phone
{$where}
GROUP BY {$groupBy}";
}

function cdr_sql_enrich_location_temp(string $sourceTable, string $outputTable): string
{
    return "SELECT DISTINCT A.PHONE,A.OTHER,A.STARTTIME,A.DURATION,
CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
A.IMEINUMBER,A.CELLTOWERID,
COALESCE(B.SITEADDRESS, B.AREADESCRIPTION, '') AS SITEADDRESS,
COALESCE(B.LAT, '') AS LAT,
COALESCE(B.LONG, '') AS LONG,
COALESCE(B.AZIMUTH, '') AS AZM
INTO {$outputTable} FROM {$sourceTable} A
LEFT JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID";
}

function cdr_sql_calls_between_dates(string $phone, string $fromDate, string $toDate, string $state = ''): string
{
    $phone = cdr_escape_sql_literal($phone);
    $fromDate = cdr_escape_sql_literal($fromDate);
    $toDate = cdr_escape_sql_literal($toDate);
    $state = cdr_escape_sql_literal($state);
    $stateFilter = $state !== '' ? "AND COALESCE(B.STATE, '') = '{$state}'" : '';

    return "SELECT
    A.PHONE,
    A.OTHER,
    CONVERT(VARCHAR, A.STARTTIME, 20) AS STARTTIME,
    A.DURATION,
    CASE WHEN A.INCOMING='1' THEN 'IN' ELSE 'OUT' END AS TYPE,
    A.IMEINUMBER,
    A.CELLTOWERID,
    COALESCE(B.SITEADDRESS, B.AREADESCRIPTION, 'NO LOCATION') AS AREADESCRIPTION,
    COALESCE(B.OPERATOR, '') AS OPERATOR
FROM CDATDUPL.DBO.CDATPCSUSPECT A
LEFT JOIN CDATDUPL.DBO.CDATCELLTOWERAREANEW B ON A.CELLTOWERID=B.CELLTOWERID
WHERE A.PHONE='{$phone}'
  AND convert(char(10), A.STARTTIME, 121) BETWEEN '{$fromDate}' AND '{$toDate}'
  {$stateFilter}
ORDER BY A.STARTTIME";
}
