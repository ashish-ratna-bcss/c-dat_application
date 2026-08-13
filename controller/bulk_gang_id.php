<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Bulk Gang Id');
cdat_sum_page_open();
cdat_sum_search_card(
    'Bulk Addresses',
    'Enter comma-separated IR keys to search gang IDs in bulk.',
    'bulk_gang_id_search.php',
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
