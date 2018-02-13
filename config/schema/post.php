<?php //-->
 return array (
  'singular' => 'Post',
  'plural' => 'Posts',
  'name' => 'post',
  'detail' => 'This is where post detail stored.',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'name',
      'name' => 'name',
      'field' => 
      array (
        'type' => 'none',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Post name is required',
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
      'label' => 'like',
      'name' => 'likes',
      'field' => 
      array (
        'type' => 'number',
      ),
      'list' => 
      array (
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '0',
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
        'format' => 'none',
      ),
      'detail' => 
      array (
        'format' => 'none',
      ),
      'default' => '1',
      'filterable' => '1',
      'sortable' => '1',
    ),
  ),
);