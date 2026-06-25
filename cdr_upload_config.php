<?php
/**
 * cdr_upload_config.php
 * Simplified Configuration for the Common Data Upload Framework.
 * Maps modules directly to existing CDAT database tables.
 */

return [
    'cdatpcsuspect' => [
        'name' => 'CDR Call Logs',
        'table' => 'cdatpcsuspect',
        'description' => 'Upload raw call records (CDR) including suspect phone, caller, callee, duration, time, IMEI, and tower details.',
        'columns' => [
            'phone' => ['type' => 'phone'],
            'other' => ['type' => 'phone'],
            'incoming' => ['type' => 'incoming'],
            'starttime' => ['type' => 'datetime'],
            'duration' => ['type' => 'integer'],
            'imeinumber' => ['type' => 'string'],
            'celltowerid' => ['type' => 'string'],
            'state_key' => ['type' => 'integer'],
            'provider_key' => ['type' => 'integer']
        ]
    ],
    'cdataddress' => [
        'name' => 'Subscriber Directory',
        'table' => 'cdataddress',
        'description' => 'Upload subscriber directory records to search and resolve addresses of suspect or caller numbers.',
        'columns' => [
            'phone' => ['type' => 'phone'],
            'fullname' => ['type' => 'string'],
            'fulladdress' => ['type' => 'string'],
            'doa' => ['type' => 'datetime'],
            'category_type' => ['type' => 'string'],
            'eff_from_date' => ['type' => 'datetime'],
            'eff_to_date' => ['type' => 'datetime']
        ]
    ],
    'cdatcelltowerareanew' => [
        'name' => 'Cell Tower Directory',
        'table' => 'cdatcelltowerareanew',
        'description' => 'Upload cell tower records defining tower cell IDs, coordinates, operators, and state details.',
        'columns' => [
            'celltowerid' => ['type' => 'string'],
            'bts_id' => ['type' => 'string'],
            'operator' => ['type' => 'string'],
            'state' => ['type' => 'string'],
            'siteaddress' => ['type' => 'string'],
            'lat' => ['type' => 'string'],
            'long' => ['type' => 'string'],
            'azimuth' => ['type' => 'string'],
            'state_key' => ['type' => 'integer'],
            'provider_key' => ['type' => 'integer'],
            'lastupdate' => ['type' => 'datetime']
        ]
    ],
    'cdatsuspect' => [
        'name' => 'Suspect Profiles',
        'table' => 'cdatsuspect',
        'description' => 'Upload suspect records to define nicknames, roles, MOs, and investigating officers.',
        'columns' => [
            'phone' => ['type' => 'phone'],
            'nickname' => ['type' => 'string'],
            'role' => ['type' => 'string'],
            'mo' => ['type' => 'string'],
            'category' => ['type' => 'string'],
            'inc_officer' => ['type' => 'string']
        ]
    ]
];
