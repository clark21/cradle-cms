<?php //-->
 return array (
  'singular' => 'File',
  'plural' => 'Files',
  'name' => 'file',
  'icon' => 'fas fa-file-alt',
  'detail' => 'Manages Files',
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
          'placeholder' => 'sample.docx',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Name is Required',
        ),
      ),
      'list' => 
      array (
        'format' => 'link',
        'parameters' => 
        array (
          0 => '/download?filename={{file_name}}&location={{file_data}}',
          1 => '{{file_name}}',
        ),
      ),
      'detail' => 
      array (
        'format' => 'link',
        'parameters' => 
        array (
          0 => '/download?filename={{file_name}}&location={{file_data}}',
          1 => '{{file_name}}',
        ),
      ),
      'default' => '',
      'searchable' => '1',
    ),
    1 => 
    array (
      'label' => 'Description',
      'name' => 'description',
      'field' => 
      array (
        'type' => 'textarea',
        'attributes' => 
        array (
          'placeholder' => 'Describe this file',
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
      'label' => 'Data',
      'name' => 'data',
      'field' => 
      array (
        'type' => 'file',
        'attributes' => 
        array (
          'accept' => 'image/*,text/*,application/vnd.*,application/pdf',
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
    3 => 
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
    4 => 
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
    5 => 
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
  'suggestion' => '{{file_name}}',
);