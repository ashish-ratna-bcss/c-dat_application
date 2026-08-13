<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$criteriaOptions = [
    '' => 'Select search criteria',
    'EMPLOYEE_ID' => 'EMPLOYEE ID',
    'GENERAL_NO' => 'GENERAL NO',
    'NAME' => 'NAME',
];
$rankOptions = [
    '' => 'Select rank',
    'INSPECTOR' => 'INSPECTOR',
    'SI' => 'SI',
    'ASI' => 'ASI',
    'HC' => 'HC',
    'PC' => 'PC',
    'HG' => 'HG',
];

layout_begin('Training Module2');
cdat_sum_page_open();
cdat_sum_search_card(
    'Employee Search',
    'Search training / PWDMS employee records.',
    'training_module1.php',
    cdat_sum_searchable_select('EMPLOYEE_SEARCH', 'Search criteria', $criteriaOptions, '', 'Select search criteria', true)
        . cdat_sum_field_text('EMPLOYEE_SEARCH_NO', 'Emp Search', '', 'CAF', 'Emp Search')
        . cdat_sum_searchable_select('EMPLOYEE_SEARCH_RANK', 'Rank', $rankOptions, '', 'Select rank', false),
    'BTN_CDAT',
    'Submit'
);
cdat_sum_hub_card(
    'Trainings',
    'Training database snapshot.',
    '../assets/images/training_db1.gif'
);
cdat_sum_page_close();
layout_end();
