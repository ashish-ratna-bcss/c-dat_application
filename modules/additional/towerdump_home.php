<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Towerdump Home');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Tower Dump Reports',
    'Tower dump reports. Open Suspect Search, Other State Numbers, Inter Tower Calls, or Previous Offenders from the sidebar.',
    CDAT_ASSETS . '/images/tower2.jpeg',
    'Please mail raw data to <strong>crimelab@hyd.tspolice.gov.in</strong> to view reports.'
);
cdat_sum_page_close();
layout_end();
