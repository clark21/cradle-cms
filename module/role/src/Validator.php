<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Role;

use Cradle\Module\Role\Service as RoleService;

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
        if(!isset($data['role_name']) || empty($data['role_name'])) {
            $errors['role_name'] = 'Role Name is required';
        } else if (RoleService::get('sql')->exists($data['role_name'])) {
            $errors['role_name'] = 'Role Name Exists';
        }

        if (!isset($data['role_permissions']) || empty($data['role_permissions'])) {
            $errors['role_permissions'] = 'Role Permissions is required';
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

    /**
     * Returns Role Auth Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getRoleAuthErrors(array $data, array $errors = [])
    {
        if(!isset($data['auth_id']) || empty($data['auth_id'])) {
            $errors['auth_id'] = 'Auth Id is required';
        } else if (RoleService::get('sql')->existsAuth($data['auth_id'])) {
            $errors['auth_id'] = 'Auth Exists';
        }

        if(!isset($data['role_id']) || empty($data['role_id'])) {
            $errors['role_id'] = 'Role Id is required';
        }

        return $errors;
    }
}
