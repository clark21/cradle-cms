<?php //-->
return [
    'singular' => 'Node',
    'plural' => 'Nodes',
    'primary' => 'node_id',
    'active' => 'node_active',
    'created' => 'node_created',
    'updated' => 'node_updated',
    'relations' => [
        'user' => [
            'primary' => 'user_id',
            'many' => false
        ],
        'node' => [
            'primary' => 'node_id',
            'many' => true
        ]
    ],
    'fields' => [
        'node_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Image',
                'type' => 'image-field',
                'attributes' => [
                    'data-do' => 'image-field',
                ]
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Should be a valid url',
                    'parameters' => '/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]'
                    .'*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i'
                ]
            ],
            'list' => [
                'label' => 'Image',
                'format' => 'image',
                'parameters' => [100]
            ],
            'detail' => [
                'label' => 'Image',
                'format' => 'image',
                'parameters' => [100]
            ],
            'test' => [
                'pass' => 'https://www.google.com.ph/images/branding/googlelogo/1x/googlelogo_color_272x92dp.png',
                'fail' => 'not a good image',
            ]
        ],
        'node_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 254,
                'required' => true,
                'index' => true,
                'searchable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Title',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a Title',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ],
                [
                    'method' => 'char_gt',
                    'message' => 'Title should be longer than 10 characters',
                    'parameters' => 10
                ],
                [
                    'method' => 'char_lt',
                    'message' => 'Title should be less than 255 characters',
                    'parameters' => 255
                ]
            ],
            'list' => [
                'label' => 'Title',
                'format' => 'link',
                'parameters' => [
                    'href' => '/node/{{node_slug}}',
                    'target' => '_blank'
                ]
            ],
            'test' => [
                'pass' => 'Foobar Title',
                'fail' => 'Foobar'
            ]
        ],
        'node_slug' => [
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
                'label' => 'Slug',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Enter a unique SEO slug',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Slug is required'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Slug must only have letters, numbers, dashes',
                    'parameters' => '#^[a-zA-Z0-9\-_]+$#'
                ],
                [
                    'method' => 'unique',
                    'message' => 'Slug must be unique'
                ]
            ],
            'test' => [
                'pass' => 'a-Good-slug_1',
                'fail' => 'not a good slug'
            ]
        ],
        'node_detail' => [
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
        'node_tags' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Tags',
                'type' => 'tag-field'
            ],
            'detail' => [
                'label' => 'Tags',
                'format' => 'link',
                'parameters' => [
                    'href' => '/node/search?product_tag=:product_tag'
                ]
            ]
        ],
        'node_meta' => [
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
        'node_files' => [
            'sql' => [
                'type' => 'json'
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Files',
                'type' => 'files-field'
            ]
        ],
        'node_status' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'default' => 'pending',
                'searchable' => true,
                'sortable' => true,
                'filterable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Type',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'pending',
                ]
            ],
            'list' => [
                'label' => 'Status',
                'format' => 'capital'
            ]
        ],
        'node_published' => [
            'sql' => [
                'type' => 'datetime',
                'sortable' => true
            ],
            'elastic' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss'
            ],
            'form' => [
                'label' => 'Published Date',
                'type' => 'date',
                'default' => 'NOW()'
            ],
            'list' => [
                'label' => 'Published',
                'searchable' => true,
                'sortable' => true,
                'format' => 'date',
                'parameters' => 'M d'
            ],
            'detail' => [
                'label' => 'Published On',
                'format' => 'date',
                'parameters' => 'F d, y g:iA'
            ]
        ],
        'node_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'filterable' => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'list' => [
                'label' => 'Type'
            ],
        ],
        'node_flag' => [
            'sql' => [
                'type' => 'int',
                'length' => 1,
                'default' => 0,
                'attribute' => 'unsigned'
            ],
            'elastic' => [
                'type' => 'integer'
            ],
            'form' => [
                'label' => 'Flag',
                'type' => 'number',
                'attributes' => [
                    'step' => '1',
                    'placeholder' => '0'
                ]
            ],
            'list' => [
                'label' => 'Flag'
            ]
        ]
    ],
    'fixtures' => []
];
