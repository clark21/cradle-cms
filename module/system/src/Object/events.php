<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Object\Service as ObjectService;
use Cradle\Module\System\Object\Validator as ObjectValidator;
use Cradle\Module\System\Object\Schema as ObjectSchema;

use Cradle\Module\System\Schema as SystemSchema;
use Cradle\Module\System\Exception as SystemException;

/**
 * System Object Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    $errors = $schema
        ->model()
        ->validator()
        ->getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $schema
        ->model()
        ->formatter()
        ->formatData(
            $data,
            $this->package('global')->service('s3-main'),
            $this->package('global')->path('upload')
        );

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');
    //save object to database
    $results = $objectSql->create($data);

    //get the primary value
    $primary = $results[$schema->getPrimary()];

    $table = $schema->getTableName();
    $relations = $schema->getRelations();

    //loop through relations
    foreach ($relations as $name => $relation) {
        //link relations
        if(isset($data[$relation['primary2']])) {
            $objectSql->link(
                $table,
                $primary,
                $data[$relation['primary2']]
            );
        }
    }

    //index object
    $objectElastic->create($primary);

    //invalidate cache
    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * System Object Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    $id = $key = null;
    $slugs = $schema->getSlugs($schema->getPrimary());

    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $id = $data[$slug];
            $key = $slug;
            break;
        }
    }

    //----------------------------//
    // 2. Validate Data
    //we need an id
    if (!$id) {
        return $response->setError(true, 'Invalid ID');
    }

    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $objectRedis->getDetail($key . '-' . $id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $objectElastic->get($key, $id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $objectSql->get($key, $id);
        }

        if ($results) {
            //cache it from database or index
            $objectRedis->createDetail($key . '-' . $id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * System Object Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    $primary = $schema->getPrimary();
    $active = $schema->getActive();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save to database
    if($active) {
        $payload = [];
        $payload[$primary] = $data[$primary];
        $payload[$active] = 0;

        $results = $objectSql->update($payload);
    } else {
        $results = $objectSql->remove($data[$primary]);
    }

    //remove from index
    $objectElastic->remove($data[$primary]);

    //invalidate cache
    $slugs = $schema->getSlugs($primary);
    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $objectRedis->removeDetail($data[$slug]);
        }
    }

    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * System Object Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);
    $primary = $schema->getPrimary();
    $active = $schema->getActive();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save to database
    $payload = [];
    $payload[$primary] = $data[$primary];
    $payload[$active] = 1;

    $results = $objectSql->update($payload);

    //create index
    $objectElastic->create($data[$primary]);

    //invalidate cache
    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * System Object Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $objectRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $objectElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $objectSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $objectRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * System Object Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('system-object-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('system-object-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    if(!isset($data['schema'])) {
        throw SystemException::forNoSchema();
    }

    $schema = SystemSchema::i($data['schema']);

    //----------------------------//
    // 2. Validate Data
    $errors = $schema
        ->model()
        ->validator()
        ->getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $schema
        ->model()
        ->formatter()
        ->formatData(
            $data,
            $this->package('global')->service('s3-main'),
            $this->package('global')->path('upload')
        );

    //get the primary value
    $primary = $schema->getPrimary();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = $schema->model()->service('sql');
    $objectRedis = $schema->model()->service('redis');
    $objectElastic = $schema->model()->service('elastic');

    //save object to database
    $results = $objectSql->update($data);

    //index object
    $objectElastic->update($results[$primary]);

    //invalidate cache
    $slugs = $schema->getSlugs($primary);
    foreach($slugs as $slug) {
        if(isset($data[$slug])) {
            $objectRedis->removeDetail($data[$slug]);
        }
    }

    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
