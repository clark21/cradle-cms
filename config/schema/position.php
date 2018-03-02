<?php //-->
 return array (
  'singular' => 'Position',
  'plural' => 'Positions',
  'name' => 'position',
  'icon' => 'fas fa-graduation-cap',
  'detail' => 'Manages employee positions',
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
        'type' => 'wysiwyg',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Description is required',
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
      'searchable' => '1',
    ),
    2 => 
    array (
      'label' => 'Qualifications',
      'name' => 'qualifications',
      'field' => 
      array (
        'type' => 'wysiwyg',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Qualifications are required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Qualifications cannot be empty',
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
      'searchable' => '1',
    ),
    3 => 
    array (
      'label' => 'Responsibilities',
      'name' => 'responsibilities',
      'field' => 
      array (
        'type' => 'wysiwyg',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Responsibilities is required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Responsibilities cannot be empty',
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
      'searchable' => '1',
    ),
    4 => 
    array (
      'label' => 'Persona',
      'name' => 'persona',
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
    ),
    5 => 
    array (
      'label' => 'Minimum Salary',
      'name' => 'minimum_salary',
      'field' => 
      array (
        'type' => 'number',
        'attributes' => 
        array (
          'min' => '0',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid number',
        ),
        1 => 
        array (
          'method' => 'gt',
          'parameters' => '0',
          'message' => 'Should be greater than 0',
        ),
      ),
      'list' => 
      array (
        'format' => 'number',
      ),
      'detail' => 
      array (
        'format' => 'number',
      ),
      'default' => '0',
      'filterable' => '1',
      'sortable' => '1',
    ),
    6 => 
    array (
      'label' => 'Maximum Salary',
      'name' => 'maximum_salary',
      'field' => 
      array (
        'type' => 'number',
        'attributes' => 
        array (
          'min' => '0',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid number',
        ),
        1 => 
        array (
          'method' => 'gt',
          'parameters' => '0',
          'message' => 'Should be greater than 0',
        ),
      ),
      'list' => 
      array (
        'format' => 'number',
      ),
      'detail' => 
      array (
        'format' => 'number',
      ),
      'default' => '0',
      'filterable' => '1',
      'sortable' => '1',
    ),
    7 => 
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
    8 => 
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
    9 => 
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
      'name' => 'department',
    ),
  ),
  'suggestion' => '{{position_title}}',
);