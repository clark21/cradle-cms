<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Role\Permission;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  role
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
        if(!isset($data['permission_label']) || empty($data['permission_label'])) {
            $errors['permission_label'] = 'Permission Label is required';
        }

        if (!isset($data['permission_method']) || empty($data['permission_method'])) {
            $errors['permission_method'] = 'Permission Method is required';
        }

        if (!isset($data['permission_path']) || empty($data['permission_path'])) {
            $errors['permission_path'] = 'Permission Url is required';
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
        if(!isset($data['role_id']) || !is_numeric($data['role_id'])) {
            $errors['role_id'] = 'Invalid ID';
        }


        if(isset($data['role_name']) && empty($data['role_name'])) {
            $errors['role_name'] = 'Role Name is required';
        }

        if(!isset($data['role_permissions']) || empty($data['role_permissions'])) {
            $errors['role_permissions'] = 'Role Permissions is required';
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
        return $errors;
    }
}
