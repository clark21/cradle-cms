<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2016-2018 Acme Products Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

/**
 * Render the User Search Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/search', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    if(!$request->hasStage('range')) {
        $request->setStage('range', 50);
    }

    //filter possible filter options
    //we do this to prevent SQL injections
    if(is_array($request->getStage('filter'))) {
        $filterable = [
            'user_active'
        ];

        foreach($request->getStage('filter') as $key => $value) {
            if(!in_array($key, $filterable)) {
                $request->removeStage('filter', $key);
            }
        }
    }

    //trigger job
    cradle()->trigger('user-search', $request, $response);

    //if we only want the raw data
    if($request->getStage('render') === 'false') {
        return;
    }

    $data = array_merge($request->getStage(), $response->getResults());

    //----------------------------//
    // 3. Render Template
    $class = 'page-admin-user-search page-admin';
    $data['title'] = cradle('global')->translate('Users');
    $body = cradle('/app/admin')->template('user/search', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //if we only want the body
    if($request->getStage('render') === 'body') {
        return;
    }

    //render page
    cradle()->trigger('render-admin-page', $request, $response);
});

/**
 * Render the User Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/create', function($request, $response) {
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
    $class = 'page-developer-user-create page-admin';
    $data['title'] = cradle('global')->translate('Create User');
    $body = cradle('/app/admin')->template('user/form', $data);

    //set content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //render page
}, 'render-admin-page');

/**
 * Render the User Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/update/:user_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    $data = ['item' => $request->getPost()];

    //if no item
    if(empty($data['item'])) {
        cradle()->trigger('user-detail', $request, $response);

        //can we update ?
        if($response->isError()) {
            //add a flash
            cradle('global')->flash($response->getMessage(), 'danger');
            return cradle('global')->redirect('/admin/user/search');
        }

        $data['item'] = $response->getResults();
    }

    if($response->isError()) {
        $response->setFlash($response->getMessage(), 'danger');
        $data['errors'] = $response->getValidation();
    }

    //----------------------------//
    // 3. Render Template
    $class = 'page-developer-user-update page-admin';
    $data['title'] = cradle('global')->translate('Updating User');
    $body = cradle('/app/admin')->template('user/form', $data);

    //Set Content
    $response
        ->setPage('title', $data['title'])
        ->setPage('class', $class)
        ->setContent($body);

    //Render page
}, 'render-admin-page');

/**
 * Process the User Create Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/create', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //user_slug is disallowed
    $request->removeStage('user_slug');

    //if user_meta has no value make it null
    if ($request->hasStage('user_meta') && !$request->getStage('user_meta')) {
        $request->setStage('user_meta', null);
    }

    //if user_files has no value make it null
    if ($request->hasStage('user_files') && !$request->getStage('user_files')) {
        $request->setStage('user_files', null);
    }

    //user_type is disallowed
    $request->removeStage('user_type');

    //user_flag is disallowed
    $request->removeStage('user_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-create', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        return cradle()->triggerRoute('get', '/admin/user/create', $request, $response);
    }

    //record logs
    cradle()->log('User '. ucfirst($request->getStage('user_slug')) . ' is created', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('User was Created', 'success');

    //redirect
    cradle('global')->redirect('/admin/user/search');
});

/**
 * Process the User Update Page
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->post('/admin/user/update/:user_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data

    //user_slug is disallowed
    $request->removeStage('user_slug');

    //if user_meta has no value make it null
    if ($request->hasStage('user_meta') && !$request->getStage('user_meta')) {
        $request->setStage('user_meta', null);
    }

    //if user_files has no value make it null
    if ($request->hasStage('user_files') && !$request->getStage('user_files')) {
        $request->setStage('user_files', null);
    }

    //user_type is disallowed
    $request->removeStage('user_type');

    //user_flag is disallowed
    $request->removeStage('user_flag');

    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-update', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        $route = '/admin/user/update/' . $request->getStage('user_id');
        return cradle()->triggerRoute('get', $route, $request, $response);
    }

    //record logs
    cradle()->log('User #'. ucfirst($request->getStage('user_id')) . ' is updated', $request, $response);

    //it was good
    //add a flash
    cradle('global')->flash('User was Updated', 'success');

    //redirect
    cradle('global')->redirect('/admin/user/search');
});

/**
 * Process the User Remove
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/remove/:user_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-remove', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('User was Removed');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log('User #'. ucfirst($request->getStage('user_id')) . ' removed', $request, $response);
    }


    cradle('global')->redirect('/admin/user/search');
});

/**
 * Process the User Restore
 *
 * @param Request $request
 * @param Response $response
 */
$cradle->get('/admin/user/restore/:user_id', function($request, $response) {
    //----------------------------//
    // 1. Route Permissions
    //only for admin
    cradle('global')->requireLogin('admin');

    //----------------------------//
    // 2. Prepare Data
    // no data to preapre
    //----------------------------//
    // 3. Process Request
    cradle()->trigger('user-restore', $request, $response);

    //----------------------------//
    // 4. Interpret Results
    if($response->isError()) {
        //add a flash
        cradle('global')->flash($response->getMessage(), 'danger');
    } else {
        //add a flash
        $message = cradle('global')->translate('User was Restored');
        cradle('global')->flash($message, 'success');

        //record logs
        cradle()->log('User #'. ucfirst($request->getStage('user_id')) . ' restored', $request, $response);
    }

    cradle('global')->redirect('/admin/user/search');
});
