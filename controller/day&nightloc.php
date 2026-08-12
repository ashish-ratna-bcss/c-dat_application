<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Day / Night Location');
cdat_sum_page_open();
cdat_sum_search_card(
    'Top 10 Day and Night Locations',
    'Find the top day and night cell tower locations for a mobile number.',
    'd%26n_loc.php',
    cdat_sum_field_phone()
);
cdat_sum_page_close();
layout_end();
