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

    if(isset($data['user_meta'])) {
        $data['user_meta'] = json_encode($data['user_meta']);
    }

    if(isset($data['user_files'])) {
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
    if(isset($data['comment_id'])) {
        $userSql->linkComment($results['user_id'], $data['comment_id']);
    }
    //link address
    if(isset($data['address_id'])) {
        $userSql->linkAddress($results['user_id'], $data['address_id']);
    }
    //link history
    if(isset($data['history_id'])) {
        $userSql->linkHistory($results['user_id'], $data['history_id']);
    }
    //link user
    if(isset($data['user_id'])) {
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

    if(isset($data['user_meta'])) {
        $data['user_meta'] = json_encode($data['user_meta']);
    }

    if(isset($data['user_files'])) {
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
 * Links User to comment
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-link-comment', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['comment_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->linkComment(
        $data['user_id'],
        $data['comment_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks User from comment
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlink-comment', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['comment_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkComment(
        $data['user_id'],
        $data['comment_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all User from comment
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlinkall-comment', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkAllComment($data['user_id']);

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links User to address
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-link-address', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['address_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->linkAddress(
        $data['user_id'],
        $data['address_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks User from address
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlink-address', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['address_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkAddress(
        $data['user_id'],
        $data['address_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all User from address
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlinkall-address', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkAllAddress($data['user_id']);

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links User to history
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-link-history', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->linkHistory(
        $data['user_id'],
        $data['history_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks User from history
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlink-history', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'], $data['history_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkHistory(
        $data['user_id'],
        $data['history_id']
    );

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all User from history
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlinkall-history', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkAllHistory($data['user_id']);

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Links User to user
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-link-user', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id_1'], $data['user_id_2'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->linkUser(
        $data['user_id_1'],
        $data['user_id_2']
    );

    //index post
    $userElastic->update($data['user_id_1']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks User from user
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlink-user', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id_1'], $data['user_id_2'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkUser(
        $data['user_id_1'],
        $data['user_id_2']
    );

    //index post
    $userElastic->update($data['user_id_1']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks all User from user
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('user-unlinkall-user', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['user_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $userSql = UserService::get('sql');
    $userRedis = UserService::get('redis');
    $userElastic = UserService::get('elastic');

    $results = $userSql->unlinkAllUser($data['user_id']);

    //index post
    $userElastic->update($data['user_id']);

    //invalidate cache
    $userRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});
