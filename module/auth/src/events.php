<?php //-->
/**
 * This file is part of the Cradle PHP Kitchen Sink Faucet Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Auth\Service as AuthService;
use Cradle\Module\Auth\Validator as AuthValidator;

use Cradle\Module\System\Schema as SystemSchema;

use Cradle\Http\Request;
use Cradle\Http\Response;

/**
 * Auth Create Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-create', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $schema = SystemSchema::i('profile');

    //check if profile_email exist
    foreach ($schema->getFields() as $field) {
        if ($field['name'] === 'email') {
            $data['profile_email'] = $data['auth_slug'];

            //then reset stage
            $request->setStage($data);
        }
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getCreateErrors($data);

    $errors = $schema
        ->model()
        ->validator()
        ->getCreateErrors($data, $errors);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }


    //----------------------------//
    // 3. Prepare Data
    if (isset($data['auth_password'])) {
        $data['auth_password'] = md5($data['auth_password']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //save auth to database
    $results = $authSql->create($data);

    //link profile
    if (isset($data['profile_id'])) {
        $authSql->linkProfile($results['auth_id'], $data['profile_id']);
    } else {
        //create profile
        if (!$request->getStage('profile_name')) {
            // set profile name
            $request->setStage('profile_name', $request->getStage('auth_slug'));
        }

        $request->setStage('schema', 'profile');
        cradle()->trigger('system-object-create', $request, $response);

        if ($response->isError()) {
            return;
        }

        $user = $response->getResults();

        $authSql->linkProfile($results['auth_id'], $user['profile_id']);
    }

    //index auth
    $authElastic->create($results['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Detail Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-detail', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $id = null;
    if (isset($data['auth_id'])) {
        $id = $data['auth_id'];
    } else if (isset($data['auth_slug'])) {
        $id = $data['auth_slug'];
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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    $results = null;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $authRedis->getDetail($id);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $authElastic->get($id);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $authSql->get($id);
        }

        if ($results) {
            //cache it from database or index
            $authRedis->createDetail($id, $results);
        }
    }

    if (!$results) {
        return $response->setError(true, 'Not Found');
    }

    $response->setError(false)->setResults($results);
});

/**
 * Auth Forgot Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-forgot', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $this->trigger('auth-detail', $request, $response);

    if ($response->isError()) {
        return;
    }

    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 3. Validate Data
    //validate
    $errors = AuthValidator::getForgotErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //send mail
    $request->setSoftStage($response->getResults());

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    //try to queue, and if not
    if (!$this->package('global')->queue('auth-forgot-mail', $data)) {
        //send mail manually
        $this->trigger('auth-forgot-mail', $request, $response);
    }

    //return response format
    $response->setError(false);
});

/**
 * Auth Forgot Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-forgot-mail', function ($request, $response) {
    $config = $this->package('global')->service('mail-main');

    if (!$config) {
        return;
    }

    //if it's not configured
    if ($config['user'] === '<EMAIL ADDRESS>'
        || $config['pass'] === '<EMAIL PASSWORD>'
    ) {
        return;
    }

    //form hash
    $authId = $request->getStage('auth_id');
    $authUpdated = $request->getStage('auth_updated');
    $hash = md5($authId.$authUpdated);

    //form link
    $host = $request->getStage('host');
    $link = $host . '/recover/' . $authId . '/' . $hash;

    //prepare data
    $from = [];
    $from[$config['user']] = $config['name'];

    $to = [];
    $to[$request->getStage('auth_slug')] = null;

    $subject = $this->package('global')->translate('Password Recovery from Cradle!');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/recover.txt');
    $template = $handlebars->compile($contents);
    $text = $template(['link' => $link]);

    $contents = file_get_contents(__DIR__ . '/template/email/recover.html');
    $template = $handlebars->compile($contents);
    $html = $template([
        'host' => $host,
        'link' => $link
    ]);

    //send mail
    $message = new Swift_Message($subject);
    $message->setFrom($from);
    $message->setTo($to);
    $message->setBody($html, 'text/html');
    $message->addPart($text, 'text/plain');

    $transport = Swift_SmtpTransport::newInstance();
    $transport->setHost($config['host']);
    $transport->setPort($config['port']);
    $transport->setEncryption($config['type']);
    $transport->setUsername($config['user']);
    $transport->setPassword($config['pass']);

    $swift = Swift_Mailer::newInstance($transport);
    $swift->send($message, $failures);
});

/**
 * Links Authentication to profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-link-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['auth_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    $results = $authSql->linkProfile(
        $data['auth_id'],
        $data['profile_id']
    );

    //index post
    $authElastic->update($data['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Login Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-login', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getLoginErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Process Data
    $this->trigger('auth-detail', $request, $response);
});

/**
 * Auth Recover Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-recover', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getRecoverErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //update
    $this->trigger('auth-update', $request, $response);

    //return response format
    $response->setError(false);
});

/**
 * Auth Remove Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-remove', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the auth detail
    $this->trigger('auth-detail', $request, $response);

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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //save to database
    $results = $authSql->update([
        'auth_id' => $data['auth_id'],
        'auth_active' => 0
    ]);

    //remove from index
    $authElastic->remove($data['auth_id']);

    //invalidate cache
    $authRedis->removeDetail($data['auth_id']);
    $authRedis->removeDetail($data['auth_slug']);
    $authRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Auth Restore Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-restore', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the auth detail
    $this->trigger('auth-detail', $request, $response);

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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //save to database
    $results = $authSql->update([
        'auth_id' => $data['auth_id'],
        'auth_active' => 1
    ]);

    //create index
    $authElastic->create($data['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    $response->setError(false)->setResults($results);
});

/**
 * Auth Search Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-search', function ($request, $response) {
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
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    $results = false;

    //if no flag
    if (!$request->hasGet('nocache')) {
        //get it from cache
        $results = $authRedis->getSearch($data);
    }

    //if no results
    if (!$results) {
        //if no flag
        if (!$request->hasGet('noindex')) {
            //get it from index
            $results = $authElastic->search($data);
        }

        //if no results
        if (!$results) {
            //get it from database
            $results = $authSql->search($data);
        }

        if ($results) {
            //cache it from database or index
            $authRedis->createSearch($data, $results);
        }
    }

    //set response format
    $response->setError(false)->setResults($results);
});

/**
 * Unlinks Authentication from profile
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-unlink-profile', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    if (!isset($data['auth_id'], $data['profile_id'])) {
        return $response->setError(true, 'No ID provided');
    }

    //----------------------------//
    // 3. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    $results = $authSql->unlinkProfile(
        $data['auth_id'],
        $data['profile_id']
    );

    //index post
    $authElastic->update($data['auth_id']);

    //invalidate cache
    $authRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Update Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-update', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    //get the auth detail
    $this->trigger('auth-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //get data from stage
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    $schema = SystemSchema::i('profile');

    //check if user_email exist
    if (isset($data['auth_slug'])) {
        foreach ($schema->getFields() as $field) {
            if ($field['name'] === 'email') {
                $data['profile_email'] = $data['auth_slug'];

                //then reset stage
                $request->setStage($data);
            }
        }
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getUpdateErrors($data);

    $errors = $schema
        ->model()
        ->validator()
        ->getUpdateErrors($data, $errors);

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

    if (isset($data['auth_password'])) {
        $data['auth_password'] = md5($data['auth_password']);
    }

    //----------------------------//
    // 4. Process Data
    //this/these will be used a lot
    $authSql = AuthService::get('sql');
    $authRedis = AuthService::get('redis');
    $authElastic = AuthService::get('elastic');

    //save auth to database
    $results = $authSql->update($data);

    if (isset($data['profile_id'])) {
        $request->setStage('schema', 'profile');
        cradle()->trigger('system-object-update', $request, $response);
    }

    //index auth
    $authElastic->update($response->getResults('auth_id'));

    //invalidate cache
    $authRedis->removeDetail($response->getResults('auth_id'));
    $authRedis->removeDetail($data['auth_slug']);
    $authRedis->removeSearch();

    //return response format
    $response->setError(false)->setResults($results);
});

/**
 * Auth Verify Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-verify', function ($request, $response) {
    //----------------------------//
    // 1. Get Data
    $data = [];
    if ($request->hasStage()) {
        $data = $request->getStage();
    }

    //----------------------------//
    // 2. Validate Data
    $errors = AuthValidator::getVerifyErrors($data);

    //if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Parameters')
            ->set('json', 'validation', $errors);
    }

    //----------------------------//
    // 3. Prepare Data
    //get the auth detail
    $this->trigger('auth-detail', $request, $response);

    //if there's an error
    if ($response->isError()) {
        return;
    }

    //send mail
    $request->setSoftStage($response->getResults());

    //because there's no way the CLI queue would know the host
    $protocol = 'http';
    if ($request->getServer('SERVER_PORT') === 443) {
        $protocol = 'https';
    }

    $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
    $data = $request->getStage();

    //----------------------------//
    // 3. Process Data
    //try to queue, and if not
    if (!$this->package('global')->queue('auth-verify-mail', $data)) {
        //send mail manually
        $this->trigger('auth-verify-mail', $request, $response);
    }

    //return response format
    $response->setError(false);
});

/**
 * Auth Verify Mail Job (supporting job)
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-verify-mail', function ($request, $response) {
    $config = $this->package('global')->service('mail-main');

    if (!$config) {
        return;
    }

    //if it's not configured
    if ($config['user'] === '<EMAIL ADDRESS>'
        || $config['pass'] === '<EMAIL PASSWORD>'
    ) {
        return;
    }

    //form hash
    $authId = $request->getStage('auth_id');
    $authUpdated = $request->getStage('auth_updated');
    $hash = md5($authId.$authUpdated);

    //form link
    $host = $request->getStage('host');
    $link = $host . '/activate/' . $authId . '/' . $hash;

    //prepare data
    $from = [];
    $from[$config['user']] = $config['name'];

    $to = [];
    $to[$request->getStage('auth_slug')] = null;

    $subject = $this->package('global')->translate('Account Verification from Cradle!');
    $handlebars = $this->package('global')->handlebars();

    $contents = file_get_contents(__DIR__ . '/template/email/verify.txt');
    $template = $handlebars->compile($contents);
    $text = $template(['link' => $link]);

    $contents = file_get_contents(__DIR__ . '/template/email/verify.html');
    $template = $handlebars->compile($contents);
    $html = $template([
        'host' => $host,
        'link' => $link
    ]);

    //send mail
    $message = new Swift_Message($subject);
    $message->setFrom($from);
    $message->setTo($to);
    $message->setBody($html, 'text/html');
    $message->addPart($text, 'text/plain');

    $transport = Swift_SmtpTransport::newInstance();
    $transport->setHost($config['host']);
    $transport->setPort($config['port']);
    $transport->setEncryption($config['type']);
    $transport->setUsername($config['user']);
    $transport->setPassword($config['pass']);

    $swift = Swift_Mailer::newInstance($transport);
    $swift->send($message, $failures);
});

/**
 * Auth Import Job
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->on('auth-import', function ($request, $response) {
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
        $error = AuthValidator::getCreateErrors($row);

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
        if (isset($row['auth_created'])) {
            unset($row['auth_created']);
        }

        if (isset($row['auth_updated'])) {
            unset($row['auth_updated']);
        }

        if (isset($row['user_created'])) {
            unset($row['user_created']);
        }

        if (isset($row['user_updated'])) {
            unset($row['user_updated']);
        }

        $rowRequest = Request::i()
            ->setStage($row);

        $rowResponse = Response::i()->load();

        cradle()->trigger('auth-detail', $rowRequest, $rowResponse);

        if ($rowResponse->hasResults()) {
            // trigger single object update event
            cradle()->trigger('auth-update', $rowRequest, $rowResponse);

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
        cradle()->trigger('auth-create', $rowRequest, $rowResponse);

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
