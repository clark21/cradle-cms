<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\System\Service as SystemService;

/**
 * Process Rest Auth and Token Issuance
 * 
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/rest/system/auth', function ($request, $response) {
    // get errors
    $errors = [];

    // validate auth slug
    if (!$request->getStage('auth_slug')) {
        $errors['auth_slug'] = 'Slug is required';
    }

    // validate auth password
    if (!$request->getStage('auth_password')) {
        $errors['auth_password'] = 'Password is required';
    }

    // validate auth type
    if (!$request->getStage('auth_type')) {
        $errors['auth_type'] = 'Type is required';
    }

    // if there are errors
    if (!empty($errors)) {
        return $response
            ->setError(true, 'Invalid Request')
            ->set('json', 'validation', $errors);
    }

    // get the auth detail
    $auth = SystemService::get('sql')
        ->getResource()
        ->search('auth')
        ->filterByAuthSlug($request->getStage('auth_slug'))
        ->filterByAuthPassword(md5($request->getStage('auth_password')))
        ->filterByAuthType($request->getStage('auth_type'))
        ->filterByAuthActive(1)
        ->getRow();

    // if auth does not exists
    if (!$auth) {
        return $response
            ->setError(true, 'Invalid Request')
            ->set('json', 'validation', [
                'auth_slug' => 'Invalid slug or password',
                'auth_password' => 'Invalid slug or password'
            ]);
    }

    // create the signing data
    $data = [
        'auth_id'       => $auth['auth_id'],
        'auth_slug'     => $auth['auth_slug'],
        'auth_password' => $auth['auth_password'],
        'auth_type'     => $auth['auth_type'],
        'auth_active'   => $auth['auth_active']
    ];

    // set data to stage
    $request->setStage(['data' => $data]);

    // sign the token data
    cradle()->trigger('auth-jwt-sign', $request, $response);

    // is there an error?
    if ($response->isError()) {
        return;
    }
});