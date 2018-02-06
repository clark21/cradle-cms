<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Node\Service as NodeService;
use Cradle\Module\Node\Validator as NodeValidator;

use Cradle\Module\Utility\File;

/**
 * Node Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = NodeValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['node_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['node_image'] = File::base64ToS3($data['node_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['node_image'] = File::base64ToUpload($data['node_image'], $upload);
    }

    if(isset($data['node_tags'])) {
        $data['node_tags'] = json_encode($data['node_tags']);
    }

    if(isset($data['node_meta'])) {
        $data['node_meta'] = json_encode($data['node_meta']);
    }

    //if there is an image
    if (isset($data['node_files'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['node_files'] = File::base64ToS3($data['node_files'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['node_files'] = File::base64ToUpload($data['node_files'], $upload);
    }

    if(isset($data['node_files'])) {
        $data['node_files'] = json_encode($data['node_files']);
    }

    if(isset($data['node_published'])) {
        $data['node_published'] = date('Y-m-d H:i:s', strtotime($data['node_published']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    //save node to database
    $results = $nodeSql->create($data);
    //link user
    if(isset($data['user_id'])) {
        $nodeSql->linkUser($results['node_id'], $data['user_id']);
    }
    //link node
    if(isset($data['node_id'])) {
        $nodeSql->linkNode($results['node_id'], $data['node_id']);
    }

    //index node
    $nodeElastic->create($results['node_id']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Node Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['node_id'])) {
        $id = $data['node_id'];
    } else if (isset($data['node_slug'])) {
        $id = $data['node_slug'];
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
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $nodeRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $nodeElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $nodeSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $nodeRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Node Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the node detail
    $this->trigger('node-detail', $request, $response);

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
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    //save to database
    $results = $nodeSql->update([
        'node_id' => $data['node_id'],
        'node_active' => 0
    ]);

    //remove from index
    $nodeElastic->remove($data['node_id']);

    //invalidate cache
    $nodeRedis->removeDetail($data['node_id']);
    $nodeRedis->removeDetail($data['node_slug']);
    $nodeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Node Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the node detail
    $this->trigger('node-detail', $request, $response);

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
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    //save to database
    $results = $nodeSql->update([
        'node_id' => $data['node_id'],
        'node_active' => 1
    ]);

    //create index
    $nodeElastic->create($data['node_id']);

    //invalidate cache
    $nodeRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Node Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-search', function ($request, $response) {
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
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $nodeRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $nodeElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $nodeSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $nodeRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Node Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the node detail
    $this->trigger('node-detail', $request, $response);

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
    $errors = NodeValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    //if there is an image
    if (isset($data['node_image'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['node_image'] = File::base64ToS3($data['node_image'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['node_image'] = File::base64ToUpload($data['node_image'], $upload);
    }

    if(isset($data['node_tags'])) {
        $data['node_tags'] = json_encode($data['node_tags']);
    }

    if(isset($data['node_meta'])) {
        $data['node_meta'] = json_encode($data['node_meta']);
    }

    //if there is an image
    if (isset($data['node_files'])) {
        //upload files
        //try cdn if enabled
        $config = $this->package('global')->service('s3-main');
        $data['node_files'] = File::base64ToS3($data['node_files'], $config);
        //try being old school
        $upload = $this->package('global')->path('upload');
        $data['node_files'] = File::base64ToUpload($data['node_files'], $upload);
    }

    if(isset($data['node_files'])) {
        $data['node_files'] = json_encode($data['node_files']);
    }

    if(isset($data['node_published'])) {
        $data['node_published'] = date('Y-m-d H:i:s', strtotime($data['node_published']));
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    //save node to database
    $results = $nodeSql->update($data);

    //index node
    $nodeElastic->update($response->getResults('node_id'));

    //invalidate cache
    $nodeRedis->removeDetail($response->getResults('node_id'));
    $nodeRedis->removeDetail($data['node_slug']);
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links Node to user
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-link-user', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['node_id'], $data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = $nodeSql->linkUser(
        $data['node_id'],
        $data['user_id']
    );

    //index post
    $nodeElastic->update($data['node_id']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks Node from user
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-unlink-user', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['node_id'], $data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = $nodeSql->unlinkUser(
        $data['node_id'],
        $data['user_id']
    );

    //index post
    $nodeElastic->update($data['node_id']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links Node to node
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-link-node', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['node_id_1'], $data['node_id_2'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = $nodeSql->linkNode(
        $data['node_id_1'],
        $data['node_id_2']
    );

    //index post
    $nodeElastic->update($data['node_id_1']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks Node from node
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-unlink-node', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['node_id_1'], $data['node_id_2'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = $nodeSql->unlinkNode(
        $data['node_id_1'],
        $data['node_id_2']
    );

    //index post
    $nodeElastic->update($data['node_id_1']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all Node from node
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('node-unlinkall-node', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['node_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $nodeSql = NodeService::get('sql');
    $nodeRedis = NodeService::get('redis');
    $nodeElastic = NodeService::get('elastic');

    $results = $nodeSql->unlinkAllNode($data['node_id']);

    //index post
    $nodeElastic->update($data['node_id']);

    //invalidate cache
    $nodeRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
