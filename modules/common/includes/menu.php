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
 * is what refuses the request.
 */
return [
    ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'home'],

    ['label' => 'Data Upload', 'icon' => 'upload', 'role' => 'uploader', 'children' => [
        ['label' => 'CDR (Call Data Record)',   'url' => '/data-upload/cdr', 'role' => 'uploader'],
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
    ]],

    ['label' => 'Others', 'icon' => 'map', 'children' => [
        ['label' => 'Cell ID Search',               'url' => '/others/cell-id'],
        ['label' => 'Vehicle Search',               'url' => '/others/vehicle'],
        ['label' => 'Common Contacts',              'url' => '/others/common-contacts'],
        ['label' => 'Trainings',                    'url' => '/others/trainings'],
        ['label' => 'Offender Subclassification',   'url' => '/others/offender-subclassification'],
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
];
