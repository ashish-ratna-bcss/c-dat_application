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

function cdat_sum_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
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
    return '<div class="sum-search-form__field">'
         . '<label for="' . $id . '">Mobile No</label>'
         . '<input type="text" name="PHONE_NO" id="' . $id . '" placeholder="Enter mobile number"'
         . ' required="required" minlength="7" maxlength="15" pattern="^\+?[0-9]+$"'
         . ' title="Please enter a valid phone number (numbers and optional + only)"'
         . ' oninput="this.value = this.value.replace(/[^0-9+]/g, \'\')" autocomplete="off"'
         . ' inputmode="tel" value="' . $val . '"/>'
         . '</div>';
}

function cdat_sum_field_state(string $selected = '', bool $required = true): string
{
    $options = [
        '' => 'Select state',
        'ANDAMAN AND NICOBAR ISLANDS' => 'ANDAMAN AND NICOBAR ISLANDS',
        'ANDHRA PRADESH' => 'ANDHRA PRADESH',
        'ASSAM' => 'ASSAM',
        'BIHAR' => 'BIHAR',
        'CHENNAI' => 'CHENNAI',
        'DELHI' => 'DELHI',
        'GUJARAT' => 'GUJARAT',
        'HARYANA' => 'HARYANA',
        'HIMACHAL PRADESH' => 'HIMACHAL PRADESH',
        'JAMMU_KASHMIR' => 'JAMMU_KASHMIR',
        'KARNATAKA' => 'KARNATAKA',
        'KERALA' => 'KERALA',
        'KOLKATA' => 'KOLKATA',
        'MADHYA PRADESH' => 'MADHYA PRADESH',
        'MAHARASHTRA' => 'MAHARASHTRA',
        'MUMBAI' => 'MUMBAI',
        'NORTH_EAST' => 'NORTH_EAST',
        'ORISSA' => 'ORISSA',
        'PUNJAB' => 'PUNJAB',
        'RAJASTHAN' => 'RAJASTHAN',
        'TAMILNADU' => 'TAMILNADU',
        'UP_EAST' => 'UP_EAS',
        'UP_WEST' => 'UP_WEST',
        'WEST BENGAL' => 'WEST BENGAL',
    ];
    return cdat_sum_searchable_select(
        'STATE',
        'State',
        $options,
        $selected,
        'Select state',
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
    $html = '<div class="sum-search-form__field' . $extra . '">'
          . '<label for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
          . '<select name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"'
          . $req . ' class="sum-select" data-searchable-select="1"'
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

function cdat_sum_field_date(string $name, string $label, string $id = '', string $value = '',
                             bool $required = true): string
{
    $idAttr = $id !== '' ? $id : $name;
    $req = $required ? ' required="required"' : '';
    return '<div class="sum-search-form__field sum-search-form__field--date">'
         . '<label for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
         . '<input type="text" name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"'
         . ' class="sum-date-input" data-date-picker="1" placeholder="yyyy-mm-dd"'
         . ' pattern="^\d{4}-\d{2}-\d{2}$" inputmode="numeric" autocomplete="off"'
         . ' title="Date must be in yyyy-mm-dd format"' . $req
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
    echo '<p class="sum-back-link"><a href="' . cdat_sum_h($url) . '">&larr; ' . cdat_sum_h($label) . '</a></p>';
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
    $formClass = 'sum-search-form' . ($methodAttr === 'get' ? ' no-ajax' : '');
    echo '<section class="sum-search-card" aria-label="Search">';
    echo '<div class="sum-search-card__head">';
    echo '<div class="sum-search-card__icon" aria-hidden="true">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"'
       . ' stroke-linecap="round" stroke-linejoin="round">'
       . '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg>';
    echo '</div><div>';
    echo '<h2 class="sum-search-card__title">' . cdat_sum_h($title) . '</h2>';
    echo '<p class="sum-search-card__desc">' . cdat_sum_h($desc) . '</p>';
    echo '</div></div>';
    echo '<form id="form1" name="form1" method="' . $methodAttr . '" action="' . cdat_sum_h($action)
       . '" class="' . $formClass . '">';
    echo $fieldsHtml;
    echo '<input type="submit" name="' . cdat_sum_h($submitName) . '" id="' . cdat_sum_h($submitName) . '"'
       . ' class="sum-search-form__submit" value="' . cdat_sum_h($submitValue) . '" />';
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
    return '<div class="sum-search-form__field">'
         . '<label for="' . cdat_sum_h($idAttr) . '">' . cdat_sum_h($label) . '</label>'
         . '<input type="text" name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($idAttr) . '"' . $ph . $req
         . ' autocomplete="off" value="' . cdat_sum_h($value) . '"' . $mode . '/>'
         . '</div>';
}

function cdat_sum_field_other_phone(string $value = ''): string
{
    return cdat_sum_field_text('OTHER_NO', 'Other No', $value, 'OTHER_NO', 'Enter other number', true, 'tel');
}

function cdat_sum_field_imei(string $value = ''): string
{
    return '<div class="sum-search-form__field">'
         . '<label for="IMEI">IMEI Number</label>'
         . '<input type="text" name="IMEI_NO" id="IMEI" placeholder="Enter IMEI number"'
         . ' required="required" minlength="15" maxlength="15" pattern="^[0-9]{15}$"'
         . ' title="IMEI must be exactly 15 digits"'
         . ' oninput="this.value = this.value.replace(/[^0-9]/g, \'\')" autocomplete="off"'
         . ' inputmode="numeric" value="' . cdat_sum_h($value) . '"/>'
         . '</div>';
}

function cdat_sum_field_textarea(string $name, string $label, string $value = '', string $placeholder = ''): string
{
    return '<div class="sum-search-form__field sum-search-form__field--textarea">'
         . '<label for="' . cdat_sum_h($name) . '">' . cdat_sum_h($label) . '</label>'
         . '<textarea name="' . cdat_sum_h($name) . '" id="' . cdat_sum_h($name) . '" rows="3"'
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
    return [
        '' => 'Select state',
        'ANDAMAN AND NICOBAR ISLANDS' => 'ANDAMAN AND NICOBAR ISLANDS',
        'ANDHRA PRADESH' => 'ANDHRA PRADESH',
        'ARUNACHAL PRADESH' => 'ARUNACHAL PRADESH',
        'ASSAM' => 'ASSAM',
        'BIHAR' => 'BIHAR',
        'CHENNAI' => 'CHENNAI',
        'CHHATTISGARH' => 'CHHATTISGARH',
        'DELHI' => 'DELHI',
        'GUJARAT' => 'GUJARAT',
        'HARYANA' => 'HARYANA',
        'HIMACHAL PRADESH' => 'HIMACHAL PRADESH',
        'JAMMU_KASHMIR' => 'JAMMU_KASHMIR',
        'JHARKHAND' => 'JHARKHAND',
        'KARNATAKA' => 'KARNATAKA',
        'KERALA' => 'KERALA',
        'KOLKATA' => 'KOLKATA',
        'MADHYA PRADESH' => 'MADHYA PRADESH',
        'MAHARASHTRA' => 'MAHARASHTRA',
        'MANIPUR' => 'MANIPUR',
        'MEGHALAYA' => 'MEGHALAYA',
        'MIZORAM' => 'MIZORAM',
        'MUMBAI' => 'MUMBAI',
        'NAGALAND' => 'NAGALAND',
        'NORTH_EAST' => 'NORTH_EAST',
        'ORISSA' => 'ORISSA',
        'PUNJAB' => 'PUNJAB',
        'RAJASTHAN' => 'RAJASTHAN',
        'TAMILNADU' => 'TAMILNADU',
        'TRIPURA' => 'TRIPURA',
        'UP_EAST' => 'UP_EAST',
        'UP_WEST' => 'UP_WEST',
        'WEST BENGAL' => 'WEST BENGAL',
    ];
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
    echo '<section class="sum-table-panel" aria-label="' . cdat_sum_h($panelTitle) . '">';
    echo '<div class="sum-table-panel__head">';
    echo '<div class="sum-table-panel__head-left">';
    echo '<h3 class="sum-table-panel__title">' . cdat_sum_h($panelTitle) . '</h3>';
    if ($count > 0) {
        echo '<span class="sum-badge">' . (int) $count . ' records</span>';
    }
    echo '</div>';
    echo '<div class="sum-table-panel__actions" id="sum-table-actions"></div>';
    echo '</div>';
    echo '<div class="sum-table-scroll"><table id="' . cdat_sum_h($tableId) . '" class="sum-data-table"'
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
    echo '<div class="sum-empty-state" role="alert">' . cdat_sum_h($message) . '</div>';
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

    echo '<section class="sum-subject-card" aria-label="Subject summary">';
    echo '<div class="sum-subject-hero">';
    echo '<div class="sum-subject-hero__identity">';
    echo '<span class="sum-subject-hero__eyebrow">' . cdat_sum_h($reportLabel) . '</span>';
    echo '<span class="sum-subject-hero__phone">' . cdat_sum_h($phone) . '</span>';
    if ($nick !== '') {
        echo '<span class="sum-subject-hero__nick">' . cdat_sum_h($nick) . '</span>';
    }
    echo '</div><div class="sum-subject-stats">';
    echo '<div class="sum-stat-tile"><span class="sum-stat-tile__label">First Call</span>'
       . '<span class="sum-stat-tile__value">' . cdat_sum_h($first !== '' ? $first : '—') . '</span></div>';
    echo '<div class="sum-stat-tile"><span class="sum-stat-tile__label">Last Call</span>'
       . '<span class="sum-stat-tile__value">' . cdat_sum_h($last !== '' ? $last : '—') . '</span></div>';
    echo '<div class="sum-stat-tile sum-stat-tile--accent"><span class="sum-stat-tile__label">Contacts</span>'
       . '<span class="sum-stat-tile__value">' . (int) $contactCount . '</span></div>';
    echo '</div></div>';
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
    echo '<section class="sum-table-panel" aria-label="' . cdat_sum_h($title) . '">';
    echo '<div class="sum-table-panel__head">';
    echo '<div class="sum-table-panel__head-left">';
    echo '<h3 class="sum-table-panel__title">' . cdat_sum_h($title) . '</h3>';
    echo '<span class="sum-badge">' . (int) $count . ' contacts</span>';
    echo '</div>';
    echo '<div class="sum-table-panel__actions" id="sum-table-actions"></div>';
    echo '</div>';
    echo '<div class="sum-table-scroll"><table id="' . cdat_sum_h($tableId) . '" class="sum-data-table"'
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

/** @return array<int,array<string,mixed>> */
function cdat_sum_fetch_all($stmt): array
{
    $rows = [];
    if ($stmt === false) {
        return $rows;
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
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
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
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
    echo '<section class="sum-search-card sum-entry-card" aria-label="' . cdat_sum_h($title) . '">';
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
    $classes = 'sum-entry-form no-ajax' . ($formClass !== '' ? ' ' . $formClass : '');
    echo '<form id="' . cdat_sum_h($formId) . '" name="' . cdat_sum_h($formId) . '" method="'
       . cdat_sum_h(strtolower($method)) . '" action="' . cdat_sum_h($action) . '" class="'
       . cdat_sum_h($classes) . '"';
    if ($enctype !== '') {
        echo ' enctype="' . cdat_sum_h($enctype) . '"';
    }
    echo '>';
}

function cdat_sum_entry_card_close(string $submitValue = 'Submit', string $submitName = ''): void
{
    $nameAttr = $submitName !== '' ? ' name="' . cdat_sum_h($submitName) . '"' : '';
    echo '<div class="sum-entry-form__actions">';
    echo '<input type="submit"' . $nameAttr . ' class="sum-search-form__submit" value="'
       . cdat_sum_h($submitValue) . '" />';
    echo '</div></form></section>';
    echo '<div id="global-ajax-results" class="sum-ajax-results" aria-live="polite"></div>';
}

function cdat_sum_status_message(string $message, bool $success = true): void
{
    $class = $success ? 'sum-status--success' : 'sum-status--error';
    echo '<div class="sum-status ' . $class . '" role="status">' . cdat_sum_h($message) . '</div>';
}
