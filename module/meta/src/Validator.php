<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Meta;

use Cradle\Module\Meta\Service as MetaService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  meta
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
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
        if(!isset($data['meta_singular']) || empty($data['meta_singular'])) {
            $errors['meta_singular'] = 'Singular is required';
        }
                
        if(!isset($data['meta_plural']) || empty($data['meta_plural'])) {
            $errors['meta_plural'] = 'Plural is required';
        }
                
        if(!isset($data['meta_key']) || empty($data['meta_key'])) {
            $errors['meta_key'] = 'Keyword is required';
        }
                
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Update Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        if(!isset($data['meta_id']) || !is_numeric($data['meta_id'])) {
            $errors['meta_id'] = 'Invalid ID';
        }

        
        if(isset($data['meta_singular']) && empty($data['meta_singular'])) {
            $errors['meta_singular'] = 'Singular is required';
        }
                
        if(isset($data['meta_plural']) && empty($data['meta_plural'])) {
            $errors['meta_plural'] = 'Plural is required';
        }
                
        if(isset($data['meta_key']) && empty($data['meta_key'])) {
            $errors['meta_key'] = 'Keyword is required';
        }
                
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        //validations
        
        $choices = array('node', 'user');
        if (isset($data['meta_type']) && !in_array($data['meta_type'], $choices)) {
            $errors['meta_type'] = 'Must choose a valid type';
        }
                
        if(isset($data['meta_singular']) && strlen($data['meta_singular']) <= 3) {
            $errors['meta_singular'] = 'Singular should be longer than 3 characters';
        }
                
        if(isset($data['meta_singular']) && strlen($data['meta_singular']) >= 255) {
            $errors['meta_singular'] = 'Singular should be less than 255 characters';
        }
                
        if(isset($data['meta_plural']) && strlen($data['meta_plural']) <= 3) {
            $errors['meta_plural'] = 'Plural should be longer than 3 characters';
        }
                
        if(isset($data['meta_plural']) && strlen($data['meta_plural']) >= 255) {
            $errors['meta_plural'] = 'Plural should be less than 255 characters';
        }
                
        if (isset($data['meta_key']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['meta_key'])) {
            $errors['meta_key'] = 'Keyword must only have letters, numbers, dashes';
        }
                
        if(isset($data['meta_key'])) {
            $search = Service::get('sql')
                ->getResource()
                ->search('meta')
                ->filterByMetaKey($data['meta_key']);

            if(isset($data['meta_id'])) {
                $search->addFilter('meta_id != %s', $data['meta_id']);
            }

            if($search->getTotal()) {
                $errors['meta_key'] = 'Keyword must be unique';
            }
        }
                
        return $errors;
    }

    /**
     * Validate field value based on validation
     * 
     * @param mixed $value
     * @param string $method
     * @param string $message
     * @param mixed $params
     * @return string|null
     */
    public static function validateField(
        $value, 
        $method, 
        $message, 
        $params = null
    ) {
        // switch between methods
        switch($method) {
            // required?
            case 'required':
                return self::validateRequired($value, $message);
            
            // default
            default:
                return null;
        }
    }

    /**
     * Validate required field.
     * 
     * @param mixed $value
     * @param string $type
     * @return 
     */
    private static function validateRequired($value, $message)
    {
        return (!isset($value) || empty($value)) ? $message : null;
    }
}
