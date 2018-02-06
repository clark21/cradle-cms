<?php //-->
return [
    'singular' => 'Authentication',
    'plural' => 'Authentications',
    'primary' => 'auth_id',
    'active' => 'auth_active',
    'created' => 'auth_created',
    'updated' => 'auth_updated',
    'relations' => [
        'user' => [
            'primary' => 'user_id',
            'many' => false
        ]
    ],
    'fields' => [
        'auth_slug' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'unique' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Email',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'john@doe.com',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Email is required'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Must be a valid email',
                    'parameters' => '/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]'.
                    '\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]'.
                    '\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62'.
                    '}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|'.
                    '[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})'.
                    '(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9]\.?)+\.(?:xn--[a-zA-Z0-9]'.
                    '+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25'.
                    '[0-5])){3}\])$/'
                ],
                [
                    'method' => 'unique',
                    'message' => 'Email is already taken'
                ]
            ],
            'test' => [
                'pass' => 'john@doe.com',
                'fail' => 'not a good slug'
            ]
        ],
        'auth_password' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'encoding' => 'md5',
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Password',
                'type' => 'password',
                'attributes' => [
                    'placeholder' => 'Enter a password',
                ]
            ],
            'validation' => [
                [
                    'method' => 'char_gt',
                    'message' => 'Must be at least 8 characters',
                    'parameters' => 7
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Should have at least one number',
                    'parameters' => '#\d#'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Should have at least one capital letter',
                    'parameters' => '#[A-Z]#'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Should have at least one lower case letter',
                    'parameters' => '#[a-z]#'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Should have at least one non letter/number',
                    'parameters' => '#[^a-zA-Z0-9]#'
                ]
            ],
            'test' => [
                'pass' => 'a g00d Password',
                'fail' => 'not a good password'
            ]
        ],
        'auth_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'auth_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ]
    ],
    'fixtures' => [
        [
            'auth_id' => 1,
            'user_id' => 1,
            'auth_slug' => 'john@doe.com',
            'auth_password' => '202cb962ac59075b964b07152d234b70',
            'auth_type' => 'admin',
            'auth_created' => date('Y-m-d h:i:s'),
            'auth_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
