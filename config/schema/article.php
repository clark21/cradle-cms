<?php //-->
 return array (
  'singular' => 'Article',
  'plural' => 'Articles',
  'name' => 'article',
  'detail' => 'This is for Articles na',
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
      'label' => 'Detail',
      'name' => 'detail',
      'field' => 
      array (
        'type' => 'wysiwyg',
        'attributes' => 
        array (
          'rows' => '10',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Detail is required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Cannot be empty',
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
    2 => 
    array (
      'label' => 'Status',
      'name' => 'status',
      'field' => 
      array (
        'type' => 'select',
        'options' => 
        array (
          0 => 
          array (
            'key' => 'PENDING',
            'value' => 'Pending',
          ),
          1 => 
          array (
            'key' => 'PUBLISHED',
            'value' => 'Published',
          ),
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'one',
          'parameters' => 
          array (
            0 => 'PENDING',
            1 => 'PUBLISHED',
          ),
          'message' => 'Invalid Option',
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
      'default' => 'PENDING',
    ),
    3 => 
    array (
      'label' => 'Published',
      'name' => 'published',
      'field' => 
      array (
        'type' => 'datetime',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'regex',
          'parameters' => '^[0-9]{4}\\-[0-9]{2}\\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$',
          'message' => 'Invalid Date',
        ),
      ),
      'list' => 
      array (
        'format' => 'relative',
        'parameters' => 'F d, Y',
      ),
      'detail' => 
      array (
        'format' => 'date',
        'parameters' => 'F d, Y',
      ),
      'default' => 'NOW()',
      'sortable' => '1',
    ),
    4 => 
    array (
      'label' => 'Author',
      'name' => 'author',
      'field' => 
      array (
        'type' => 'text',
        'attributes' => 
        array (
          'data-do' => 'capital',
          'data-on' => 'change',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Author is required',
        ),
        1 => 
        array (
          'method' => 'empty',
          'message' => 'Cannot be empty',
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
    5 => 
    array (
      'label' => 'Active',
      'name' => 'active',
      'field' => 
      array (
        'type' => 'active',
      ),
      'list' => 
      array (
        'format' => 'yes',
      ),
      'detail' => 
      array (
        'format' => 'yes',
      ),
      'default' => '1',
      'filterable' => '1',
      'sortable' => '1',
    ),
    6 => 
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
    7 => 
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
      'many' => '0',
      'name' => 'user',
    ),
  ),
);