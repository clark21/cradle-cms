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
 * Field layer
 *
 * @vendor   Acme
 * @package  meta
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Field
{
    /**
     * Date field types
     * 
     * @var array $dateTypes
     */
    protected $dateTypes = [];

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
    protected $jsonTypes = [];

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
        'small',
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

            // if data exists
            if(in_array($key, $data)) {
                // set data
                $field['field']['value'] = $data[$key];
            } else {
                // set default
                $field['field']['value'] = $field['default'];
            }

            // if error exists
            if(in_array($key, $errors)) {
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