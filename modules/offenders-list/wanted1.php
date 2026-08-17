<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Wanted');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Wanted',
    'Wanted persons list — List 1.',
    CDAT_ASSETS . '/images/analysis1.jpg',
    'Please mail raw data to <strong>crimelab@hyd.tspolice.gov.in</strong> to view reports.'
);
cdat_sum_page_close();
layout_end();
