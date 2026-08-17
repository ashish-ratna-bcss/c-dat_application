<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('JRMS Home');
cdat_sum_page_open();
cdat_sum_hub_card(
    'JRMS Offender Uniquing',
    'Open JRMS search, new entry, and unique-key update from the JRMS menu.',
    CDAT_ASSETS . '/images/analysis1.jpg',
    'If you have suggestions or changes, please share them with Analysis Wing.'
);
cdat_sum_page_close();
layout_end();
