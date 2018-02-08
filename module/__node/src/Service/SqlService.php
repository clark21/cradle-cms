<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Node\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Node SQL Service
 *
 * @vendor   Acme
 * @package  node
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'node';

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
            ->setNodeCreated(date('Y-m-d H:i:s'))
            ->setNodeUpdated(date('Y-m-d H:i:s'))
            ->save('node')
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
        $search = $this->resource->search('node');
        
        $search->innerJoinUsing('node_user', 'node_id');
        $search->innerJoinUsing('user', 'user_id');
        
        if (is_numeric($id)) {
            $search->filterByNodeId($id);
        } else if (isset($data['node_slug'])) {
            $search->filterByNodeSlug($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['node_tags']) {
            $results['node_tags'] = json_decode($results['node_tags'], true);
        } else {
            $results['node_tags'] = [];
        }

        if($results['node_meta']) {
            $results['node_meta'] = json_decode($results['node_meta'], true);
        } else {
            $results['node_meta'] = [];
        }

        if($results['node_files']) {
            $results['node_files'] = json_decode($results['node_files'], true);
        } else {
            $results['node_files'] = [];
        }

        if($results['user_meta']) {
            $results['user_meta'] = json_decode($results['user_meta'], true);
        } else {
            $results['user_meta'] = [];
        }

        if($results['user_files']) {
            $results['user_files'] = json_decode($results['user_files'], true);
        } else {
            $results['user_files'] = [];
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
            ->setNodeId($id)
            ->remove('node');
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
        

        
        if (!isset($filter['node_active'])) {
            $filter['node_active'] = 1;
        }
        

        $search = $this->resource
            ->search('node')
            ->setStart($start)
            ->setRange($range);

        
        //join user
        $search->innerJoinUsing('node_user', 'node_id');
        $search->innerJoinUsing('user', 'user_id');
        

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
                $where[] = 'LOWER(node_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(node_detail) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(node_status) LIKE %s';
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
            
            if($results['node_tags']) {
                $rows[$i]['node_tags'] = json_decode($results['node_tags'], true);
            } else {
                $rows[$i]['node_tags'] = [];
            }
            
            if($results['node_meta']) {
                $rows[$i]['node_meta'] = json_decode($results['node_meta'], true);
            } else {
                $rows[$i]['node_meta'] = [];
            }
            
            if($results['node_files']) {
                $rows[$i]['node_files'] = json_decode($results['node_files'], true);
            } else {
                $rows[$i]['node_files'] = [];
            }
            
            if($results['user_meta']) {
                $rows[$i]['user_meta'] = json_decode($results['user_meta'], true);
            } else {
                $rows[$i]['user_meta'] = [];
            }
            
            if($results['user_files']) {
                $rows[$i]['user_files'] = json_decode($results['user_files'], true);
            } else {
                $rows[$i]['user_files'] = [];
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
            ->setNodeUpdated(date('Y-m-d H:i:s'))
            ->save('node')
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $nodeSlug
     *
     * @return bool
     */
    public function exists($nodeSlug)
    {
        $search = $this->resource
            ->search('node')
            ->filterByNodeSlug($nodeSlug);

        return !!$search->getRow();
    }
    
    /**
     * Links user
     *
     * @param *int $nodePrimary
     * @param *int $userPrimary
     */
    public function linkUser($nodePrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setNodeId($nodePrimary)
            ->setUserId($userPrimary)
            ->insert('node_user');
    }

    /**
     * Unlinks user
     *
     * @param *int $nodePrimary
     * @param *int $userPrimary
     */
    public function unlinkUser($nodePrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setNodeId($nodePrimary)
            ->setUserId($userPrimary)
            ->remove('node_user');
    }
    
    /**
     * Links node
     *
     * @param *int $nodePrimary
     * @param *int $nodePrimary
     */
    public function linkNode($nodePrimary1, $nodePrimary2)
    {
        return $this->resource
            ->model()
            ->setNodeId1($nodePrimary1)
            ->setNodeId2($nodePrimary2)
            ->insert('node_node');
    }

    /**
     * Unlinks node
     *
     * @param *int $nodePrimary
     * @param *int $nodePrimary
     */
    public function unlinkNode($nodePrimary1, $nodePrimary2)
    {
        return $this->resource
            ->model()
            ->setNodeId1($nodePrimary)
            ->setNodeId2($nodePrimary)
            ->remove('node_node');
    }

    /**
    * Unlinks All node
    *
    * @param *int $nodePrimary
    * @param *int $nodePrimary
    */
    public function unlinkAllNode($nodePrimary)
    {
        return $this->resource
            ->model()
            ->setNodeId($nodePrimary)
            ->remove('node_node');
    }
    
}
