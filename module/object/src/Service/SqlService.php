<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Object\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Object SQL Service
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'object';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        return $this->resource
            ->model($data)
            ->setObjectCreated(date('Y-m-d H:i:s'))
            ->setObjectUpdated(date('Y-m-d H:i:s'))
            ->save('object')
            ->get();
    }

    /**
     * Creates a table
     *
     * @param array $data
     * @param bool  $formatted
     *
     * @return array
     */
    public function createTable(array $data, $formatted = false)
    {
        //queries to run
        $queries = [];

        //translate object data to sql data
        if(!$formatted) {
            $data = $this->getFormattedSqlData($data);
        }

        //determine the create schema
        $query = $this
            ->resource
            ->getCreateQuery($data['name'])
            ->addPrimaryKey($data['primary'])
            ->addField($data['primary'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED',
                'auto_increment' => true,
            ]);

        foreach($data['columns'] as $name => $column) {
            $attributes = ['type' => $column['type']];

            if(isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if(isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if(!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if(isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if(isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $query->addField($name, $attributes);

            if(isset($column['index']) && $column['index']) {
                $query->addKey($name, [$name]);
            }

            if(isset($column['unique']) && $column['unique']) {
                $query->addUniqueKey($name, [$name]);
            }

            if(isset($column['primary']) && $column['primary']) {
                $query->addPrimaryKey($name);
            }
        }

        $queries[] = 'DROP TABLE IF EXISTS `' . $data['name'] . '`;';
        $queries[] = (string) $query;

        //determine the relation schema
        foreach($data['relations'] as $relation) {
            $query = $this->resource->getCreateQuery($relation['name']);

            $query->addPrimaryKey($relation['primary1']);
            $query->addField($relation['primary1'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $query->addPrimaryKey($relation['primary2']);
            $query->addField($relation['primary2'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $queries[] = 'DROP TABLE IF EXISTS `'. $relation['name'] . '`;';
            $queries[] = (string) $query;
        }

        //execute queries
        $results = [];
        foreach($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('object');

        if (is_numeric($id)) {
            $search->filterByObjectId($id);
        } else if (isset($data['object_key'])) {
            $search->filterByObjectKey($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['object_relations']) {
            $results['object_relations'] = json_decode($results['object_relations'], true);
        } else {
            $results['object_relations'] = [];
        }

        if($results['object_fields']) {
            $results['object_fields'] = json_decode($results['object_fields'], true);
        } else {
            $results['object_fields'] = [];
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setObjectId($id)
            ->remove('object');
    }

    /**
     * Removes a table
     *
     * @param array $data
     *
     * @return array
     */
    public function removeTable(array $data, $restorable = true)
    {
        //queries to run
        $queries = [];
        //translate object data to sql data
        $data = $this->getFormattedSqlData($data);
        if($this->systemExists($data['name'])) {
            if($restorable) {
                $queries[] = 'RENAME TABLE `' . $data['name'] . '` TO `_' . $data['name'] . '`;';

                //determine the relation schema
                foreach($data['relations'] as $relation) {
                    $queries[] = 'RENAME TABLE `' . $relation['name'] . '` TO `_' . $relation['name'] . '`;';
                }
            } else {
                $queries[] = 'DROP TABLE IF EXISTS `' . $data['name'] . '`;';
                $queries[] = 'DROP TABLE IF EXISTS `_' . $data['name'] . '`;';

                //determine the relation schema
                foreach($data['relations'] as $relation) {
                    $queries[] = 'DROP TABLE IF EXISTS `'. $relation['name'] . '`;';
                    $queries[] = 'DROP TABLE IF EXISTS `_'. $relation['name'] . '`;';
                }
            }
        }

        //execute queries
        $results = [];
        foreach($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Restores a table
     *
     * @param array $data
     *
     * @return array
     */
    public function restoreTable(array $data)
    {
        //queries to run
        $queries = [];
        //translate object data to sql data
        $data = $this->getFormattedSqlData($data);

        //if there's no system
        if(!$this->systemExists('_' . $data['name'])) {
            //go to create mode
            return $this->createTable($data, true);
        }

        $queries[] = 'RENAME TABLE `_' . $data['name'] . '` TO `' . $data['name'] . '`;';

        //determine the relation schema
        foreach($data['relations'] as $relation) {
            $queries[] = 'RENAME TABLE `_' . $relation['name'] . '` TO `' . $relation['name'] . '`;';
        }

        //execute queries
        $results = [];
        foreach($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }


        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }



        if (!isset($filter['object_active'])) {
            $filter['object_active'] = 1;
        }


        $search = $this->resource
            ->search('object')
            ->setStart($start)
            ->setRange($range);



        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }


        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(object_singular) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(object_plural) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(object_detail) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }


        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {

            if($results['object_relations']) {
                $rows[$i]['object_relations'] = json_decode($results['object_relations'], true);
            } else {
                $rows[$i]['object_relations'] = [];
            }

            if($results['object_fields']) {
                $rows[$i]['object_fields'] = json_decode($results['object_fields'], true);
            } else {
                $rows[$i]['object_fields'] = [];
            }

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setObjectUpdated(date('Y-m-d H:i:s'))
            ->save('object')
            ->get();
    }

    /**
     * Updates a table
     *
     * @param array $data
     * @param bool  $formatted
     *
     * @return array
     */
    public function updateTable(array $data, $formatted = false)
    {
        //queries to run
        $queries = [];
        //this is used to determine whether to add/alter/remove columns
        $exists = [];

        //translate object data to sql data
        if(!$formatted) {
            $data = $this->getFormattedSqlData($data);
        }

        //if there's no system
        if(!$this->systemExists($data['name'])) {
            //go to create mode
            return $this->createTable($data, true);
        }

        $columns = $this->resource->getColumns($data['name']);
        $query = $this->resource->getAlterQuery($data['name']);

        //remove or change fields
        foreach($columns as $current) {
            //don't do primary
            if($primary === $current['Field']) {
                continue;
            }

            $exists[] = $name = $current['Field'];

            //if there is no field in the data
            if(!isset($data['columns'][$name])) {
                $query->removeField($name);
                continue;
            }

            $column = $data['columns'][$name];

            $attributes = ['type' => $column['type']];

            if(isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if(isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if(!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if(isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if(isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $default = null;
            if (isset($attributes['default'])) {
                $default = $attributes['default'];
            }

            //if all matches
            if($attributes['type'] === $current['Type']
                && $attributes['null'] == ($current['Null'] === 'YES')
                && $default === $current['Default']
            ) {
                continue;
            }

            //do the alter
            $query->changeField($name, $attributes);
        }

        //add fields
        foreach($data['columns'] as $name => $column) {
            if(in_array($name, $exists)) {
                continue;
            }

            $attributes = ['type' => $column['type']];

            if(isset($column['length'])) {
                $attributes['type'] .= '(' . $column['length'] . ')';
            }

            if(isset($column['default']) && strlen($column['default'])) {
                $attributes['default'] = $column['default'];
            } else if(!isset($column['required']) || !$column['required']) {
                $attributes['null'] = true;
            }

            if(isset($column['required']) && $column['required']) {
                $attributes['null'] = false;
            }

            if(isset($column['attribute']) && $column['attribute']) {
                $attributes['attribute'] = $column['attribute'];
            }

            $query->addField($name, $attributes);

            if(isset($column['index']) && $column['index']) {
                $query->addKey($name, [$name]);
            }

            if(isset($column['unique']) && $column['unique']) {
                $query->addUniqueKey($name, [$name]);
            }

            if(isset($column['primary']) && $column['primary']) {
                $query->addPrimaryKey($name);
            }
        }

        $query = (string) $query;
        if($query !== 'ALTER TABLE `' . $data['name'] . '` ;') {
            $queries[] = $query;
        }

        $installed = $this->resource->getTables($data['name'] . '_%');
        $relations = array_keys($data['relations']);

        //determine the relation tables that need to be removed
        foreach($installed as $relation) {
            $relation = str_replace($data['name'] . '_', '', $relation);
            //uninstall if it's not in the schema
            if (!in_array($relation, $relations)) {
                $queries[] = 'DROP TABLE IF EXISTS `' . $data['name'] . '_' . $relation . '`;';
            }
        }

        //determine the relation tables that need to be added
        foreach($data['relations'] as $relation) {
            //install if it's installed
            if (in_array($relation['name'], $installed)) {
                continue;
            }

            $query = $this->resource->getCreateQuery($relation['name']);

            $query->addPrimaryKey($relation['primary1']);
            $query->addField($relation['primary1'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $query->addPrimaryKey($relation['primary2']);
            $query->addField($relation['primary2'], [
                'type' => 'int(10)',
                'null' => false,
                'attribute' => 'UNSIGNED'
            ]);

            $queries[] = 'DROP TABLE IF EXISTS `'. $relation['name'] . '`;';
            $queries[] = (string) $query;
        }

        //execute queries
        $results = [];
        foreach($queries as $query) {
            $results[] = [
                'query' => $query,
                'results' => $this->resource->query($query)
            ];
        }

        return $results;
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $objectKey
     *
     * @return bool
     */
    public function exists($objectKey)
    {
        $search = $this->resource
            ->search('object')
            ->filterByObjectKey($objectKey);

        return !!$search->getRow();
    }

    /**
     * Returns true if the system exists in the database
     *
     * @param string $name
     *
     * @return bool
     */
    public function systemExists($name)
    {
        $system = $this->resource->getTables($name);
        return !empty($system);
    }

    /**
     * Transforms field data to SQL data
     *
     * @param array $data
     *
     * @return array
     */
    protected function getFormattedSqlData(array $data)
    {
        $formatted = [
            'name' => $data['object_key'],
            'primary' => $data['object_key'] . '_id',
            'columns' => [],
            'relations' => []
        ];

        foreach($data['object_fields'] as $field) {
            if(!isset(self::$fieldTypes[$field['field']['type']])) {
                continue;
            }

            $name = $formatted['name'] . '_' . $field['key'];
            $format = self::$fieldTypes[$field['field']['type']];

            if (
                // if type is int or float
                ($format['type'] === 'INT' || $format['type'] === 'FLOAT')
                // and there's a min attribute
                && isset($field['field']['attributes']['min'])
                // and its a number
                && is_numeric($field['field']['attributes']['min'])
                // and its a positive number
                && $field['field']['attributes']['min'] >= 0
            ) {
                //it should be unsigned
                $format['attribute'] = 'unsigned';
            }

            //if no length was defined
            if (!isset($format['length'])) {
                //if type is int
                if ($format['type'] === 'INT') {
                    //by default it's 10
                    $format['length'] = 10;
                    //if there is a max
                    if (isset($field['field']['attributes']['max'])
                        && is_numeric($field['field']['attributes']['max'])
                    ) {
                        //get the length from the max
                        $numbers = explode('.', '' . $field['field']['attributes']['max']);
                        $format['length'] = strlen($numbers[0]);
                    }
                //if it's a float
                } else if ($format['type'] === 'FLOAT') {
                    $integers = $decimals = 0;
                    //if there's a max
                    if (isset($field['field']['attributes']['max'])
                        && is_numeric($field['field']['attributes']['max'])
                    ) {
                        //determine the initial integer and decimal
                        $numbers = explode('.', '' . $field['field']['attributes']['max']);
                        $integers = strlen($numbers[0]);
                        $decimals = strlen($numbers[1]);
                    }

                    //if there's a step
                    if (isset($field['field']['attributes']['step'])
                        && is_numeric($field['field']['attributes']['step'])
                    ) {
                        $numbers = explode('.', '' . $field['field']['attributes']['step']);
                        //choose the larger of each integer and decimal
                        $integers = max($integers, strlen($numbers[0]));
                        $decimals = max($decimals, strlen($numbers[1]));
                    }

                    //if integers is still 0
                    if (!$integers) {
                        //make it 10
                        $integers = 10;
                    }

                    //if decimals is still 0
                    if (!$decimals) {
                        //make it 10
                        $decimals = 10;
                    }

                    //finalize the length
                    $format['length'] = $integers . ',' . $decimals;
                }
            }

            //if theres a reason to index
            if ((isset($field['searchable']) && $field['searchable'])
                || (isset($field['filterable']) && $field['filterable'])
                || (isset($field['sortable']) && $field['sortable'])
            ) {
                //index it
                $format['index'] = true;
            }

            //determine the default
            if (isset($field['default'])
                && strpos($field['default'], '()') === false
            ) {
                $format['default'] = $field['default'];
            }

            //determine unique and required
            $format['required'] = false;
            $format['unique'] = false;

            if (isset($field['validation'])) {
                foreach ($field['validation'] as $validation) {
                    if ($validation === 'required') {
                        $format['required'] = true;
                    }

                    if ($validation === 'unique') {
                        $format['unique'] = true;
                        $format['index'] = false;
                    }
                }
            }

            $formatted['columns'][$name] = $format;
        }

        foreach($data['object_relations'] as $relation) {
            $formatted['relations'][$relation['object']] = [
                'name' => $formatted['name'] . '_' . $relation['object'],
                'primary1' => $formatted['primary'],
                'primary2' => $relation['object'] . '_id',
                'many' => $relation['many']
            ];
        }

        return $formatted;
    }

    /**
     * @var array $fieldTyles
     */
    protected static $fieldTypes = [
        'text' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'email' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'password' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'search' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'url' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'color' => [
            'type' => 'varchar',
            'length' => 7
        ],
        'format' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'slug' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'textarea' => [
            'type' => 'text'
        ],
        'wysiwyg' => [
            'type' => 'text'
        ],
        'number' => [
            'type' => 'INT'
        ],
        'small' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'range' => [
            'type' => 'INT'
        ],
        'float' => [
            'type' => 'FLOAT'
        ],
        'price' => [
            'type' => 'FLOAT',
            'length' => '10,2'
        ],
        'date' => [
            'type' => 'date'
        ],
        'time' => [
            'type' => 'time'
        ],
        'datetime' => [
            'type' => 'datetime'
        ],
        'week' => [
            'type' => 'INT',
            'length' => 2,
            'attribute' => 'unsigned'
        ],
        'month' => [
            'type' => 'INT',
            'length' => 2,
            'attribute' => 'unsigned'
        ],
        'checkbox' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'switch' => [
            'type' => 'INT',
            'length' => 1,
            'attribute' => 'unsigned'
        ],
        'select' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'checkboxes' => [
            'type' => 'JSON'
        ],
        'radios' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'file' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'image' => [
            'type' => 'varchar',
            'length' => 255
        ],
        'files' => [
            'type' => 'JSON'
        ],
        'images' => [
            'type' => 'JSON'
        ],
        'tag' => [
            'type' => 'JSON'
        ],
        'meta' => [
            'type' => 'JSON'
        ],
        'multirange' => [
            'type' => 'JSON'
        ],
        'active' => [
            'type' => 'INT',
            'length' => 1,
            'null' => false,
            'default' => 1,
            'attribute' => 'UNSIGNED'
        ],
        'created' => [
            'type' => 'datetime',
            'null' => false
        ],
        'updated' => [
            'type' => 'datetime',
            'null' => false
        ]
    ];
}
