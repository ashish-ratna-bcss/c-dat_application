<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('New Contacts');
cdat_sum_page_open();
cdat_sum_search_card(
    'New Contacts Summary',
    'Find new contacts for a mobile number from a given date.',
    'sum_new_no.php',
    cdat_sum_field_phone()
    . cdat_sum_field_date('FROM_DT', 'New Contacts From', 'datepickerID')
);
cdat_sum_page_close();
layout_end();
