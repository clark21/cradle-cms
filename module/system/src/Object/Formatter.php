<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\System\Object;

use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Module\Utility\File;

use Cradle\Helper\InstanceTrait;

/**
 * Formatter layer
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Formatter
{
    use InstanceTrait;

    /**
     * @var SystemSchema|null $schema
     */
    protected $schema = null;

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     */
    public function __construct(SystemSchema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns formatted data
     *
     * @param *array      $data
     * @param array|false $s3
     * @param string|null $upload
     *
     * @return array
     */
    public static function formatData(array $data, $s3 = false, $upload = null) {
        $fields = $this->schema->getFields();
        $table = $this->schema->getTableName();

        foreach($fields as $field) {
            $name = $table . '_' . $field['key'];
            //if there's no data
            if(!isset($data[$name])) {
                //no need to format
                continue;
            }

            switch($field['field']['type']) {
                case 'file':
                case 'image':
                    //upload files
                    //try cdn if enabled
                    $data[$name] = File::base64ToS3($data[$name], $s3);
                    //try being old school
                    $data[$name] = File::base64ToUpload($data[$name], $upload);
                    break;
                case 'files':
                case 'images':
                    //upload files
                    //try cdn if enabled
                    $data[$name] = File::base64ToS3($data[$name], $s3);
                    //try being old school
                    $data[$name] = File::base64ToUpload($data[$name], $upload);
                    $data[$name] = json_encode($data[$name]);
                    break;
                case 'tag':
                case 'meta':
                case 'checkboxes':
                case 'multirange':
                    $data[$name] = json_encode($data[$name]);
                    break;
                case 'created':
                case 'updated':
                case 'datetime':
                    $data[$name] = date('Y-m-d H:i:s', strtotime($data[$name]));
                    break;
                case 'date':
                    $data[$name] = date('Y-m-d', strtotime($data[$name]));
                    break;
                case 'time':
                    $data[$name] = date('H:i:s', strtotime($data[$name]));
                    break;
                case 'password':
                case 'md5':
                    $data[$name] = md5($data[$name]);
                    break;
                case 'sha1':
                    $data[$name] = sha1($data[$name]);
                    break;
                case 'active':
                case 'checkbox':
                    $data[$name] = 0;
                    if($data[$name]) {
                        $data[$name] = 1;
                    }
                    break;
                case 'uuid':
                case 'token':
                    $data[$name] = md5(uniqid());
                    break;
            }
        }

        return $data;
    }
}