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
                
        if(!isset($data['meta_slug']) || empty($data['meta_slug'])) {
            $errors['meta_slug'] = 'Slug is required';
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
                
        if(isset($data['meta_slug']) && empty($data['meta_slug'])) {
            $errors['meta_slug'] = 'Slug is required';
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
        
        $choices = array('meta', 'user');
        if (isset($data['meta_type']) && !in_array($data['meta_type'], $choices)) {
            $errors['meta_type'] = 'Must choose a verification method';
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
                
        if (isset($data['meta_slug']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['meta_slug'])) {
            $errors['meta_slug'] = 'Slug must only have letters, numbers, dashes';
        }
                
        if(isset($data['meta_slug'])) {
            $search = Service::get('sql')
                ->getResource()
                ->search('meta')
                ->filterByMetaSlug($data['meta_slug']);

            if(isset($data['meta_id'])) {
                $search->addFilter('meta_id != %s', $data['meta_id']);
            }

            if($search->getTotal()) {
                $errors['meta_slug'] = 'Slug must be unique';
            }
        }
                
        return $errors;
    }
}
