<?php
/**
 * Shared UI helpers for Summary menu pages (sum_home, sum_in_state, etc.).
 * PHP logic and SQL stay in each page — this file only renders HTML shells.
 */

function cdat_sum_is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/** AJAX POST with empty fields must not re-render the search form (that duplicates it). */
function cdat_sum_ajax_need_search(bool $hasSearch, string $message = 'Fill in the required fields and try again.'): void
{
    if ($hasSearch) {
        cdat_sum_begin_heavy_search();
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !cdat_sum_is_ajax() || $hasSearch) {
        return;
    }
    cdat_sum_empty_state($message);
    exit;
}

function cdat_sum_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}

/** @return list<string> */
function cdat_sum_split_phones(string $raw): array
{
    $out = [];
    foreach (preg_split('/[,\s]+/', $raw) as $p) {
        $p = trim($p);
        if ($p !== '') {
            $out[] = $p;
        }
    }
    return array_values(array_unique($out));
}

function cdat_sum_sql_phone_in(array $phones): string
{
    $esc = [];
    foreach ($phones as $p) {
        $esc[] = str_replace("'", "''", $p);
    }
    return implode("','", $esc);
}

/** Insert one row per phone into a temp table (Postgres cannot run the old MSSQL multi-INSERT trick). */
function cdat_sum_insert_phones($conn, string $tempTable, array $phones): void
{
    $table = ltrim($tempTable, '#');
    if (!preg_match('/^[a-z][a-z0-9_]*$/', $table)) {
        throw new InvalidArgumentException('Invalid temp table name: ' . $tempTable);
    }
    if ($phones === []) {
        return;
    }

    $st = $conn->prepare("INSERT INTO {$table} (phone) VALUES (?)");
    foreach ($phones as $p) {
        $p = trim((string) $p);
        if ($p === '') {
            continue;
        }
        $st->execute([$p]);
    }
}

/** Format address for display: one segment per line (UI only). */
function cdat_sum_address_lines(string $address): string
{
    if ($address === '') {
        return '';
    }
    $parts = preg_split('/,\s*/', $address);
    $parts = array_values(array_filter(array_map('trim', $parts)));
    return implode('<br>', array_map(static fn(string $p): string => cdat_sum_h($p), $parts));
}

function cdat_sum_field_phone(string $value = '', string $id = 'SUM'): string
{
    $val = cdat_sum_h($value);
    return '<div class="sum-search-form__field col-12 col-sm-6 col-lg-4">'
         . '<label class="form-label" for="' . $id . '">Mobile No</label>'
         . '<input type="text" name="PHONE_NO" id="' . $id . '" class="form-control" placeholder="Enter mobile number"'
         . ' required="required" minlength="7" maxlength="15" pattern="^\+?[0-9]+$"'
         . ' title="Please enter a valid phone number (numbers and optional || only)"'
         . ' oninput="this.value = this.value.replace(/[^0-9+]/g, \'\')" autocomplete="off"'
         . ' inputmode="tel" value="' . $val . '"/>'
         . '</div>';
}

/** Normalize cdatphonearea.state for dropdown values and SQL filters. */
function cdat_sum_normalize_phone_area_state(string $state): string
{
    $state = strtoupper(trim($state));
    $state = str_replace('-', '_', $state);
    return preg_replace('/\s+/', ' ', $state) ?? $state;
}

/** SQL expression matching {@see cdat_sum_normalize_phone_area_state()}. */
function cdat_sum_sql_phone_area_state_expr(string $column): string
{
    return 'UPPER(TRIM(REPLACE(' . $column . ", '-', '_')))";
}

/** Map cdatphonearea.state variants to a canonical Indian state / region label. */
function cdat_sum_phone_area_state_canonical(string $state): ?string
{
    $norm = cdat_sum_normalize_phone_area_state($state);
    if ($norm === '') {
        return null;
    }

    static $excluded = [
        'BSNL_INMARSAT' => true,
        'BSNL INMARSAT' => true,
    ];
    if (isset($excluded[$norm])) {
        return null;
    }

    static $aliases = [
        'CHENNAI' => 'TAMILNADU',
        'KOLKATA' => 'WEST BENGAL',
        'MUMBAI' => 'MAHARASHTRA',
        'UP_EAST' => 'UTTAR PRADESH',
        'UP_WEST' => 'UTTAR PRADESH',
        'UPEAST' => 'UTTAR PRADESH',
        'UPWEST' => 'UTTAR PRADESH',
        'HIMACHALPRADESH' => 'HIMACHAL PRADESH',
        'MADHYAPRADESH' => 'MADHYA PRADESH',
        'MAHARASTRA' => 'MAHARASHTRA',
        'RAJASTAN' => 'RAJASTHAN',
        'RAHASTHAN' => 'RAJASTHAN',
        'PANJAB' => 'PUNJAB',
        'KERLA' => 'KERALA',
        'WESTBENGAL' => 'WEST BENGAL',
        'JAMMUKASHMIR' => 'JAMMU_KASHMIR',
    ];

    return $aliases[$norm] ?? $norm;
}

/** @return list<string> DB state values that belong to a canonical state selection. */
function cdat_sum_phone_area_state_db_values(string $state): array
{
    $canonical = cdat_sum_phone_area_state_canonical($state) ?? cdat_sum_normalize_phone_area_state($state);

    static $circles = [
        'TAMILNADU' => ['TAMILNADU', 'CHENNAI'],
        'WEST BENGAL' => ['WEST BENGAL', 'WESTBENGAL', 'KOLKATA'],
        'MAHARASHTRA' => ['MAHARASHTRA', 'MAHARASTRA', 'MUMBAI'],
        'UTTAR PRADESH' => ['UP_EAST', 'UP_WEST', 'UPEAST', 'UPWEST'],
        'HIMACHAL PRADESH' => ['HIMACHAL PRADESH', 'HIMACHALPRADESH'],
        'MADHYA PRADESH' => ['MADHYA PRADESH', 'MADHYAPRADESH'],
        'RAJASTHAN' => ['RAJASTHAN', 'RAJASTAN', 'RAHASTHAN'],
        'PUNJAB' => ['PUNJAB', 'PANJAB'],
        'KERALA' => ['KERALA', 'KERLA'],
        'JAMMU_KASHMIR' => ['JAMMU_KASHMIR', 'JAMMUKASHMIR'],
    ];

    $values = $circles[$canonical] ?? [$canonical];
    $out = [];
    foreach ($values as $value) {
        $norm = cdat_sum_normalize_phone_area_state($value);
        if ($norm !== '') {
            $out[$norm] = $norm;
        }
    }
    return array_values($out);
}

function cdat_sum_sql_phone_area_state_filter(string $column, string $state, bool $exclude = false): string
{
    $expr = cdat_sum_sql_phone_area_state_expr($column);
    $values = cdat_sum_phone_area_state_db_values($state);
    if ($values === []) {
        $escaped = str_replace("'", "''", cdat_sum_normalize_phone_area_state($state));
        return $exclude ? "{$expr} <> '{$escaped}'" : "{$expr} = '{$escaped}'";
    }

    $quoted = [];
    foreach ($values as $value) {
        $quoted[] = "'" . str_replace("'", "''", $value) . "'";
    }
    $in = implode(', ', $quoted);

    return $exclude ? "{$expr} NOT IN ({$in})" : "{$expr} IN ({$in})";
}

/** @return array<string,string> */
function cdat_sum_phone_area_state_options(string $placeholder = 'Select state'): array
{
    static $loaded = false;
    static $values = [];

    if (!$loaded) {
        $loaded = true;
        try {
            if (!function_exists('get_cdat_pdo')) {
                require_once dirname(__DIR__) . '/db_connect.php';
            }
            $sql = 'SELECT DISTINCT UPPER(TRIM(REPLACE(state, \'-\', \'_\'))) AS state_norm'
                 . ' FROM cdatphonearea'
                 . ' WHERE state IS NOT NULL AND BTRIM(state) <> \'\''
                 . ' ORDER BY 1';
            $canonical = [];
            foreach (get_cdat_pdo()->query($sql) as $row) {
                $norm = trim((string) ($row['STATE_NORM'] ?? ''));
                $label = cdat_sum_phone_area_state_canonical($norm);
                if ($label !== null) {
                    $canonical[$label] = $label;
                }
            }
            $values = array_keys($canonical);
            sort($values, SORT_STRING);
        } catch (Throwable $e) {
            $values = [];
        }
    }

    $options = ['' => $placeholder];
    static $labels = [
        'NORTH_EAST' => 'NORTH EAST (telecom region)',
        'DELHI' => 'DELHI (NCR circle)',
        'JAMMU_KASHMIR' => 'JAMMU & KASHMIR',
    ];
    foreach ($values as $state) {
        $options[$state] = $labels[$state] ?? $state;
    }
    return $options;
}

function cdat_sum_field_state(string $selected = '', bool $required = true): string
{
    $options = cdat_sum_phone_area_state_options();
    return cdat_sum_searchable_select(
        'STATE',
        'State / Region',
        $options,
        $selected,
        'Select state or region',
        $required,
        'sum-search-form__field--state'
    );
}

/**
 * Searchable select with visible placeholder.
 *
 * @param array<string,string> $options value => label (empty key = placeholder)
 */
function cdat_sum_searchable_select(string $name, string $label, array $options,
                                    string $selected = '', string $placeholder = 'Select…',
                                    bool $required = false, string $fieldClass = '',
                                    string $id = ''): string
{
    $idAttr = $id !== '' ? $id : $name;
    $req = $required ? ' required="required"' : '';
    $extra = $fieldClass !== '' ? ' ' . $fieldClass : '';
    $html = '<div class="sum-search-form__field col-12 col-sm-6 col-lg-4' . $extra . '">'
          . '<label class="form-label" for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
          . '<select name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"'
          . $req . ' class="form-select sum-select" data-searchable-select="1"'
          . ' data-placeholder="' . cdat_sum_h($placeholder) . '">';
    foreach ($options as $value => $optLabel) {
        $isPlaceholder = ((string) $value === '');
        $text = $isPlaceholder ? $placeholder : $optLabel;
        $sel = ((string) $selected === (string) $value) ? ' selected="selected"' : '';
        if ($isPlaceholder) {
            $html .= '<option value=""' . $sel . ($required ? ' disabled="disabled"' : '')
                   . ' data-placeholder="1">' . cdat_sum_h($text) . '</option>';
        } else {
            $html .= '<option value="' . cdat_sum_h((string) $value) . '"' . $sel . '>'
                   . cdat_sum_h((string) $optLabel) . '</option>';
        }
    }
    $html .= '</select></div>';
    return $html;
}

function cdat_sum_field_hms(string $hName, string $mName, string $sName, string $label,
                            string $h = '00', string $m = '00', string $s = '00',
                            bool $required = true): string
{
    $req = $required ? ' required="required"' : '';
    return '<div class="sum-search-form__field sum-search-form__field--hms col-12 col-sm-6 col-lg-4">'
         . '<label class="form-label">' . cdat_sum_h($label) . '</label>'
         . '<div class="sum-hms input-group">'
         . '<input type="number" class="form-control" name="' . cdat_sum_h($hName) . '" min="0" max="23" value="'
         . cdat_sum_h($h) . '"' . $req . ' />'
         . '<span>:</span>'
         . '<input type="number" class="form-control" name="' . cdat_sum_h($mName) . '" min="0" max="59" value="'
         . cdat_sum_h($m) . '"' . $req . ' />'
         . '<span>:</span>'
         . '<input type="number" class="form-control" name="' . cdat_sum_h($sName) . '" min="0" max="59" value="'
         . cdat_sum_h($s) . '"' . $req . ' />'
         . '</div></div>';
}

function cdat_sum_tower_cascade_script(): void
{
    echo '<script src="' . htmlspecialchars(CDAT_ASSETS . '/js/jquerydynamic.js', ENT_QUOTES)
       . '"></script>'
       . '<script>function getps(val){jQuery.ajax({type:"POST",url:'
       . json_encode(function_exists('cdat_href') ? cdat_href('/api/crime-numbers') : '/api/crime-numbers')
       . ',data:"POLICE_STATION="+val,success:function(data){jQuery("temp_Crime-list").html(data);}});}'
       . 'function getyear(val1){jQuery.ajax({type:"POST",url:'
       . json_encode(function_exists('cdat_href') ? cdat_href('/api/years') : '/api/years')
       . ',data:"CRIME_NO="+val1,success:function(data){jQuery("temp_YEAR").html(data);}});}</script>';
}

function cdat_sum_field_date(string $name, string $label, string $id = '', string $value = '',
                             bool $required = true): string
{
    $idAttr = $id !== '' ? $id : $name;
    $req = $required ? ' required="required"' : '';
    return '<div class="sum-search-form__field sum-search-form__field--date col-12 col-sm-6 col-lg-3">'
         . '<label class="form-label" for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
         . '<input type="text" name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"'
         . ' class="form-control sum-date-input" data-date-picker="1" placeholder="yyyy-mm-dd"'
         . ' pattern="^\d{4}-\d{2}-\d{2}$" readonly="readonly" inputmode="none" autocomplete="off"'
         . ' title="Select a date from the calendar"' . $req
         . ' value="' . cdat_sum_h($value) . '"/>'
         . '</div>';
}

function cdat_sum_field_date_native(string $name, string $label, string $value = '',
                                    bool $required = true): string
{
    // Same custom date component as cdat_sum_field_date (native picker removed for consistency).
    return cdat_sum_field_date($name, $label, $name, $value, $required);
}

function cdat_sum_back_link(string $url, string $label = 'Back to search'): void
{
    if ($url !== '' && function_exists('cdat_page') && !str_starts_with($url, '/') && !preg_match('#^(https?:)?//#i', $url)) {
        $url = cdat_page($url);
    } elseif ($url !== '' && function_exists('cdat_href') && str_starts_with($url, '/')) {
        $url = cdat_href($url);
    }
    echo '<p class="sum-back-link"><a href="' . cdat_sum_h($url) . '" class="btn btn-outline-secondary btn-sm">&larr; ' . cdat_sum_h($label) . '</a></p>';
}

function cdat_sum_page_open(string $extraClass = ''): void
{
    $class = 'sum-page' . ($extraClass !== '' ? ' ' . cdat_sum_h($extraClass) : '');
    echo '<div class="' . $class . '">';
}

function cdat_sum_page_close(): void
{
    echo '</div>';
}

function cdat_sum_search_card(string $title, string $desc, string $action, string $fieldsHtml,
                              string $submitName = 'BTN_SUM', string $submitValue = 'Search',
                              string $method = 'post'): void
{
    $methodAttr = strtolower($method) === 'get' ? 'get' : 'post';
    $formClass = 'sum-search-form row g-3 align-items-end' . ($methodAttr === 'get' ? ' no-ajax' : '');
    echo '<section class="sum-search-card mb-3 pb-3 border-bottom" aria-label="Search">';
    echo '<div class="sum-search-card__head mb-3">';
    echo '<div class="sum-search-card__icon" aria-hidden="true">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"'
       . ' stroke-linecap="round" stroke-linejoin="round">'
       . '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>';
    echo '</div><div>';
    echo '<h2 class="sum-search-card__title h5 mb-1">' . cdat_sum_h($title) . '</h2>';
    echo '<p class="sum-search-card__desc text-secondary small mb-0">' . cdat_sum_h($desc) . '</p>';
    echo '</div></div>';
    $formAction = function_exists('cdat_form_action') ? cdat_form_action($action) : $action;
    echo '<form id="form1" name="form1" method="' . $methodAttr . '" action="' . cdat_sum_h($formAction)
       . '" class="' . $formClass . '">';
    echo $fieldsHtml;
    echo '<div class="col-12 col-sm-auto">';
    echo '<input type="submit" name="' . cdat_sum_h($submitName) . '" id="' . cdat_sum_h($submitName) . '"'
       . ' class="sum-search-form__submit btn btn-primary w-100 w-sm-auto" value="' . cdat_sum_h($submitValue) . '" />';
    echo '</div>';
    echo '</form></section>';
    echo '<div id="global-ajax-results" class="sum-ajax-results" aria-live="polite"></div>';
}

function cdat_sum_field_text(string $name, string $label, string $value = '', string $id = '',
                             string $placeholder = '', bool $required = true, string $inputMode = ''): string
{
    $idAttr = $id !== '' ? $id : $name;
    $req = $required ? ' required="required"' : '';
    $ph = $placeholder !== '' ? ' placeholder="' . cdat_sum_h($placeholder) . '"' : '';
    $mode = $inputMode !== '' ? ' inputmode="' . cdat_sum_h($inputMode) . '"' : '';
    return '<div class="sum-search-form__field col-12 col-sm-6 col-lg-4">'
         . '<label class="form-label" for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
         . '<input type="text" class="form-control" name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"' . $ph . $req
         . ' autocomplete="off" value="' . cdat_sum_h($value) . '"' . $mode . '/>'
         . '</div>';
}

function cdat_sum_field_other_phone(string $value = ''): string
{
    return cdat_sum_field_text('OTHER_NO', 'Other No', $value, 'OTHER_NO', 'Enter other number', true, 'tel');
}

function cdat_sum_field_imei(string $value = ''): string
{
    return '<div class="sum-search-form__field col-12 col-sm-6 col-lg-4">'
         . '<label class="form-label" for="IMEI">IMEI Number</label>'
         . '<input type="text" name="IMEI_NO" id="IMEI" class="form-control" placeholder="Enter IMEI number"'
         . ' required="required" minlength="15" maxlength="15" pattern="^[0-9]{15}$"'
         . ' title="IMEI must be exactly 15 digits"'
         . ' oninput="this.value = this.value.replace(/[^0-9]/g, \'\')" autocomplete="off"'
         . ' inputmode="numeric" value="' . cdat_sum_h($value) . '"/>'
         . '</div>';
}

function cdat_sum_field_textarea(string $name, string $label, string $value = '', string $placeholder = ''): string
{
    return '<div class="sum-search-form__field sum-search-form__field--textarea col-12">'
         . '<label class="form-label" for="' . cdat_sum_h($name) . '">' . cdat_sum_h($label) . '</label>'
         . '<textarea name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($name) . '" class="form-control" rows="3"'
         . ' placeholder="' . cdat_sum_h($placeholder) . '" required="required">'
         . cdat_sum_h($value) . '</textarea></div>';
}

/**
 * Shared file / image upload field (styled dropzone via JS).
 */
function cdat_sum_field_file(string $name, string $label = 'Upload file',
                             string $accept = '', bool $multiple = false,
                             bool $required = true, string $id = '',
                             string $hint = ''): string
{
    $idAttr = $id !== '' ? $id : preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
    $req = $required ? ' required="required"' : '';
    $mult = $multiple ? ' multiple="multiple"' : '';
    $acc = $accept !== '' ? ' accept="' . cdat_sum_h($accept) . '"' : '';
    $isImage = $accept !== '' && (stripos($accept, 'image') !== false || $accept === 'image/*');
    $mode = $isImage ? 'image' : 'file';
    $defaultHint = $isImage
        ? ($multiple ? 'PNG, JPG, or WebP — drag & drop or browse' : 'PNG, JPG, or WebP — drag & drop or browse')
        : ($multiple ? 'Drag & drop files, or browse' : 'Drag & drop a file, or browse');
    $hintText = $hint !== '' ? $hint : $defaultHint;

    return '<div class="sum-search-form__field sum-search-form__field--file">'
         . '<label for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
         . '<input type="file" name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"'
         . ' class="sum-file-input" data-file-upload="1" data-upload-mode="' . cdat_sum_h($mode) . '"'
         . ' data-upload-hint="' . cdat_sum_h($hintText) . '"'
         . $acc . $mult . $req . '/>'
         . '</div>';
}

function cdat_sum_field_image(string $name = 'image', string $label = 'Image',
                              bool $required = true, bool $multiple = false,
                              string $id = ''): string
{
    return cdat_sum_field_file(
        $name,
        $label,
        'image/*',
        $multiple,
        $required,
        $id !== '' ? $id : $name,
        $multiple ? 'Drag & drop images, or browse' : 'Drag & drop an image, or browse'
    );
}

/** @return array<string,string> */
function cdat_sum_operator_options(): array
{
    return [
        '' => 'Select operator',
        'AIRCEL_TOWER' => 'AIRCEL_TOWER',
        'AIRTEL_TOWER' => 'AIRTEL_TOWER',
        'BPL_TOWER' => 'BPL_TOWER',
        'CELLONE_TOWER' => 'CELLONE_TOWER',
        'ETISALAT_TOWER' => 'ETISALAT_TOWER',
        'IDEA_TOWER' => 'IDEA_TOWER',
        'JIO_TOWER' => 'JIO_TOWER',
        'MTS_TOWER' => 'MTS_TOWER',
        'RELIANCE_TOWER' => 'RELIANCE_TOWER',
        'TATA_TOWER' => 'TATA_TOWER',
        'UNINOR_TOWER' => 'UNINOR_TOWER',
        'VIDEOCON_TOWER' => 'VIDEOCON_TOWER',
        'VODAFONE_TOWER' => 'VODAFONE_TOWER',
    ];
}

function cdat_sum_field_operator(string $selected = ''): string
{
    return cdat_sum_searchable_select(
        'OPERATOR',
        'Operator',
        cdat_sum_operator_options(),
        $selected,
        'Select operator',
        false,
        'sum-search-form__field--operator'
    );
}

/** @return array<string,string> */
function cdat_sum_call_state_options(): array
{
    return cdat_sum_phone_area_state_options();
}

function cdat_sum_field_call_state(string $selected = '', bool $required = false): string
{
    return cdat_sum_searchable_select(
        'STATE',
        'State',
        cdat_sum_call_state_options(),
        $selected,
        'Select state',
        $required,
        'sum-search-form__field--state'
    );
}

function cdat_sum_report_banner(string $title, string $subtitle = ''): void
{
    echo '<div class="sum-report-banner">';
    echo '<h2 class="sum-report-banner__title">' . cdat_sum_h($title) . '</h2>';
    if ($subtitle !== '') {
        echo '<p class="sum-report-banner__subtitle">' . cdat_sum_h($subtitle) . '</p>';
    }
    echo '</div>';
}

/**
 * @param array<int,string> $headers
 */
function cdat_sum_generic_table_open(string $panelTitle, array $headers,
                                     string $tableId = 'results_table',
                                     string $exportName = 'export.csv', int $count = 0,
                                     string $tbodyId = ''): void
{
    echo '<section class="sum-table-panel mb-3" aria-label="' . cdat_sum_h($panelTitle) . '">';
    echo '<div class="sum-table-panel__head d-flex flex-wrap align-items-center gap-2 mb-2">';
    echo '<div class="sum-table-panel__head-left d-flex flex-wrap align-items-center gap-2">';
    echo '<h3 class="sum-table-panel__title h6 mb-0">' . cdat_sum_h($panelTitle) . '</h3>';
    if ($count > 0) {
        echo '<span class="sum-badge badge text-bg-primary">' . (int) $count . ' records</span>';
    }
    echo '</div>';
    echo '<div class="sum-table-panel__actions ms-auto" id="sum-table-actions"></div>';
    echo '</div>';
    echo '<div class="table-responsive sum-table-scroll"><table id="' . cdat_sum_h($tableId) . '" class="table table-striped table-hover table-sm mb-0 sum-data-table"'
       . ' border="1" data-export-name="' . cdat_sum_h($exportName) . '"><thead><tr>';
    foreach ($headers as $header) {
        echo '<th>' . cdat_sum_h($header) . '</th>';
    }
    $tbodyAttr = $tbodyId !== '' ? ' id="' . cdat_sum_h($tbodyId) . '"' : '';
    echo '</tr></thead><tbody' . $tbodyAttr . '>';
}

function cdat_sum_generic_table_close(): void
{
    echo '</tbody></table></div></section>';
}

/**
 * @param array<int, string|array{html?:string,text?:string,class?:string}> $cells
 */
function cdat_sum_table_row(array $cells): void
{
    echo '<tr>';
    foreach ($cells as $cell) {
        if (is_array($cell)) {
            $class = isset($cell['class']) ? ' class="' . cdat_sum_h($cell['class']) . '"' : '';
            $content = $cell['html'] ?? cdat_sum_h((string) ($cell['text'] ?? ''));
            echo '<td' . $class . '>' . $content . '</td>';
        } else {
            echo '<td>' . cdat_sum_h((string) $cell) . '</td>';
        }
    }
    echo '</tr>';
}

function cdat_sum_empty_state(string $message = 'Records not found'): void
{
    echo '<div class="alert alert-warning sum-empty-state" role="alert">' . cdat_sum_h($message) . '</div>';
}

function cdat_sum_results_open(): void
{
    echo '<div class="sum-results">';
}

function cdat_sum_results_close(): void
{
    echo '</div>';
}

/** @param array<string,mixed> $row */
function cdat_sum_subject_card(array $row, int $contactCount, string $reportLabel = 'Call Summary Report'): void
{
    $phone = trim((string) ($row['PHONE'] ?? ''));
    $nick = trim((string) ($row['NICKNAME'] ?? ''));
    $first = trim((string) ($row['FIRST_CALL'] ?? ''));
    $last = trim((string) ($row['LAST_CALL'] ?? ''));
    $address = cdat_sum_address_lines((string) ($row['ADDRESS'] ?? ''));

    echo '<section class="sum-subject-card mb-3" aria-label="Subject summary">';
    echo '<div class="sum-subject-hero">';
    echo '<div class="sum-subject-hero__identity">';
    echo '<span class="sum-subject-hero__eyebrow">' . cdat_sum_h($reportLabel) . '</span>';
    echo '<span class="sum-subject-hero__phone">' . cdat_sum_h($phone) . '</span>';
    if ($nick !== '') {
        echo '<span class="sum-subject-hero__nick">' . cdat_sum_h($nick) . '</span>';
    }
    echo '</div>';
    echo '<div class="sum-subject-stats">';
    echo '<div class="sum-stat-tile"><span class="sum-stat-tile__label">First Call</span>'
       . '<span class="sum-stat-tile__value">' . cdat_sum_h($first !== '' ? $first : '—') . '</span></div>';
    echo '<div class="sum-stat-tile"><span class="sum-stat-tile__label">Last Call</span>'
       . '<span class="sum-stat-tile__value">' . cdat_sum_h($last !== '' ? $last : '—') . '</span></div>';
    echo '<div class="sum-stat-tile sum-stat-tile--accent"><span class="sum-stat-tile__label">Contacts</span>'
       . '<span class="sum-stat-tile__value">' . (int) $contactCount . '</span></div>';
    echo '</div>';
    echo '</div>';
    if ($address !== '') {
        echo '<div class="sum-subject-address"><span class="sum-subject-address__label">Address</span>'
           . '<div class="sum-subject-address__body" title="' . cdat_sum_h((string) ($row['ADDRESS'] ?? '')) . '">'
           . $address . '</div></div>';
    }
    echo '</section>';
}

function cdat_sum_table_panel_open(string $title, int $count, string $tableId = 'contact_results_table',
                                   string $exportName = 'contact_analysis.csv'): void
{
    echo '<section class="sum-table-panel mb-3" aria-label="' . cdat_sum_h($title) . '">';
    echo '<div class="sum-table-panel__head d-flex flex-wrap align-items-center gap-2 mb-2">';
    echo '<div class="sum-table-panel__head-left d-flex flex-wrap align-items-center gap-2">';
    echo '<h3 class="sum-table-panel__title h6 mb-0">' . cdat_sum_h($title) . '</h3>';
    echo '<span class="sum-badge badge text-bg-primary">' . (int) $count . ' contacts</span>';
    echo '</div>';
    echo '<div class="sum-table-panel__actions ms-auto" id="sum-table-actions"></div>';
    echo '</div>';
    echo '<div class="table-responsive sum-table-scroll"><table id="' . cdat_sum_h($tableId) . '" class="table table-striped table-hover table-sm mb-0 sum-data-table"'
       . ' border="1" data-export-name="' . cdat_sum_h($exportName) . '">';
    echo '<thead><tr>'
       . '<th>PHONE</th><th>OTHER</th><th>IN</th><th>OUT</th><th>CALLS</th>'
       . '<th>DUR</th><th>FIRST_CALL</th><th>LAST_CALL</th><th>ADDRESS</th>'
       . '</tr></thead><tbody>';
}

function cdat_sum_table_panel_close(): void
{
    echo '</tbody></table></div></section>';
}

/** @param array<string,mixed> $row */
function cdat_sum_contact_row(array $row): void
{
    $other = (string) ($row['OTHER'] ?? '');
    $address = (string) ($row['ADDRESS'] ?? '');
    $first = (string) ($row['FIRSTCALL'] ?? $row['FIRST_CALL'] ?? '');
    $last = (string) ($row['LASTCALL'] ?? $row['LAST_CALL'] ?? '');
    $addressHtml = cdat_sum_address_lines($address);

    echo '<tr>';
    echo '<td class="sum-cell-num">' . cdat_sum_h((string) ($row['PHONE'] ?? '')) . '</td>';
    echo '<td class="sum-cell-other">' . cdat_sum_h($other) . '</td>';
    echo '<td class="sum-cell-num">' . cdat_sum_h((string) ($row['IN'] ?? '')) . '</td>';
    echo '<td class="sum-cell-num">' . cdat_sum_h((string) ($row['OUT'] ?? '')) . '</td>';
    echo '<td class="sum-cell-num sum-cell-calls">' . cdat_sum_h((string) ($row['CALLS'] ?? '')) . '</td>';
    echo '<td class="sum-cell-num">' . cdat_sum_h((string) ($row['DUR'] ?? '')) . '</td>';
    echo '<td class="sum-cell-date">' . cdat_sum_h($first) . '</td>';
    echo '<td class="sum-cell-date">' . cdat_sum_h($last) . '</td>';
    echo '<td class="sum-address-cell">' . ($addressHtml !== '' ? $addressHtml : '—') . '</td>';
    echo '</tr>';
}

/** Allow long-running CDR/summary searches (PHP default is often 30s). */
function cdat_sum_begin_heavy_search(): void
{
    set_time_limit(0);
}

/** @return array<int,array<string,mixed>> */
function cdat_sum_fetch_all($stmt): array
{
    $rows = [];
    if ($stmt === false) {
        return $rows;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

/** @return array<string,mixed>|null */
function cdat_sum_fetch_one($stmt): ?array
{
    if ($stmt === false) {
        return null;
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/**
 * @param array<string,mixed> $headerRow
 * @param array<int,array<string,mixed>> $contactRows
 */
function cdat_sum_render_results(array $headerRow, array $contactRows,
                                 string $exportName = 'contact_analysis.csv',
                                 string $reportLabel = 'Call Summary Report'): void
{
    if (empty($contactRows)) {
        cdat_sum_empty_state();
        return;
    }

    cdat_sum_results_open();
    cdat_sum_subject_card($headerRow, count($contactRows), $reportLabel);
    cdat_sum_table_panel_open('Contact Analysis', count($contactRows), 'contact_results_table', $exportName);
    foreach ($contactRows as $row) {
        cdat_sum_contact_row($row);
    }
    cdat_sum_table_panel_close();
    cdat_sum_results_close();
}

function cdat_sum_entry_card_open(string $title, string $desc, string $action,
                                  string $method = 'post', string $enctype = '',
                                  string $formId = 'form1', string $formClass = ''): void
{
    echo '<section class="sum-search-card sum-entry-card mb-3 pb-3 border-bottom" aria-label="' . cdat_sum_h($title) . '">';
    echo '<div class="sum-search-card__head">';
    echo '<div class="sum-search-card__icon" aria-hidden="true">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"'
       . ' stroke-linecap="round" stroke-linejoin="round">'
       . '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/>'
       . '<path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>';
    echo '</div><div>';
    echo '<h2 class="sum-search-card__title">' . cdat_sum_h($title) . '</h2>';
    echo '<p class="sum-search-card__desc">' . cdat_sum_h($desc) . '</p>';
    echo '</div></div>';
    $classes = 'sum-entry-form no-ajax row g-3' . ($formClass !== '' ? ' ' . $formClass : '');
    $formAction = function_exists('cdat_form_action') ? cdat_form_action($action) : $action;
    echo '<form id="' . cdat_sum_h($formId) . '" name="' . cdat_sum_h($formId) . '" method="'
       . cdat_sum_h(strtolower($method)) . '" action="' . cdat_sum_h($formAction) . '" class="'
       . cdat_sum_h($classes) . '"';
    if ($enctype !== '') {
        echo ' enctype="' . cdat_sum_h($enctype) . '"';
    }
    echo '>';
}

function cdat_sum_entry_card_close(string $submitValue = 'Submit', string $submitName = ''): void
{
    $nameAttr = $submitName !== '' ? ' name="' . cdat_sum_h($submitName) . '"' : '';
    echo '<div class="sum-entry-form__actions col-12">';
    echo '<input type="submit"' . $nameAttr . ' class="sum-search-form__submit btn btn-primary" value="'
       . cdat_sum_h($submitValue) . '" />';
    echo '</div></form></section>';
    echo '<div id="global-ajax-results" class="sum-ajax-results" aria-live="polite"></div>';
}

function cdat_sum_status_message(string $message, bool $success = true): void
{
    $class = $success ? 'alert alert-success sum-status sum-status--success' : 'alert alert-danger sum-status sum-status--error';
    echo '<div class="' . $class . '" role="status">' . cdat_sum_h($message) . '</div>';
}

function cdat_sum_hub_card(string $title, string $desc = '', string $image = '',
                           string $notice = ''): void
{
    echo '<section class="sum-search-card sum-hub-card mb-3 pb-3 border-bottom" aria-label="' . cdat_sum_h($title) . '">';
    echo '<div class="sum-search-card__head">';
    echo '<div class="sum-search-card__icon" aria-hidden="true">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"'
       . ' stroke-linecap="round" stroke-linejoin="round">'
       . '<path d="M3 12h4l2-7 4 14 2-7h6"/></svg>';
    echo '</div><div>';
    echo '<h2 class="sum-search-card__title">' . cdat_sum_h($title) . '</h2>';
    if ($desc !== '') {
        echo '<p class="sum-search-card__desc">' . cdat_sum_h($desc) . '</p>';
    }
    echo '</div></div>';
    if ($notice !== '') {
        echo '<div class="sum-notice-bar" role="note">' . $notice . '</div>';
    }
    if ($image !== '') {
        echo '<div class="sum-hub-card__media"><img src="' . cdat_sum_h($image)
           . '" alt="" /></div>';
    }
    echo '</section>';
}

/** Normalize PostgreSQL BYTEA / LOB values (may be a stream resource) to string. */
function cdat_pg_binary_to_string($value): ?string
{
    if ($value === null) {
        return null;
    }
    if (is_resource($value)) {
        $contents = stream_get_contents($value);
        return $contents === false ? null : $contents;
    }
    if (!is_string($value)) {
        return (string) $value;
    }
    return $value;
}

function cdat_default_suspect_image_local($conn): ?string
{
    static $image = false;
    if ($image !== false) {
        return $image;
    }

    $st = $conn->query("SELECT image FROM suspect_image_table WHERE irkey = '113769' LIMIT 1");
    if ($st && ($row = $st->fetch(PDO::FETCH_ASSOC))) {
        $image = cdat_pg_binary_to_string($row['IMAGE'] ?? null);
    } else {
        $image = null;
    }

    return $image;
}

/**
 * Build an img src value for base64 or binary image columns.
 * Returns a data URI when image data exists, otherwise a static placeholder.
 */
function cdat_base64_image_src($data, string $fallback = 'IMAGES/emp.png'): string
{
    $data = cdat_pg_binary_to_string($data);
    if ($data === null || $data === '') {
        return $fallback;
    }

    // Raw binary image bytes from PostgreSQL BYTEA
    if (isset($data[0]) && $data[0] === "\xFF" && ($data[1] ?? '') === "\xD8") {
        return 'data:image/jpeg;base64,' . base64_encode($data);
    }
    if (str_starts_with($data, "\x89PNG")) {
        return 'data:image/png;base64,' . base64_encode($data);
    }
    if (str_starts_with($data, 'GIF87a') || str_starts_with($data, 'GIF89a')) {
        return 'data:image/gif;base64,' . base64_encode($data);
    }

    $data = trim($data);
    if ($data === '') {
        return $fallback;
    }

    if (stripos($data, 'data:image') === 0) {
        $fixed = preg_replace('/^data:image;\s*base64,/i', 'data:image/jpeg;base64,', $data, 1);
        return $fixed !== '' ? $fixed : $fallback;
    }

    if (preg_match('/^data:image\/[^;]+;base64,(.+)$/is', $data, $matches)) {
        $data = $matches[1];
    }

    $data = preg_replace('/\s+/', '', $data);
    if ($data === '') {
        return $fallback;
    }

    $mime = 'image/jpeg';
    if (str_starts_with($data, 'iVBORw0KGgo')) {
        $mime = 'image/png';
    } elseif (str_starts_with($data, 'R0lGOD')) {
        $mime = 'image/gif';
    }

    return 'data:' . $mime . ';base64,' . $data;
}

function cdat_sum_img_html($src, int $width = 120, int $height = 120): string
{
    $url = cdat_base64_image_src($src);
    return '<img height="' . $height . '" width="' . $width . '" src="' . cdat_sum_h($url) . '" alt="">';
}
