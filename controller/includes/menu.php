<?php
/**
 * The navigation, in one place.
 *
 * This is the only file to edit when menus change -- every page renders its
 * sidebar from here via layout.php, instead of carrying its own copy of the
 * markup (which is why the old menus had drifted into 15 different versions).
 *
 * Shape:
 *   ['label' => 'Text', 'url' => 'page.php']          a link
 *   ['label' => 'Text', 'children' => [ ... ]]        a group
 *
 * A group is NOT a link. It renders as a button that expands its children in
 * place, so clicking a parent never navigates away. The old Spry menu pointed
 * its parents at home.php, which is why opening "Call Details" threw you back
 * to the dashboard.
 *
 * Urls are relative to controller/.
 */
return [
    ['label' => 'Dashboard', 'url' => 'home.php', 'icon' => 'home'],
    ['label' => 'Data Upload', 'url' => 'admin_upload.php', 'icon' => 'upload'],

    ['label' => 'Summary', 'icon' => 'chart', 'children' => [
        ['label' => 'Summary Total',            'url' => 'sum_home.php'],
        ['label' => 'Between Dates',            'url' => 'sum_between_dates.php'],
        ['label' => 'ISD Contacts',             'url' => 'sum_isd_cnts.php'],
        ['label' => 'New Contacts',             'url' => 'sum_new_nos.php'],
        ['label' => 'Within a State',           'url' => 'sum_in_state.php'],
        ['label' => 'Other than a State',       'url' => 'sum_out_state.php'],
    ]],

    ['label' => 'Call Details', 'icon' => 'phone', 'children' => [
        ['label' => 'Movements',                'url' => 'movements.php'],
        ['label' => 'Movements Between Two Nos','url' => 'movements_between_two_numbers.php'],
        ['label' => 'Movements Comparison',     'url' => 'movements_between_two_numbers_comparision.php'],
        ['label' => 'Calls Between Dates',      'url' => 'calls_btwn_dates.php'],
        ['label' => 'Calls Between Two Nos',    'url' => 'calls_bt_nos.php'],
    ]],

    ['label' => 'CDAT', 'icon' => 'grid', 'children' => [
        ['label' => 'Cdat Contacts',            'url' => 'cdatcnts.php'],
        ['label' => 'Bulk Cdat Contacts',       'url' => 'bulk_cdat_contacts.php'],
        ['label' => 'Others Cdat',              'url' => 'otherscdat.php'],
        ['label' => 'Common Contacts',          'url' => 'common_cnts.php'],
    ]],

    ['label' => 'IMEI', 'icon' => 'device', 'children' => [
        ['label' => 'Phones used in IMEI',      'url' => 'imeisearch.php'],
        ['label' => 'IMEIs used in Phone',      'url' => 'imeisinphone.php'],
    ]],

    ['label' => 'Address', 'icon' => 'pin', 'children' => [
        ['label' => 'Single Address',           'url' => 'address.php'],
        ['label' => 'Bulk Addresses',           'url' => 'bulkaddress.php'],
    ]],

    ['label' => 'Location', 'icon' => 'map', 'children' => [
        ['label' => 'Cell ID Search',           'url' => 'cellid_search.php'],
        ['label' => 'Day / Night Location',     'url' => '../view/day%26nightloc.html'],
        ['label' => 'Day / Night Between Dates','url' => '../view/day%26nightloc_btwn_dates.html'],
        ['label' => 'Nearest Cell IDs',         'url' => 'nearest_cellids.php'],
    ]],

    ['label' => 'Interrogation Reports', 'icon' => 'file', 'children' => [
        ['label' => 'IR Home',                  'url' => 'home_ir.php'],
        ['label' => 'IR Search By Name',        'url' => 'ir_search.php'],
        ['label' => 'IR Module',                'url' => 'ir_module.php'],
        ['label' => 'IR Forms',                 'url' => 'bulk_irsearch_irkey.php'],
    ]],

    ['label' => 'Records', 'icon' => 'folder', 'children' => [
        ['label' => 'JRMS',                     'url' => 'jrms_main_page1.php'],
        ['label' => 'PDACT',                    'url' => 'pdact_main_page_search.php'],
        ['label' => 'Rowdy Sheeter By PS',      'url' => 'rowdysheeter_ps_wise_search.php'],
        ['label' => 'Offender Search By MO',    'url' => 'offender_search_by_mo.php'],
        ['label' => 'Habitual Offenders',       'url' => 'habitual.php'],
    ]],

    ['label' => 'Vehicle', 'icon' => 'car', 'children' => [
        ['label' => 'Vehicle Search',           'url' => 'vehicle_search.php'],
        ['label' => 'Search Criteria',          'url' => 'vehicle_search_criteria.php'],
    ]],

    ['label' => 'Administration', 'icon' => 'cog', 'children' => [
        ['label' => 'User Activity',            'url' => 'admin_activity_log.php'],
        ['label' => 'SQL Query Console',        'url' => 'admin_sql_console.php'],
        ['label' => 'Create User',              'url' => 'admin_create_user.php'],
        ['label' => 'Upload History',           'url' => 'admin_upload_history.php'],
        ['label' => 'Tower Dump Reports',       'url' => 'tower_home.php'],
        ['label' => 'Trainings',                'url' => 'training_module1.php'],
    ]],
];
