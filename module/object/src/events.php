<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Object\Service as ObjectService;
use Cradle\Module\Object\Validator as ObjectValidator;

/**
 * Object Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ObjectValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['object_fields'])) {
        $data['object_fields'] = json_encode($data['object_fields']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

    //save object to database
    $results = $objectSql->create($data);

    //index object
    $objectElastic->create($results['object_id']);

    //invalidate cache
    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Object Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['object_id'])) {
        $id = $data['object_id'];
    } else if (isset($data['object_key'])) {
        $id = $data['object_key'];
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
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $objectRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $objectElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $objectSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $objectRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Object Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

    //save to database
    $results = $objectSql->update([
        'object_id' => $data['object_id'],
        'object_active' => 0
    ]);

    //remove from index
    $objectElastic->remove($data['object_id']);

    //invalidate cache
    $objectRedis->removeDetail($data['object_id']);
    $objectRedis->removeDetail($data['object_key']);
    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Object Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('object-detail', $request, $response);

    //----------------------------//
    // 2. Validate Data
    if ($response->isError()) {
        return;
    }

    //----------------------------//
    // 3. Prepare Data
    $data = $response->getResults();

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

    //save to database
    $results = $objectSql->update([
        'object_id' => $data['object_id'],
        'object_active' => 1
    ]);

    //create index
    $objectElastic->create($data['object_id']);

    //invalidate cache
    $objectRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Object Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-search', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    //no validation needed
    //----------------------------//
    // 3. Prepare Data
    //no preparation needed
    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

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
 * Object Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('object-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the object detail
    $this->trigger('object-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = ObjectValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['object_fields'])) {
        $data['object_fields'] = json_encode($data['object_fields']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $objectSql = ObjectService::get('sql');
    $objectRedis = ObjectService::get('redis');
    $objectElastic = ObjectService::get('elastic');

    //save object to database
    $results = $objectSql->update($data);

    //index object
    $objectElastic->update($response->getResults('object_id'));

    //invalidate cache
    $objectRedis->removeDetail($response->getResults('object_id'));
    $objectRedis->removeDetail($data['object_key']);
    $objectRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
