<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Utility\File;

/**
 * Render the Signup Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/signup', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //add captcha
    cradle()->trigger('captcha-load', $request, $response);
    $data['captcha'] = $response->getResults('captcha');

    if ($response->isError()) {
        if ($response->getValidation('auth_slug')) {
            $message = $response->getValidation('auth_slug');
            $response->addValidation('profile_email', $message);
        }

        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-signup';
    $title = cradle('global')->translate('Sign Up');
    $body = cradle('/module/auth')->template('signup', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Render the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/login', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');


    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');

        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-login';
    $title = cradle('global')->translate('Log In');
    $body = cradle('/module/auth')->template('login', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Process the Logout
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/logout', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    $request->removeSession('me');

    //add a flash
    cradle('global')->flash('Log Out Successful', 'success');

    //redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    cradle('global')->redirect($redirect);
});

/**
 * Render the Account Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/account', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //Need to be logged in
    cradle('global')->requireLogin();

    //----------------------------//
    // 2. Prepare Data
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CDN
    $config = $this->package('global')->service('s3-main');
    $data['cdn_config'] = File::getS3Client($config);

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    //If no post
    if (!$request->hasPost('profile_name')) {
        //set default data
        $data['item'] = $request->getSession('me');
    }

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-account';
    $title = cradle('global')->translate('Account Settings');
    $body = cradle('/module/auth')->template('account', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Render the Forgot Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/forgot', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-forgot';
    $title = cradle('global')->translate('Forgot Password');
    $body = cradle('/module/auth')->template('forgot', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Render the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/recover/:auth_id/:hash', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    //get the detail
    cradle()->trigger('auth-detail', $request, $response);

    //form hash
    $authId = $response->getResults('auth_id');
    $authUpdated = $response->getResults('auth_updated');
    $hash = md5($authId.$authUpdated);

    //check the verification hash
    if ($hash !== $request->getStage('hash')) {
        cradle('global')->flash('Invalid verification. Try again.', 'error');
        return cradle('global')->redirect('/auth/verify');
    }

    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-recover';
    $title = cradle('global')->translate('Recover Password');
    $body = cradle('/module/auth')->template('recover', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Render the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/auth/verify', function ($request, $response) {
    //----------------------------//
    // 1. Security Checks
    //----------------------------//
    // 2. Prepare Data
    //Prepare body
    $data = ['item' => $request->getPost()];

    //add CSRF
    cradle()->trigger('csrf-load', $request, $response);
    $data['csrf'] = $response->getResults('csrf');

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'error');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    //Render body
    $class = 'page-auth-verify';
    $title = cradle('global')->translate('Verify Account');
    $body = cradle('/module/auth')->template('verify', $data);

    //Set Content
    $response
        ->setPage('title', $title)
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if ($request->getStage('render') === 'body') {
        return;
    }

    //Render blank page
    cradle()->trigger('render-www-blank', $request, $response);
});

/**
 * Process the Account Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/account', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = '/auth/account';
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //determine redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //----------------------------//
    // 2. Security Checks
    //need to be online
    cradle('global')->requireLogin();

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Prepare Data
    //set the auth_id and profile_id
    $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
    $request->setStage('profile_id', $request->getSession('me', 'profile_id'));
    $request->setStage('permission', $request->getSession('me', 'profile_id'));

    //remove password if empty
    if (!$request->getStage('auth_password')) {
        $request->removeStage('auth_password');
    }

    if (!$request->getStage('confirm')) {
        $request->removeStage('confirm');
    }

    //----------------------------//
    // 4. Process Request
    //trigger the job
    cradle()->trigger('auth-update', $request, $response);

    //----------------------------//
    // 5. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good
    //update the session
    cradle()->trigger('auth-detail', $request, $response);
    $request->setSession('me', $response->getResults());

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate('Update Successful');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process the Login Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/login', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = '/auth/login';
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //determine redirect
    $redirect = '/';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //----------------------------//
    // 2. Security Checks
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Request
    //call the job
    cradle()->trigger('auth-login', $request, $response);

    //----------------------------//
    // 5. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //it was good

    //store to session
    //TODO: Sessions for clusters
    $request->setSession('me', $response->getResults());

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate('Welcome!');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process the Forgot Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/forgot', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = '/auth/forgot';
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //determine redirect
    $redirect = '/auth/forgot';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //----------------------------//
    // 2. Security Checks
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Request
    //trigger the job
    cradle()->trigger('auth-forgot', $request, $response);

    //----------------------------//
    // 5. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //its good

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate('An email with recovery instructions will be sent in a few minutes.');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/recover/:auth_id/:hash', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = sprintf('/auth/recover/%s/%s', $authId, $hash);
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //determine redirect
    $redirect = '/auth/login';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //----------------------------//
    // 2. Security Checks
    //----------------------------//
    // 3. Prepare Data
    //get the detail
    cradle()->trigger('auth-detail', $request, $response);

    //form hash
    $authId = $response->getResults('auth_id');
    $authUpdated = $response->getResults('auth_updated');
    $hash = md5($authId.$authUpdated);

    //check the recovery hash
    if ($hash !== $request->getStage('hash')) {
        $message = cradle('global')->translate('This recovery page is expired. Please try again.');

        //if we dont want to redirect
        if ($redirect === 'false') {
            return $response->setError(true, $message);
        }

        cradle('global')->flash($message, 'error');
        return cradle('global')->redirect('/auth/forgot');
    }

    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 4. Process Request
    //trigger the job
    cradle()->trigger('auth-recover', $request, $response);

    //----------------------------//
    // 5. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate('Recovery Successful');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process the Signup Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/signup', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = '/auth/signup';
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //determine redirect
    $redirect = '/auth/login';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //----------------------------//
    // 2. Security Checks
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //captcha check
    cradle()->trigger('captcha-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 4. Process Request
    //trigger the job
    cradle()->trigger('auth-create', $request, $response);

    //----------------------------//
    // 5. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //add a flash
    $message = cradle('global')->translate('Sign Up Successful. Please check your email for verification process.');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});

/**
 * Process the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/auth/verify', function ($request, $response) {
    //----------------------------//
    // 1. Setup Overrides
    //determine route
    $route = '/auth/verify';
    if ($request->hasStage('route')) {
        $route = $request->getStage('route');
    }

    //----------------------------//
    // 2. Security Checks
    //csrf check
    cradle()->trigger('csrf-validate', $request, $response);

    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //----------------------------//
    // 3. Prepare Data
    //----------------------------//
    // 3. Process Request
    //trigger the job
    cradle()->trigger('auth-verify', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if ($response->isError()) {
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //determine redirect
    $redirect = '/auth/verify';
    if ($request->hasGet('redirect_uri')) {
        $redirect = $request->getGet('redirect_uri');
    }

    //if we dont want to redirect
    if ($redirect === 'false') {
        return;
    }

    //its good
    $message = cradle('global')->translate('An email with verification instructions will be sent in a few minutes.');
    cradle('global')->flash($message, 'success');
    cradle('global')->redirect($redirect);
});
