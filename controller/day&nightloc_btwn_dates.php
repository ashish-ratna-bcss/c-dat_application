<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Day / Night Between Dates');
cdat_sum_page_open();
cdat_sum_search_card(
    'Top 10 Day & Night Locations Between Dates',
    'Find top day and night locations for a mobile number within a date range.',
    'd%26n_bt_dts.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date('FROM_DT', 'Date From', 'datepickerID')
    . cdat_sum_field_date('TO_DT', 'Date To', 'datepickerID1')
);
cdat_sum_page_close();
layout_end();
