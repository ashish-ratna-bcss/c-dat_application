<?php
require_once __DIR__ . '/../common/bootstrap.php';
require_once CDAT_COMMON . '/includes/layout.php';
require_once CDAT_COMMON . '/includes/sum_ui.php';

layout_begin('Bulk Irkey');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk Addresses',
    'Enter comma-separated IR keys to search in bulk.',
    'bulk_irsearch_irkey1.php',
    cdat_sum_field_textarea(
        'IRKEY',
        'Bulk IRKEY Search',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_page_close();
layout_end();
