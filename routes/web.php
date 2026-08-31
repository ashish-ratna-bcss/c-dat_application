<?php

return [

    [
        'method'  => 'GET',
        'path'    => '/health',
        'name'    => 'health',
        'handler' => 'modules/common/health.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    [
        'method'  => 'GET',
        'path'    => '/login',
        'name'    => 'login',
        'handler' => 'modules/common/auth.php',
    ],

    [
        'method'  => 'POST',
        'path'    => '/login',
        'name'    => 'login.submit',
        'handler' => 'modules/common/login.php',
    ],

    [
        'method'  => 'GET',
        'path'    => '/logout',
        'name'    => 'logout',
        'handler' => 'modules/common/logout.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/',
        'name'    => 'dashboard',
        'handler' => 'modules/dashboard/home.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/dashboard',
        'name'    => 'dashboard.home',
        'handler' => 'modules/dashboard/home.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Upload
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload',
        'name'    => 'data-upload',
        'handler' => 'modules/data-upload/admin_upload_cdr.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/cdr',
        'name'    => 'data-upload.cdr',
        'handler' => 'modules/data-upload/admin_upload_cdr.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/sdr',
        'name'    => 'data-upload.sdr',
        'handler' => 'modules/data-upload/admin_upload_sdr.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/custom',
        'name'    => 'data-upload.custom',
        'handler' => 'modules/data-upload/admin_upload_custom.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/history',
        'name'    => 'data-upload.history',
        'handler' => 'modules/data-upload/admin_upload_history.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/verify',
        'name'    => 'data-upload.verify',
        'handler' => 'modules/data-upload/admin_upload_verify.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/job-status',
        'name'    => 'data-upload.job-status',
        'handler' => 'modules/data-upload/admin_upload_job_status.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/data-upload/sync-jobs',
        'name'    => 'data-upload.sync-jobs',
        'handler' => 'modules/data-upload/admin_upload_sync_jobs.php',
    ],

    [
        'method'  => 'GET',
        'path'    => '/data-upload/template',
        'name'    => 'data-upload.template',
        'handler' => 'modules/data-upload/download_template.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/api/police-stations',
        'name'    => 'api.police-stations',
        'handler' => 'modules/common/get_ps.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/api/years',
        'name'    => 'api.years',
        'handler' => 'modules/common/get_year.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/api/crime-numbers',
        'name'    => 'api.crime-numbers',
        'handler' => 'modules/common/get_crno.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/api/divisions',
        'name'    => 'api.divisions',
        'handler' => 'modules/common/get_division.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/api/quick-links',
        'name'    => 'api.quick-links',
        'handler' => 'modules/common/quick_links_api.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary',
        'name'    => 'summary',
        'handler' => 'modules/summary/sum_home.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/total',
        'name'    => 'summary.total',
        'handler' => 'modules/summary/sum_home.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/between-dates',
        'name'    => 'summary.between-dates',
        'handler' => 'modules/summary/sum_between_dates.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/isd-contacts',
        'name'    => 'summary.isd-contacts',
        'handler' => 'modules/summary/sum_isd_cnts.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/new-contacts',
        'name'    => 'summary.new-contacts',
        'handler' => 'modules/summary/sum_new_nos.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/in-state',
        'name'    => 'summary.in-state',
        'handler' => 'modules/summary/sum_in_state.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/summary/out-state',
        'name'    => 'summary.out-state',
        'handler' => 'modules/summary/sum_out_state.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Call Details
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/call-details',
        'name'    => 'call-details',
        'handler' => 'modules/call-details/movements.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/call-details/movements',
        'name'    => 'call-details.movements',
        'handler' => 'modules/call-details/movements.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/call-details/movements-between-numbers',
        'name'    => 'call-details.movements-between-numbers',
        'handler' => 'modules/call-details/movements_between_two_numbers.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/call-details/comparison',
        'name'    => 'call-details.comparison',
        'handler' => 'modules/call-details/movements_between_two_numbers_comparision.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/call-details/calls-between-dates',
        'name'    => 'call-details.calls-between-dates',
        'handler' => 'modules/call-details/calls_btwn_dates.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | CDAT
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/cdat',
        'name'    => 'cdat',
        'handler' => 'modules/cdat/cdatcnts.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/cdat/contacts',
        'name'    => 'cdat.contacts',
        'handler' => 'modules/cdat/cdatcnts.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/cdat/bulk-contacts',
        'name'    => 'cdat.bulk-contacts',
        'handler' => 'modules/cdat/bulk_cdat_contacts.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/cdat/other',
        'name'    => 'cdat.other',
        'handler' => 'modules/cdat/otherscdat.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | IMEI Search
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/imei-search',
        'name'    => 'imei-search',
        'handler' => 'modules/imei/imeisearch.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/imei-search/phones-by-imei',
        'name'    => 'imei-search.phones-by-imei',
        'handler' => 'modules/imei/imeisearch.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/imei-search/imeis-by-phone',
        'name'    => 'imei-search.imeis-by-phone',
        'handler' => 'modules/imei/imeisinphone.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Address
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/address',
        'name'    => 'address',
        'handler' => 'modules/address/address.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/address/single',
        'name'    => 'address.single',
        'handler' => 'modules/address/address.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/address/bulk',
        'name'    => 'address.bulk',
        'handler' => 'modules/address/bulkaddress.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Day / Night Location
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/day-night-location',
        'name'    => 'day-night-location',
        'handler' => 'modules/day-night-location/day&nightloc.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/day-night-location/top-10',
        'name'    => 'day-night-location.top-10',
        'handler' => 'modules/day-night-location/day&nightloc.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/day-night-location/by-date',
        'name'    => 'day-night-location.by-date',
        'handler' => 'modules/day-night-location/day&nightloc_btwn_dates.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Offenders List
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/offenders-list',
        'name'    => 'offenders-list',
        'handler' => 'modules/offenders-list/habitual.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/offenders-list/habitual',
        'name'    => 'offenders-list.habitual',
        'handler' => 'modules/offenders-list/habitual.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/offenders-list/undetected',
        'name'    => 'offenders-list.undetected',
        'handler' => 'modules/offenders-list/fp_list.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Others
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others',
        'name'    => 'others',
        'handler' => 'modules/others/cellid_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/cell-id',
        'name'    => 'others.cell-id',
        'handler' => 'modules/others/cellid_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/vehicle',
        'name'    => 'others.vehicle',
        'handler' => 'modules/others/vehicle_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/common-contacts',
        'name'    => 'others.common-contacts',
        'handler' => 'modules/others/common_cnts.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/trainings',
        'name'    => 'others.trainings',
        'handler' => 'modules/others/training_module1.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/offender-subclassification',
        'name'    => 'others.offender-subclassification',
        'handler' => 'modules/others/offender_search_by_mo.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/others/offender-fd',
        'name'    => 'others.offender-fd',
        'handler' => 'modules/others/offender_fd.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogation Reports
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/interrogation-reports',
        'name'    => 'interrogation-reports',
        'handler' => 'modules/interrogation-reports/ir_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/interrogation-reports/name',
        'name'    => 'interrogation-reports.name',
        'handler' => 'modules/interrogation-reports/ir_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/interrogation-reports/name-crime-head',
        'name'    => 'interrogation-reports.name-crime-head',
        'handler' => 'modules/interrogation-reports/ir_search_by_head.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/interrogation-reports/gender-crime-head',
        'name'    => 'interrogation-reports.gender-crime-head',
        'handler' => 'modules/interrogation-reports/ir_search_by_head_gender.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/interrogation-reports/detail',
        'name'    => 'interrogation-reports.detail',
        'handler' => 'modules/interrogation-reports/ir.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | JRMS
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/jrms',
        'name'    => 'jrms',
        'handler' => 'modules/jrms/jrms_name_search_php.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/jrms/name',
        'name'    => 'jrms.name',
        'handler' => 'modules/jrms/jrms_name_search_php.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/jrms/date',
        'name'    => 'jrms.date',
        'handler' => 'modules/jrms/jrms_search_by_dates.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/jrms/police-station',
        'name'    => 'jrms.police-station',
        'handler' => 'modules/jrms/jrms_ps_wise_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/jrms/unique-key',
        'name'    => 'jrms.unique-key',
        'handler' => 'modules/jrms/jrms_search_for_uniquekey.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | PD Act
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/pd-act',
        'name'    => 'pd-act',
        'handler' => 'modules/pd-act/pdact_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/pd-act/name',
        'name'    => 'pd-act.name',
        'handler' => 'modules/pd-act/pdact_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/pd-act/mo',
        'name'    => 'pd-act.mo',
        'handler' => 'modules/pd-act/pdact_mo_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/pd-act/police-station',
        'name'    => 'pd-act.police-station',
        'handler' => 'modules/pd-act/pdact_ps_wise_search.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/pd-act/detail',
        'name'    => 'pd-act.detail',
        'handler' => 'modules/pd-act/pdact_main.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Administration
    |--------------------------------------------------------------------------
    */

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/administration',
        'name'    => 'administration',
        'handler' => 'modules/administration/admin_activity_log.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/administration/user-activity',
        'name'    => 'administration.user-activity',
        'handler' => 'modules/administration/admin_activity_log.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/administration/sql-console',
        'name'    => 'administration.sql-console',
        'handler' => 'modules/administration/admin_sql_console.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/administration/create-user',
        'name'    => 'administration.create-user',
        'handler' => 'modules/administration/admin_create_user.php',
    ],

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/administration/upload-history',
        'name'    => 'administration.upload-history',
        'handler' => 'modules/data-upload/admin_upload_history.php',
    ],

];
