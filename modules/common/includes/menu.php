<?php
/**
 * The navigation, in one place.
 *
 * This is the only file to edit when menus change -- every page renders its
 * sidebar from here via layout.php, instead of carrying its own copy of the
 * markup (which is why the old menus had drifted into 15 different versions).
 *
 * Shape:
 *   ['label' => 'Text', 'url' => '/summary/total']          a link
 *   ['label' => 'Text', 'children' => [ ... ]]        a group
 *
 * A group is NOT a link. It renders as a button that expands its children in
 * place, so clicking a parent never navigates away. The old Spry menu pointed
 * its parents at home.php, which is why opening "Call Details" threw you back
 * to the dashboard.
 *
 * Urls are pretty paths from routes/web.php (e.g. /summary/total).
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
    ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'home'],

    ['label' => 'Data Upload', 'icon' => 'upload', 'role' => 'uploader', 'children' => [
        ['label' => 'CDR',                      'url' => '/data-upload/cdr', 'role' => 'uploader'],
        ['label' => 'SDR',                      'url' => '/data-upload/sdr', 'role' => 'uploader'],
        ['label' => 'Custom Table',             'url' => '/data-upload/custom', 'role' => 'uploader'],
        ['label' => 'Upload History',           'url' => '/data-upload/history', 'role' => 'uploader'],
    ]],

    ['label' => 'Summary', 'icon' => 'chart', 'children' => [
        ['label' => 'Summary Total',            'url' => '/summary/total'],
        ['label' => 'Summary Between Dates',    'url' => '/summary/between-dates'],
        ['label' => 'ISD Contacts',             'url' => '/summary/isd-contacts'],
        ['label' => 'New Contacts',             'url' => '/summary/new-contacts'],
        ['label' => 'State-wise Summary',       'url' => '/summary/in-state'],
        ['label' => 'Other State Summary',      'url' => '/summary/out-state'],
    ]],

    ['label' => 'Call Details', 'icon' => 'phone', 'children' => [
        ['label' => 'Movements',                        'url' => '/call-details/movements'],
        ['label' => 'Movements Between Two Numbers',    'url' => '/call-details/movements-between-numbers'],
        ['label' => 'Comparison Between Two Numbers',     'url' => '/call-details/comparison'],
        ['label' => 'Calls Between Dates',              'url' => '/call-details/calls-between-dates'],
    ]],

    ['label' => 'CDAT', 'icon' => 'grid', 'children' => [
        ['label' => 'CDAT Contacts',            'url' => '/cdat/contacts'],
        ['label' => 'Bulk CDAT Contacts',       'url' => '/cdat/bulk-contacts'],
        ['label' => 'Other CDAT',               'url' => '/cdat/other'],
    ]],

    ['label' => 'IMEI Search', 'icon' => 'device', 'children' => [
        ['label' => 'Phones Used in IMEI',      'url' => '/imei-search/phones-by-imei'],
        ['label' => 'IMEIs Used in Phone',      'url' => '/imei-search/imeis-by-phone'],
    ]],

    ['label' => 'Address', 'icon' => 'pin', 'children' => [
        ['label' => 'Single Address',           'url' => '/address/single'],
        ['label' => 'Bulk Address',             'url' => '/address/bulk'],
    ]],

    ['label' => 'Day/Night Location', 'icon' => 'map', 'children' => [
        ['label' => 'Top 10 Day/Night Locations',       'url' => '/day-night-location/top-10'],
        ['label' => 'Date-based Day/Night Locations',   'url' => '/day-night-location/by-date'],
    ]],

    ['label' => 'Offenders List', 'icon' => 'file', 'children' => [
        ['label' => 'Habitual Offenders',       'url' => '/offenders-list/habitual'],
        ['label' => 'Undetected Cases List',    'url' => '/offenders-list/undetected'],
        ['label' => 'List - 1',                 'url' => '/offenders-list/list-1'],
    ]],

    ['label' => 'Others', 'icon' => 'map', 'children' => [
        ['label' => 'Cell ID Search',               'url' => '/others/cell-id'],
        ['label' => 'Vehicle Search',               'url' => '/others/vehicle'],
        ['label' => 'Common Contacts',              'url' => '/others/common-contacts'],
        ['label' => 'Trainings',                    'url' => '/others/trainings'],
        ['label' => 'Offender Subclassification',   'url' => '/others/offender-subclassification'],
        ['label' => 'Rowdy Sheeter Search by PS',   'url' => '/others/rowdy-sheeter'],
    ]],

    ['label' => 'Interrogation Reports', 'icon' => 'file', 'children' => [
        ['label' => 'Search by Name',                   'url' => '/interrogation-reports/name'],
        ['label' => 'Search by Name + Crime Head',      'url' => '/interrogation-reports/name-crime-head'],
        ['label' => 'Search by Gender + Crime Head',    'url' => '/interrogation-reports/gender-crime-head'],
    ]],

    ['label' => 'JRMS', 'icon' => 'folder', 'children' => [
        ['label' => 'Name Search',              'url' => '/jrms/name'],
        ['label' => 'Date Search',              'url' => '/jrms/date'],
        ['label' => 'PS-wise Search',           'url' => '/jrms/police-station'],
    ]],

    ['label' => 'PD Act', 'icon' => 'folder', 'children' => [
        ['label' => 'Name Search',              'url' => '/pd-act/name'],
        ['label' => 'MO Search',                'url' => '/pd-act/mo'],
        ['label' => 'PS Search',                'url' => '/pd-act/police-station'],
    ]],

    ['label' => 'Administration', 'icon' => 'cog', 'children' => [
        ['label' => 'User Activity',            'url' => '/administration/user-activity',      'role' => 'admin'],
        ['label' => 'SQL Query Console',        'url' => '/administration/sql-console',       'role' => 'admin'],
        ['label' => 'Create User',              'url' => '/administration/create-user',       'role' => 'admin'],
    ]],

    // Everything not in the main CDAT menu above.
    ['label' => 'Additional pages', 'icon' => 'folder', 'children' => [
        // Call Details (extra)
        ['label' => 'Call Details Total',       'url' => '/additional/calls-tot'],
        ['label' => 'Calls Between Two Nos',    'url' => '/additional/calls-bt-nos'],
        ['label' => 'Call Details (old)',       'url' => '/additional/calldetails'],
        ['label' => 'Movements In Particular Place', 'url' => '/additional/movements-in-particular-place'],

        // IMEI (extra)
        ['label' => 'Lost Report IMEI Home',    'url' => '/additional/home-imei'],
        ['label' => 'IMEI Request Status',      'url' => '/additional/imei-request-status'],
        ['label' => 'IMEI Request Traced Details', 'url' => '/additional/imei-request-traced-details'],
        ['label' => 'Traced Phone Summary',     'url' => '/additional/imei-request-sum'],
        ['label' => 'Traced Phone Movements',   'url' => '/additional/imei-request-movements'],
        ['label' => 'Traced Phone Max Spent Loc', 'url' => '/additional/maxspentlocation-imei'],
        ['label' => 'Traced Phone Day & Night Loc', 'url' => '/additional/dayandnightloc-imei'],

        // Day/Night & location (extra)
        ['label' => 'Nearest Cell IDs',         'url' => '/additional/nearest-cellids'],
        ['label' => 'Near By Cell Tower IDs',   'url' => '/additional/near-by-celltowerids'],
        ['label' => 'Day Night Loc (handler)',  'url' => '/additional/dandn-loc'],
        ['label' => 'Day Night Between Dates (handler)', 'url' => '/additional/dandn-bt-dts'],
        ['label' => 'Day Night IMEI (handler)', 'url' => '/additional/dandn-loc-imei'],

        // Others (extra)
        ['label' => 'Vehicle Search Criteria',  'url' => '/additional/vehicle-search-criteria'],
        ['label' => 'Vehicle Chassis Search',   'url' => '/additional/vehicle-chas-search'],
        ['label' => 'Vehicle Engine Search',    'url' => '/additional/vehicle-eng-search'],
        ['label' => 'Trainings 2',              'url' => '/additional/training-module2'],
        ['label' => 'Offender FD',              'url' => '/additional/offender-fd'],
        ['label' => 'VBR Search',               'url' => '/additional/vbr-search'],
        ['label' => 'Name Search',              'url' => '/additional/name-search'],
        ['label' => 'CAF Search',               'url' => '/additional/caf-search'],
        ['label' => 'CIS Data Name Search',     'url' => '/additional/cis-data-name-search'],
        ['label' => 'Migrant Labours Report',   'url' => '/additional/migrant-labours-report'],
        ['label' => 'Migrant Labours Date Report', 'url' => '/additional/migrant-labours-date-report'],
        ['label' => 'NBWS',                     'url' => '/additional/nbws'],
        ['label' => 'RTA Nike',                 'url' => '/additional/rta-nike'],
        ['label' => 'All Data',                 'url' => '/additional/alldata'],
        ['label' => 'All Data Search',          'url' => '/additional/alldata-search'],
        ['label' => 'Summary All DB',           'url' => '/additional/sum-alldb'],
        ['label' => 'Bulk Gang ID',             'url' => '/additional/bulk-gang-id'],
        ['label' => 'Bulk Gang ID Search',      'url' => '/additional/bulk-gang-id-search'],
        ['label' => 'MO Image List',            'url' => '/additional/mo-image-list'],

        // Interrogation Reports (extra)
        ['label' => 'IR Home',                  'url' => '/additional/home-ir'],
        ['label' => 'IR Module',                'url' => '/additional/ir-module'],
        ['label' => 'IR Forms',                 'url' => '/additional/bulk-irsearch-irkey'],
        ['label' => 'IR Report',                'url' => '/additional/irreport'],
        ['label' => 'Family History',           'url' => '/additional/family-history'],
        ['label' => 'Offence Details',          'url' => '/additional/offence-details'],
        ['label' => 'Previous Offence Details', 'url' => '/additional/previous-offence-details'],
        ['label' => 'Local Contacts',           'url' => '/additional/local-contacts'],
        ['label' => 'Gangs / Associates',       'url' => '/additional/relation-with-other-associates-and-gangs'],
        ['label' => 'Property Details',         'url' => '/additional/disposal-of-property'],
        ['label' => 'Brief Facts',              'url' => '/additional/brief-facts'],
        ['label' => 'Image',                    'url' => '/additional/image-list'],
        ['label' => 'Mulakath Details',         'url' => '/additional/mulakath-entry'],
        ['label' => 'Retrieve',                 'url' => '/additional/retrieve'],
        ['label' => 'Bulk IR Key',              'url' => '/additional/bulk-irkey'],
        ['label' => 'Bulk IR Key NDPS',         'url' => '/additional/bulk-irkey-ndps'],
        ['label' => 'Bulk IR Search NDPS',      'url' => '/additional/bulk-irsearch-irkey-ndps'],
        ['label' => 'IR NDPS',                  'url' => '/additional/ir-ndps'],
        ['label' => 'CDAT IR Form',             'url' => '/additional/cdat-irform'],
        ['label' => 'IR (old hub)',             'url' => '/additional/ir'],
        ['label' => 'IR NDPS 1',                'url' => '/additional/ir-ndps1'],
        ['label' => 'IR Search Test',           'url' => '/additional/ir-search-test'],

        // JRMS (extra)
        ['label' => 'JRMS Main',                'url' => '/additional/jrms-main-page1'],
        ['label' => 'JRMS Home',                'url' => '/additional/home-jrms'],
        ['label' => 'New Records Entry',        'url' => '/additional/jrms-new-records-entry-uniqueness'],
        ['label' => 'Search By Release Date',   'url' => '/additional/jrms-datewise-search-uniqueness'],
        ['label' => 'Search By CIN Number',     'url' => '/additional/jrms-cin-search-uniqueness'],
        ['label' => 'Search By Prisoner No',    'url' => '/additional/jrms-search-by-prisonerno-uniqueness'],
        ['label' => 'Search By Name and Unique IDs', 'url' => '/additional/jrms-search-uniqueness'],
        ['label' => 'Unique Key Update',        'url' => '/additional/jrms-unique-key-update'],
        ['label' => 'JRMS Search',              'url' => '/additional/jrms-search'],
        ['label' => 'JRMS Search New',          'url' => '/additional/jrms-search-new'],
        ['label' => 'JRMS Search For Unique Key', 'url' => '/additional/jrms-search-for-uniquekey'],
        ['label' => 'JRMS Uniqueness Update',   'url' => '/additional/jrms-uniqueness-update'],

        // PD Act (extra)
        ['label' => 'PDACT Main',               'url' => '/additional/pdact-main-page-search'],
        ['label' => 'PDACT Entry Form',         'url' => '/additional/pdact-main-page'],
        ['label' => 'PDACT Main (copy)',        'url' => '/additional/pdact-main'],
        ['label' => 'PDACT PS Wise Search PHP', 'url' => '/additional/pdact-ps-wise-search-php'],

        // Tower Dump
        ['label' => 'Tower Dump Home',          'url' => '/additional/tower-home'],
        ['label' => 'Tower Dump Home (old)',    'url' => '/additional/towerdump-home'],
        ['label' => 'Suspect Search In Dump',   'url' => '/additional/suspect-search'],
        ['label' => 'Other State Numbers In Dump', 'url' => '/additional/other-state-number'],
        ['label' => 'Inter Tower Calls',        'url' => '/additional/inter-tower-calls'],
        ['label' => 'Previous Offenders In Dump', 'url' => '/additional/pre-off-search'],
        ['label' => 'Suspect Search TWR',       'url' => '/additional/suspect-search-twr'],
        ['label' => 'Other State Number TWR',   'url' => '/additional/other-state-number-twr'],
        ['label' => 'Inter Tower Calls TWR',    'url' => '/additional/inter-tower-calls-twr'],
        ['label' => 'Previous Offenders TWR',   'url' => '/additional/pre-off-search-twr'],

        // Dump analysis
        ['label' => 'Dump',                     'url' => '/additional/dump'],
        ['label' => 'Dump Search',              'url' => '/additional/dump-search'],
        ['label' => 'Dump Analysis',            'url' => '/additional/dump-analysis'],

        // Test / copies
        ['label' => 'Profile',                  'url' => '/profile'],
        ['label' => 'My Index',                 'url' => '/additional/myindex'],
        ['label' => 'Demo',                     'url' => '/additional/demo-php'],
        ['label' => 'Chandu',                   'url' => '/additional/chandu'],
        ['label' => 'Name Search (copy)',       'url' => '/additional/namesearch'],
        ['label' => 'CIS Data Name Search PHP', 'url' => '/additional/cis-data-name-search-php'],
        ['label' => 'Bulk Address (copy)',      'url' => '/additional/bulk-address'],
        ['label' => 'Summary Between Dates (copy)', 'url' => '/additional/sum-btwn-dates'],
        ['label' => 'Summary New No (copy)',    'url' => '/additional/sum-new-no'],
        ['label' => 'Rowdy Sheeter PHP copy',   'url' => '/additional/rowdysheeter-ps-wise-search-php'],
    ]],
];
