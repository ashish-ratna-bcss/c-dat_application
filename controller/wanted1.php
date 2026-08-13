<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Wanted');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Wanted',
    'Wanted persons list — List 1.',
    '../assets/images/analysis1.jpg',
    'Please mail raw data to <strong>crimelab@hyd.tspolice.gov.in</strong> to view reports.'
);
cdat_sum_page_close();
layout_end();
