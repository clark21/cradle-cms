<?php //-->
 return array (
  'disable' => '1',
  'singular' => 'User',
  'plural' => 'Users',
  'name' => 'user',
  'icon' => 'fas fa-user',
  'detail' => 'Manages Logged In Users',
  'fields' =>
  array (
    0 =>
    array (
      'disable' => '1',
      'label' => 'Name',
      'name' => 'name',
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
      'searchable' => '1',
      'filterable' => '1',
    ),
    1 =>
    array (
      'disable' => '1',
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
      'disable' => '1',
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
    3 =>
    array (
      'disable' => '1',
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
  'relations' =>
  array (
  ),
  'suggestion' => '{{user_name}}',
);
