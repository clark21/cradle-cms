<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\System;

use Cradle\Module\System\Object\Model as ObjectModel;

use Cradle\Helper\InstanceTrait;

/**
 * Object Schema Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Acme
 * @package  system
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Schema
{
    use InstanceTrait;

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * Sets up the schema
     *
     * @param string|array $name
     */
    public function __construct($name)
    {
        $this->data = $name;
        if(!is_array($this->data)) {
            $this->data = cradle()
                ->package('global')
                ->config('schema/' . $name);
        }

        if(!$this->data || empty($this->data)) {
            throw Exception::forSchemaNotFound($name);
        }
    }

    /**
     * Returns data
     *
     * @return string|false
     */
    public function get()
    {
        return $this->data;
    }

    /**
     * Returns active field
     *
     * @param *array $this->data
     *
     * @return string|false
     */
    public function getActive()
    {
        if(!isset($this->data['fields'])
            || empty($this->data['fields'])
        ) {
            return false;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            if($field['name'] === 'active') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns created field
     *
     * @return string|false
     */
    public function getCreated()
    {
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return false;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            if($field['name'] === 'created') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns filterable fields
     *
     * @return array
     */
    public function getFilterable()
    {
        $results = [];
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if(isset($field['filterable']) && $field['filterable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns All fields
     *
     * @return array
     */
    public function getFields()
    {
        if(!isset($this->data['fields'])
            || empty($this->data['fields'])
        ) {
            return [];
        }

        return $this->data['fields'];
    }

    /**
     * Returns JSON fields
     *
     * @return array
     */
    public function getJsonFields()
    {
        $results = [];
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if(
                in_array(
                    $field['field']['type'],
                    [
                        'files',
                        'images',
                        'tag',
                        'meta',
                        'checkboxes',
                        'multirange'
                    ]
                )
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns listable fields
     *
     * @return string
     */
    public function getListable()
    {
        $results = [];
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];

            if(isset($field['list']['format'])
                && $field['list']['format'] !== 'hide'
            ) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns primary
     *
     * @return string
     */
    public function getPrimary()
    {
        return $this->getTableName() . '_id';
    }

    /**
     * Returns relational data
     *
     * @param int $many
     *
     * @return array
     */
    public function getRelations($many = -1)
    {
        $results = [];
        if(!isset($this->data['relations'])
            || empty($this->data['relations'])
        ) {
            return $results;
        }

        $table = $this->getTableName();
        $primary = $this->getPrimary();

        foreach($this->data['relations'] as $relation) {
            $relation = [
                'name' => $table . '_' . $relation['name'],
                'primary1' => $primary,
                'primary2' => $relation['name'] . '_id',
                'many' => $relation['many']
            ];

            if($many === -1 || $many === $relation['many']) {
                $results[$relation['name']] = $relation;
                continue;
            }
        }

        return $results;
    }

    /**
     * Returns searchable fields
     *
     * @return array
     */
    public function getSearchable()
    {
        $results = [];
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if(isset($field['searchable']) && $field['searchable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns slug fields
     *
     * @param string|false $primary
     *
     * @return array
     */
    public function getSlugs($primary = false)
    {
        $results = [];
        if($primary) {
            $results[] = $primary;
        }

        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if($field['type'] === 'slug') {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns sortable fields
     *
     * @return array
     */
    public function getSortable()
    {
        $results = [];
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return $results;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            $name = $table . '_' . $field['name'];
            if(isset($field['sortable']) && $field['sortable']) {
                $results[] = $name;
            }
        }

        return $results;
    }

    /**
     * Returns plural name
     *
     * @return string
     */
    public function getPlural()
    {
        return $this->data['plural'];
    }

    /**
     * Returns singular name
     *
     * @return string
     */
    public function getSingular()
    {
        return $this->data['singular'];
    }

    /**
     * Returns table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->data['name'];
    }

    /**
     * Returns updated field
     *
     * @return string|false
     */
    public function getUpdated()
    {
        if(!isset($this->data['fields']) || empty($this->data['fields'])) {
            return false;
        }

        $table = $this->data['name'];
        foreach($this->data['fields'] as $field) {
            if($field['name'] === 'updated') {
                return $table . '_' . $field['name'];
            }
        }

        return false;
    }

    /**
     * Returns an Object Model
     *
     * @return ObjectModel
     */
    public function model()
    {
        return ObjectModel::i($this);
    }

    /**
     * Returns a service. To prevent having to define a method per
     * service, instead we roll everything into one function
     *
     * @param *string $name
     * @param string  $key
     *
     * @return object
     */
    public function service($name, $key = 'main')
    {
        $service = Service::get($name, $key);

        if($service instanceof NoopService) {
            return $service;
        }

        return $service->setSchema($this);
    }

    /**
     * Transforms to SQL data
     *
     * @return array
     */
    public function toSql()
    {
        $data = [
            'name' => $this->getTableName(),
            'primary' => $this->getPrimary(),
            'columns' => [],
            'relations' => $this->getRelations()
        ];

        foreach($this->data['fields'] as $field) {
            if(!isset(self::$fieldTypes[$field['field']['type']])) {
                continue;
            }

            $name = $data['name'] . '_' . $field['name'];
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

            $data['columns'][$name] = $format;
        }

        return $data;
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
