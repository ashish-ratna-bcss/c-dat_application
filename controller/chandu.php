<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

layout_begin('Chandu');
cdat_sum_page_open();
cdat_sum_hub_card(
    'Chandu',
    'Hello World! I AM ENTERING INTO THE SOFTWARE WORLD'
);
cdat_sum_page_close();
layout_end();
