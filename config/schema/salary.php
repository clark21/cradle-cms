<?php //-->
 return array (
  'singular' => 'Salary',
  'plural' => 'Salaries',
  'name' => 'salary',
  'icon' => 'fas fa-money-bill-alt',
  'detail' => 'Manages Salary Computations',
  'fields' => 
  array (
    0 => 
    array (
      'label' => 'Label',
      'name' => 'label',
      'field' => 
      array (
        'type' => 'text',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Label is required',
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
    1 => 
    array (
      'label' => 'Basic Salary',
      'name' => 'basic_salary',
      'field' => 
      array (
        'type' => 'price',
        'attributes' => 
        array (
          'min' => '0',
        ),
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'required',
          'message' => 'Salary is required',
        ),
        1 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid number',
        ),
        2 => 
        array (
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    2 => 
    array (
      'label' => 'SSS',
      'name' => 'sss',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    3 => 
    array (
      'label' => 'De Minimis',
      'name' => 'de_minimis',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    4 => 
    array (
      'label' => 'HDMF',
      'name' => 'hdmf',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    5 => 
    array (
      'label' => 'PHIC',
      'name' => 'phic',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    6 => 
    array (
      'label' => 'Health Card',
      'name' => 'health_card',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    7 => 
    array (
      'label' => 'Laptop Allowance',
      'name' => 'laptop_allowance',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    8 => 
    array (
      'label' => 'Mobile Allowance',
      'name' => 'mobile_allowance',
      'field' => 
      array (
        'type' => 'price',
      ),
      'validation' => 
      array (
        0 => 
        array (
          'method' => 'number',
          'message' => 'Should be a valid amount',
        ),
        1 => 
        array (
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    9 => 
    array (
      'label' => 'Transportation Allowance',
      'name' => 'transportation_allowance',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    10 => 
    array (
      'label' => 'Meal Allowance',
      'name' => 'meal_allowance',
      'field' => 
      array (
        'type' => 'price',
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
          'method' => 'gte',
          'parameters' => '0',
          'message' => 'Should be a positive number',
        ),
      ),
      'list' => 
      array (
        'format' => 'price',
      ),
      'detail' => 
      array (
        'format' => 'price',
      ),
      'default' => '0.00',
      'filterable' => '1',
      'sortable' => '1',
    ),
    11 => 
    array (
      'label' => 'Type',
      'name' => 'type',
      'field' => 
      array (
        'type' => 'select',
        'options' => 
        array (
          0 => 
          array (
            'key' => 'opex',
            'value' => 'Operational Expense',
          ),
          1 => 
          array (
            'key' => 'cos',
            'value' => 'Cost of Sale',
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
            0 => 'opex',
            1 => 'cos',
          ),
          'message' => 'Should be Operational Expense or Cost of Sale',
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
      'default' => 'opex',
      'searchable' => '1',
      'filterable' => '1',
    ),
    12 => 
    array (
      'label' => 'Start Date',
      'name' => 'start_date',
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
    13 => 
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
      'default' => '',
      'filterable' => '1',
      'sortable' => '1',
    ),
    14 => 
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
    15 => 
    array (
      'label' => 'Gross Pay',
      'name' => 'gross_pay',
      'field' => 
      array (
        'type' => 'none',
      ),
      'list' => 
      array (
        'format' => 'formula',
        'parameters' => 'ceil(({{salary_basic_salary}} + {{salary_sss}}) / {{salary_phic}})',
      ),
      'detail' => 
      array (
        'format' => 'formula',
      ),
      'default' => '',
    ),
    16 => 
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
    17 => 
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
  'suggestion' => '{{salary_label}} - {{salary_type}}',
);