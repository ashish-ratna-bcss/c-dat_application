<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Between Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Summary Between Dates',
    'Search call summary for a mobile number within a date range.',
    'sum_btwn_dates.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
    . cdat_sum_field_date('TO_DT', 'Date To', 'datepickerID1')
);
cdat_sum_page_close();
layout_end();
