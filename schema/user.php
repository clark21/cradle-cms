<?php //-->
return [
    'singular' => 'User',
    'plural' => 'Users',
    'primary' => 'user_id',
    'active' => 'user_active',
    'created' => 'user_created',
    'updated' => 'user_updated',
    'relations' => [
        'user' => [
            'primary' => 'user_id',
            'many' => true
        ],
        'node' => [
            'primary' => 'node_id',
            'many' => true
        ]
    ],
    'fields' => [
        'user_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'form' => [
                'label' => 'Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'John Doe',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Name is required'
                ]
            ],
            'list' => [
                'label' => 'Name',
                'searchable' => true,
                'sortable' => true
            ],
            'detail' => [
                'label' => 'Name'
            ],
            'test' => [
                'pass' => 'John Doe',
                'fail' => ''
            ]
        ],
        'user_slug' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Slug must only have letters, numbers, dashes',
                    'parameters' => '#^[a-zA-Z0-9\-_]+$#'
                ]
            ],
            'test' => [
                'pass' => 'a-Good-slug_1',
                'fail' => 'not a good slug'
            ]
        ],
        'user_meta' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'object'
            ],
            'form' => [
                'label' => 'Meta Data',
                'type' => 'meta-field',
                'attributes' => [
                    'data-do' => 'meta-field',
                ]
            ],
        ],
        'user_files' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Files',
                'type' => 'file',
                'attributes' => [
                    'multiple' => 'multiple',
                ]
            ]
        ],
        'user_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true
            ],
            'elastic' => [
                'type' => 'string'
            ]
        ],
        'user_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => '0',
                'index' => true,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ]
        ]
    ],
    'fixtures' => [
        [
            'user_name' => 'John Doe',
            'user_email' => 'john@acme.com',
            'user_phone' => '555-2424',
            'user_image' => '/images/default-avatar.png',
            'user_created' => date('Y-m-d h:i:s'),
            'user_updated' => date('Y-m-d h:i:s')
        ],
    ]
];
