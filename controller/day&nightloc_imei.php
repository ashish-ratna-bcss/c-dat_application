<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Day&nightloc IMEI');
cdat_sum_page_open();
cdat_sum_search_card(
    'Top 10 Day and Night Locations',
    'Find the top day and night cell tower locations for a lost-report mobile number.',
    'd%26n_loc_imei.php',
    cdat_sum_field_phone('', 'calls')
);
cdat_sum_page_close();
layout_end();
