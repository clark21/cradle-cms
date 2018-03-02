<?php //-->
 return array (
  'singular' => 'Department',
  'plural' => 'Departments',
  'name' => 'department',
  'icon' => 'fas fa-sitemap',
  'detail' => 'Manages Departments',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Title',
      'name' => 'title',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Title is required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Title cannot be empty',
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
      'filterable' => '1',
    ),
    1 => 
    array (
      'label' => 'Description',
      'name' => 'description',
      'field' => 
      array (
        'type' => 'textarea',
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
    2 => 
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
    3 => 
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
    4 => 
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
      'name' => 'department',
    ),
  ),
  'suggestion' => '{{department_title}}',
);