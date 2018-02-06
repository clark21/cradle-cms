<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Meta\Service as MetaService;
use Cradle\Module\Meta\Validator as MetaValidator;

/**
 * Meta Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = MetaValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['meta_fields'])) {
        $data['meta_fields'] = json_encode($data['meta_fields']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    //save meta to database
    $results = $metaSql->create($data);

    //index meta
    $metaElastic->create($results['meta_id']);

    //invalidate cache
    $metaRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Meta Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['meta_id'])) {
        $id = $data['meta_id'];
    } else if (isset($data['meta_key'])) {
        $id = $data['meta_key'];
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
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $metaRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $metaElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $metaSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $metaRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Meta Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the meta detail
    $this->trigger('meta-detail', $request, $response);

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
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    //save to database
    $results = $metaSql->update([
        'meta_id' => $data['meta_id'],
        'meta_active' => 0
    ]);

    //remove from index
    $metaElastic->remove($data['meta_id']);

    //invalidate cache
    $metaRedis->removeDetail($data['meta_id']);
    $metaRedis->removeDetail($data['meta_key']);
    $metaRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Meta Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the meta detail
    $this->trigger('meta-detail', $request, $response);

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
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    //save to database
    $results = $metaSql->update([
        'meta_id' => $data['meta_id'],
        'meta_active' => 1
    ]);

    //create index
    $metaElastic->create($data['meta_id']);

    //invalidate cache
    $metaRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Meta Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-search', function ($request, $response) {
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
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $metaRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $metaElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $metaSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $metaRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Meta Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('meta-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the meta detail
    $this->trigger('meta-detail', $request, $response);

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
    $errors = MetaValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if(isset($data['meta_fields'])) {
        $data['meta_fields'] = json_encode($data['meta_fields']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $metaSql = MetaService::get('sql');
    $metaRedis = MetaService::get('redis');
    $metaElastic = MetaService::get('elastic');

    //save meta to database
    $results = $metaSql->update($data);

    //index meta
    $metaElastic->update($response->getResults('meta_id'));

    //invalidate cache
    $metaRedis->removeDetail($response->getResults('meta_id'));
    $metaRedis->removeDetail($data['meta_key']);
    $metaRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
