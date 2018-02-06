<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Node;

use Cradle\Module\Node\Service as NodeService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  node
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
        if(!isset($data['node_title']) || empty($data['node_title'])) {
            $errors['node_title'] = 'Title is required';
        }
                
        if(!isset($data['node_slug']) || empty($data['node_slug'])) {
            $errors['node_slug'] = 'Slug is required';
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
        if(!isset($data['node_id']) || !is_numeric($data['node_id'])) {
            $errors['node_id'] = 'Invalid ID';
        }

        
        if(isset($data['node_title']) && empty($data['node_title'])) {
            $errors['node_title'] = 'Title is required';
        }
                
        if(isset($data['node_slug']) && empty($data['node_slug'])) {
            $errors['node_slug'] = 'Slug is required';
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
        
        if (isset($data['node_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['node_image'])) {
            $errors['node_image'] = 'Should be a valid url';
        }
                
        if(isset($data['node_title']) && strlen($data['node_title']) <= 10) {
            $errors['node_title'] = 'Title should be longer than 10 characters';
        }
                
        if(isset($data['node_title']) && strlen($data['node_title']) >= 255) {
            $errors['node_title'] = 'Title should be less than 255 characters';
        }
                
        if (isset($data['node_slug']) && !preg_match('#^[a-zA-Z0-9\-_]+$#', $data['node_slug'])) {
            $errors['node_slug'] = 'Slug must only have letters, numbers, dashes';
        }
                
        if(isset($data['node_slug'])) {
            $search = Service::get('sql')
                ->getResource()
                ->search('node')
                ->filterByNodeSlug($data['node_slug']);

            if(isset($data['node_id'])) {
                $search->addFilter('node_id != %s', $data['node_id']);
            }

            if($search->getTotal()) {
                $errors['node_slug'] = 'Slug must be unique';
            }
        }
                
        return $errors;
    }
}
