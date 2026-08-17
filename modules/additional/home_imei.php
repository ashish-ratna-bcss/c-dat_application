<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Lost Report IMEI');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Lost Report IMEI Analysis',
    'Use the IMEI menu for request status, traced details, summary, movements, max spent location, and day & night location.',
    CDAT_ASSETS . '/images/analysis1.jpg',
    'If you have suggestions or changes, please share them with Analysis Wing.'
);
cdat_sum_page_close();
layout_end();
