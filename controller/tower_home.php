<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Tower Dump Reports');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Tower Data Analysis',
    'Tower dump reports. Open Suspect Search, Other State Numbers, Inter Tower Calls, or Previous Offenders from the sidebar.',
    '../assets/images/tower2.jpeg',
    'Please mail raw data to <strong>crimelab@hyd.tspolice.gov.in</strong> to view reports.'
);
cdat_sum_page_close();
layout_end();
