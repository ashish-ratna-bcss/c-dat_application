<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/sum_ui.php';

$name = trim((string) ($_POST['NAME'] ?? ''));
$father = trim((string) ($_POST['FATHER_NAME'] ?? ''));
$phone = trim((string) ($_POST['PHONE'] ?? ''));
$aadhaar = trim((string) ($_POST['AADHAAR_NO'] ?? ''));
$voter = trim((string) ($_POST['VOTER_ID'] ?? ''));

$fieldsHtml = cdat_sum_field_text('NAME', 'Accused Name', $name, 'NAME', 'Accused name', false)
            . cdat_sum_field_text('FATHER_NAME', 'Father Name', $father, 'FATHER_NAME', 'Father name', false)
            . cdat_sum_field_text('PHONE', 'Phone', $phone, 'PHONE', 'Phone', false, 'tel')
            . cdat_sum_field_text('AADHAAR_NO', 'Aadhaar Number', $aadhaar, 'AADHAAR_NO', 'Aadhaar number', false)
            . cdat_sum_field_text('VOTER_ID', 'Voter ID', $voter, 'VOTER_ID', 'Voter ID', false);

layout_begin('JRMS Search Uniqueness');
cdat_sum_page_open();
cdat_sum_search_card(
    'Jail Release Search',
    'Search by name, father name, phone, Aadhaar, or voter ID.',
    'jrms_search_uniqueness.php',
    $fieldsHtml,
    'BTN_SUM',
    'Submit'
);
cdat_sum_page_close();
layout_end();
