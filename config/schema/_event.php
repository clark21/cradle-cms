<?php //-->
 return array (
  'singular' => 'Event',
  'plural' => 'Events',
  'name' => 'event',
  'icon' => 'fas fa-calendar-alt',
  'detail' => 'This is for managing events',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Name',
      'name' => 'name',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'placeholder' => 'Event Name',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Name is Required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Cannot be empty',
        ),
      ),
      'list' => 
      array (
        'format' => 'capital',
      ),
      'detail' => 
      array (
        'format' => 'capital',
      ),
      'default' => '',
      'searchable' => '1',
    ),
    1 => 
    array (
      'label' => 'Detail',
      'name' => 'detail',
      'field' => 
      array (
        'type' => 'wysiwyg',
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
          'method' => 'required',
          'message' => 'Start date is required',
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
      'default' => 'NOW()',
      'filterable' => '1',
      'sortable' => '1',
    ),
    3 => 
    array (
      'label' => 'End Date',
      'name' => 'end_date',
      'field' => 
      array (
        'type' => 'date',
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
      'default' => 'NOW()',
      'filterable' => '1',
      'sortable' => '1',
    ),
    4 => 
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
    5 => 
    array (
      'label' => 'Created',
      'name' => 'created',
      'field' => 
      array (
        'type' => 'created',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
    6 => 
    array (
      'label' => 'Updated',
      'name' => 'updated',
      'field' => 
      array (
        'type' => 'updated',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
  ),
  'suggestion' => '',
);