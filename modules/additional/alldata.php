<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Alldata');
cdat_sum_page_open();
cdat_sum_search_card(
    'All Data Search',
    'Search SDR, RTA, licence, civil supply, suspect, and passport data by phone.',
    'alldata_search.php',
    cdat_sum_field_text('PHONE', 'Phone', '', 'PHONE', 'PHONE', true, 'tel'),
    '',
    'Submit'
);
cdat_sum_page_close();
layout_end();
