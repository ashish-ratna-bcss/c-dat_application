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
 *
 * 'role' hides an entry from anyone without it, and a group with nothing left
 * in it disappears too:
 *
 *   'role' => 'admin'      admin only
 *   'role' => 'uploader'   admin and poweruser
 *   (absent)               any signed-in user
 *
 * These mirror the guard the page itself enforces -- audit_require_admin() and
 * audit_require_uploader(). Hiding a link is tidiness, not a control: the page
 * is what refuses the request. scripts/check_menu_roles.php compares the two
 * and fails if they ever drift apart, so add the guard to the page first and
 * the 'role' here second.
 */
return [
    ['label' => 'Dashboard', 'url' => 'home.php', 'icon' => 'home'],
    ['label' => 'Data Upload', 'url' => 'admin_upload.php', 'icon' => 'upload',
     'role' => 'uploader'],

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
        // ['label' => 'Common Contacts',          'url' => 'common_cnts.php'],
    ]],

    ['label' => 'IMEI', 'icon' => 'device', 'children' => [
        ['label' => 'Phones used in IMEI',      'url' => 'imeisearch.php'],
        ['label' => 'IMEIs used in Phone',      'url' => 'imeisinphone.php'],
    ]],

    ['label' => 'Address', 'icon' => 'pin', 'children' => [
        ['label' => 'Single Address',           'url' => 'address.php'],
        ['label' => 'Bulk Addresses',           'url' => 'bulkaddress.php'],
    ]],

    ['label' => 'Day / Night Loc', 'icon' => 'map', 'children' => [
        // ['label' => 'Cell ID Search',           'url' => 'cellid_search.php'],
        ['label' => 'Top 10 Day Night Loc',     'url' => '../controller/day%26nightloc.html'],
        ['label' => 'Top 10 Day Night Loc Between Dates','url' => '../controller/day%26nightloc_btwn_dates.html'],
        // ['label' => 'Nearest Cell IDs',         'url' => 'nearest_cellids.php'],
    ]],

    ['label' => 'Others', 'icon' => 'map', 'children' => [
        ['label' => 'Cell ID Search',           'url' => 'cellid_search.php'],
        ['label' => 'Vehicle Search',           'url' => 'vehicle_search.php'],
        ['label' => 'Common Cnts',           'url' => 'common_cnts.php'],
        ['label' => 'Offender Search by Mobile',  'url' => 'offender_search_by_mo.php'],
        ['label' => 'User Activity',            'url' => 'admin_activity_log.php',  'role' => 'admin'],
        ['label' => 'SQL Query Console',        'url' => 'admin_sql_console.php',   'role' => 'admin'],
         ['label' => 'Vehicle Search Criteria',          'url' => 'vehicle_search_criteria.php'],
        // ['label' => 'Top 10 Day Night Loc',     'url' => '../controller/day%26nightloc.html'],
        // ['label' => 'Top 10 Day Night Loc Between Dates','url' => '../controller/day%26nightloc_btwn_dates.html'],
        // ['label' => 'Nearest Cell IDs',         'url' => 'nearest_cellids.php'],
    ]],

    ['label' => 'Interrogation Reports', 'icon' => 'file', 'children' => [
        // ['label' => 'IR Home',                  'url' => '../controller/home_ir.php'],
        ['label' => 'IR Search By Name',        'url' => '../controller/ir_search.php'],
        // ['label' => 'IR Module',                'url' => '../controller/ir_module.php'],
        // ['label' => 'IR Forms',                 'url' => '../controller/bulk_irsearch_irkey.php'],
        // The entry screens the old IR home carried in its own menu bar. Its
        // "Crime Details" submenu is flattened here -- the sidebar nests one
        // level, and these two are the only things that were under it.
        ['label' => 'IR Report',                'url' => 'irreport.php'],
        ['label' => 'Family History',           'url' => 'family_history.php'],
        ['label' => 'Offence Details',          'url' => 'offence_details.php'],
        ['label' => 'Previous Offence Details', 'url' => 'previous_offence_details.php'],
        ['label' => 'Local Contacts',           'url' => 'local_contacts.php'],
        ['label' => 'Gangs / Associates',       'url' => 'relation_with_other_associates_and_gangs.php'],
        ['label' => 'Property Details',         'url' => 'disposal_of_property.php'],
        ['label' => 'Brief Facts',              'url' => 'brief_facts.php'],
        ['label' => 'Image',                    'url' => 'image_list.php'],
        ['label' => 'Mulakath Details',         'url' => 'mulakath_entry.php'],
        ['label' => 'Retrieve',                 'url' => 'retrieve.php'],
    ]],



    ['label' => 'Administration', 'icon' => 'cog', 'children' => [
        ['label' => 'User Activity',            'url' => 'admin_activity_log.php',  'role' => 'admin'],
        ['label' => 'SQL Query Console',        'url' => 'admin_sql_console.php',   'role' => 'admin'],
        ['label' => 'Create User',              'url' => 'admin_create_user.php',   'role' => 'admin'],
        ['label' => 'Upload History',           'url' => 'admin_upload_history.php','role' => 'uploader'],
    
    ]],
];
