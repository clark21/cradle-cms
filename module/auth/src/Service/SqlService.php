<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Auth\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\System\Utility\Service\SqlServiceInterface;
use Cradle\Module\System\Utility\Service\AbstractSqlService;

/**
 * Auth SQL Service
 *
 * @vendor   Acme
 * @package  auth
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'auth';

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
            ->setAuthCreated(date('Y-m-d H:i:s'))
            ->setAuthUpdated(date('Y-m-d H:i:s'))
            ->save('auth')
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
        $search = $this->resource->search('auth');

        $search->innerJoinUsing('auth_user', 'auth_id');
        $search->innerJoinUsing('user', 'user_id');

        if (is_numeric($id)) {
            $search->filterByAuthId($id);
        } else {
            $search->filterByAuthSlug($id);
        }

        $results = $search->getRow();

        if (!$results) {
            return $results;
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
            ->setAuthId($id)
            ->remove('auth');
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

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['auth_active'])) {
            $filter['auth_active'] = 1;
        }


        $search = $this->resource
            ->search('auth')
            ->setStart($start)
            ->setRange($range);


        //join user
        $search->innerJoinUsing('auth_user', 'auth_id');
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
                $where[] = 'LOWER(auth_slug) LIKE %s';
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
            ->setAuthUpdated(date('Y-m-d H:i:s'))
            ->save('auth')
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $authSlug
     *
     * @return bool
     */
    public function exists($authSlug)
    {
        $search = $this->resource
            ->search('auth')
            ->filterByAuthSlug($authSlug);

        return !!$search->getRow();
    }

    /**
     * Links user
     *
     * @param *int $authPrimary
     * @param *int $userPrimary
     */
    public function linkUser($authPrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setAuthId($authPrimary)
            ->setUserId($userPrimary)
            ->insert('auth_user');
    }

    /**
     * Unlinks user
     *
     * @param *int $authPrimary
     * @param *int $userPrimary
     */
    public function unlinkUser($authPrimary, $userPrimary)
    {
        return $this->resource
            ->model()
            ->setAuthId($authPrimary)
            ->setUserId($userPrimary)
            ->remove('auth_user');
    }
}
