<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\User\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * User SQL Service
 *
 * @vendor   Acme
 * @package  user
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'user';

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
            ->setUserCreated(date('Y-m-d H:i:s'))
            ->setUserUpdated(date('Y-m-d H:i:s'))
            ->save('user')
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
        $search = $this->resource->search('user');
        
        
        $search->filterByUserId($id);

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
            ->setUserId($id)
            ->remove('user');
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

        

        
        if (!isset($filter['user_active'])) {
            $filter['user_active'] = 1;
        }
        

        $search = $this->resource
            ->search('user')
            ->setStart($start)
            ->setRange($range);

        
        

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            
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
            ->setUserUpdated(date('Y-m-d H:i:s'))
            ->save('user')
            ->get();
    }
    /**
     * Links comment
     *
     * @param *int $userPrimary
     * @param *int $commentPrimary
     */
    public function linkComment($userPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setCommentId($commentPrimary)
            ->insert('user_comment');
    }

    /**
     * Unlinks comment
     *
     * @param *int $userPrimary
     * @param *int $commentPrimary
     */
    public function unlinkComment($userPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setCommentId($commentPrimary)
            ->remove('user_comment');
    }

    /**
    * Unlinks All comment
    *
    * @param *int $userPrimary
    * @param *int $commentPrimary
    */
    public function unlinkAllComment($userPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->remove('user_comment');
    }
    
    /**
     * Links address
     *
     * @param *int $userPrimary
     * @param *int $addressPrimary
     */
    public function linkAddress($userPrimary, $addressPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setAddressId($addressPrimary)
            ->insert('user_address');
    }

    /**
     * Unlinks address
     *
     * @param *int $userPrimary
     * @param *int $addressPrimary
     */
    public function unlinkAddress($userPrimary, $addressPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setAddressId($addressPrimary)
            ->remove('user_address');
    }

    /**
    * Unlinks All address
    *
    * @param *int $userPrimary
    * @param *int $addressPrimary
    */
    public function unlinkAllAddress($userPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->remove('user_address');
    }
    
    /**
     * Links history
     *
     * @param *int $userPrimary
     * @param *int $historyPrimary
     */
    public function linkHistory($userPrimary, $historyPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setHistoryId($historyPrimary)
            ->insert('user_history');
    }

    /**
     * Unlinks history
     *
     * @param *int $userPrimary
     * @param *int $historyPrimary
     */
    public function unlinkHistory($userPrimary, $historyPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->setHistoryId($historyPrimary)
            ->remove('user_history');
    }

    /**
    * Unlinks All history
    *
    * @param *int $userPrimary
    * @param *int $historyPrimary
    */
    public function unlinkAllHistory($userPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->remove('user_history');
    }
    
    /**
     * Links user
     *
     * @param *int $userPrimary
     * @param *int $userPrimary
     */
    public function linkUser($userPrimary1, $userPrimary2)
    {
        return $this->resource
            ->model()
            ->setUserId1($userPrimary1)
            ->setUserId2($userPrimary2)
            ->insert('user_user');
    }

    /**
     * Unlinks user
     *
     * @param *int $userPrimary
     * @param *int $userPrimary
     */
    public function unlinkUser($userPrimary1, $userPrimary2)
    {
        return $this->resource
            ->model()
            ->setUserId1($userPrimary)
            ->setUserId2($userPrimary)
            ->remove('user_user');
    }

    /**
    * Unlinks All user
    *
    * @param *int $userPrimary
    * @param *int $userPrimary
    */
    public function unlinkAllUser($userPrimary)
    {
        return $this->resource
            ->model()
            ->setUserId($userPrimary)
            ->remove('user_user');
    }
    
}
