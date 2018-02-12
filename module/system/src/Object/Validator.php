<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\System\Object;

use Cradle\Module\System\Schema as SystemSchema;
use Cradle\Module\System\Object\Service as ObjectService;

use Cradle\Module\Utility\Validator as UtilityValidator;

use Cradle\Helper\InstanceTrait;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    use InstanceTrait;

    /**
     * @var SystemSchema|null $schema
     */
    protected $schema = null;

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     */
    public function __construct(SystemSchema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getTableName();

        foreach($fields as $field) {
            $name = $table . '_' . $field['key'];
            foreach($field['validation'] as $validation) {
                if($validation['method'] === 'required'
                    && (!isset($data[$name]) || empty($data[$name]))
                ) {
                    $errors[$name] = $validation['message'];
                }
            }
        }

        return self::getOptionalErrors($object, $data, $errors);
    }

    /**
     * Returns Create Errors
     *
     * @param *array $object
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $object, array $data, array $errors = [])
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getTableName();
        $primary = $this->schema->getPrimary();

        if(!isset($data[$primary]) || !is_numeric($data[$primary])) {
            $errors[$primary] = 'Invalid ID';
        }

        foreach($fields as $field) {
            $name = $table . '_' . $field['key'];
            foreach($field['validation'] as $validation) {
                if($validation['method'] === 'required'
                    && isset($data[$name])
                    && empty($data[$name])
                ) {
                    $errors[$name] = $validation['message'];
                }
            }
        }

        return self::getOptionalErrors($object, $data, $errors);

        return $errors;
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $object
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $object, array $data, array $errors = [])
    {
        $fields = $this->schema->getFields();
        $table = $this->schema->getTableName();
        $primary = $this->schema->getPrimary();

        foreach($fields as $field) {
            $name = $table . '_' . $field['key'];
            //if there is no data
            if(!isset($data[$name])) {
                //no need to validate
                continue;
            }

            foreach($field['validation'] as $validation) {
                switch(true) {
                    case $validation['method'] === 'empty'
                        && empty($data[$name]):
                    case $validation['method'] === 'number'
                        && !is_numeric($data[$name]):
                    case $validation['method'] === 'regexp'
                        && !preg_match($validation['parameters'], $data[$name]):
                    case $validation['method'] === 'gt'
                        && !($data[$name] > $validation['parameters']):
                    case $validation['method'] === 'gte'
                        && !($data[$name] >= $validation['parameters']):
                    case $validation['method'] === 'lt'
                        && !($data[$name] < $validation['parameters']):
                    case $validation['method'] === 'lte'
                        && !($data[$name] <= $validation['parameters']):
                    case $validation['method'] === 'char_gt'
                        && !(strlen($data[$name]) > $validation['parameters']):
                    case $validation['method'] === 'char_gte'
                        && !(strlen($data[$name]) >= $validation['parameters']):
                    case $validation['method'] === 'char_lt'
                        && !(strlen($data[$name]) < $validation['parameters']):
                    case $validation['method'] === 'char_lte'
                        && !(strlen($data[$name]) <= $validation['parameters']):
                    case $validation['method'] === 'word_gt'
                        && !(str_word_count($data[$name]) > $validation['parameters']):
                    case $validation['method'] === 'word_gte'
                        && !(str_word_count($data[$name]) >= $validation['parameters']):
                    case $validation['method'] === 'word_lt'
                        && !(str_word_count($data[$name]) < $validation['parameters']):
                    case $validation['method'] === 'word_lte'
                        && !(str_word_count($data[$name]) <= $validation['parameters']):
                        $errors[$name] = $validation['message'];
                        break;
                    case $validation['method'] === 'one':
                        if(!in_array($data[$name], $validation['parameters'])) {
                            $errors[$name] = $validation['message'];
                        }
                        break;
                    case $validation['method'] === 'unique':
                        $search = Service::get('sql')
                            ->getResource()
                            ->search($table)
                            ->addFilter($name . '= %s', $data[$name]);

                        if(isset($data[$primary])) {
                            $search->addFilter($primary . ' != %s', $data[$primary]);
                        }

                        if($search->getTotal()) {
                            $errors[$name] = $validation['message'];
                        }
                        break;
                }
            }
        }

        return $errors;
    }
}
