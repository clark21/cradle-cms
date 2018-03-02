<?php //-->
 return array (
  'singular' => 'Employee',
  'plural' => 'Employees',
  'name' => 'employee',
  'icon' => 'fas fa-user-circle',
  'detail' => 'Manages Employees',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Photo',
      'name' => 'photo',
      'field' => 
      array (
        'type' => 'image',
        'attributes' => 
        array (
          'data-width' => '200',
          'data-height' => '200',
        ),
      ),
      'list' => 
      array (
        'format' => 'image',
        'parameters' => 
        array (
          0 => '100',
          1 => '100',
        ),
      ),
      'detail' => 
      array (
        'format' => 'image',
        'parameters' => 
        array (
          0 => '200',
          1 => '200',
        ),
      ),
      'default' => '',
    ),
    1 => 
    array (
      'label' => 'First Name',
      'name' => 'first_name',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'First Name is required',
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
      'searchable' => '1',
    ),
    2 => 
    array (
      'label' => 'Middle Name',
      'name' => 'middle_name',
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
      'searchable' => '1',
    ),
    3 => 
    array (
      'label' => 'Last Name',
      'name' => 'last_name',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Last name is required',
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
      'searchable' => '1',
    ),
    4 => 
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
          'method' => 'required',
          'message' => 'Email is required',
        ),
        1 => 
        array (
          'method' => 'email',
          'message' => 'Invalid email',
        ),
      ),
      'list' => 
      array (
        'format' => 'email',
        'parameters' => '{{employee_email}}',
      ),
      'detail' => 
      array (
        'format' => 'email',
        'parameters' => '{{employee_email}}',
      ),
      'default' => '',
      'searchable' => '1',
    ),
    5 => 
    array (
      'label' => 'Phone',
      'name' => 'phone',
      'field' => 
      array (
        'type' => 'mask',
        'attributes' => 
        array (
          'data-format' => '(999) 999-9999',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'regex',
          'parameters' => '\\([0-9]{3}\\) [0-9]{3}\\-[0-9]{4}',
          'message' => 'Invalid Format',
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
    6 => 
    array (
      'label' => 'TIN Number',
      'name' => 'tin_number',
      'field' => 
      array (
        'type' => 'mask',
        'attributes' => 
        array (
          'data-format' => '999-999-999-999',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'regex',
          'parameters' => '[0-9]{3}\\-[0-9]{3}\\-[0-9]{3}\\-[0-9]{3}',
          'message' => 'Invalid Format',
        ),
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
    7 => 
    array (
      'label' => 'SSS Number',
      'name' => 'sss_number',
      'field' => 
      array (
        'type' => 'mask',
        'attributes' => 
        array (
          'data-format' => '999-99-9999',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'regex',
          'parameters' => '[0-9]{3}\\-[0-9]{2}\\-[0-9]{4}',
          'message' => 'Invalid Format',
        ),
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
    8 => 
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
    9 => 
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
    10 => 
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
      'many' => '1',
      'name' => 'position',
    ),
    1 => 
    array (
      'many' => '1',
      'name' => 'salary',
    ),
    2 => 
    array (
      'many' => '0',
      'name' => 'user',
    ),
    3 => 
    array (
      'many' => '2',
      'name' => 'file',
    ),
  ),
  'suggestion' => '{{employee_first_name}} {{employee_flast_name}}',
);