<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Object;

use Cradle\Module\Object\Service as ObjectService;

use Cradle\Module\Utility\Validator as UtilityValidator;

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
        if(!isset($data['object_singular']) || empty($data['object_singular'])) {
            $errors['object_singular'] = 'Singular is required';
        }
                
        if(!isset($data['object_plural']) || empty($data['object_plural'])) {
            $errors['object_plural'] = 'Plural is required';
        }
                
        if(!isset($data['object_key']) || empty($data['object_key'])) {
            $errors['object_key'] = 'Keyword is required';
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
        if(!isset($data['object_id']) || !is_numeric($data['object_id'])) {
            $errors['object_id'] = 'Invalid ID';
        }

        
        if(isset($data['object_singular']) && empty($data['object_singular'])) {
            $errors['object_singular'] = 'Singular is required';
        }
                
        if(isset($data['object_plural']) && empty($data['object_plural'])) {
            $errors['object_plural'] = 'Plural is required';
        }
                
        if(isset($data['object_key']) && empty($data['object_key'])) {
            $errors['object_key'] = 'Keyword is required';
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
        
        if(isset($data['object_singular']) && strlen($data['object_singular']) <= 3) {
            $errors['object_singular'] = 'Singular should be longer than 3 characters';
        }
                
        if(isset($data['object_singular']) && strlen($data['object_singular']) >= 255) {
            $errors['object_singular'] = 'Singular should be less than 255 characters';
        }
                
        if(isset($data['object_plural']) && strlen($data['object_plural']) <= 3) {
            $errors['object_plural'] = 'Plural should be longer than 3 characters';
        }
                
        if(isset($data['object_plural']) && strlen($data['object_plural']) >= 255) {
            $errors['object_plural'] = 'Plural should be less than 255 characters';
        }
                
        if (isset($data['object_key']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['object_key'])) {
            $errors['object_key'] = 'Keyword must only have letters, numbers, dashes';
        }
                
        if(isset($data['object_key'])) {
            $search = Service::get('sql')
                ->getResource()
                ->search('object')
                ->filterByObjectKey($data['object_key']);

            if(isset($data['object_id'])) {
                $search->addFilter('object_id != %s', $data['object_id']);
            }

            if($search->getTotal()) {
                $errors['object_key'] = 'Keyword must be unique';
            }
        }
                
        return $errors;
    }
}
