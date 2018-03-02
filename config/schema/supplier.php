<?php //-->
 return array (
  'singular' => 'Supplier',
  'plural' => 'Suppliers',
  'name' => 'supplier',
  'icon' => 'fa fa-user',
  'detail' => 'Manages Suppliers',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Name',
      'name' => 'name',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Name is required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Cannot be empty',
        ),
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    1 => 
    array (
      'label' => 'Company',
      'name' => 'company',
      'field' => 
      array (
        'type' => 'none',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    2 => 
    array (
      'label' => 'Email',
      'name' => 'email',
      'field' => 
      array (
        'type' => 'email',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'email',
          'message' => 'Invalid Format',
        ),
      ),
      'list' => 
      array (
        'format' => 'email',
        'parameters' => '{{customer_email}}',
      ),
      'detail' => 
      array (
        'format' => 'email',
        'parameters' => '{{customer_email}}',
      ),
      'default' => '',
    ),
    3 => 
    array (
      'label' => 'Phone',
      'name' => 'phone',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    4 => 
    array (
      'label' => 'Billing Address 1',
      'name' => 'billing_address_1',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'hide',
      ),
      'detail' => 
      array (
        'format' => 'hide',
      ),
      'default' => '',
    ),
    5 => 
    array (
      'label' => 'Billing Address 2',
      'name' => 'billing_address_2',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'hide',
      ),
      'detail' => 
      array (
        'format' => 'hide',
      ),
      'default' => '',
    ),
    6 => 
    array (
      'label' => 'Billing City',
      'name' => 'billing_city',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    7 => 
    array (
      'label' => 'Billing State',
      'name' => 'billing_state',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    8 => 
    array (
      'label' => 'Billing Country',
      'name' => 'billing_country',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    9 => 
    array (
      'label' => 'Billing Postal',
      'name' => 'billing_postal',
      'field' => 
      array (
        'type' => 'text',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '',
    ),
    10 => 
    array (
      'label' => 'Active',
      'name' => 'active',
      'field' => 
      array (
        'type' => 'active',
      ),
      'list' => 
      array (
        'format' => 'hide',
      ),
      'detail' => 
      array (
        'format' => 'hide',
      ),
      'default' => '1',
      'filterable' => '1',
      'sortable' => '1',
    ),
    11 => 
    array (
      'label' => 'Created',
      'name' => 'created',
      'field' => 
      array (
        'type' => 'created',
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'detail' => 
      array (
        'format' => 'date',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
    12 => 
    array (
      'label' => 'Updated',
      'name' => 'updated',
      'field' => 
      array (
        'type' => 'updated',
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'detail' => 
      array (
        'format' => 'date',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
  ),
  'relations' => 
  array (
    0 => 
    array (
      'many' => '2',
      'name' => 'file',
    ),
  ),
  'suggestion' => '{{supplier_company}} - {{supplier_name}}',
);