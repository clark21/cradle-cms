<?php //-->
return [
    'singular' => 'History',
    'plural' => 'History',
    'primary' => 'history_id',
    'active' => 'history_active',
    'created' => 'history_created',
    'updated' => 'history_updated',
    'relations' => [
        'user' => [
            'primary' => 'user_id',
            'many' => false
        ]
    ],
    'fields' => [
        'history_remote_address' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 50,
                'default' => null,
                'filterable' => true,
                'key' => true
            ],
            'test' => [
                'pass' => 'pending',
                'fail' => ''
            ]
        ],
        'history_activity' => [
            'sql' => [
                'type' => 'text',
                'length' => 1000,
                'attribute' => 'CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                'required' => true,
                'sortable' => true,
                'filterable' => true,
            ],
            'validation' =>  [
                [
                    'method' => 'required',
                    'message' => 'History Activity is required'
                ],
                [
                    'method' => 'empty',
                    'message' => 'History Activity cannot be empty'
                ]
            ],
            'test' => [
                'pass' => 'admin created a cashback',
                'fail' => ''
            ]
        ],
        'history_page' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => null,
                'key' => true
            ],
            'test' => [
                'pass' => '',
            ]
        ],
        'history_meta' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'object'
            ],
        ],
        'history_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => null,
                'key' => true
            ],
            'test' => [
                'pass' => '',
            ]
        ],
        'history_flag' => [
            'sql' => [
                'type' => 'tinyint',
                'length' => 1,
                'required' => false,
                'default' => 0,
                'index' => true
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'parameters' => [1,0],
                    'message' => 'Flag should be specified.'
                ]
            ],
            'test' => [
                'pass' => 0,
                'fail' => 5
            ]
        ]
    ]
];
