<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\User\Service as UserService;
use Cradle\Module\User\Validator as UserValidator;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * User Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = UserValidator::getCreateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['user_meta'])) {
        $data['user_meta'] = json_encode($data['user_meta']);
    }

    if (isset($data['user_files'])) {
        $data['user_files'] = json_encode($data['user_files']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    //save user to database
    $results = $userSql->create($data);
    //link comment
    if (isset($data['comment_id'])) {
        $userSql->linkComment($results['user_id'], $data['comment_id']);
    }
    //link address
    if (isset($data['address_id'])) {
        $userSql->linkAddress($results['user_id'], $data['address_id']);
    }
    //link history
    if (isset($data['history_id'])) {
        $userSql->linkHistory($results['user_id'], $data['history_id']);
    }
    //link user
    if (isset($data['user_id'])) {
        $userSql->linkUser($results['user_id'], $data['user_id']);
    }

    //index user
    $userElastic->create($results['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * User Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['user_id'])) {
        $id = $data['user_id'];
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
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $userRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $userElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $userSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $userRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * User Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the user detail
    $this->trigger('user-detail', $request, $response);

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
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    //save to database
    $results = $userSql->update([
        'user_id' => $data['user_id'],
        'user_active' => 0
    ]);

    //remove from index
    $userElastic->remove($data['user_id']);

    //invalidate cache
    $userRedis->removeDetail($data['user_id']);
    $userRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * User Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the user detail
    $this->trigger('user-detail', $request, $response);

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
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    //save to database
    $results = $userSql->update([
        'user_id' => $data['user_id'],
        'user_active' => 1
    ]);

    //create index
    $userElastic->create($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * User Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-search', function ($request, $response) {
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
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $userRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $userElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $userSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $userRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * User Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the user detail
    $this->trigger('user-detail', $request, $response);

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
    $errors = UserValidator::getUpdateErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data

    if (isset($data['user_meta'])) {
        $data['user_meta'] = json_encode($data['user_meta']);
    }

    if (isset($data['user_files'])) {
        $data['user_files'] = json_encode($data['user_files']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    //save user to database
    $results = $userSql->update($data);

    //index user
    $userElastic->update($response->getResults('user_id'));

    //invalidate cache
    $userRedis->removeDetail($response->getResults('user_id'));
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * User Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-import', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //set counter
    $results = [
        'data' => [],
        'new' => 0,
        'old' => 0
    ];

    //----------------------------//
    // 2. Validate Data
    //validate data
    $errors = [];
    foreach ($data['rows'] as $i => $row) {
        $error = UserValidator::getCreateErrors($row);

        //if there are errors
        if (!empty($error)) {
            $errors[$i] = $error;
        }
    }

    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Row/s')
            ->set('json', 'validation', $errors);
    }

    // There is no error,
    // So proceed on adding/updating the items one by one
    foreach ($data['rows'] as $i => $row) {
        if (isset($row['user_created'])) {
            unset($row['user_created']);
        }

        if (isset($row['user_updated'])) {
            unset($row['user_updated']);
        }

        $rowRequest = Request::i()
            ->setStage($row);

        $rowResponse = Response::i()->load();

        cradle()->trigger('user-detail', $rowRequest, $rowResponse);

        if ($rowResponse->hasResults()) {
            // trigger single object update event
            cradle()->trigger('user-update', $rowRequest, $rowResponse);

            // check response if there is an error
            if ($rowResponse->isError()) {
                $results['data'][$i] = [
                    'action' => 'update',
                    'row' => [],
                    'error' => $rowResponse->getMessage()
                ];
                continue;
            }

            //increment old counter
            $results['data'][$i] = [
                'action' => 'update',
                'row' => $rowResponse->getResults(),
                'error' => false
            ];

            $results['old'] ++;
            continue;
        }

        // trigger single object update event
        cradle()->trigger('user-create', $rowRequest, $rowResponse);

        // check response if there is an error
        if ($rowResponse->isError()) {
            $results['data'][$i] = [
                'action' => 'create',
                'row' => [],
                'error' => $rowResponse->getMessage()
            ];
            continue;
        }

        //increment old counter
        $results['data'][$i] = [
            'action' => 'create',
            'row' => $rowResponse->getResults(),
            'error' => false
        ];

        $results['new'] ++;
    }

    $response->setError(false)->setResults($results);
});
