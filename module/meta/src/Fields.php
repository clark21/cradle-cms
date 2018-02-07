<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Meta;

use Cradle\Module\Meta\Validator as MetaValidator;

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
     * Date field types
     * 
     * @var array $dateTypes
     */
    protected $dateTypes = [
        'date'
    ];

    /**
     * File field types
     * 
     * @var array $fileTypes
     */
    protected $fileTypes = [
        'file',
        'files',
        'image',
        'images'
    ];

    /**
     * JSON field types
     * 
     * @var array $jsonTypes
     */
    protected $jsonTypes = [
        'meta',
        'tag'
    ];

    /**
     * Number field types
     * 
     * @var array $numberTypes
     */
    protected $numberTypes = [
        'float',
        'number',
        'price',
        'range',
        'small'
    ];

    /**
     * Option field types
     * 
     * @var array $optionTypes
     */
    protected $optionTypes = [];

    /**
     * String field types
     * 
     * @var array $stringTypes
     */
    protected $stringTypes = [
        'email',
        'password',
        'slug',
        'text',
        'textarea',
        'wysiwyg'
    ];

    /**
     * Default node fields
     * 
     * @var array $nodeFields
     */
    protected $nodeFields = [
        'node_image',
        'node_title',
        'node_slug',
        'node_detail',
        'node_tags',
        'node_meta',
        'node_files',
        'node_published',
        'node_status',
        'node_type',
        'node_flag',
        'node_created',
        'node_updated'
    ];

    /**
     * Fields to process
     * 
     * @var array $fields
     */
    protected $fields = [];

    /**
     * Field data
     * 
     * @var array $data
     */
    protected $data = [];

    /**
     * Field errors
     * 
     * @var array $errors
     */
    protected $errors = [];

    /**
     * Initialize template fields
     * 
     * @param array $fields
     */
    public function __construct(array $fields = [])
    {
        // set fields
        $this->fields = $fields;
    }

    /**
     * Set field data
     * 
     * @param array $data
     * @return $this
     */
    public function setData(array $data = [])
    {
        // set field data
        $this->data = $data;

        return $this;
    }

    /**
     * Set field errors
     * 
     * @param array $errors
     * @return $this
     */
    public function setError(array $errors = [])
    {
        // set field errors
        $this->errors = $errors;

        return $this;
    }

    /**
     * Compile the fields into
     * a single template based on
     * the given fields.
     * 
     * @param bool $html
     * @return string|array
     */
    public function compile($html = true)
    {
        // get fields
        $fields = $this->fields;
        
        // prepare fields
        $this->prepare($fields, $this->data, $this->errors);

        // if not html
        if(!$html) {
            return $fields;
        }

        // create handlebars
        $handlebars = cradle('global')->handlebars();

        // compiled template fields
        $compiled = [];

        // iterate on each fields
        foreach($fields as $field) {
            // type or key not set?
           if(!isset($field['type'])
           || !isset($field['key'])) {
               continue;
           }

            // get template path
            $template = sprintf(
                __DIR__ . '/template/fields/%s/%s.html',
                $field['type'],
                $field['field']['type']
            );

            // template exists?
            if(!file_exists($template)) {
                continue;
            }

            // compile template
            $template = $handlebars->compile(file_get_contents($template));

            // set compiled field
            $compiled[] = $template($field);
        }

        // get compile template
        $template = __DIR__ . '/template/fields/compiled.html';

        // final compiled template
        $final = $handlebars->compile(file_get_contents($template));

        return $final(['fields' => $compiled]);
    }

    /**
     * Returns field validations
     * 
     * @return array
     */
    public function getValidation()
    {
        // get fields
        $fields = $this->fields;
        
        // prepare fields
        $this->prepare($fields, $this->data, $this->errors);

        // filter the fields
        $fields = $this->filterFields($fields);

        // validations
        $validations = [];

        // get the validations
        array_map(function($field) use (&$validations) {
            // if key is not set
            if(!isset($field['key'])) {
                return false;
            }

            // get field key
            $key = $field['key'];

            // if name is set
            if(isset($field['field']['attributes']['name'])) {
                // get name instead
                $key = $field['field']['attributes']['name'];
            }

            // iterate on each validations
            foreach($field['validation'] as $validation) {
                // get validation parameters
                $params = null;

                // if parameters is set
                if(isset($validation['parameters'])) {
                    // get params
                    $params = $validation['parameters'];
                }

                // get the error
                $error = MetaValidator::validateField(
                    $field['field']['value'],
                    $validation['method'],
                    $validation['message'],
                    $params
                );

                // if we have error
                if($error) {
                    $validations[$key] = $error; 
                }
            }
        }, $fields);

        return $validations;
    }

    /**
     * Returns filtered non-default values
     * 
     * @return array
     */
    public function getValues()
    {
        // get filtered fields
        $fields = $this->filterFields($this->fields);

        // prepare fields
        $this->prepare($fields, $this->data, $this->errors);

        // pairs
        $pairs = [];

        // map on each field
        foreach($fields as $field) {
            $pairs[$field['key']] = $field['field']['value'];
        }

        return $pairs;
    }

    /**
     * Filter the given fields.
     * 
     * @param array $fields
     * @return array
     */
    private function filterFields(array $fields = [])
    {
        // return filtered fields
        return array_filter($fields, function($field) {
            // if key is not set
            if(!isset($field['key'])) {
                return false;
            }

            // get field key
            $key = $field['key'];

            // if name is set
            if(isset($field['field']['attributes']['name'])) {
                // get name instead
                $key = $field['field']['attributes']['name'];
            }

            // default node fields?
            if(in_array($key, $this->nodeFields)) {
                return false;
            }

            return $field;
        });
    }

    /**
     * Prepare fields before compilation
     * 
     * @param array $fields
     * @param array $data
     * @param array $errors
     * @return array
     */
    private function prepare(
        array &$fields = [], 
        array $data = [], 
        array $errors = []
    ) {
        // map on each fields
        $fields = array_map(function($field) use ($data, $errors) {
            // if key is not set
            if(!isset($field['key'])) {
                return;
            }

            // get field key
            $key = $field['key'];

            // format label
            $field['label'] = ucwords($field['label']);

            // if name is set
            if(isset($field['field']['attributes']['name'])) {
                // get name instead
                $key = $field['field']['attributes']['name'];
            }

            // set field key
            $field['field']['key'] = $key;

            // if data exists
            if(array_key_exists($key, $data)) {
                // set data
                $field['field']['value'] = $data[$key];
            } else {
                // set default
                $field['field']['value'] = $field['default'];
            }

            // if error exists
            if(array_key_exists($key, $errors)) {
                // set error
                $field['field']['error'] = $errors[$key];
            }

            // get parent field type
            $field['type'] = $this->getCommonType($field['field']['type']);

            return $field;
        }, $fields);

        return $fields;
    }

    /**
     * Get common field type
     * 
     * @param string $key
     * @return string
     */
    private function getCommonType($key)
    {
        $type = null;

        // date type?
        if(in_array($key, $this->dateTypes)) {
            $type = 'date';

        // file type?
        } else if(in_array($key, $this->fileTypes)) {
            $type = 'file';

        // json type?
        } else if(in_array($key, $this->jsonTypes)) {
            $type = 'json';

        // number type?
        } else if(in_array($key, $this->numberTypes)) {
            $type = 'number';

        // option type?
        } else if(in_array($key, $this->optionTypes)) {
            $type = 'option';

        // string type?
        } else if(in_array($key, $this->stringTypes)) {
            $type = 'string';

        // none type?
        } else {
            $type = 'none';
        }

        return $type;
    }
}