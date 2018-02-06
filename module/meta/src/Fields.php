<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Meta;

/**
 * Fields layer
 *
 * @vendor   Acme
 * @package  meta
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Fields
{
    /**
     * @var array $numericTypes
     */
    protected $numericTypes = [
        'int',
        'float',
        'double',
        'small',
        'price'
    ];


    /**
     * Pre-compile fields based on the
     * given data and errors.
     * 
     * @param array $fields
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function compile($fields, $data, $errors)
    {
        // get meta keys
        $keys = array_map(function($field) {
            if(!isset($field['key'])) {
                return false;
            }

            return $field['key'];
        }, $fields);

        // get meta values
        $values = array_values($fields);

        // update fields
        $fields = array_combine($keys, $values);

        // format fields
        $fields = array_map(function($field) use ($data, $errors) {
            // if key is set
            if(!isset($field['key'])) {
                return false;
            }

            // get field key
            $key = $field['key'];

            // check if numeric
            if(in_array(
                $field['field']['type'], 
                $this->numericTypes)
            ) {
                $field['number_field'] = true;
            }

            // format label
            $field['label'] = ucwords($field['label']);

            // get value
            $field['value'] = $this->getValue($key, $data, $field['default']);            

            // get error
            $field['error'] = $this->getError($key, $errors);

            return $field;
        }, $fields);

        return $fields;
    }

    /**
     * Get value between default
     * and the given data.
     * 
     * @param string $key
     * @param array $data
     * @param string $default
     * @return *mixed
     */
    private function getValue($key, $data, $default)
    {
        $value = null;

        // is value set?
        if(in_array($key, $data)) {
            // set value
            $value = $data[$key];
        } else {
            // set default value
            $value = $default;
        }

        return $value;
    }
    
    /**
     * Get errors based on key and
     * error data.
     * 
     * @param string $key
     * @param array $errors
     * @return string
     */
    private function getError($key, $errors)
    {
        $error = null;

        // error exists?
        if(in_array($key, $errors)) {
            // set error
            $error = $errors[$key];
        }

        return $error;
    }
}
