<?php //-->
return [
    'singular'  => 'Role',
    'plural'    => 'Roles',
    'primary'   => 'role_id',
    'active'    => 'role_active',
    'created'   => 'role_created',
    'updated'   => 'role_updated',
    'relations' => [
        'history' => [
            'primary' => 'history_id',
            'many' => true
        ]
    ],
    'fields'    => [
        'role_name' => [
            'sql' => [
                'type'      => 'varchar',
                'length'    => 255,
                'required'  => true,
                'index'         => true,
                'searchable'    => true,
                'sortable'      => true,
                'filterable'    => true
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
                'label' => 'Role Name',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Administrator',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Role Name is required'
                ]
            ],
            'list' => [
                'label' => 'Name'
            ],
            'test' => [
                'pass' => 'Apple',
                'fail' => ''
            ]
        ],
        'role_permissions' => [
            'sql' => [
                'type'      => 'json',
                'required'  => true
            ],
            'elastic' => [
                'type' => 'string'
            ],
            'form' => [
                'label' => 'Permissions',
                'type' => 'text',
                'attributes' => [
                    'placeholder' => 'Permissions',
                ]
            ]
        ],
        'role_type' => [
            'sql' => [
                'type'      => 'varchar',
                'length'    => 255,
                'required'  => false,
                'index'     => false
            ]
        ],
        'role_flag' => [
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
            'role_name' => 'Super Admin',
            'role_permissions' => json_encode(
                [
                    'user:create',
                    'user:update',
                    'user:remove',
                    'user:restore',
                    'auth:create',
                    'auth:update',
                    'auth:remove',
                    'auth:restore',
                    'role:create',
                    'role:update',
                    'role:remove',
                    'role:restore',
                    'schema:create',
                    'schema:update',
                    'schema:remove',
                    'schema:restore',
                    'object:create',
                    'object:update',
                    'object:remove',
                    'object:restore',
                    'object:export',
                    'object:import',
                ]
            ),
            'role_type' => 'admin',
            'role_created' => date('Y-m-d h:i:s'),
            'role_updated' => date('Y-m-d h:i:s')
        ]
    ]
];
