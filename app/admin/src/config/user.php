<?php //-->
return [
    [
        'disable' => true,
        'label' => 'Name',
        'key' => 'user_name',
        'field' => [
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
            'format' => 'none'
        ],
        'detail' => [
            'format' => 'none'
        ],
        'searchable' => 1,
        'filterable' => 0,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Slug',
        'key' => 'user_slug',
        'field' => [
            'type' => 'none'
        ],
        'validation' => [
            [
                'method' => 'regexp',
                'message' => 'Slug must only have letters, numbers, dashes',
                'parameters' => '#^[a-zA-Z0-9\-_]+$#'
            ]
        ],
        'list' => [
            'format' => 'hide'
        ],
        'detail' => [
            'format' => 'hide'
        ],
        'searchable' => 1,
        'filterable' => 1,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Type',
        'key' => 'user_type',
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
        'default' => 'user',
        'searchable' => 1,
        'filterable' => 1,
        'sortable' => 1
    ],
    [
        'disable' => true,
        'label' => 'Flag',
        'key' => 'user_flag',
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
        'key' => 'user_created',
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
        'key' => 'user_updated',
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
