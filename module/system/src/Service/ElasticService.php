<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\System\Service;

use Cradle\Module\System\Service;
use Cradle\Module\System\Schema as SystemSchema;

use Elasticsearch\Client as Resource;

use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

use Cradle\Module\Utility\Service\ElasticServiceInterface;
use Cradle\Module\Utility\Service\AbstractElasticService;

/**
 * Object ElasticSearch Service
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class ElasticService extends AbstractElasticService implements ElasticServiceInterface
{
    /**
     * @const INDEX_NAME Index name
     */
    const INDEX_NAME = 'object';

    /**
     * @var SystemSchema|null $schema
     */
    protected $schema = null;

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
        $this->sql = Service::get('sql');
    }

    /*
     * Create elastic map
     *
     * @param array $data
    */
    public function createMap() {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }
        
        // translate data first to sql
        $data = $this->schema->toSql();
        
        // then translate it to elastic mapping
        $mapping = $this->schema->toElastic($data);
        // get schema path
        $path = cradle()->package('global')->path('config') . '/admin/schema/elastic';
        
        if(!is_dir($path)) {
            mkdir($path, 0777);
        }

        // save mapping
        file_put_contents(
            $path . '/' . $data['name'] . '.php',
            '<?php //-->' . "\n return " .
            var_export($mapping, true) . ';'
        );
        

    }

    /*
     * Map elastic
     *
     */
    public function map() {
        // no schema validation
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getName();
        $path = cradle()->package('global')->path('config') . '/admin/schema/elastic/' . $table . '.php';
        
        // if mapped file doesn't exist,
        // do nothing
        if (!file_exists($path)) {
            return false;
        }

        $data = include_once($path);
        
        $index = cradle()->package('global')->service('elastic-main');
        // try mapping 
        try {
            $index->indices()->create(['index' => $table]);
            $index->indices()->putMapping([
                'index' => $table,
                'type' => 'main',
                'body' => [
                    '_source' => [
                        'enabled' => true
                    ],
                    'properties' => $data[$table]
                ]
            ]);
        } catch (NoNodesAvailableException $e) {
            //because there is no reason to continue;
            return false;
        } catch (BadRequest400Exception $e) {
            //already mapped
            return false;
        } catch (\Throwable $e) { die('gfgf');
            // something is not right
            return false;
        }

        return true;
    }

    /* Populate elastic
     *
     *
     */
    public function populate() {
        // no schema validation
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        die('sdf');
    }
    
    /**
     * Search in index
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        //set the defaults
        $filter = [];
        $range = 50;
        $start = 0;
        $order = ['object_id' => 'asc'];
        $count = 0;

        //merge passed data with default data
        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        //prepare the search object
        $search = [];


        //keyword search
        if (isset($data['q'])) {
            if (!is_array($data['q'])) {
                $data['q'] = [$data['q']];
            }

            foreach ($data['q'] as $keyword) {
                $search['query']['bool']['filter'][]['query_string'] = [
                    'query' => $keyword . '*',
                    'fields' => [
                        'object_singular','object_plural','object_detail',
                    ],
                    'default_operator' => 'AND'
                ];
            }
        }


        //generic full match filters

        //object_active
        if (!isset($filter['object_active'])) {
            $filter['object_active'] = 1;
        }


        foreach ($filter as $key => $value) {
            $search['query']['bool']['filter'][]['term'][$key] = $value;
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search['sort'] = [$sort => $direction];
        }

        try {
            $results = $this->resource->search([
                'index' => static::INDEX_NAME,
                'type' => static::INDEX_TYPE,
                'body' => $search,
                'size' => $range,
                'from' => $start
            ]);
        } catch (NoNodesAvailableException $e) {
            return false;
        }

        // fix it
        $rows = array();

        foreach ($results['hits']['hits'] as $item) {
            $rows[] = $item['_source'];
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $results['hits']['total']
        ];
    }

    /**
     * Adds System Schema
     *
     * @param SystemSchema $schema
     *
     * @return SqlService
     */
    public function setSchema(SystemSchema $schema)
    {
        $this->schema = $schema;
        return $this;
    }
}
