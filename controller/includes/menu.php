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
        ['label' => 'Call Details Total',       'url' => 'calls_tot.php'],
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
        ['label' => 'Lost Report IMEI Home',    'url' => 'home_imei.php'],
        ['label' => 'Phones used in IMEI',      'url' => 'imeisearch.php'],
        ['label' => 'IMEIs used in Phone',      'url' => 'imeisinphone.php'],
        ['label' => 'IMEI Request Status',      'url' => 'imei_request_status.php'],
        ['label' => 'IMEI Request Traced Details','url' => 'imei_request_traced_details.php'],
        ['label' => 'Traced Phone Summary',     'url' => 'imei_request_sum.php'],
        ['label' => 'Traced Phone Movements',   'url' => 'imei_request_movements.php'],
        ['label' => 'Traced Phone Max Spent Loc','url' => 'maxspentlocation_imei.php'],
        ['label' => 'Traced Phone Day & Night Loc','url' => 'day%26nightloc_imei.php'],
    ]],

    ['label' => 'Address', 'icon' => 'pin', 'children' => [
        ['label' => 'Single Address',           'url' => 'address.php'],
        ['label' => 'Bulk Addresses',           'url' => 'bulkaddress.php'],
    ]],

    ['label' => 'Day / Night Loc', 'icon' => 'map', 'children' => [
        // ['label' => 'Cell ID Search',           'url' => 'cellid_search.php'],
        ['label' => 'Top 10 Day Night Loc',     'url' => 'day%26nightloc.php'],
        ['label' => 'Top 10 Day Night Loc Between Dates','url' => 'day%26nightloc_btwn_dates.php'],
        // ['label' => 'Nearest Cell IDs',         'url' => 'nearest_cellids.php'],
    ]],

    ['label' => 'Others', 'icon' => 'map', 'children' => [
        ['label' => 'Cell ID Search',           'url' => 'cellid_search.php'],
        ['label' => 'Vehicle Search',           'url' => 'vehicle_search.php'],
        ['label' => 'Vehicle Search Criteria',  'url' => 'vehicle_search_criteria.php'],
        ['label' => 'Common Cnts',              'url' => 'common_cnts.php'],
        ['label' => 'Offender Search by Mobile','url' => 'offender_search_by_mo.php'],
        ['label' => 'Trainings',                'url' => 'training_module1.php'],
        ['label' => 'User Activity',            'url' => 'admin_activity_log.php',  'role' => 'admin'],
        ['label' => 'SQL Query Console',        'url' => 'admin_sql_console.php',   'role' => 'admin'],
    ]],

    ['label' => 'Offenders List', 'icon' => 'file', 'children' => [
        ['label' => 'Habitual Offenders',       'url' => 'habitual.php'],
        ['label' => 'Undetected Cases List',    'url' => 'fp_list.php'],
        ['label' => 'List - 1',                 'url' => 'wanted1.php'],
        ['label' => 'Rowdy Sheeter By PS',      'url' => 'rowdysheeter_ps_wise_search.php'],
    ]],

    ['label' => 'Interrogation Reports', 'icon' => 'file', 'children' => [
        ['label' => 'IR Home',                  'url' => 'home_ir.php'],
        ['label' => 'IR Search By Name',        'url' => 'ir_search.php'],
        ['label' => 'IR Search By Gender and Head','url' => 'ir_search_by_head_gender.php'],
        ['label' => 'IR Module',                'url' => 'ir_module.php'],
        ['label' => 'IR Forms',                 'url' => 'bulk_irsearch_irkey.php'],
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

    ['label' => 'JRMS', 'icon' => 'folder', 'children' => [
        ['label' => 'JRMS Main',                'url' => 'jrms_main_page1.php'],
        ['label' => 'New Records Entry',        'url' => 'jrms_new_records_entry_uniqueness.php'],
        ['label' => 'Search By Release Date',   'url' => 'jrms_datewise_search_uniqueness.php'],
        ['label' => 'Search By CIN Number',     'url' => 'jrms_cin_search_uniqueness.php'],
        ['label' => 'Search By Prisoner No',    'url' => 'jrms_search_by_prisonerno_uniqueness.php'],
        ['label' => 'Search By Name and Unique IDs','url' => 'jrms_search_uniqueness.php'],
        ['label' => 'Unique Key Update',        'url' => 'jrms_unique_key_update.php'],
        ['label' => 'Name Search',              'url' => 'jrms_name_search_php.php'],
        ['label' => 'Search By Date',           'url' => 'jrms_search_by_dates.php'],
        ['label' => 'PS Wise Search',           'url' => 'jrms_ps_wise_search.php'],
    ]],

    ['label' => 'PDACT', 'icon' => 'folder', 'children' => [
        ['label' => 'PDACT Main',               'url' => 'pdact_main_page_search.php'],
        ['label' => 'Name Search',              'url' => 'pdact_search.php'],
        ['label' => 'Search By MO',             'url' => 'pdact_mo_search.php'],
        ['label' => 'Search By PS',             'url' => 'pdact_ps_wise_search.php'],
    ]],

    ['label' => 'Tower Dump', 'icon' => 'map', 'children' => [
        ['label' => 'Tower Dump Home',          'url' => 'tower_home.php'],
        ['label' => 'Suspect Search In Dump',   'url' => 'suspect_search.php'],
        ['label' => 'Other State Numbers In Dump','url' => 'other_state_number.php'],
        ['label' => 'Inter Tower Calls',        'url' => 'inter_tower_calls.php'],
        ['label' => 'Previous Offenders In Dump','url' => 'pre_off_search.php'],
    ]],



    // Extra pages that exist in this app but were not in the old main menu.
    // This tab is new — it does not copy the old application navigation.
    ['label' => 'Additional pages', 'icon' => 'folder', 'children' => [
        ['label' => 'CAF Search',               'url' => 'caf_search.php'],
        ['label' => 'Bulk Gang ID',             'url' => 'bulk_gang_id.php'],
        ['label' => 'Bulk Gang ID Search',      'url' => 'bulk_gang_id_search.php'],
        ['label' => 'Bulk IR Key',              'url' => 'bulk_irkey.php'],
        ['label' => 'Bulk IR Key NDPS',         'url' => 'bulk_irkey_ndps.php'],
        ['label' => 'Bulk IR Search NDPS',      'url' => 'bulk_irsearch_irkey_ndps.php'],
        ['label' => 'RTA Nike',                 'url' => 'rta_nike.php'],
        ['label' => 'Trainings 2',              'url' => 'training_module2.php'],
        ['label' => 'IR Search By Head',        'url' => 'ir_search_by_head.php'],
        ['label' => 'IR NDPS',                  'url' => 'ir_ndps.php'],
        ['label' => 'CDAT IR Form',             'url' => 'cdat_irform.php'],
        ['label' => 'MO Image List',            'url' => 'mo_image_list.php'],
        ['label' => 'All Data',                 'url' => 'alldata.php'],
        ['label' => 'All Data Search',          'url' => 'alldata_search.php'],
        ['label' => 'Call Details (old)',       'url' => 'calldetails.php'],
        ['label' => 'Summary All DB',           'url' => 'sum_alldb.php'],
        ['label' => 'Dump',                     'url' => 'dump.php'],
        ['label' => 'Dump Search',              'url' => 'dump_search.php'],
        ['label' => 'Dump Analysis',            'url' => 'dump_analysis.php'],
        ['label' => 'Movements In Particular Place','url' => 'movements_in_particular_place.php'],
        ['label' => 'Nearest Cell IDs',         'url' => 'nearest_cellids.php'],
        ['label' => 'Near By Cell Tower IDs',   'url' => 'near_by_celltowerids.php'],
        ['label' => 'Vehicle Chassis Search',   'url' => 'vehicle_chas_search.php'],
        ['label' => 'Vehicle Engine Search',    'url' => 'vehicle_eng_search.php'],
        ['label' => 'VBR Search',               'url' => 'vbr_search.php'],
        ['label' => 'Name Search',              'url' => 'name_search.php'],
        ['label' => 'CIS Data Name Search',     'url' => 'cis_data_name_search.php'],
        ['label' => 'Migrant Labours Report',   'url' => 'migrant_labours_report.php'],
        ['label' => 'Migrant Labours Date Report','url' => 'migrant_labours_date_report.php'],
        ['label' => 'NBWS',                     'url' => 'nbws.php'],
        ['label' => 'Offender FD',              'url' => 'offender_fd.php'],
        ['label' => 'PDACT Entry Form',         'url' => 'pdact_main_page.php'],
        ['label' => 'JRMS Home',                'url' => 'home_jrms.php'],
        ['label' => 'JRMS Search',              'url' => 'jrms_search.php'],
        ['label' => 'JRMS Search New',          'url' => 'jrms_search_new.php'],
        ['label' => 'JRMS Search For Unique Key','url' => 'jrms_search_for_uniquekey.php'],
        ['label' => 'JRMS Uniqueness Update',   'url' => 'jrms_uniqueness_update.php'],
        ['label' => 'JRMS Name Search (alt)',   'url' => 'jrms_name_search.php'],
    ]],

    // Test pages, old copies, and duplicate filenames — for verify only.
    ['label' => 'Test / copies', 'icon' => 'file', 'children' => [
        ['label' => 'IR Search Test',           'url' => 'ir_search_test.php'],
        ['label' => 'Profile',                  'url' => '../view/profile.html'],
        ['label' => 'My Index',                 'url' => 'myindex.php'],
        ['label' => 'Demo',                     'url' => 'demo.php.php'],
        ['label' => 'Chandu',                   'url' => 'chandu.php'],
        ['label' => 'IR (old hub)',             'url' => 'ir.php'],
        ['label' => 'IR NDPS 1',                'url' => 'ir_ndps1.php'],
        ['label' => 'Name Search (copy)',       'url' => 'namesearch.php'],
        ['label' => 'CIS Data Name Search PHP', 'url' => 'cis_data_name_search_php.php'],
        ['label' => 'Bulk Address (copy)',      'url' => 'bulk_address.php'],
        ['label' => 'Summary Between Dates (copy)','url' => 'sum_btwn_dates.php'],
        ['label' => 'Summary New No (copy)',    'url' => 'sum_new_no.php'],
        // These are POST handlers. Opening them without submit redirects
        // to the real search form (same as old dev: HTML form -> PHP handler).
        ['label' => 'Day Night Loc (handler)',  'url' => 'd&n_loc.php'],
        ['label' => 'Day Night Between Dates (handler)','url' => 'd&n_bt_dts.php'],
        ['label' => 'Day Night IMEI (handler)', 'url' => 'd&n_loc_imei.php'],
        ['label' => 'Tower Dump Home (old)',    'url' => 'towerdump_home.php'],
        ['label' => 'Suspect Search TWR',       'url' => 'suspect_search_twr.php'],
        ['label' => 'Other State Number TWR',   'url' => 'other_state_number_twr.php'],
        ['label' => 'Inter Tower Calls TWR',    'url' => 'inter_tower_calls_twr.php'],
        ['label' => 'Previous Offenders TWR',   'url' => 'pre_off_search_twr.php'],
        ['label' => 'JRMS (copy)',              'url' => 'jrms.php'],
        ['label' => 'JRMS Main Page (copy)',    'url' => 'jrms_main_page.php'],
        ['label' => 'JRMS New Entry PHP',       'url' => 'jrms_new_records_entry_uniqueness_php.php'],
        ['label' => 'JRMS New Entry Mahesh',    'url' => 'jrms_new_records_entry_uniqueness_mahesh_php.php'],
        ['label' => 'JRMS PS Wise Search 1',    'url' => 'jrms_ps_wise_search1.php'],
        ['label' => 'JRMS Prisoner No Old',     'url' => 'jrms_search_by_prisonerno_uniqueness_old.php'],
        ['label' => 'JRMS Prisoner No Mahesh',  'url' => 'jrms_search_by_prisonerno_uniqueness_mahesh.php'],
        ['label' => 'JRMS Search Uniqueness PHP','url' => 'jrms_search_uniqueness_php.php'],
        ['label' => 'PDACT Main (copy)',        'url' => 'pdact_main.php'],
        ['label' => 'PDACT PS Wise Search PHP', 'url' => 'pdact_ps_wise_search_php.php'],
        ['label' => 'Rowdy Sheeter PHP copy',   'url' => 'rowdysheeter_ps_wise_search_php.php'],
        ['label' => 'Upload Staging Preview',   'url' => 'admin_upload_verify.php', 'role' => 'uploader'],
    ]],

    ['label' => 'Administration', 'icon' => 'cog', 'children' => [
        ['label' => 'User Activity',            'url' => 'admin_activity_log.php',  'role' => 'admin'],
        ['label' => 'SQL Query Console',        'url' => 'admin_sql_console.php',   'role' => 'admin'],
        ['label' => 'Create User',              'url' => 'admin_create_user.php',   'role' => 'admin'],
        ['label' => 'Upload History',           'url' => 'admin_upload_history.php','role' => 'uploader'],
    ]],
];
