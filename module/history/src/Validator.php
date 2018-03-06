<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\History;

use Cradle\Module\History\Service as HistoryService;

use Cradle\Module\System\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  history
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
        if (!isset($data['history_activity']) || empty($data['history_activity'])) {
            $errors['history_activity'] = 'History Activity is required';
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
        if (!isset($data['history_id']) || !is_numeric($data['history_id'])) {
            $errors['history_id'] = 'Invalid ID';
        }

        
        if (isset($data['history_activity']) && empty($data['history_activity'])) {
            $errors['history_activity'] = 'History Activity is required';
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
        
        if (isset($data['history_activity']) && empty($data['history_activity'])) {
            $errors['history_activity'] = 'History Activity cannot be empty';
        }
                
        $choices = array(1, 0);
        if (isset($data['history_flag']) && !in_array($data['history_flag'], $choices)) {
            $errors['history_flag'] = 'Flag should be specified.';
        }
                
        return $errors;
    }
}
