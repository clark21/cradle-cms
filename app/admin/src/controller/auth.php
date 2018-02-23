<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the Auth Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    //record logs
    cradle()->log('View auth listing',
        $request,
        $response
    );

    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
            'auth_active'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('auth-search', $request, $response);
    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-auth-search page-admin';
    $data['title'] = cradle('global')->translate('Authentications');
    $body = cradle('/app/admin')->template('auth/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Auth Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    if ($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-auth-create page-admin';
    $data['title'] = cradle('global')->translate('Create Authentication');
    $body = cradle('/app/admin')->template('auth/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the Auth Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/update/:auth_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('auth-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/auth/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-auth-update page-admin';
    $data['title'] = cradle('global')->translate('Updating Authentication');
    $body = cradle('/app/admin')->template('auth/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the Auth Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if auth_password has no value make it null
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->setStage('auth_password', null);
    }

    //auth_type is disallowed
    $request->removeStage('auth_type');

    //auth_flag is disallowed
    $request->removeStage('auth_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/auth/create', $request, $response);
    }

    //record logs
    cradle()->log('New Authentication was created.',
        $request,
        $response
    );

    //it was good
    //add a flash
    cradle('global')->flash('Authentication was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/auth/search');
});

/**
 * Process the Auth Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/auth/update/:auth_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //if auth_password has no value make it null
    if ($request->hasStage('auth_password') && !$request->getStage('auth_password')) {
        $request->setStage('auth_password', null);
    }

    //auth_type is disallowed
    $request->removeStage('auth_type');

    //auth_flag is disallowed
    $request->removeStage('auth_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/auth/update/' . $request->getStage('auth_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log('Authentication #'. $request->getStage('auth_id') . ' was updated.',
        $request,
        $response
    );

    //it was good
    //add a flash
    cradle('global')->flash('Authentication was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/auth/search');
});

/**
 * Process the Auth Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/remove/:auth_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Authentication was Removed');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log('Authentication #'. $request->getStage('auth_id') . ' was removed.',
            $request,
            $response
        );
    }


    cradle('global')->redirect('/admin/auth/search');
});

/**
 * Process the Auth Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/auth/restore/:auth_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('auth-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('Authentication was Restored');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log('Authentication #'. $request->getStage('auth_id') . ' was Restored.',
            $request,
            $response
        );
    }


    cradle('global')->redirect('/admin/auth/search');
});

