<?php //-->
return [
    'singular' => 'Meta',
    'plural' => 'Meta',
    'primary' => 'meta_id',
    'active' => 'meta_active',
    'created' => 'meta_created',
    'updated' => 'meta_updated',
    'relations' => [],
    'fields' => [
        'meta_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'post',
                'searchable' => true,
                'sortable' => true,
                'filterable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Type',
                'type' => 'select',
                'options' => [
                    'meta' => 'Post',
                    'user' => 'User'
                ]
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'message' => 'Must choose a valid type',
                    'parameters' => [
                        'node',
                        'user'
                    ]
                ]
            ],
            'list' => [
                'label' => 'Type'
            ]
        ],
        'meta_singular' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Singular',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a Singular',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Singular is required'
                ],
                [
                    'method' => 'char_gt',
                    'message' => 'Singular should be longer than 3 characters',
                    'parameters' => 3
                ],
                [
                    'method' => 'char_lt',
                    'message' => 'Singular should be less than 255 characters',
                    'parameters' => 255
                ]
            ],
            'test' => [
                'pass' => 'Foobar Singular',
                'fail' => 'Foobar'
            ]
        ],
        'meta_plural' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Plural',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a Plural',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Plural is required'
                ],
                [
                    'method' => 'char_gt',
                    'message' => 'Plural should be longer than 3 characters',
                    'parameters' => 3
                ],
                [
                    'method' => 'char_lt',
                    'message' => 'Plural should be less than 255 characters',
                    'parameters' => 255
                ]
            ],
            'test' => [
                'pass' => 'Foobar Plural',
                'fail' => 'Foobar'
            ]
        ],
        'meta_key' => [
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
                'label' => 'Keyword',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a unique SEO slug',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Keyword is required'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Keyword must only have letters, numbers, dashes',
                    'parameters' => '#^[a-zA-Z0-9\-_]+$#'
                ],
                [
                    'method' => 'unique',
                    'message' => 'Keyword must be unique'
                ]
            ],
            'test' => [
                'pass' => 'a-Good-slug_1',
                'fail' => 'not a good slug'
            ]
        ],
        'meta_detail' => [
            'sql' => [
                'type' => 'text',
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'text',
                'fields' => [
                    'keyword' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'form' => [
                'label' => 'Detail',
                'type' => 'textarea',
                'attributes' => [
                    'placeholder' => 'Write about something',
                ]
            ]
        ],
        'meta_fields' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'object'
            ],
            'form' => [
                'label' => 'Fields',
                'type' => 'meta-field',
                'attributes' => [
                    'data-do' => 'meta-field',
                ]
            ],
        ],
        'meta_flag' => [
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
    'fixtures' => []
];
