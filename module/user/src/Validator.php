<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\User;

use Cradle\Module\User\Service as UserService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  user
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
        if(!isset($data['user_name']) || empty($data['user_name'])) {
            $errors['user_name'] = 'Name is required';
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
        if(!isset($data['user_id']) || !is_numeric($data['user_id'])) {
            $errors['user_id'] = 'Invalid ID';
        }

        
        if(isset($data['user_name']) && empty($data['user_name'])) {
            $errors['user_name'] = 'Name is required';
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
        
        if (isset($data['user_slug']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['user_slug'])) {
            $errors['user_slug'] = 'Slug must only have letters, numbers, dashes';
        }
                
        return $errors;
    }
}
