<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('IR Search Test');
cdat_sum_page_open();
cdat_sum_search_card(
    'Offender IR Search By Name',
    'Search IR records by offender name.',
    'ir_search.php',
    cdat_sum_field_text('NAME', 'Name of the Offender', '', 'NAME', 'Enter NAME'),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
