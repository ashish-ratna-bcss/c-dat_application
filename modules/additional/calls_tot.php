<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Call Details Total');
cdat_sum_page_open();
cdat_sum_search_card(
    'Call Details of Mobile Number',
    'Search full call details for a mobile number. Optional operator and state filters.',
    'calldetails.php',
    cdat_sum_field_phone()
        . cdat_sum_field_operator()
        . cdat_sum_field_call_state(),
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
