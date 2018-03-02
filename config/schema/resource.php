<?php //-->
 return array (
  'singular' => 'Resource',
  'plural' => 'Resources',
  'name' => 'resource',
  'icon' => 'fas fa-check',
  'detail' => 'Manages Resources',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Hours',
      'name' => 'hours',
      'field' => 
      array (
        'type' => 'price',
      ),
      'list' => 
      array (
        'format' => 'number',
      ),
      'detail' => 
      array (
        'format' => 'number',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    1 => 
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
    2 => 
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
    3 => 
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
    4 => 
    array (
      'label' => 'Start Date',
      'name' => 'start_date',
      'field' => 
      array (
        'type' => 'date',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'date',
          'message' => 'Invalid format',
        ),
        1 => 
        array (
          'method' => 'required',
          'message' => 'Start Date is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'detail' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'default' => '',
    ),
  ),
  'relations' => 
  array (
    0 => 
    array (
      'many' => '1',
      'name' => 'employee',
    ),
    1 => 
    array (
      'many' => '1',
      'name' => 'task',
    ),
  ),
  'suggestion' => 'WO {{task_wo}}: {{task_title}} - {{employee_first_name}} {{employee_first_name}} - {{resource_hours}}',
);