<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Bulk Addresses');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk Addresses',
    'Enter comma-separated mobile numbers to look up addresses.',
    'bulk_address.php',
    cdat_sum_field_textarea(
        'PHONE_NO',
        'Addresses of Mobile Numbers',
        '',
        'Enter Mobile Numbers Seperated by comma without space Ex: 9989xxxxxx,7899xxxxxx,8977xxxxxx'
    ),
    'BTN_CDAT',
    'Search'
);
cdat_sum_page_close();
layout_end();
