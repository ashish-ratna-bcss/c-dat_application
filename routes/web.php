<?php

return [

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

    [
        'method'  => ['GET', 'POST'],
        'path'    => '/offenders-list/list-1',
        'name'    => 'offenders-list.list-1',
        'handler' => 'modules/offenders-list/wanted1.php',
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
        'path'    => '/others/rowdy-sheeter',
        'name'    => 'others.rowdy-sheeter',
        'handler' => 'modules/others/rowdysheeter_ps_wise_search.php',
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


    /*
    |--------------------------------------------------------------------------
    | Additional pages
    |--------------------------------------------------------------------------
    */
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/calls-tot',
        'name'    => 'additional.calls-tot',
        'handler' => 'modules/additional/calls_tot.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/calls-bt-nos',
        'name'    => 'additional.calls-bt-nos',
        'handler' => 'modules/additional/calls_bt_nos.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/calldetails',
        'name'    => 'additional.calldetails',
        'handler' => 'modules/additional/calldetails.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/movements-in-particular-place',
        'name'    => 'additional.movements-in-particular-place',
        'handler' => 'modules/additional/movements_in_particular_place.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/home-imei',
        'name'    => 'additional.home-imei',
        'handler' => 'modules/additional/home_imei.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/imei-request-status',
        'name'    => 'additional.imei-request-status',
        'handler' => 'modules/additional/imei_request_status.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/imei-request-traced-details',
        'name'    => 'additional.imei-request-traced-details',
        'handler' => 'modules/additional/imei_request_traced_details.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/imei-request-sum',
        'name'    => 'additional.imei-request-sum',
        'handler' => 'modules/additional/imei_request_sum.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/imei-request-movements',
        'name'    => 'additional.imei-request-movements',
        'handler' => 'modules/additional/imei_request_movements.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/maxspentlocation-imei',
        'name'    => 'additional.maxspentlocation-imei',
        'handler' => 'modules/additional/maxspentlocation_imei.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dayandnightloc-imei',
        'name'    => 'additional.dayandnightloc-imei',
        'handler' => 'modules/additional/day&nightloc_imei.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/nearest-cellids',
        'name'    => 'additional.nearest-cellids',
        'handler' => 'modules/additional/nearest_cellids.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/near-by-celltowerids',
        'name'    => 'additional.near-by-celltowerids',
        'handler' => 'modules/additional/near_by_celltowerids.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dandn-loc',
        'name'    => 'additional.dandn-loc',
        'handler' => 'modules/day-night-location/day&nightloc.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dandn-bt-dts',
        'name'    => 'additional.dandn-bt-dts',
        'handler' => 'modules/day-night-location/day&nightloc_btwn_dates.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dandn-loc-imei',
        'name'    => 'additional.dandn-loc-imei',
        'handler' => 'modules/additional/d&n_loc_imei.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/vehicle-search-criteria',
        'name'    => 'additional.vehicle-search-criteria',
        'handler' => 'modules/additional/vehicle_search_criteria.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/vehicle-chas-search',
        'name'    => 'additional.vehicle-chas-search',
        'handler' => 'modules/additional/vehicle_chas_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/vehicle-eng-search',
        'name'    => 'additional.vehicle-eng-search',
        'handler' => 'modules/additional/vehicle_eng_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/training-module2',
        'name'    => 'additional.training-module2',
        'handler' => 'modules/additional/training_module2.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/offender-fd',
        'name'    => 'additional.offender-fd',
        'handler' => 'modules/additional/offender_fd.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/vbr-search',
        'name'    => 'additional.vbr-search',
        'handler' => 'modules/additional/vbr_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/name-search',
        'name'    => 'additional.name-search',
        'handler' => 'modules/additional/name_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/caf-search',
        'name'    => 'additional.caf-search',
        'handler' => 'modules/additional/caf_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/cis-data-name-search',
        'name'    => 'additional.cis-data-name-search',
        'handler' => 'modules/additional/cis_data_name_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/migrant-labours-report',
        'name'    => 'additional.migrant-labours-report',
        'handler' => 'modules/additional/migrant_labours_report.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/migrant-labours-date-report',
        'name'    => 'additional.migrant-labours-date-report',
        'handler' => 'modules/additional/migrant_labours_date_report.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/nbws',
        'name'    => 'additional.nbws',
        'handler' => 'modules/additional/nbws.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/rta-nike',
        'name'    => 'additional.rta-nike',
        'handler' => 'modules/additional/rta_nike.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/alldata',
        'name'    => 'additional.alldata',
        'handler' => 'modules/additional/alldata.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/alldata-search',
        'name'    => 'additional.alldata-search',
        'handler' => 'modules/additional/alldata_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/sum-alldb',
        'name'    => 'additional.sum-alldb',
        'handler' => 'modules/additional/sum_alldb.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-gang-id',
        'name'    => 'additional.bulk-gang-id',
        'handler' => 'modules/additional/bulk_gang_id.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-gang-id-search',
        'name'    => 'additional.bulk-gang-id-search',
        'handler' => 'modules/additional/bulk_gang_id_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/mo-image-list',
        'name'    => 'additional.mo-image-list',
        'handler' => 'modules/additional/mo_image_list.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/home-ir',
        'name'    => 'additional.home-ir',
        'handler' => 'modules/additional/home_ir.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/ir-module',
        'name'    => 'additional.ir-module',
        'handler' => 'modules/additional/ir_module.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-irsearch-irkey',
        'name'    => 'additional.bulk-irsearch-irkey',
        'handler' => 'modules/additional/bulk_irsearch_irkey.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/irreport',
        'name'    => 'additional.irreport',
        'handler' => 'modules/additional/irreport.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/family-history',
        'name'    => 'additional.family-history',
        'handler' => 'modules/additional/family_history.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/offence-details',
        'name'    => 'additional.offence-details',
        'handler' => 'modules/additional/offence_details.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/previous-offence-details',
        'name'    => 'additional.previous-offence-details',
        'handler' => 'modules/additional/previous_offence_details.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/local-contacts',
        'name'    => 'additional.local-contacts',
        'handler' => 'modules/additional/local_contacts.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/relation-with-other-associates-and-gangs',
        'name'    => 'additional.relation-with-other-associates-and-gangs',
        'handler' => 'modules/additional/relation_with_other_associates_and_gangs.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/disposal-of-property',
        'name'    => 'additional.disposal-of-property',
        'handler' => 'modules/additional/disposal_of_property.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/brief-facts',
        'name'    => 'additional.brief-facts',
        'handler' => 'modules/additional/brief_facts.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/image-list',
        'name'    => 'additional.image-list',
        'handler' => 'modules/additional/image_list.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/mulakath-entry',
        'name'    => 'additional.mulakath-entry',
        'handler' => 'modules/additional/mulakath_entry.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/retrieve',
        'name'    => 'additional.retrieve',
        'handler' => 'modules/additional/retrieve.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-irkey',
        'name'    => 'additional.bulk-irkey',
        'handler' => 'modules/additional/bulk_irkey.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-irkey-ndps',
        'name'    => 'additional.bulk-irkey-ndps',
        'handler' => 'modules/additional/bulk_irkey_ndps.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-irsearch-irkey-ndps',
        'name'    => 'additional.bulk-irsearch-irkey-ndps',
        'handler' => 'modules/additional/bulk_irsearch_irkey_ndps.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/ir-ndps',
        'name'    => 'additional.ir-ndps',
        'handler' => 'modules/additional/ir_ndps.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/cdat-irform',
        'name'    => 'additional.cdat-irform',
        'handler' => 'modules/additional/cdat_irform.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/ir',
        'name'    => 'additional.ir',
        'handler' => 'modules/additional/ir.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/ir-ndps1',
        'name'    => 'additional.ir-ndps1',
        'handler' => 'modules/additional/ir_ndps1.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/ir-search-test',
        'name'    => 'additional.ir-search-test',
        'handler' => 'modules/additional/ir_search_test.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-main-page1',
        'name'    => 'additional.jrms-main-page1',
        'handler' => 'modules/additional/jrms_main_page1.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/home-jrms',
        'name'    => 'additional.home-jrms',
        'handler' => 'modules/additional/home_jrms.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-new-records-entry-uniqueness',
        'name'    => 'additional.jrms-new-records-entry-uniqueness',
        'handler' => 'modules/additional/jrms_new_records_entry_uniqueness.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-datewise-search-uniqueness',
        'name'    => 'additional.jrms-datewise-search-uniqueness',
        'handler' => 'modules/additional/jrms_datewise_search_uniqueness.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-cin-search-uniqueness',
        'name'    => 'additional.jrms-cin-search-uniqueness',
        'handler' => 'modules/additional/jrms_cin_search_uniqueness.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-search-by-prisonerno-uniqueness',
        'name'    => 'additional.jrms-search-by-prisonerno-uniqueness',
        'handler' => 'modules/additional/jrms_search_by_prisonerno_uniqueness.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-search-uniqueness',
        'name'    => 'additional.jrms-search-uniqueness',
        'handler' => 'modules/additional/jrms_search_uniqueness.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-unique-key-update',
        'name'    => 'additional.jrms-unique-key-update',
        'handler' => 'modules/additional/jrms_unique_key_update.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-search',
        'name'    => 'additional.jrms-search',
        'handler' => 'modules/additional/jrms_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-search-new',
        'name'    => 'additional.jrms-search-new',
        'handler' => 'modules/additional/jrms_search_new.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-search-for-uniquekey',
        'name'    => 'additional.jrms-search-for-uniquekey',
        'handler' => 'modules/additional/jrms_search_for_uniquekey.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/jrms-uniqueness-update',
        'name'    => 'additional.jrms-uniqueness-update',
        'handler' => 'modules/additional/jrms_uniqueness_update.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pdact-main-page-search',
        'name'    => 'additional.pdact-main-page-search',
        'handler' => 'modules/additional/pdact_main_page_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pdact-main-page',
        'name'    => 'additional.pdact-main-page',
        'handler' => 'modules/additional/pdact_main_page.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pdact-main',
        'name'    => 'additional.pdact-main',
        'handler' => 'modules/additional/pdact_main.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pdact-ps-wise-search-php',
        'name'    => 'additional.pdact-ps-wise-search-php',
        'handler' => 'modules/additional/pdact_ps_wise_search_php.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/tower-home',
        'name'    => 'additional.tower-home',
        'handler' => 'modules/additional/tower_home.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/towerdump-home',
        'name'    => 'additional.towerdump-home',
        'handler' => 'modules/additional/towerdump_home.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/suspect-search',
        'name'    => 'additional.suspect-search',
        'handler' => 'modules/additional/suspect_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/other-state-number',
        'name'    => 'additional.other-state-number',
        'handler' => 'modules/additional/other_state_number.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/inter-tower-calls',
        'name'    => 'additional.inter-tower-calls',
        'handler' => 'modules/additional/inter_tower_calls.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pre-off-search',
        'name'    => 'additional.pre-off-search',
        'handler' => 'modules/additional/pre_off_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/suspect-search-twr',
        'name'    => 'additional.suspect-search-twr',
        'handler' => 'modules/additional/suspect_search_twr.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/other-state-number-twr',
        'name'    => 'additional.other-state-number-twr',
        'handler' => 'modules/additional/other_state_number_twr.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/inter-tower-calls-twr',
        'name'    => 'additional.inter-tower-calls-twr',
        'handler' => 'modules/additional/inter_tower_calls_twr.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/pre-off-search-twr',
        'name'    => 'additional.pre-off-search-twr',
        'handler' => 'modules/additional/pre_off_search_twr.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dump',
        'name'    => 'additional.dump',
        'handler' => 'modules/additional/dump.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dump-search',
        'name'    => 'additional.dump-search',
        'handler' => 'modules/additional/dump_search.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/dump-analysis',
        'name'    => 'additional.dump-analysis',
        'handler' => 'modules/additional/dump_analysis.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/profile',
        'name'    => 'additional.profile',
        'handler' => 'view/profile.html',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/myindex',
        'name'    => 'additional.myindex',
        'handler' => 'modules/additional/myindex.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/demo-php',
        'name'    => 'additional.demo-php',
        'handler' => 'modules/additional/demo.php.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/chandu',
        'name'    => 'additional.chandu',
        'handler' => 'modules/additional/chandu.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/namesearch',
        'name'    => 'additional.namesearch',
        'handler' => 'modules/additional/namesearch.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/cis-data-name-search-php',
        'name'    => 'additional.cis-data-name-search-php',
        'handler' => 'modules/additional/cis_data_name_search_php.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/bulk-address',
        'name'    => 'additional.bulk-address',
        'handler' => 'modules/address/bulkaddress.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/sum-btwn-dates',
        'name'    => 'additional.sum-btwn-dates',
        'handler' => 'modules/summary/sum_between_dates.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/sum-new-no',
        'name'    => 'additional.sum-new-no',
        'handler' => 'modules/summary/sum_new_nos.php',
    ],
    [
        'method'  => ['GET', 'POST'],
        'path'    => '/additional/rowdysheeter-ps-wise-search-php',
        'name'    => 'additional.rowdysheeter-ps-wise-search-php',
        'handler' => 'modules/additional/rowdysheeter_ps_wise_search_php.php',
    ],

];
