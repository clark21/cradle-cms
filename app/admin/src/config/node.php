<?php //-->
return [
    [
        'disable' => true,
        'label' => 'Image',
        'key' => 'node_image',
        'field' => [
            'type' => 'image',
            'attributes' => [
                'width' => 200,
                'height' => 200
            ]
        ],
        'validation' => [
            [
                'method' => 'regex',
                'message' => 'Should be a valid url',
                'parameters' => '/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]'
                .'*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i'
            ]
        ],
        'list' => [
            'format' => 'image',
            'parameters' => [100, 100]
        ],
        'detail' => [
            'format' => 'image',
            'parameters' => [100, 100]
        ],
        'searchable' => 0,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Title',
        'key' => 'node_title',
        'field' => [
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
            'format' => 'link',
            'parameters' => ['/node/{{node_slug}}', 'Click here']
        ],
        'detail' => [
            'format' => 'link',
            'parameters' => ['/node/{{node_slug}}', 'Click here']
        ],
        'searchable' => 1,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Slug',
        'key' => 'node_slug',
        'field' => [
            'type' => 'slug',
            'attributes' => [
                'placeholder' => 'Enter a Slug',
                'data-target' => 'title'
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
        'list' => [
            'format' => 'hide'
        ],
        'detail' => [
            'format' => 'hide'
        ],
        'searchable' => 1,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Detail',
        'key' => 'node_detail',
        'field' => [
            'type' => 'wysiwyg',
            'attributes' => [
                'placeholder' => 'Write about something'
            ]
        ],
        'validation' => [],
        'list' => [
            'format' => 'hide'
        ],
        'detail' => [
            'format' => 'hide'
        ],
        'searchable' => 1,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Tags',
        'key' => 'node_tags',
        'field' => [
            'type' => 'tag'
        ],
        'validation' => [
            [
                'method' => 'one',
                'parameters' => [
                    'foo',
                    'bar'
                ]
            ]
        ],
        'list' => [
            'format' => 'link',
            'parameters' => [
                'href' => '/node/search?product_tag=:product_tag'
            ]
        ],
        'detail' => [
            'format' => 'link',
            'parameters' => [
                'href' => '/node/search?product_tag=:product_tag'
            ]
        ],
        'searchable' => 0,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Files',
        'key' => 'node_files',
        'field' => [
            'type' => 'files'
        ],
        'validation' => [],
        'list' => [
            'format' => 'hide'
        ],
        'detail' => [
            'format' => 'hide'
        ],
        'searchable' => 0,
        'filterable' => 0,
        'sortable' => 0
    ],
    [
        'disable' => true,
        'label' => 'Status',
        'key' => 'node_status',
        'field' => [
            'type' => 'text',
            'attributes' => [
                'placeholder' => 'pending',
            ]
        ],
        'validation' => [],
        'list' => [
            'format' => 'capital'
        ],
        'detail' => [
            'format' => 'capital'
        ],
        'searchable' => 1,
        'filterable' => 1,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Published',
        'key' => 'node_published',
        'field' => [
            'type' => 'date'
        ],
        'validation' => [],
        'list' => [
            'format' => 'date',
            'parameters' => 'M d'
        ],
        'detail' => [
            'format' => 'date',
            'parameters' => 'F d, y g:iA'
        ],
        'default' => 'NOW()',
        'searchable' => 1,
        'filterable' => 0,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Type',
        'key' => 'node_type',
        'field' => [
            'type' => 'none'
        ],
        'validation' => [],
        'list' => [
            'format' => 'lower'
        ],
        'detail' => [
            'format' => 'lower'
        ],
        'default' => 'node',
        'searchable' => 1,
        'filterable' => 1,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Flag',
        'key' => 'node_flag',
        'field' => [
            'type' => 'small'
        ],
        'validation' => [
            [
                'method' => 'lt',
                'message' => 'Flag should be between 0 and 9',
                'parameters' => 10
            ],
            [
                'method' => 'gte',
                'message' => 'Flag should be between 0 and 9',
                'parameters' => 0
            ]
        ],
        'list' => [
            'format' => 'none'
        ],
        'detail' => [
            'format' => 'none'
        ],
        'default' => 0,
        'filterable' => 1,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Created',
        'key' => 'node_created',
        'field' => [
            'type' => 'none'
        ],
        'validation' => [],
        'list' => [
            'format' => 'date',
            'parameters' => 'M d'
        ],
        'detail' => [
            'format' => 'date',
            'parameters' => 'F d, y g:iA'
        ],
        'filterable' => 0,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Updated',
        'key' => 'node_updated',
        'field' => [
            'type' => 'none'
        ],
        'validation' => [],
        'list' => [
            'format' => 'date',
            'parameters' => 'M d'
        ],
        'detail' => [
            'format' => 'date',
            'parameters' => 'F d, y g:iA'
        ],
        'filterable' => 0,
        'sortable' => 1
    ]
];
