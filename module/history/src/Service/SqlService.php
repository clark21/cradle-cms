<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\History\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * History SQL Service
 *
 * @vendor   Acme
 * @package  history
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'history';

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
            ->setHistoryCreated(date('Y-m-d H:i:s'))
            ->setHistoryUpdated(date('Y-m-d H:i:s'))
            ->save('history')
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
        $search = $this->resource->search('history');
        
        $search->innerJoinUsing('history_user', 'history_id');
        $search->innerJoinUsing('user', 'user_id');
        
        $search->filterByHistoryId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
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
            ->setHistoryId($id)
            ->remove('history');
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

        
        if (!isset($filter['history_active'])) {
            $filter['history_active'] = 1;
        }
        

        $search = $this->resource
            ->search('history')
            ->setStart($start)
            ->setRange($range);

        
        //join user
        $search->innerJoinUsing('history_user', 'history_id');
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
                $where[] = 'LOWER(history_activity) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(user_name) LIKE %s';
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
            if($results['history_meta']) {
                $rows[$i]['history_meta'] = json_decode($results['history_meta'], true);
            } else {
                $rows[$i]['history_meta'] = [];
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
            ->setHistoryUpdated(date('Y-m-d H:i:s'))
            ->save('history')
            ->get();
    }
    /**
     * Links user
     *
     * @param *int $historyPrimary
     * @param *int $userPrimary
     */
    public function linkUser($historyPrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setUserId($userPrimary)
            ->insert('history_user');
    }

    /**
     * Unlinks user
     *
     * @param *int $historyPrimary
     * @param *int $userPrimary
     */
    public function unlinkUser($historyPrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setUserId($userPrimary)
            ->remove('history_user');
    }
    
}
