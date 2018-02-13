<?php //-->
 return array (
  'singular' => 'Transaction',
  'plural' => 'transactions',
  'name' => 'transaction',
  'detail' => 'Table for transactions',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'id',
      'name' => 'ids',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'unique',
          'message' => 'Post id is required',
        ),
      ),
      'list' => 
      array (
        'format' => 'upper',
      ),
      'detail' => 
      array (
        'format' => 'upper',
      ),
      'default' => '0',
    ),
  ),
);