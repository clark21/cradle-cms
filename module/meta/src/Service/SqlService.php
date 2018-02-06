<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Meta\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Meta SQL Service
 *
 * @vendor   Acme
 * @package  meta
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'meta';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        return $this->resource
            ->model($data)
            ->setMetaCreated(date('Y-m-d H:i:s'))
            ->setMetaUpdated(date('Y-m-d H:i:s'))
            ->save('meta')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('meta');
        
        if (is_numeric($id)) {
            $search->filterByMetaId($id);
        } else if (isset($data['meta_key'])) {
            $search->filterByMetaKey($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['meta_fields']) {
            $results['meta_fields'] = json_decode($results['meta_fields'], true);
        } else {
            $results['meta_fields'] = [];
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setMetaId($id)
            ->remove('meta');
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;
        
        $keywords = null;
        
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

        
        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }
        

        
        if (!isset($filter['meta_active'])) {
            $filter['meta_active'] = 1;
        }
        

        $search = $this->resource
            ->search('meta')
            ->setStart($start)
            ->setRange($range);

        

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        
        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(meta_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(meta_singular) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(meta_plural) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(meta_detail) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }
        

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            
            if($results['meta_fields']) {
                $rows[$i]['meta_fields'] = json_decode($results['meta_fields'], true);
            } else {
                $rows[$i]['meta_fields'] = [];
            }
            
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setMetaUpdated(date('Y-m-d H:i:s'))
            ->save('meta')
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $metaKey
     *
     * @return bool
     */
    public function exists($metaKey)
    {
        $search = $this->resource
            ->search('meta')
            ->filterByMetaKey($metaKey);

        return !!$search->getRow();
    }
    
}
