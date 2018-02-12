<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\System\Object\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

use Cradle\Module\System\Schema as SystemSchema;
use Cradle\Module\System\Exception as SystemException;

/**
 * Object SQL Service
 *
 * @vendor   Acme
 * @package  object
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
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
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param *array $object
     * @param *array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getTableName();
        $created = $this->schema->getCreated($object);
        $updated = $this->schema->getUpdated($object);

        if($created) {
            $data[$created] = date('Y-m-d H:i:s');
        }

        if($updated) {
            $data[$updated] = date('Y-m-d H:i:s');
        }

        return $this
            ->resource
            ->model($data)
            ->save($table)
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *array $object
     * @param *int   $id
     *
     * @return array
     */
    public function get($key, $id)
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $search = $this
            ->resource
            ->search($this->schema->getTableName())
            ->addFilter($key . ' = %s', $id);

        //get 1:1 relations
        $relations = $this->schema->getRelations(1);

        foreach($relations as $table => $relation) {
            $search
                ->innerJoinUsing(
                    $relation['name'],
                    $relation['primary1']
                )
                ->innerJoinUsing(
                    $table,
                    $relation['primary2']
                );
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        $fields = $this->schema->getJsonFields();

        foreach($fields as $field) {
            if(isset($results[$field]) && $results[$field]) {
                $results[$field] = json_decode($results[$field], true);
            } else {
                $results[$field] = [];
            }
        }

        //get 1:0 relations
        $relations = $this->schema->getRelations(0);
        foreach($relations as $table => $relation) {
            $results[$table] = $this
                ->resource
                ->search($relation['name'])
                ->innerJoinUsing($table, $relation['primary2'])
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRow();
        }

        //get 1:N relations
        $relations = $this->schema->getRelations(2);
        foreach($relations as $table => $relation) {
            $results[$table] = $this
                ->resource
                ->search($relation['name'])
                ->innerJoinUsing($table, $relation['primary2'])
                ->addFilter($relation['primary1'] . ' = %s', $id)
                ->getRows();
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *array $object
     * @param *int $id
     */
    public function remove($id)
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getTableName();
        $primary = $this->schema->getPrimary();
        //please rely on SQL CASCADING ON DELETE
        $model = $this->resource->model();
        $model[$primary] = $id;
        $model->remove($table);
    }

    /**
     * Search in database
     *
     * @param *array $object
     * @param array  $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

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

        $active = $this->schema->getActive();
        if ($active && !isset($filter[$active])) {
            $filter[$active] = 1;
        }

        $search = $this->resource
            ->search('object')
            ->setStart($start)
            ->setRange($range);

        //get 1:1 relations
        $relations = $this->schema->getRelations(1);

        foreach($relations as $table => $relation) {
            $search
                ->innerJoinUsing(
                    $relation['name'],
                    $relation['primary1']
                )
                ->innerJoinUsing(
                    $table,
                    $relation['primary2']
                );
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //keyword?
        $searchable = $this->schema->getSearchable(1);
        if(!empty($searchable)) {
            $keywords = [];

            if (isset($data['q'])) {
                $keywords = $data['q'];

                if(!is_array($keywords)) {
                    $keywords = [$keywords];
                }
            }

            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                foreach($searchable as $name) {
                    $where[] = 'LOWER(' . $name . ') LIKE %s';
                    $or[] = '%' . strtolower($keyword) . '%';
                }

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();
        $fields = $this->schema->getJsonFields();

        foreach($rows as $i => $results) {
            foreach($fields as $field) {
                if(isset($results[$field]) && $results[$field]) {
                    $rows[$i][$field] = json_decode($results[$field], true);
                } else {
                    $rows[$i][$field] = [];
                }
            }
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
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

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $table = $this->schema->getTableName();
        $updated = $this->schema->getUpdated();

        if($updated) {
            $data[$updated] = date('Y-m-d H:i:s');
        }

        return $this
            ->resource
            ->model($data)
            ->save($table)
            ->get();
    }

    /**
     * Checks to see if unique.0 already exists
     *
     * @param *string $objectKey
     *
     * @return bool
     */
    public function exists($object, $key, $value)
    {
        if(is_null($this->schema)) {
            throw SystemException::forNoSchema();
        }

        $search = $this
            ->resource
            ->search($this->schema->getTableName())
            ->addFilter($key . ' = %s', $value);

        return !!$search->getRow();
    }
}
